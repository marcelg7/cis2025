<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\ActivityType;
use App\Models\CommitmentPeriod;
use App\Models\Contract;
use App\Models\ContractAddOn;
use App\Models\ContractOneTimeFee;
use App\Models\BellDevice;
use App\Models\BellPricing;
use App\Models\BellDroPricing;
use App\Models\Customer;
use App\Models\User;
use App\Models\RatePlan;
use App\Models\MobileInternetPlan;
use App\Models\PlanAddOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use setasign\Fpdi\Fpdi;
use Carbon\Carbon;
use HTMLPurifier; // Import the Purifier class
use HTMLPurifier_Config; // For configuration
use App\Services\VaultFtpService;
use App\Services\ContractPdfService;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contract::with('subscriber.mobilityAccount.ivueAccount.customer', 'updatedBy');
        if ($request->filled('customer')) {
            $query->whereHas('subscriber.mobilityAccount.ivueAccount.customer', function ($q) use ($request) {
                $q->where('display_name', 'LIKE', '%' . $request->input('customer') . '%');
            });
        }
        if ($request->filled('device')) {
            $query->where(function ($q) use ($request) {
                $q->where('manufacturer', 'LIKE', '%' . $request->input('device') . '%')
                  ->orWhere('model', 'LIKE', '%' . $request->input('device') . '%')
                  ->orWhere('version', 'LIKE', '%' . $request->input('device') . '%')
                  ->orWhere('device_storage', 'LIKE', '%' . $request->input('device') . '%')
                  ->orWhere('extra_info', 'LIKE', '%' . $request->input('device') . '%');
            });
        }
       
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', $request->input('start_date'));
        }
        $contracts = $query->latest()->paginate(10)->appends($request->query());
        return view('contracts.index', compact('contracts'));
    }
	public function create($subscriberId = null)
	{
		$customers = Customer::orderBy('last_name')->get();
		$users = User::orderBy('name')->get();
		$activityTypes = ActivityType::orderBy('name')->get();
		$commitmentPeriods = CommitmentPeriod::orderBy('name')->get();
		$bellDevices = BellDevice::orderBy('model')->get();
		
		// Add cellular pricing data
		$ratePlans = RatePlan::current()->active()->orderBy('plan_type')->orderBy('tier')->orderBy('base_price')->get();
		$mobileInternetPlans = MobileInternetPlan::current()->active()->orderBy('monthly_rate')->get();
		$planAddOns = PlanAddOn::current()->active()->orderBy('category')->orderBy('add_on_name')->get();
		
		// Get subscriber if ID provided
		$subscriber = null;
		if ($subscriberId) {
			$subscriber = Subscriber::findOrFail($subscriberId);
		}
		
		$tiers = $ratePlans->pluck('tier')->unique()->sort()->values()->toArray();
		
		// NEW: Get available tiers for each device
		$deviceTiers = [];
		foreach ($bellDevices as $device) {
			$availableTiers = [];
			
			// Check SmartPay pricing
			if ($device->has_smartpay) {
				$smartpayTiers = \App\Models\BellPricing::where('bell_device_id', $device->id)
					->pluck('tier')
					->unique()
					->toArray();
				$availableTiers = array_merge($availableTiers, $smartpayTiers);
			}
			
			// Check DRO pricing
			if ($device->has_dro) {
				$droTiers = \App\Models\BellDroPricing::where('bell_device_id', $device->id)
					->pluck('tier')
					->unique()
					->toArray();
				$availableTiers = array_merge($availableTiers, $droTiers);
			}
			
			$deviceTiers[$device->id] = array_unique($availableTiers);
		}
		
		// Define default first bill date
		$defaultFirstBillDate = Carbon::now()->addMonth()->startOfMonth();
	   
		return view('contracts.create', compact(
			'customers',
			'users',
			'activityTypes',
			'commitmentPeriods',
			'bellDevices',
			'ratePlans',
			'mobileInternetPlans',
			'planAddOns',
			'subscriber',
			'tiers',
			'defaultFirstBillDate',
			'deviceTiers'  // NEW: Add this
		));
	}
    public function store(Request $request, $subscriberId)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'activity_type_id' => 'required|exists:activity_types,id',
            'contract_date' => 'required|date',
            'location' => 'required|in:zurich,exeter,grand_bend',
            'bell_device_id' => 'nullable|exists:bell_devices,id',
            'bell_pricing_type' => 'nullable|in:smartpay,dro,byod', // Added 'byod'
            'bell_tier' => 'nullable|in:Ultra,Max,Select,Lite',
            'bell_retail_price' => 'nullable|numeric|min:0',
            'bell_monthly_device_cost' => 'nullable|numeric|min:0',
            'bell_plan_cost' => 'nullable|numeric|min:0',
            'bell_dro_amount' => 'nullable|numeric|min:0',
            'bell_plan_plus_device' => 'nullable|numeric|min:0',
            'agreement_credit_amount' => 'required|numeric|min:0',
            'required_upfront_payment' => 'required|numeric|min:0',
            'optional_down_payment' => 'nullable|numeric|min:0',
            'deferred_payment_amount' => 'nullable|numeric|min:0',
            'commitment_period_id' => 'required|exists:commitment_periods,id',
            'first_bill_date' => 'required|date',
            'add_ons' => 'nullable|array',
            'add_ons.*.name' => 'required_with:add_ons.*.code|required_with:add_ons.*.cost|string|max:100',
            'add_ons.*.code' => 'required_with:add_ons.*.name|required_with:add_ons.*.cost|string|max:50',
            'add_ons.*.cost' => 'required_with:add_ons.*.name|required_with:add_ons.*.code|numeric',
            'one_time_fees' => 'nullable|array',
            'one_time_fees.*.name' => 'required_with:one_time_fees.*.cost|string|max:100',
            'one_time_fees.*.cost' => 'required_with:one_time_fees.*.name|numeric',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'mobile_internet_plan_id' => 'nullable|exists:mobile_internet_plans,id',
            'rate_plan_price' => 'nullable|numeric|min:0',
            'mobile_internet_price' => 'nullable|numeric|min:0',
            'selected_tier' => 'nullable|string|in:Lite,Select,Max,Ultra',
            'custom_device_name' => 'nullable|string|max:255',
        ]);
        $subscriber = Subscriber::with('mobilityAccount.ivueAccount.customer')->findOrFail($subscriberId);
        $price = 0;
        if ($request->filled('bell_device_id')) {
            $price = $request->bell_retail_price ?? 0;
        }
        // If BYOD plan, null Bell fields
        if ($request->rate_plan_id) {
            $ratePlan = RatePlan::find($request->rate_plan_id);
            if ($ratePlan && $ratePlan->plan_type === 'byod') {
                $request->merge([
                    'bell_device_id' => null,
                    'bell_pricing_type' => 'byod',
                    'bell_retail_price' => 0,
                    'bell_monthly_device_cost' => 0,
                    'bell_dro_amount' => 0,
                    'bell_plan_plus_device' => 0,
                ]);
                $price = 0;
            }
        }
        $contract = Contract::create([
            'subscriber_id' => $subscriberId,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'activity_type_id' => $request->activity_type_id,
            'contract_date' => $request->contract_date,
            'location' => $request->location,
            'bell_device_id' => $request->bell_device_id,
            'bell_pricing_type' => $request->bell_pricing_type,
            'bell_tier' => $request->bell_tier,
            'bell_retail_price' => $request->bell_retail_price,
            'bell_monthly_device_cost' => $request->bell_monthly_device_cost,
            'bell_plan_cost' => $request->bell_plan_cost,
            'bell_dro_amount' => $request->bell_dro_amount,
            'bell_plan_plus_device' => $request->bell_plan_plus_device,
            'manufacturer' => null,
            'model' => null,
            'version' => null,
            'device_storage' => null,
            'extra_info' => null,
            'device_price' => $price,
            'agreement_credit_amount' => $request->agreement_credit_amount,
            'required_upfront_payment' => $request->required_upfront_payment,
            'optional_down_payment' => $request->optional_down_payment,
            'deferred_payment_amount' => $request->deferred_payment_amount,
            'commitment_period_id' => $request->commitment_period_id,
            'first_bill_date' => $request->first_bill_date,
            'status' => 'draft',
            'updated_by' => auth()->id(),
            'rate_plan_id' => $request->rate_plan_id,
            'mobile_internet_plan_id' => $request->mobile_internet_plan_id,
            'rate_plan_price' => $request->rate_plan_price,
            'mobile_internet_price' => $request->mobile_internet_price,
            'selected_tier' => $request->selected_tier,
            'custom_device_name' => $request->custom_device_name,
        ]);
       
        // Set financing status based on whether financing is required
        if ($contract->requiresFinancing()) {
            $contract->update(['financing_status' => 'pending']);
            Log::info('Contract requires financing form', ['contract_id' => $contract->id]);
        } else {
            $contract->update(['financing_status' => 'not_required']);
            Log::info('Contract does not require financing form', ['contract_id' => $contract->id]);
        }
        if ($request->has('add_ons')) {
            foreach ($request->add_ons as $addOn) {
                ContractAddOn::create([
                    'contract_id' => $contract->id,
                    'name' => $addOn['name'],
                    'code' => $addOn['code'],
                    'cost' => $addOn['cost'],
                ]);
            }
        }
        if ($request->has('one_time_fees')) {
            foreach ($request->one_time_fees as $fee) {
                ContractOneTimeFee::create([
                    'contract_id' => $contract->id,
                    'name' => $fee['name'],
                    'cost' => $fee['cost'],
                ]);
            }
        }
        $contract->load('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer', 'activityType', 'commitmentPeriod', 'ratePlan', 'mobileInternetPlan', 'bellDevice');
        // Financial calculations (matching view method)
        $devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
        $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
        $monthlyDevicePayment = ($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0)) / 24;
        $earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
        $monthlyReduction = $monthlyDevicePayment;
        $totalAddOnCost = $contract->addOns->sum('cost') ?? 0;
        $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost') ?? 0;
        $totalCost = ($totalAddOnCost + ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) + $monthlyDevicePayment) * 24 + $totalOneTimeFeeCost;
        Log::debug('Calculated financials for store', [
            'contract_id' => $contract->id,
            'devicePrice' => $devicePrice,
            'deviceAmount' => $deviceAmount,
            'totalFinancedAmount' => $totalFinancedAmount,
            'monthlyDevicePayment' => $monthlyDevicePayment,
            'earlyCancellationFee' => $earlyCancellationFee,
            'monthlyReduction' => $monthlyReduction,
            'totalAddOnCost' => $totalAddOnCost,
            'totalOneTimeFeeCost' => $totalOneTimeFeeCost,
            'totalCost' => $totalCost
        ]);
        // Sanitize ratePlan features and mobileInternetPlan description
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', 'p,strong,em,ul,ol,li,a[href|title],br,div[class],span[class],table,tr,td,th,hr');
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.Linkify', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true]);
        $config->set('Cache.SerializerPath', storage_path('htmlpurifier')); // Set custom cache directory
        $purifier = new HTMLPurifier($config);
        Log::debug('HTMLPurifier initialized for store');
        if ($contract->ratePlan && $contract->ratePlan->features) {
            $originalFeatures = $contract->ratePlan->features;
            $contract->ratePlan->features = $purifier->purify($contract->ratePlan->features);
            Log::debug('Sanitized ratePlan features', [
                'contract_id' => $contract->id,
                'original' => $originalFeatures,
                'sanitized' => $contract->ratePlan->features
            ]);
        } else {
            Log::debug('No ratePlan features to sanitize in store', [
                'contract_id' => $contract->id,
                'hasRatePlan' => !empty($contract->ratePlan),
                'hasFeatures' => !empty($contract->ratePlan->features)
            ]);
        }
        if ($contract->mobileInternetPlan && $contract->mobileInternetPlan->description) {
            $originalDescription = $contract->mobileInternetPlan->description;
            $contract->mobileInternetPlan->description = $purifier->purify($contract->mobileInternetPlan->description);
            Log::debug('Sanitized mobileInternetPlan description', [
                'contract_id' => $contract->id,
                'original' => $originalDescription,
                'sanitized' => $contract->mobileInternetPlan->description
            ]);
        } else {
            Log::debug('No mobileInternetPlan description to sanitize in store', [
                'contract_id' => $contract->id
            ]);
        }
        $pdf = Pdf::loadView('contracts.view', compact(
            'contract',
            'totalAddOnCost',
            'totalOneTimeFeeCost',
            'totalCost',
            'devicePrice',
            'deviceAmount',
            'totalFinancedAmount',
            'monthlyDevicePayment',
            'earlyCancellationFee',
            'monthlyReduction'
        ))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'memory_limit' => '512M',
                'chroot' => base_path(),
                'isPhpEnabled' => true,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
            ]);
        $pdfPath = "contracts/contract_{$contract->id}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());
        $contract->update(['pdf_path' => $pdfPath]);
        $customerId = $contract->subscriber->mobilityAccount->ivueAccount->customer_id;
        return redirect()->route('customers.show', $customerId)->with('success', 'Contract created successfully.');
    }
	
	public function edit(Contract $contract)
	{
		$customers = Customer::orderBy('last_name')->get();
		$users = User::orderBy('name')->get();
		$activityTypes = ActivityType::orderBy('name')->get();
		$commitmentPeriods = CommitmentPeriod::orderBy('name')->get();
		$bellDevices = BellDevice::orderBy('model')->get();
		   
		// Add cellular pricing data
		$ratePlans = RatePlan::current()->active()->orderBy('plan_type')->orderBy('tier')->orderBy('base_price')->get();
		$mobileInternetPlans = MobileInternetPlan::current()->active()->orderBy('monthly_rate')->get();
		$planAddOns = PlanAddOn::current()->active()->orderBy('category')->orderBy('add_on_name')->get();
	   
		$tiers = $ratePlans->pluck('tier')->unique()->sort()->values()->toArray();
	   
		// NEW: Get available tiers for each device
		$deviceTiers = [];
		foreach ($bellDevices as $device) {
			$availableTiers = [];
			
			// Check SmartPay pricing
			if ($device->has_smartpay) {
				$smartpayTiers = \App\Models\BellPricing::where('bell_device_id', $device->id)
					->pluck('tier')
					->unique()
					->toArray();
				$availableTiers = array_merge($availableTiers, $smartpayTiers);
			}
			
			// Check DRO pricing
			if ($device->has_dro) {
				$droTiers = \App\Models\BellDroPricing::where('bell_device_id', $device->id)
					->pluck('tier')
					->unique()
					->toArray();
				$availableTiers = array_merge($availableTiers, $droTiers);
			}
			
			$deviceTiers[$device->id] = array_unique($availableTiers);
		}
	   
		return view('contracts.edit', compact(
			'contract',
			'customers',
			'users',
			'activityTypes',
			'commitmentPeriods',
			'bellDevices',
			'ratePlans',
			'mobileInternetPlans',
			'planAddOns',
			'tiers',
			'deviceTiers'  // NEW: Pass device tier availability
		));
	}
	   
	public function update(Request $request, Contract $contract)
	{
		$validated = $request->validate([
			'start_date' => 'required|date',
			'end_date' => 'required|date|after:start_date',
			'activity_type_id' => 'required|exists:activity_types,id',
			'contract_date' => 'required|date',
			'location' => 'required|in:zurich,exeter,grand_bend',
			'bell_device_id' => 'nullable|exists:bell_devices,id',
			'bell_pricing_type' => 'nullable|in:smartpay,dro,byod',
			'bell_tier' => 'nullable|in:Ultra,Max,Select,Lite',
			'bell_retail_price' => 'nullable|numeric|min:0',
			'bell_monthly_device_cost' => 'nullable|numeric|min:0',
			'bell_plan_cost' => 'nullable|numeric|min:0',
			'bell_dro_amount' => 'nullable|numeric|min:0',
			'bell_plan_plus_device' => 'nullable|numeric|min:0',
			'agreement_credit_amount' => 'nullable|numeric|min:0',
			'required_upfront_payment' => 'nullable|numeric|min:0',
			'optional_down_payment' => 'nullable|numeric|min:0',
			'deferred_payment_amount' => 'nullable|numeric|min:0',
			'commitment_period_id' => 'required|exists:commitment_periods,id',
			'first_bill_date' => 'required|date|after_or_equal:start_date',
			'add_ons' => 'nullable|array',
			'add_ons.*.name' => 'required|string|max:255',
			'add_ons.*.code' => 'required|string|max:255',
			'add_ons.*.cost' => 'required|numeric|min:0',
			'one_time_fees' => 'nullable|array',
			'one_time_fees.*.name' => 'required|string|max:255',
			'one_time_fees.*.cost' => 'required|numeric|min:0',
			'rate_plan_id' => 'nullable|exists:rate_plans,id',
			'mobile_internet_plan_id' => 'nullable|exists:mobile_internet_plans,id',
			'rate_plan_price' => 'nullable|numeric|min:0',
			'mobile_internet_price' => 'nullable|numeric|min:0',
			'selected_tier' => 'nullable|string|in:Lite,Select,Max,Ultra',
			'custom_device_name' => 'nullable|string|max:255',
		]);

		$price = 0;
		$customDeviceName = null; // NEW: Initialize custom device name
		
		// If Bell pricing is used, override device_price with bell_retail_price
		if ($request->filled('bell_device_id')) {
			$price = $request->bell_retail_price ?? 0;
		}

		// If BYOD plan, null Bell fields and keep custom device name
		if ($request->rate_plan_id) {
			$ratePlan = RatePlan::find($request->rate_plan_id);
			if ($ratePlan && $ratePlan->plan_type === 'byod') {
				$request->merge([
					'bell_device_id' => null,
					'bell_pricing_type' => 'byod',
					'bell_retail_price' => 0,
					'bell_monthly_device_cost' => 0,
					'bell_dro_amount' => 0,
					'bell_plan_plus_device' => 0,
				]);
				$price = 0;
				$customDeviceName = $request->custom_device_name; // Keep custom device for BYOD
			} else {
				// NEW: For non-BYOD plans, clear custom device name
				$customDeviceName = null;
			}
		}

		$contract->update([
			'start_date' => $request->start_date,
			'end_date' => $request->end_date,
			'activity_type_id' => $request->activity_type_id,
			'contract_date' => $request->contract_date,
			'location' => $request->location,
			'bell_device_id' => $request->bell_device_id,
			'bell_pricing_type' => $request->bell_pricing_type,
			'bell_tier' => $request->bell_tier,
			'bell_retail_price' => $request->bell_retail_price,
			'bell_monthly_device_cost' => $request->bell_monthly_device_cost,
			'bell_plan_cost' => $request->bell_plan_cost,
			'bell_dro_amount' => $request->bell_dro_amount,
			'bell_plan_plus_device' => $request->bell_plan_plus_device,
			'manufacturer' => null,
			'model' => null,
			'version' => null,
			'device_storage' => null,
			'extra_info' => null,
			'device_price' => $price,
			'agreement_credit_amount' => $request->agreement_credit_amount,
			'required_upfront_payment' => $request->required_upfront_payment,
			'optional_down_payment' => $request->optional_down_payment,
			'deferred_payment_amount' => $request->deferred_payment_amount,
			'commitment_period_id' => $request->commitment_period_id,
			'first_bill_date' => $request->first_bill_date,
			'updated_by' => auth()->id(),
			'rate_plan_id' => $request->rate_plan_id,
			'mobile_internet_plan_id' => $request->mobile_internet_plan_id,
			'rate_plan_price' => $request->rate_plan_price,
			'mobile_internet_price' => $request->mobile_internet_price,
			'selected_tier' => $request->selected_tier,
			'custom_device_name' => null, // no longer used
		]);
       
        // Update financing status based on whether financing is required
        if ($contract->requiresFinancing()) {
            if ($contract->financing_status === 'not_required') {
                $contract->update(['financing_status' => 'pending']);
                Log::info('Contract now requires financing form', ['contract_id' => $contract->id]);
            }
        } else {
            if ($contract->financing_status !== 'not_required') {
                $contract->update(['financing_status' => 'not_required']);
                Log::info('Contract no longer requires financing form', ['contract_id' => $contract->id]);
            }
        }
       
        // Update add-ons
        $contract->addOns()->delete();
        if ($request->add_ons) {
            foreach ($request->add_ons as $addOn) {
                ContractAddOn::create([
                    'contract_id' => $contract->id,
                    'name' => $addOn['name'],
                    'code' => $addOn['code'],
                    'cost' => $addOn['cost'],
                ]);
            }
        }
        // Update one-time fees
        $contract->oneTimeFees()->delete();
        if ($request->one_time_fees) {
            foreach ($request->one_time_fees as $fee) {
                ContractOneTimeFee::create([
                    'contract_id' => $contract->id,
                    'name' => $fee['name'],
                    'cost' => $fee['cost'],
                ]);
            }
        }
        return redirect()->route('contracts.view', $contract->id)->with('success', 'Contract updated successfully.');
    }
    public function sign($id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        Log::debug('Contract status in sign method', ['contract_id' => $id, 'status' => $contract->status, 'fresh' => $contract->freshTimestamp()]);
        if ($contract->status !== 'draft') {
            Log::warning('Contract cannot be signed due to status', ['contract_id' => $id, 'status' => $contract->status]);
            return redirect()->route('contracts.view', $id)->with('error', 'Contract cannot be signed.');
        }
        return view('contracts.sign', compact('contract'));
    }
    public function storeSignature(Request $request, $id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        Log::debug('Contract status before signature update', ['contract_id' => $id, 'status' => $contract->status]);
        if ($contract->status !== 'draft') {
            Log::warning('Contract cannot be signed due to status in storeSignature', ['contract_id' => $id, 'status' => $contract->status]);
            return redirect()->route('contracts.view', $id)->with('error', 'Contract cannot be signed.');
        }
        $request->validate([
            'signature' => 'required|string|regex:/^data:image\/png;base64,/',
        ], [
            'signature.required' => 'Please provide a signature.',
            'signature.regex' => 'Invalid signature format.',
        ]);
        try {
            $signature = $request->signature;
            Log::info('Received signature data', ['contract_id' => $id, 'signature' => substr($signature, 0, 50) . '...']);
            $signaturePath = "signatures/contract_{$contract->id}.png";
            $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature));
            if ($signatureData === false) {
                Log::error('Failed to decode signature data', ['contract_id' => $id]);
                return redirect()->back()->with('error', 'Failed to process signature.');
            }
            $stored = Storage::disk('public')->put($signaturePath, $signatureData);
            if (!$stored) {
                Log::error('Failed to store signature file', ['contract_id' => $id, 'path' => $signaturePath]);
                return redirect()->back()->with('error', 'Failed to save signature file.');
            }
            Log::info('Signature file stored', ['contract_id' => $id, 'path' => $signaturePath]);
            $contract->update([
                'signature_path' => 'storage/' . $signaturePath,
                'status' => 'signed',
                'updated_by' => auth()->id(),
            ]);
            $contract->refresh();
            Log::info('Contract status after update', ['contract_id' => $id, 'status' => $contract->status]);
            $contract->load('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer', 'activityType', 'commitmentPeriod');
            $devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
            $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
            $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
            $monthlyDevicePayment = ($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0)) / 24;
            $earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
            $monthlyReduction = $monthlyDevicePayment;
            $totalAddOnCost = $contract->addOns->sum('cost');
            $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost');
            $totalFinancingCost = ($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0) + ($contract->deferred_payment_amount ?? 0);
            $totalCost = ($contract->device_price ?? 0) + $totalAddOnCost + $totalOneTimeFeeCost + $totalFinancingCost;
            Log::debug('Generating PDF for contract', ['contract_id' => $contract->id, 'sections' => ['Header', 'Your Information', 'Device Details', 'Return Policy', 'Rate Plan Details', 'Minimum Monthly Charge', 'Total Monthly Charges', 'Add-ons', 'One-Time Fees', 'One-Time Charges', 'Total Cost', 'Signature']]);
            $pdf = Pdf::loadView('contracts.view', compact(
                'contract',
                'totalAddOnCost',
                'totalOneTimeFeeCost',
                'totalFinancingCost',
                'totalCost',
                'devicePrice',
                'deviceAmount',
                'totalFinancedAmount',
                'monthlyDevicePayment',
                'earlyCancellationFee',
                'monthlyReduction'
            ))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'dpi' => 150,
                    'defaultFont' => 'sans-serif',
                    'memory_limit' => '512M',
                    'chroot' => base_path(),
                    'isPhpEnabled' => true,
                    'margin_top' => 10,
                    'margin_bottom' => 10,
                    'margin_left' => 10,
                    'margin_right' => 10,
                ]);
            $pdfPath = "contracts/contract_{$contract->id}.pdf";
            Storage::disk('public')->put($pdfPath, $pdf->output());
            $contract->update(['pdf_path' => $pdfPath]);
            Log::info('PDF regenerated with signature', ['contract_id' => $id, 'pdf_path' => $pdfPath]);
            // Clear any previous error messages and redirect with success
            return redirect()->route('contracts.view', $id)->with('success', 'Contract signed successfully');
        } catch (\Exception $e) {
            Log::error('Error in storeSignature', ['contract_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save signature: ' . $e->getMessage());
        }
    }
   
    public function finalize($id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        if ($contract->status !== 'signed') {
            return redirect()->route('contracts.view', $id)->with('error', 'Contract must be signed before finalizing.');
        }
        $contract->update([
            'status' => 'finalized',
            'updated_by' => auth()->id(),
        ]);
        $contract->load('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer', 'activityType', 'commitmentPeriod', 'ratePlan', 'mobileInternetPlan', 'bellDevice');
        // Financial calculations
        $devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
        $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
        $monthlyDevicePayment = ($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0)) / 24;
        $earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
        $monthlyReduction = $monthlyDevicePayment;
        $totalAddOnCost = $contract->addOns->sum('cost') ?? 0;
        $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost') ?? 0;
        $totalCost = ($totalAddOnCost + ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) + $monthlyDevicePayment) * 24 + $totalOneTimeFeeCost;
        Log::debug('Calculated financials for finalize', [
            'contract_id' => $contract->id,
            'devicePrice' => $devicePrice,
            'deviceAmount' => $deviceAmount,
            'totalFinancedAmount' => $totalFinancedAmount,
            'monthlyDevicePayment' => $monthlyDevicePayment,
            'earlyCancellationFee' => $earlyCancellationFee,
            'monthlyReduction' => $monthlyReduction,
            'totalAddOnCost' => $totalAddOnCost,
            'totalOneTimeFeeCost' => $totalOneTimeFeeCost,
            'totalCost' => $totalCost
        ]);
        // Sanitize ratePlan features and mobileInternetPlan description
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', 'p,strong,em,ul,ol,li,a[href|title],br,div[class],span[class],table,tr,td,th,hr');
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.Linkify', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true]);
        $config->set('Cache.SerializerPath', storage_path('htmlpurifier')); // Set custom cache directory
        $purifier = new HTMLPurifier($config);
        if ($contract->ratePlan && $contract->ratePlan->features) {
            $contract->ratePlan->features = $purifier->purify($contract->ratePlan->features);
        }
        if ($contract->mobileInternetPlan && $contract->mobileInternetPlan->description) {
            $contract->mobileInternetPlan->description = $purifier->purify($contract->mobileInternetPlan->description);
        }
        $pdf = Pdf::loadView('contracts.view', compact(
            'contract',
            'totalAddOnCost',
            'totalOneTimeFeeCost',
            'totalCost',
            'devicePrice',
            'deviceAmount',
            'totalFinancedAmount',
            'monthlyDevicePayment',
            'earlyCancellationFee',
            'monthlyReduction'
        ))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'memory_limit' => '512M',
                'chroot' => base_path(),
                'isPhpEnabled' => true,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
            ]);
        $pdfPath = "contracts/contract_{$contract->id}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());
        $contract->update(['pdf_path' => $pdfPath]);
        Log::info('PDF generated for finalized contract', ['contract_id' => $id, 'pdf_path' => $pdfPath]);
       // **FTP Upload to Vault**
        $ftpService = new VaultFtpService();
        $remoteFilename = $ftpService->getRemoteFilename($contract);
        $result = $ftpService->uploadToVault($pdfPath, $remoteFilename);
        if ($result['success']) {
            $contract->update([
                'ftp_to_vault' => true,
                'ftp_at' => now(),
                'vault_path' => $result['path'],
                'ftp_error' => null
            ]);
           
            Log::info('Contract uploaded to vault during finalization', [
                'contract_id' => $id,
                'vault_filename' => $remoteFilename
            ]);
           
            return redirect()->route('contracts.view', $id)
                ->with('success', 'Contract finalized and uploaded to vault successfully.');
        } else {
            // Log error but don't block finalization
            $contract->update([
                'ftp_to_vault' => false,
                'ftp_error' => $result['error']
            ]);
           
            Log::warning('Contract finalized but FTP upload failed', [
                'contract_id' => $id,
                'error' => $result['error']
            ]);
           
            return redirect()->route('contracts.view', $id)
                ->with('warning', 'Contract finalized successfully, but upload to vault failed. You can retry from the contract page.');
        }
    }
    public function createRevision($id)
    {
        $originalContract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        if ($originalContract->status !== 'finalized') {
            return redirect()->route('contracts.view', $id)->with('error', 'Only finalized contracts can have revisions.');
        }
      
        $revision = $originalContract->replicate();
        $revision->status = 'draft';
        $revision->signature_path = null;
        $revision->save();
      
        foreach ($originalContract->addOns as $addOn) {
            $newAddOn = $addOn->replicate();
            $newAddOn->contract_id = $revision->id;
            $newAddOn->save();
        }
      
        foreach ($originalContract->oneTimeFees as $fee) {
            $newFee = $fee->replicate();
            $newFee->contract_id = $revision->id;
            $newFee->save();
        }
      
        return redirect()->route('contracts.edit', $revision->id)->with('success', 'Revision created successfully.');
    }
    public function ftp($id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
       
        if ($contract->status !== 'finalized') {
            return redirect()->back()->with('error', 'Only finalized contracts can be uploaded to vault.');
        }
       
        if (!$contract->pdf_path || !Storage::disk('public')->exists($contract->pdf_path)) {
            return redirect()->back()->with('error', 'Contract PDF not found.');
        }
       
        $ftpService = new VaultFtpService();
        $remoteFilename = $ftpService->getRemoteFilename($contract);
        $result = $ftpService->uploadToVault($contract->pdf_path, $remoteFilename);
       
        if ($result['success']) {
            $contract->update([
                'ftp_to_vault' => true,
                'ftp_at' => now(),
                'vault_path' => $result['path'],
                'ftp_error' => null
            ]);
           
            return redirect()->back()->with('success', "Contract uploaded to vault successfully as: {$remoteFilename}");
        } else {
            $contract->update([
                'ftp_to_vault' => false,
                'ftp_error' => $result['error']
            ]);
           
            return redirect()->back()->with('error', 'FTP upload failed: ' . $result['error']);
        }
    }
    public function download($id)
    {
        $contract = Contract::findOrFail($id);
       
        if ($contract->status !== 'finalized') {
            return redirect()->back()->with('error', 'Contract must be finalized to download.');
        }
        try {
            $pdfService = app(ContractPdfService::class);
            $mergedPdfContent = $pdfService->generateMergedPdfContent($contract);
           
            $ftpService = new \App\Services\VaultFtpService();
            $pdfFileName = $ftpService->getRemoteFilename($contract);
            return response()->streamDownload(function () use ($mergedPdfContent) {
                echo $mergedPdfContent;
            }, $pdfFileName, ['Content-Type' => 'application/pdf']);
           
        } catch (\Exception $e) {
            Log::error('PDF generation failed', [
                'contract_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
    public function view($id)
            {
                Log::debug('Starting ContractController@view', ['id' => $id]);
                        // Load the contract with relationships
                        $contract = Contract::with([
                            'ratePlan',
                            'mobileInternetPlan',
                            'addOns',
                            'oneTimeFees',
                            'subscriber.mobilityAccount.ivueAccount.customer',
                            'activityType',
                            'commitmentPeriod',
                            'bellDevice'
                        ])->findOrFail($id);
                        Log::debug('Contract loaded', ['contract_id' => $contract->id]);
                        // Initialize HTML Purifier
                        $config = HTMLPurifier_Config::createDefault();
                        $config->set('Core.Encoding', 'UTF-8');
                        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
                        $config->set('HTML.Allowed', 'p,strong,em,ul,ol,li,a[href|title],br,div[class],span[class],table,tr,td,th,hr');
                        $config->set('AutoFormat.AutoParagraph', true);
                        $config->set('AutoFormat.Linkify', true);
                        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true]);
                        $config->set('Cache.SerializerPath', storage_path('htmlpurifier')); // Set custom cache directory
                        $purifier = new HTMLPurifier($config);
                        Log::debug('HTMLPurifier initialized');
                        // Sanitize ratePlan features
                        if ($contract->ratePlan && $contract->ratePlan->features) {
                            $originalFeatures = $contract->ratePlan->features;
                            $contract->ratePlan->features = $purifier->purify($contract->ratePlan->features);
                            Log::debug('Sanitized ratePlan features', [
                                'original' => $originalFeatures,
                                'sanitized' => $contract->ratePlan->features
                            ]);
                        } else {
                            Log::debug('No ratePlan features to sanitize', [
                                'hasRatePlan' => !empty($contract->ratePlan),
                                'hasFeatures' => !empty($contract->ratePlan->features)
                            ]);
                        }
                        // Sanitize mobileInternetPlan description
                        if ($contract->mobileInternetPlan && $contract->mobileInternetPlan->description) {
                            $originalDescription = $contract->mobileInternetPlan->description;
                            $contract->mobileInternetPlan->description = $purifier->purify($contract->mobileInternetPlan->description);
                            Log::debug('Sanitized mobileInternetPlan description', [
                                'original' => $originalDescription,
                                'sanitized' => $contract->mobileInternetPlan->description
                            ]);
                        } else {
                            Log::debug('No mobileInternetPlan description to sanitize');
                        }
                        // Financial calculations
                        $devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
                        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
                        $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
                        $monthlyDevicePayment = ($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0)) / 24;
                        $earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
                        $monthlyReduction = $monthlyDevicePayment;
                        $totalAddOnCost = $contract->addOns->sum('cost') ?? 0;
                        $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost') ?? 0;
                        $totalCost = ($totalAddOnCost + ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) + $monthlyDevicePayment) * 24 + $totalOneTimeFeeCost;
                        Log::debug('Calculated financials', [
                            'devicePrice' => $devicePrice,
                            'deviceAmount' => $deviceAmount,
                            'totalFinancedAmount' => $totalFinancedAmount,
                            'monthlyDevicePayment' => $monthlyDevicePayment,
                            'earlyCancellationFee' => $earlyCancellationFee,
                            'monthlyReduction' => $monthlyReduction,
                            'totalAddOnCost' => $totalAddOnCost,
                            'totalOneTimeFeeCost' => $totalOneTimeFeeCost,
                            'totalCost' => $totalCost
                        ]);
                       
                       
                        return view('contracts.view', compact(
                            'contract',
                            'totalAddOnCost',
                            'totalOneTimeFeeCost',
                            'totalCost',
                            'devicePrice',
                            'deviceAmount',
                            'totalFinancedAmount',
                            'monthlyDevicePayment',
                            'earlyCancellationFee',
                            'monthlyReduction'
                        ));
                }
   
   
