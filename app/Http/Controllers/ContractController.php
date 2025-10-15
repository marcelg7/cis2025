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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use setasign\Fpdi\Fpdi;
use Carbon\Carbon;
use HTMLPurifier; // Import the Purifier class
use HTMLPurifier_Config; // For configuration


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
			$subscriber = Subscriber::findOrFail($subscriberId);  // Change this line: Use Subscriber, not Customer
		}
		$tiers = $ratePlans->pluck('tier')->unique()->sort()->values()->toArray();
		// Define default first bill date (adjust logic as needed, e.g., first of next month)
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
			'defaultFirstBillDate'
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
			'bell_pricing_type' => 'nullable|in:smartpay,dro',
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
		]);

		$subscriber = Subscriber::with('mobilityAccount.ivueAccount.customer')->findOrFail($subscriberId);

		$price = 0;
		if ($request->filled('bell_device_id')) {
			$price = $request->bell_retail_price ?? 0;
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
		]);

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
			'tiers'
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
			'bell_pricing_type' => 'nullable|in:smartpay,dro',
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
		]);

		$price = 0;
		// If Bell pricing is used, override device_price with bell_retail_price
		if ($request->filled('bell_device_id')) {
			$price = $request->bell_retail_price ?? 0;
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
			// Add cellular pricing fields
			'rate_plan_id' => $request->rate_plan_id,
			'mobile_internet_plan_id' => $request->mobile_internet_plan_id,
			'rate_plan_price' => $request->rate_plan_price,
			'mobile_internet_price' => $request->mobile_internet_price,
			'selected_tier' => $request->selected_tier,
		]);

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

		return redirect()->route('contracts.view', $id)->with('success', 'Contract finalized successfully.');
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
        return redirect()->back()->with('info', 'FTP to storage vault functionality will be implemented soon.');
    }

	public function download($id)
	{
		$contract = Contract::with([
			'addOns',
			'oneTimeFees',
			'subscriber.mobilityAccount.ivueAccount.customer',
			'activityType',
			'commitmentPeriod',
			'ratePlan',
			'mobileInternetPlan',
			'bellDevice'
		])->findOrFail($id);
		if ($contract->status !== 'finalized') {
			return redirect()->back()->with('error', 'Contract must be finalized to download.');
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

		Log::debug('Calculated financials for download', [
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

		try {
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
					'dpi' => 300,
					'defaultFont' => 'sans-serif',
					'memory_limit' => '512M',
					'chroot' => base_path(),
					'isPhpEnabled' => true,
					'margin_top' => 10,
					'margin_bottom' => 10,
					'margin_left' => 10,
					'margin_right' => 10,
				]);

			$tempDir = storage_path('app/temp');
			if (!Storage::exists('temp')) {
				Storage::makeDirectory('temp');
			}
			$tempFile = storage_path('app/temp/contract_' . $id . '.pdf');
			$pdf->save($tempFile);

			$fpdi = new Fpdi();
			$pageCount = $fpdi->setSourceFile($tempFile);
			for ($i = 1; $i <= $pageCount; $i++) {
				$fpdi->AddPage();
				$tplIdx = $fpdi->importPage($i);
				$fpdi->useTemplate($tplIdx);
			}

			$termsFiles = [
				public_path('pdfs/OURAGREEMENTpage.pdf'),
				public_path('pdfs/HayCommTermsOfServicerev2020.pdf'),
			];

			foreach ($termsFiles as $file) {
				if (file_exists($file)) {
					Log::debug('Merging terms file', ['file' => $file]);
					$pageCount = $fpdi->setSourceFile($file);
					for ($i = 1; $i <= $pageCount; $i++) {
						$fpdi->AddPage();
						$tplIdx = $fpdi->importPage($i);
						$fpdi->useTemplate($tplIdx);
					}
				} else {
					Log::warning('Terms file not found', ['file' => $file]);
				}
			}

			Storage::delete('temp/contract_' . $id . '.pdf');

			$mergedPdfContent = $fpdi->Output('S');
			$pdfPath = "contracts/contract_{$contract->id}.pdf";
			Storage::disk('public')->put($pdfPath, $mergedPdfContent);
			$contract->update(['pdf_path' => $pdfPath]);

			$pdfFileName = ($contract->subscriber->first_name . '_' . $contract->subscriber->last_name . '_' . $contract->id . '.pdf');
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
       
        if (!$contract->pdf_path || !Storage::disk('public')->exists($contract->pdf_path)) {
            Log::error('PDF not found for contract', ['contract_id' => $id, 'pdf_path' => $contract->pdf_path]);
            return redirect()->back()->with('error', 'PDF not found.');
        }
       
        $email = $contract->subscriber->mobilityAccount->ivueAccount->customer->email;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::error('Invalid or missing email for customer', ['contract_id' => $id, 'email' => $email]);
            return redirect()->back()->with('error', 'No valid email address found for this customer.');
        }
       
        try {
            Mail::send('emails.contract', ['contract' => $contract], function ($message) use ($contract, $email) {
                $message->to($email)
                    ->subject('Your Hay CIS Contract #' . $contract->id)
                    ->attach(storage_path('app/public/' . $contract->pdf_path), [
                        'as' => 'contract_' . $contract->id . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
            });
           
            Log::info('Contract email sent', ['contract_id' => $id, 'email' => $email]);
            return redirect()->back()->with('success', 'Contract emailed successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to send contract email', ['contract_id' => $id, 'email' => $email, 'error' => $e->getMessage()]);
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
}