public function email($id)
{
$contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
if ($contract->status !== 'finalized') {
return redirect()->back()->with('error', 'Contract must be finalized to email.');
}
  
$email = $contract->subscriber->mobilityAccount->ivueAccount->customer->email;
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
Log::error('Invalid or missing email for customer', ['contract_id' => $id, 'email' => $email]);
return redirect()->back()->with('error', 'No valid email address found for this customer.');
}
  
try {
$pdfService = app(ContractPdfService::class);
$mergedPdfContent = $pdfService->generateMergedPdfContent($contract);
$ftpService = new \App\Services\VaultFtpService();
$pdfFileName = $ftpService->getRemoteFilename($contract);
Mail::send('emails.contract', ['contract' => $contract], function ($message) use ($contract, $email, $mergedPdfContent, $pdfFileName) {
$message->to($email)
->subject('Your Hay CIS Contract #' . $contract->id);
$message->attachData($mergedPdfContent, $pdfFileName, ['mime' => 'application/pdf']);
// No financing attachment
});
  
Log::info('Contract email sent with merged PDF', ['contract_id' => $id, 'email' => $email]);
return redirect()->back()->with('success', 'Contract emailed successfully.');
} catch (\Exception $e) {
Log::error('Failed to send contract email', ['contract_id' => $id, 'email' => $email, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
}
}
  
    /**
     * Helper method to get Bell pricing for a device
     */
    private function getBellPricing($deviceId, $tier, $useDro = false)
    {
        if ($useDro) {
            return BellDroPricing::getPricing($deviceId, $tier);
        }
      
        return BellPricing::getPricing($deviceId, $tier);
    }
   
   
   
    /**
     * Display the financing form
     */
    public function financingForm($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);
        // Check if financing is required
        if (!$contract->requiresFinancing()) {
            return redirect()->route('contracts.view', $id)
                ->with('error', 'This contract does not require a financing form.');
        }
        return view('contracts.financing', compact('contract'));
    }
    /**
     * Show the financing form signature page
     */
    public function signFinancing($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);
        if (!$contract->requiresFinancing()) {
            return redirect()->route('contracts.view', $id)
                ->with('error', 'This contract does not require a financing form.');
        }
        if ($contract->financing_status !== 'pending') {
            return redirect()->route('contracts.financing', $id)
                ->with('error', 'Financing form cannot be signed at this time.');
        }
        return view('contracts.sign-financing', compact('contract'));
    }
    public function storeFinancingSignature(Request $request, $id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        if ($contract->financing_status !== 'pending') {
            Log::warning('Financing form cannot be signed - wrong status', [
                'contract_id' => $id,
                'current_status' => $contract->financing_status
            ]);
            return redirect()->route('contracts.financing', $id)
                ->with('error', 'Financing form cannot be signed at this time.');
        }
        $request->validate([
            'signature' => 'required|string|regex:/^data:image\/png;base64,/',
        ], [
            'signature.required' => 'Please provide a signature.',
            'signature.regex' => 'Invalid signature format.',
        ]);
        try {
            $signature = $request->signature;
            Log::info('Received financing signature data', ['contract_id' => $id]);
            $signaturePath = "signatures/financing_contract_{$contract->id}.png";
            $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature));
           
            if ($signatureData === false) {
                Log::error('Failed to decode financing signature data', ['contract_id' => $id]);
                return redirect()->back()->with('error', 'Failed to process signature.');
            }
            $stored = Storage::disk('public')->put($signaturePath, $signatureData);
            if (!$stored) {
                Log::error('Failed to store financing signature file', ['contract_id' => $id]);
                return redirect()->back()->with('error', 'Failed to save signature file.');
            }
            Log::info('Financing signature file stored', ['contract_id' => $id, 'path' => $signaturePath]);
            // Update the contract with all financing signature info
            $updated = $contract->update([
                'financing_signature_path' => 'storage/' . $signaturePath,
                'financing_status' => 'customer_signed',
                'financing_signed_at' => now(),
                'updated_by' => auth()->id(),
            ]);
            if (!$updated) {
                Log::error('Failed to update contract with financing signature', ['contract_id' => $id]);
                return redirect()->back()->with('error', 'Failed to save signature to database.');
            }
            // Refresh the contract to get updated values
            $contract->refresh();
            Log::info('Financing form signed successfully', [
                'contract_id' => $id,
                'new_status' => $contract->financing_status,
                'signature_path' => $contract->financing_signature_path,
                'signed_at' => $contract->financing_signed_at
            ]);
            // Generate PDF
            $this->generateFinancingPdf($contract);
            return redirect()->route('contracts.financing', $id)
                ->with('success', 'Financing form signed successfully');
               
        } catch (\Exception $e) {
            Log::error('Error in storeFinancingSignature', ['contract_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save signature: ' . $e->getMessage());
        }
    }
    /**
     * Finalize the financing form
     */
    public function finalizeFinancing($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);
        if ($contract->financing_status !== 'csr_initialed') {
            return redirect()->route('contracts.financing', $id)
                ->with('error', 'Financing form must be initialed by CSR before finalizing.');
        }
        $contract->update([
            'financing_status' => 'finalized',
            'updated_by' => auth()->id(),
        ]);
        // Regenerate PDF with initials
        $this->generateFinancingPdf($contract);
        Log::info('Financing form finalized', ['contract_id' => $id]);
        // FTP to Vault
        if (config('filesystems.disks.vault_ftp')) {
            try {
                $ftpService = new \App\Services\VaultFtpService();
                $remoteFilename = $ftpService->getRemoteFilename($contract);
                // Add _financing suffix
                $remoteFilename = str_replace('.pdf', '_financing.pdf', $remoteFilename);
               
                $result = $ftpService->uploadToVault($contract->financing_pdf_path, $remoteFilename);
               
                if ($result['success']) {
                    Log::info('Financing form uploaded to vault', [
                        'contract_id' => $id,
                        'vault_filename' => $remoteFilename
                    ]);
                } else {
                    Log::warning('Financing form finalized but FTP upload failed', [
                        'contract_id' => $id,
                        'error' => $result['error']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('FTP upload exception for financing form', [
                    'contract_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        return redirect()->route('contracts.financing', $id)
            ->with('success', 'Financing form finalized successfully.');
    }
   
    /**
     * Show the CSR initials signature pad
     */
    public function signCsrFinancing($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);
        if (!$contract->requiresFinancing() || $contract->financing_status !== 'customer_signed') {
            return redirect()->route('contracts.financing', $id)
                ->with('error', 'Financing form cannot be initialed at this time.');
        }
        return view('contracts.sign-financing-csr', compact('contract'));
    }
    /**
     * Store the CSR initials
     */
    public function storeCsrFinancingInitials(Request $request, $id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        if ($contract->financing_status !== 'customer_signed') {
            return redirect()->route('contracts.financing', $id)
                ->with('error', 'Financing form cannot be initialed at this time.');
        }
        $request->validate([
            'initials' => 'required|string|regex:/^data:image\/png;base64,/',
        ], [
            'initials.required' => 'Please provide initials.',
            'initials.regex' => 'Invalid initials format.',
        ]);
        try {
            $initials = $request->initials;
            Log::info('Received CSR initials data', ['contract_id' => $id]);
            $initialsPath = "initials/financing_contract_{$contract->id}_csr.png";
            $initialsData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $initials));
           
            if ($initialsData === false) {
                Log::error('Failed to decode CSR initials data', ['contract_id' => $id]);
                return redirect()->back()->with('error', 'Failed to process initials.');
            }
            $stored = Storage::disk('public')->put($initialsPath, $initialsData);
            if (!$stored) {
                Log::error('Failed to store CSR initials file', ['contract_id' => $id]);
                return redirect()->back()->with('error', 'Failed to save initials file.');
            }
            Log::info('CSR initials file stored', ['contract_id' => $id, 'path' => $initialsPath]);
            $contract->update([
                'financing_csr_initials_path' => 'storage/' . $initialsPath,
                'financing_status' => 'csr_initialed',
                'financing_csr_initialed_at' => now(),
                'updated_by' => auth()->id(),
            ]);
            $contract->refresh();
            // Regenerate PDF with initials
            $this->generateFinancingPdf($contract);
            return redirect()->route('contracts.financing', $id)
                ->with('success', 'Financing form initialed successfully');
        } catch (\Exception $e) {
            Log::error('Error in storeCsrFinancingInitials', ['contract_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save initials: ' . $e->getMessage());
        }
    }
   
    /**
     * Download the financing form
     */
    public function downloadFinancing($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);
        if ($contract->financing_status !== 'finalized') {
            return redirect()->back()->with('error', 'Financing form must be finalized to download.');
        }
        if (!$contract->financing_pdf_path || !Storage::disk('public')->exists($contract->financing_pdf_path)) {
            Log::error('Financing PDF not found', ['contract_id' => $id, 'path' => $contract->financing_pdf_path]);
            return redirect()->back()->with('error', 'Financing PDF not found.');
        }
        try {
            $pdfContent = Storage::disk('public')->get($contract->financing_pdf_path);
            $fileName = str_replace('.pdf', '_financing.pdf',
                $contract->subscriber->last_name . '_' .
                $contract->subscriber->first_name . '_' .
                $contract->id . '.pdf'
            );
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, $fileName, ['Content-Type' => 'application/pdf']);
        } catch (\Exception $e) {
            Log::error('Financing PDF download failed', [
                'contract_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to download PDF: ' . $e->getMessage());
        }
    }
    private function generateFinancingPdf($contract)
    {
       
        // Render the HTML from the view
        $html = view('contracts.financing', [ // Assuming 'finance' is a typo and it's 'financing.blade.php'
            'contract' => $contract,
            'pdf' => true
        ])->render();
        // Set up options (matching your current ones)
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('dpi', 150);
        $options->set('defaultFont', 'sans-serif');
        $options->set('memory_limit', '512M');
        $options->set('chroot', base_path());
        $options->set('isPhpEnabled', true);
        $options->set('margin_top', 10);
        $options->set('margin_bottom', 10);
        $options->set('margin_left', 10);
        $options->set('margin_right', 10);
        // First pass: Render to measure height (use standard A4 to estimate)
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();
        // Get the rendered height in points + buffer for margins/footer (adjust 50-100pt if needed for safety)
        $canvas = $dompdf->getCanvas();
        $height = $canvas->get_height() + 80; // Add buffer to avoid cutoff
        // Second pass: Re-render with custom paper size (A4 width = 595pt, dynamic height)
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 595, $height]); // Custom size: x,y,width,height in points
        $dompdf->render();
        // Save the PDF
        $pdfPath = "contracts/financing_contract_{$contract->id}.pdf";
        Storage::disk('public')->put($pdfPath, $dompdf->output());
        $contract->update(['financing_pdf_path' => $pdfPath]);
        Log::info('Financing PDF generated with custom height', [
            'contract_id' => $contract->id,
            'calculated_height' => $height,
            'path' => $pdfPath
        ]);
    }
   
   
}