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
use App\Models\Location;
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
use HTMLPurifier;
use HTMLPurifier_Config;
use App\Services\VaultFtpService;
use App\Services\ContractPdfService;
use App\Services\ContractFileCleanupService;


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
		$locations = Location::active()->orderBy('name')->get();
		
		$ratePlans = RatePlan::current()->active()->orderBy('plan_type')->orderBy('tier')->orderBy('base_price')->get();
		$mobileInternetPlans = MobileInternetPlan::current()->active()->orderBy('monthly_rate')->get();
		$planAddOns = PlanAddOn::current()->active()->orderBy('category')->orderBy('add_on_name')->get();
		
		$subscriber = null;
		if ($subscriberId) {
			$subscriber = Subscriber::with('mobilityAccount.ivueAccount.customer')->findOrFail($subscriberId);
		}
		
		$tiers = $ratePlans->pluck('tier')->unique()->sort()->values()->toArray();
		
		$deviceTiers = [];
		foreach ($bellDevices as $device) {
			$availableTiers = [];
			
			if ($device->has_smartpay) {
				$smartpayTiers = \App\Models\BellPricing::where('bell_device_id', $device->id)
					->pluck('tier')
					->unique()
					->toArray();
				$availableTiers = array_merge($availableTiers, $smartpayTiers);
			}
			
			if ($device->has_dro) {
				$droTiers = \App\Models\BellDroPricing::where('bell_device_id', $device->id)
					->pluck('tier')
					->unique()
					->toArray();
				$availableTiers = array_merge($availableTiers, $droTiers);
			}
			
			$deviceTiers[$device->id] = array_unique($availableTiers);
		}
		
		$defaultFirstBillDate = Carbon::now()->addMonth()->startOfMonth();
		$defaultConnectionFee = \App\Helpers\SettingsHelper::get('default_connection_fee', 80);

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
			'deviceTiers',
			'defaultConnectionFee',
			'locations'
		))->withErrors(session()->get('errors', new \Illuminate\Support\ViewErrorBag));
	}
	
    public function store(Request $request, $subscriberId)
    {
		$request->validate([
			'start_date' => 'required|date',
			'end_date' => 'nullable|date|after:start_date',
			'activity_type_id' => 'required|exists:activity_types,id',
			'contract_date' => 'required|date',
			'location' => 'nullable|in:zurich,exeter,grand_bend',
			'location_id' => 'required|exists:locations,id',
			'customer_phone' => 'nullable|string|max:100',
			'bell_device_id' => 'nullable|exists:bell_devices,id',
			'bell_pricing_type' => 'nullable|in:smartpay,dro,byod',
			'bell_tier' => 'nullable|in:Ultra,Max,Select,Lite',
			'bell_retail_price' => 'nullable|numeric|min:0|max:10000', // SECURITY: Prevent unreasonably large values
			'bell_monthly_device_cost' => 'nullable|numeric|min:0|max:10000',
			'bell_plan_cost' => 'nullable|numeric|min:0|max:10000',
			'bell_dro_amount' => 'nullable|numeric|min:0|max:10000',
			'bell_plan_plus_device' => 'nullable|numeric|min:0|max:10000',
			'agreement_credit_amount' => 'required|numeric|min:0|max:10000',
			'required_upfront_payment' => 'required|numeric|min:0|max:10000',
			'optional_down_payment' => 'nullable|numeric|min:0|max:10000',
			'deferred_payment_amount' => 'nullable|numeric|min:0|max:10000',
			'commitment_period_id' => 'required|exists:commitment_periods,id',
			'first_bill_date' => 'required|date',
			'imei' => 'nullable|string|max:20',
			'add_ons' => 'nullable|array',
			'add_ons.*.name' => 'required|string|max:100',
			'add_ons.*.code' => 'nullable|string|max:50',
			'add_ons.*.cost' => 'required|numeric|max:10000',
			'one_time_fees' => 'nullable|array',
			'one_time_fees.*.name' => 'required|string|max:100',
			'one_time_fees.*.cost' => 'required|numeric|min:-10000|max:10000', // Allow negative for credits
			'rate_plan_id' => 'nullable|exists:rate_plans,id',
			'mobile_internet_plan_id' => 'nullable|exists:mobile_internet_plans,id',
			'rate_plan_price' => 'nullable|numeric|min:0|max:10000',
			'mobile_internet_price' => 'nullable|numeric|min:0|max:10000',
			'selected_tier' => 'nullable|string|in:Lite,Select,Max,Ultra',
			'custom_device_name' => 'nullable|string|max:255',
		]);
        
		\Log::info('Contract Store - Add-ons received:', [
			'add_ons' => $request->input('add_ons'),
			'has_add_ons' => $request->has('add_ons'),
		]);		
		
        $subscriber = Subscriber::with('mobilityAccount.ivueAccount.customer')->findOrFail($subscriberId);
        $price = 0;
        
        if ($request->filled('bell_device_id')) {
            $price = $request->bell_retail_price ?? 0;
        }
        
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
            'location_id' => $request->location_id,
            'customer_phone' => $request->customer_phone,
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
			'imei' => $request->imei,
            'status' => 'draft',
            'updated_by' => auth()->id(),
            'rate_plan_id' => $request->rate_plan_id,
            'mobile_internet_plan_id' => $request->mobile_internet_plan_id,
            'rate_plan_price' => $request->rate_plan_price,
            'mobile_internet_price' => $request->mobile_internet_price,
            'selected_tier' => $request->selected_tier,
            'custom_device_name' => $request->custom_device_name,
        ]);
       
        if ($contract->requiresFinancing()) {
            $contract->update(['financing_status' => 'pending']);
            Log::info('Contract requires financing form', ['contract_id' => $contract->id]);
        } else {
            $contract->update(['financing_status' => 'not_required']);
            Log::info('Contract does not require financing form', ['contract_id' => $contract->id]);
        }
		
		if ($contract->requiresDro()) {
			$contract->update(['dro_status' => 'pending']);
			Log::info('Contract requires DRO form', ['contract_id' => $contract->id]);
		} else {
			$contract->update(['dro_status' => 'not_required']);
			Log::info('Contract does not require DRO form', ['contract_id' => $contract->id]);
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
        
        // CALCULATE ALL REQUIRED VARIABLES
        $devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
        $requiredUpfront = $contract->required_upfront_payment ?? 0;
        $optionalUpfront = $contract->optional_down_payment ?? 0;
        $deferredPayment = $contract->deferred_payment_amount ?? 0;

        $totalFinancedAmount = $deviceAmount - $requiredUpfront - $optionalUpfront;
        $remainingBalance = $totalFinancedAmount - $deferredPayment;
        $monthlyDevicePayment = $remainingBalance / 24;
        $earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
        $monthlyReduction = $monthlyDevicePayment;

        // Calculate buyout cost - factors in upfront payments customer already made
        $buyoutCost = ($devicePrice - $requiredUpfront - $optionalUpfront - $deferredPayment) / 24;
        
        // Get and format the cancellation policy
        $cancellationPolicy = '';
        if ($contract->commitmentPeriod && $contract->commitmentPeriod->cancellation_policy) {
            $cancellationPolicy = str_replace(
                ['{balance}', '{monthly_reduction}', '{start_date}', '{end_date}', '{buyout_cost}', '{device_return_option}'],
                [
                    number_format($remainingBalance, 2),
                    number_format($buyoutCost, 2),
                    $contract->start_date ? $contract->start_date->format('M d, Y') : 'N/A',
                    $contract->end_date ? $contract->end_date->format('M d, Y') : 'N/A',
                    number_format($buyoutCost, 2),
                    number_format($deferredPayment, 2)
                ],
                $contract->commitmentPeriod->cancellation_policy
            );
        }
        
        $totalAddOnCost = $contract->addOns->sum('cost') ?? 0;
        $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost') ?? 0;
        $totalCost = $deviceAmount + 
                     (($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) * 24) + 
                     ($totalAddOnCost * 24) + 
                     $totalOneTimeFeeCost;
        
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', 'p,strong,em,ul,ol,li,a[href|title],br,div[class],span[class],table,tr,td,th,hr');
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.Linkify', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true]);
        $config->set('Cache.SerializerPath', storage_path('htmlpurifier'));
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
            'monthlyReduction',
            'buyoutCost',
            'cancellationPolicy'
        ))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'memory_limit' => '512M',
                'chroot' => base_path(),
                'isPhpEnabled' => false, // SECURITY: Never enable PHP in PDFs (prevents RCE)
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
            ]);
        
        $pdfPath = "contracts/contract_{$contract->id}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());
        $contract->update(['pdf_path' => $pdfPath]);
        
        $customerId = $contract->subscriber->mobilityAccount->ivueAccount->customer_id;
        // Redirect to contract view to start signing flow
        return redirect()->route('contracts.view', $contract->id)->with('success', 'Contract created successfully. Please proceed with signing.');
    }
	
	public function edit(Contract $contract)
	{
		// Authorization check - prevent IDOR vulnerability
		$this->authorize('update', $contract);

		// Load customer relationship for contact methods
		$contract->load('subscriber.mobilityAccount.ivueAccount.customer');

		$customers = Customer::orderBy('last_name')->get();
		$users = User::orderBy('name')->get();
		$activityTypes = ActivityType::orderBy('name')->get();
		$commitmentPeriods = CommitmentPeriod::orderBy('name')->get();
		$bellDevices = BellDevice::orderBy('model')->get();
		$locations = Location::active()->orderBy('name')->get();

		$ratePlans = RatePlan::current()->active()->orderBy('plan_type')->orderBy('tier')->orderBy('base_price')->get();
		$mobileInternetPlans = MobileInternetPlan::current()->active()->orderBy('monthly_rate')->get();
		$planAddOns = PlanAddOn::current()->active()->orderBy('category')->orderBy('add_on_name')->get();
	   
		$tiers = $ratePlans->pluck('tier')->unique()->sort()->values()->toArray();
	   
		$deviceTiers = [];
		foreach ($bellDevices as $device) {
			$availableTiers = [];
			
			if ($device->has_smartpay) {
				$smartpayTiers = \App\Models\BellPricing::where('bell_device_id', $device->id)
					->pluck('tier')
					->unique()
					->toArray();
				$availableTiers = array_merge($availableTiers, $smartpayTiers);
			}
			
			if ($device->has_dro) {
				$droTiers = \App\Models\BellDroPricing::where('bell_device_id', $device->id)
					->pluck('tier')
					->unique()
					->toArray();
				$availableTiers = array_merge($availableTiers, $droTiers);
			}
			
			$deviceTiers[$device->id] = array_unique($availableTiers);
		}
	   

		$defaultConnectionFee = \App\Helpers\SettingsHelper::get('default_connection_fee', 80);


	   
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
			'deviceTiers',
			'defaultConnectionFee',
			'locations'
		));
	}
	   
	public function update(Request $request, Contract $contract)
	{
		// Authorization check - prevent IDOR vulnerability
		$this->authorize('update', $contract);

		$validated = $request->validate([
			'start_date' => 'required|date',
			'end_date' => 'required|date|after:start_date',
			'activity_type_id' => 'required|exists:activity_types,id',
			'contract_date' => 'required|date',
			'location' => 'nullable|in:zurich,exeter,grand_bend',
			'location_id' => 'required|exists:locations,id',
			'customer_phone' => 'nullable|string|max:100',
			'bell_device_id' => 'nullable|exists:bell_devices,id',
			'bell_pricing_type' => 'nullable|in:smartpay,dro,byod',
			'bell_tier' => 'nullable|in:Ultra,Max,Select,Lite',
			'bell_retail_price' => 'nullable|numeric|min:0|max:10000', // SECURITY: Prevent unreasonably large values
			'bell_monthly_device_cost' => 'nullable|numeric|min:0|max:10000',
			'bell_plan_cost' => 'nullable|numeric|min:0|max:10000',
			'bell_dro_amount' => 'nullable|numeric|min:0|max:10000',
			'bell_plan_plus_device' => 'nullable|numeric|min:0|max:10000',
			'agreement_credit_amount' => 'nullable|numeric|min:0|max:10000',
			'required_upfront_payment' => 'nullable|numeric|min:0|max:10000',
			'optional_down_payment' => 'nullable|numeric|min:0|max:10000',
			'deferred_payment_amount' => 'nullable|numeric|min:0|max:10000',
			'commitment_period_id' => 'required|exists:commitment_periods,id',
			'first_bill_date' => 'required|date|after_or_equal:start_date',
			'imei' => 'nullable|string|max:20',
			'add_ons' => 'nullable|array',
			'add_ons.*.name' => 'required|string|max:255',
			'add_ons.*.code' => 'required|string|max:255',
			'add_ons.*.cost' => 'required|numeric|max:10000',
			'one_time_fees' => 'nullable|array',
			'one_time_fees.*.name' => 'required|string|max:255',
			'one_time_fees.*.cost' => 'required|numeric|min:-10000|max:10000', // Allow negative for credits
			'rate_plan_id' => 'nullable|exists:rate_plans,id',
			'mobile_internet_plan_id' => 'nullable|exists:mobile_internet_plans,id',
			'rate_plan_price' => 'nullable|numeric|min:0|max:10000',
			'mobile_internet_price' => 'nullable|numeric|min:0|max:10000',
			'selected_tier' => 'nullable|string|in:Lite,Select,Max,Ultra',
			'custom_device_name' => 'nullable|string|max:255',
		]);

		$price = 0;
		$customDeviceName = null;
		
		if ($request->filled('bell_device_id')) {
			$price = $request->bell_retail_price ?? 0;
		}

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
				$customDeviceName = $request->custom_device_name;
			} else {
				$customDeviceName = null;
			}
		}

		$contract->update([
			'start_date' => $request->start_date,
			'end_date' => $request->end_date,
			'activity_type_id' => $request->activity_type_id,
			'contract_date' => $request->contract_date,
			'location' => $request->location,
			'location_id' => $request->location_id,
			'customer_phone' => $request->customer_phone,
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
			'imei' => $request->imei,
			'updated_by' => auth()->id(),
			'rate_plan_id' => $request->rate_plan_id,
			'mobile_internet_plan_id' => $request->mobile_internet_plan_id,
			'rate_plan_price' => $request->rate_plan_price,
			'mobile_internet_price' => $request->mobile_internet_price,
			'selected_tier' => $request->selected_tier,
			'custom_device_name' => null,
		]);
       
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
       
		if ($contract->requiresDro()) {
			if ($contract->dro_status === 'not_required') {
				$contract->update(['dro_status' => 'pending']);
				Log::info('Contract now requires DRO form', ['contract_id' => $contract->id]);
			}
		} else {
			if ($contract->dro_status !== 'not_required') {
				$contract->update(['dro_status' => 'not_required']);
				Log::info('Contract no longer requires DRO form', ['contract_id' => $contract->id]);
			}
		}
	   
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

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('sign', $contract);

        if ($contract->status !== 'draft') {
            Log::warning('Contract cannot be signed due to status', ['contract_id' => $id, 'status' => $contract->status]);
            return redirect()->route('contracts.view', $id)->with('error', 'Contract cannot be signed.');
        }

        // Check if financing needs to be signed first
        if ($contract->requiresFinancing() && $contract->financing_status === 'pending') {
            return redirect()->route('contracts.financing.sign', $id)
                ->with('success', 'Please complete the financing form first.');
        }

        // Check if DRO needs to be signed first
        if ($contract->requiresDro() && $contract->dro_status === 'pending') {
            return redirect()->route('contracts.dro.sign', $id)
                ->with('success', 'Please complete the DRO form first.');
        }

        return view('contracts.sign', compact('contract'));
    }
	
	public function storeSignature(Request $request, $id)
	{
		$contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('sign', $contract);

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
			Log::info('Received signature data', ['contract_id' => $id]);
			
			$signaturePath = "signatures/contract_{$contract->id}.png";
			$signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature));
			
			if ($signatureData === false) {
				Log::error('Failed to decode signature data', ['contract_id' => $id]);
				return redirect()->back()->with('error', 'Failed to process signature.');
			}
			
			$stored = Storage::disk('public')->put($signaturePath, $signatureData);
			if (!$stored) {
				Log::error('Failed to store signature file', ['contract_id' => $id]);
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
			
			$contract->load('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer', 'activityType', 'commitmentPeriod', 'ratePlan', 'mobileInternetPlan', 'bellDevice');
			
			// CALCULATE ALL REQUIRED VARIABLES
			$devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
			$deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
			$requiredUpfront = $contract->required_upfront_payment ?? 0;
			$optionalUpfront = $contract->optional_down_payment ?? 0;
			$deferredPayment = $contract->deferred_payment_amount ?? 0;

			$totalFinancedAmount = $deviceAmount - $requiredUpfront - $optionalUpfront;
			$remainingBalance = $totalFinancedAmount - $deferredPayment;
			$monthlyDevicePayment = $remainingBalance / 24;
			$earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
			$monthlyReduction = $monthlyDevicePayment;

			// Calculate buyout cost - factors in upfront payments customer already made
			$buyoutCost = ($devicePrice - $requiredUpfront - $optionalUpfront - $deferredPayment) / 24;
			
			// Get and format the cancellation policy
			$cancellationPolicy = '';
			if ($contract->commitmentPeriod && $contract->commitmentPeriod->cancellation_policy) {
				$cancellationPolicy = str_replace(
					['{balance}', '{monthly_reduction}', '{start_date}', '{end_date}', '{buyout_cost}', '{device_return_option}'],
					[
						number_format($remainingBalance, 2),
						number_format($buyoutCost, 2),
						$contract->start_date ? $contract->start_date->format('M d, Y') : 'N/A',
						$contract->end_date ? $contract->end_date->format('M d, Y') : 'N/A',
						number_format($buyoutCost, 2),
						number_format($deferredPayment, 2)
					],
					$contract->commitmentPeriod->cancellation_policy
				);
			}
			
			$totalAddOnCost = $contract->addOns->sum('cost');
			$totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost');
			$totalCost = $deviceAmount + 
						 (($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) * 24) + 
						 ($totalAddOnCost * 24) + 
						 $totalOneTimeFeeCost;
			
			$config = HTMLPurifier_Config::createDefault();
			$config->set('Core.Encoding', 'UTF-8');
			$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
			$config->set('HTML.Allowed', 'p,strong,em,ul,ol,li,a[href|title],br,div[class],span[class],table,tr,td,th,hr');
			$config->set('AutoFormat.AutoParagraph', true);
			$config->set('AutoFormat.Linkify', true);
			$config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true]);
			$config->set('Cache.SerializerPath', storage_path('htmlpurifier'));
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
				'monthlyReduction',
				'buyoutCost',
				'cancellationPolicy'
			))
				->setPaper('a4', 'portrait')
				->setOptions([
					'isHtml5ParserEnabled' => true,
					'isRemoteEnabled' => true,
					'dpi' => 150,
					'defaultFont' => 'sans-serif',
					'memory_limit' => '512M',
					'chroot' => base_path(),
					'isPhpEnabled' => false, // SECURITY: Never enable PHP in PDFs (prevents RCE)
					'margin_top' => 10,
					'margin_bottom' => 10,
					'margin_left' => 10,
					'margin_right' => 10,
				]);
			
			$pdfPath = "contracts/contract_{$contract->id}.pdf";
			Storage::disk('public')->put($pdfPath, $pdf->output());
			$contract->update(['pdf_path' => $pdfPath]);
			
			Log::info('PDF regenerated with signature', ['contract_id' => $id, 'pdf_path' => $pdfPath]);
			
			return redirect()->route('contracts.view', $id)->with('success', 'Contract signed successfully');
		} catch (\Exception $e) {
			Log::error('Error in storeSignature', ['contract_id' => $id, 'error' => $e->getMessage()]);
			return redirect()->back()->with('error', 'Failed to save signature: ' . $e->getMessage());
		}
	}
   
	public function finalize($id)
	{
		$contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('finalize', $contract);

		if ($contract->status !== 'signed') {
			return redirect()->route('contracts.view', $id)->with('error', 'Contract must be signed before finalizing.');
		}

		if ($contract->requiresFinancing() && $contract->financing_status !== 'finalized') {
			return redirect()->route('contracts.view', $id)
				->with('error', 'Financing form must be completed and finalized before finalizing the contract.');
		}
		
		if ($contract->requiresDro() && $contract->dro_status !== 'finalized') {
			return redirect()->route('contracts.view', $id)
				->with('error', 'DRO form must be completed and finalized before finalizing the contract.');
		}		
		
		$contract->update([
			'status' => 'finalized',
			'updated_by' => auth()->id(),
		]);

		$contract->load('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer', 'activityType', 'commitmentPeriod', 'ratePlan', 'mobileInternetPlan', 'bellDevice');

		// CALCULATE ALL REQUIRED VARIABLES
		$devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
		$deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
		$requiredUpfront = $contract->required_upfront_payment ?? 0;
		$optionalUpfront = $contract->optional_down_payment ?? 0;
		$deferredPayment = $contract->deferred_payment_amount ?? 0;

		$totalFinancedAmount = $deviceAmount - $requiredUpfront - $optionalUpfront;
		$remainingBalance = $totalFinancedAmount - $deferredPayment;
		$monthlyDevicePayment = $remainingBalance / 24;
		$earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
		$monthlyReduction = $monthlyDevicePayment;

		// Calculate buyout cost - factors in upfront payments customer already made
		$buyoutCost = ($devicePrice - $requiredUpfront - $optionalUpfront - $deferredPayment) / 24;
		
		// Get and format the cancellation policy
		$cancellationPolicy = '';
		if ($contract->commitmentPeriod && $contract->commitmentPeriod->cancellation_policy) {
			$cancellationPolicy = str_replace(
				['{balance}', '{monthly_reduction}', '{start_date}', '{end_date}', '{buyout_cost}', '{device_return_option}'],
				[
					number_format($remainingBalance, 2),
					number_format($buyoutCost, 2),
					$contract->start_date ? $contract->start_date->format('M d, Y') : 'N/A',
					$contract->end_date ? $contract->end_date->format('M d, Y') : 'N/A',
					number_format($buyoutCost, 2),
					number_format($deferredPayment, 2)
				],
				$contract->commitmentPeriod->cancellation_policy
			);
		}
		
		$totalAddOnCost = $contract->addOns->sum('cost') ?? 0;
		$totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost') ?? 0;
		$totalCost = $deviceAmount + 
					 (($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) * 24) + 
					 ($totalAddOnCost * 24) + 
					 $totalOneTimeFeeCost;
		
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', 'UTF-8');
		$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$config->set('HTML.Allowed', 'p,strong,em,ul,ol,li,a[href|title],br,div[class],span[class],table,tr,td,th,hr');
		$config->set('AutoFormat.AutoParagraph', true);
		$config->set('AutoFormat.Linkify', true);
		$config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true]);
		$config->set('Cache.SerializerPath', storage_path('htmlpurifier'));
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
			'monthlyReduction',
			'buyoutCost',
			'cancellationPolicy'
		))
			->setPaper('a4', 'portrait')
			->setOptions([
				'isHtml5ParserEnabled' => true,
				'isRemoteEnabled' => true,
				'dpi' => 150,
				'defaultFont' => 'sans-serif',
				'memory_limit' => '512M',
				'chroot' => base_path(),
				'isPhpEnabled' => false, // SECURITY: Never enable PHP in PDFs (prevents RCE)
				'margin_top' => 10,
				'margin_bottom' => 10,
				'margin_left' => 10,
				'margin_right' => 10,
			]);
		
		$pdfPath = "contracts/contract_{$contract->id}.pdf";
		Storage::disk('public')->put($pdfPath, $pdf->output());
		$contract->update(['pdf_path' => $pdfPath]);

		Log::info('PDF generated for finalized contract', ['contract_id' => $id, 'pdf_path' => $pdfPath]);

        // Generate merged PDF (includes financing, DRO, and main contract)
        $pdfService = app(ContractPdfService::class);
        $mergedPdfContent = $pdfService->generateMergedPdfContent($contract);

        // Save merged PDF
        $mergedPdfPath = "contracts/contract_{$contract->id}_merged.pdf";
        Storage::disk('public')->put($mergedPdfPath, $mergedPdfContent);

        // FTP Upload merged PDF to Vault
        $ftpService = new VaultFtpService();
        $remoteFilename = $ftpService->getRemoteFilename($contract);
        $result = $ftpService->uploadToVault($mergedPdfPath, $remoteFilename);

        $messages = [];

        if ($result['success']) {
            $contract->update([
                'ftp_to_vault' => true,
                'ftp_at' => now(),
                'vault_path' => $result['path'],
                'ftp_error' => null
            ]);

            $messages[] = $result['test_mode'] ?? false
                ? 'Contract finalized successfully (Vault upload simulated in test mode).'
                : 'Contract finalized and uploaded to vault successfully.';

            Log::info('Merged contract uploaded to vault during finalization', [
                'contract_id' => $id,
                'vault_filename' => $remoteFilename,
                'test_mode' => $result['test_mode'] ?? false
            ]);
        } else {
            $contract->update([
                'ftp_to_vault' => false,
                'ftp_error' => $result['error']
            ]);

            Log::warning('Contract finalized but FTP upload failed', [
                'contract_id' => $id,
                'error' => $result['error']
            ]);

            $messages[] = 'Contract finalized, but upload to vault failed.';
        }

        // Email customer with merged PDF
        $email = $contract->subscriber->mobilityAccount->ivueAccount->customer->email;
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                // Check if we should simulate email (development/test mode for non-Hay emails)
                $shouldSimulate = config('app.env') !== 'production' &&
                                 !str_ends_with($email, '@haymail.ca') &&
                                 !str_ends_with($email, '@hay.net');

                if ($shouldSimulate) {
                    // Simulate email sending (PII removed from logs)
                    Log::info('Email simulated (test mode for non-Hay email)', [
                        'contract_id' => $id,
                        'reason' => 'Development/test mode - email does not end in @haymail.ca or @hay.net'
                    ]);
                    $messages[] = 'Contract email simulated (test mode).';
                } else {
                    // Actually send email
                    Mail::send('emails.contract', ['contract' => $contract], function ($message) use ($contract, $email, $mergedPdfContent, $remoteFilename) {
                        $message->to($email)
                            ->subject('Your Hay CIS Contract #' . $contract->id);
                        $message->attachData($mergedPdfContent, $remoteFilename, ['mime' => 'application/pdf']);
                    });

                    // Log without exposing PII (email address removed)
                    Log::info('Contract email sent with merged PDF', ['contract_id' => $id]);
                    $messages[] = 'Contract emailed to customer successfully.';
                }
            } catch (\Exception $e) {
                // Log without exposing PII (email address removed)
                Log::error('Failed to send contract email during finalization', [
                    'contract_id' => $id,
                    'error' => $e->getMessage()
                ]);
                $messages[] = 'Contract emailing failed: ' . $e->getMessage();
            }
        }

        // Cleanup files after successful upload
        $cleanupService = new ContractFileCleanupService();
        if ($cleanupService->canCleanup($contract)) {
            $cleanupResult = $cleanupService->cleanupContractFiles($contract);
            Log::info('Contract files cleaned up', [
                'contract_id' => $id,
                'deleted_count' => $cleanupResult['deleted_count'],
                'files' => $cleanupResult['deleted_files']
            ]);

            $messages[] = sprintf('%d sensitive file(s) securely deleted.', $cleanupResult['deleted_count']);
        }

        return redirect()->route('contracts.view', $id)->with('success', implode(' ', $messages));
    }
	
    public function createRevision($id)
    {
        $originalContract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('createRevision', $originalContract);

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

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('download', $contract);

        if ($contract->status !== 'finalized') {
            return redirect()->back()->with('error', 'Only finalized contracts can be uploaded to vault.');
        }
       
        if (!$contract->pdf_path || !Storage::disk('public')->exists($contract->pdf_path)) {
            return redirect()->back()->with('error', 'Contract PDF not found.');
        }
       
        $ftpService = new VaultFtpService();
        
        if ($ftpService->isTestMode()) {
            return redirect()->back()->with('info', 'Vault is in test mode. FTP uploads are simulated. Set VAULT_TEST_MODE=false in .env to enable real uploads.');
        }
        
        $remoteFilename = $ftpService->getRemoteFilename($contract);
        $result = $ftpService->uploadToVault($contract->pdf_path, $remoteFilename);
       
        if ($result['success']) {
            $contract->update([
                'ftp_to_vault' => true,
                'ftp_at' => now(),
                'vault_path' => $result['path'],
                'ftp_error' => null
            ]);
            
            $cleanupService = new ContractFileCleanupService();
            if ($cleanupService->canCleanup($contract)) {
                $cleanupResult = $cleanupService->cleanupContractFiles($contract);
                Log::info('Contract files cleaned up after manual FTP', [
                    'contract_id' => $id,
                    'deleted_count' => $cleanupResult['deleted_count']
                ]);
            }
           
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

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('download', $contract);

        if ($contract->status !== 'finalized') {
            return redirect()->back()->with('error', 'Contract must be finalized to download.');
        }
        
        try {
            $pdfService = app(ContractPdfService::class);
            $mergedPdfContent = $pdfService->generateMergedPdfContent($contract);
           
            $ftpService = new VaultFtpService();
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

		$contract = Contract::with([
			'ratePlan',
			'mobileInternetPlan',
			'addOns',
			'oneTimeFees',
			'subscriber.mobilityAccount.ivueAccount.customer',
			'activityType',
			'commitmentPeriod',
			'bellDevice',
			'locationModel',
			'updatedBy'
		])->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('view', $contract);
		
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', 'UTF-8');
		$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$config->set('HTML.Allowed', 'p,strong,em,ul,ol,li,a[href|title],br,div[class],span[class],table,tr,td,th,hr');
		$config->set('AutoFormat.AutoParagraph', true);
		$config->set('AutoFormat.Linkify', true);
		$config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true]);
		$config->set('Cache.SerializerPath', storage_path('htmlpurifier'));
		$purifier = new HTMLPurifier($config);
		
		if ($contract->ratePlan && $contract->ratePlan->features) {
			$contract->ratePlan->features = $purifier->purify($contract->ratePlan->features);
		}
		
		if ($contract->mobileInternetPlan && $contract->mobileInternetPlan->description) {
			$contract->mobileInternetPlan->description = $purifier->purify($contract->mobileInternetPlan->description);
		}
		
		// Financial calculations - DEFINE ALL VARIABLES FIRST
		$devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
		$deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
		$requiredUpfront = $contract->required_upfront_payment ?? 0;
		$optionalUpfront = $contract->optional_down_payment ?? 0;
		$deferredPayment = $contract->deferred_payment_amount ?? 0;

		$totalFinancedAmount = $deviceAmount - $requiredUpfront - $optionalUpfront;
		$remainingBalance = $totalFinancedAmount - $deferredPayment;
		$monthlyDevicePayment = $remainingBalance / 24;
		$earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
		$monthlyReduction = $monthlyDevicePayment;

		// Calculate buyout cost - factors in upfront payments customer already made
		$buyoutCost = ($devicePrice - $requiredUpfront - $optionalUpfront - $deferredPayment) / 24;
		
		// Get and format the cancellation policy from CommitmentPeriod
		$cancellationPolicy = '';
		if ($contract->commitmentPeriod && $contract->commitmentPeriod->cancellation_policy) {
			$cancellationPolicy = str_replace(
				[
					'{balance}',
					'{monthly_reduction}',
					'{start_date}',
					'{end_date}',
					'{buyout_cost}',
					'{device_return_option}'
				],
				[
					number_format($remainingBalance, 2),
					number_format($buyoutCost, 2),
					$contract->start_date ? $contract->start_date->format('M d, Y') : 'N/A',
					$contract->end_date ? $contract->end_date->format('M d, Y') : 'N/A',
					number_format($buyoutCost, 2),
					number_format($deferredPayment, 2)
				],
				$contract->commitmentPeriod->cancellation_policy
			);
		}
		
		$totalAddOnCost = $contract->addOns->sum('cost') ?? 0;
		$totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost') ?? 0;
		$minimumMonthlyCharge = ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) + $monthlyDevicePayment;
		$totalCost = $deviceAmount + 
					 (($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) * 24) + 
					 ($totalAddOnCost * 24) + 
					 $totalOneTimeFeeCost;		
		
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
			'monthlyReduction',
			'buyoutCost',
			'cancellationPolicy'	
		));
	}
    
    public function email($id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('download', $contract);

        if ($contract->status !== 'finalized') {
            return redirect()->back()->with('error', 'Contract must be finalized to email.');
        }
      
        $email = $contract->subscriber->mobilityAccount->ivueAccount->customer->email;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Log without exposing PII (email address removed)
            Log::error('Invalid or missing email for customer', ['contract_id' => $id, 'email_provided' => !empty($email)]);
            return redirect()->back()->with('error', 'No valid email address found for this customer.');
        }
      
        try {
            $pdfService = app(ContractPdfService::class);
            $mergedPdfContent = $pdfService->generateMergedPdfContent($contract);
            $ftpService = new VaultFtpService();
            $pdfFileName = $ftpService->getRemoteFilename($contract);
            
            Mail::send('emails.contract', ['contract' => $contract], function ($message) use ($contract, $email, $mergedPdfContent, $pdfFileName) {
                $message->to($email)
                    ->subject('Your Hay CIS Contract #' . $contract->id);
                $message->attachData($mergedPdfContent, $pdfFileName, ['mime' => 'application/pdf']);
            });

            // Log without exposing PII (email address removed)
            Log::info('Contract email sent with merged PDF', ['contract_id' => $id]);
            return redirect()->back()->with('success', 'Contract emailed successfully.');
        } catch (\Exception $e) {
            // Log without exposing PII (email address removed)
            Log::error('Failed to send contract email', ['contract_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
  
    private function getBellPricing($deviceId, $tier, $useDro = false)
    {
        if ($useDro) {
            return BellDroPricing::getPricing($deviceId, $tier);
        }
      
        return BellPricing::getPricing($deviceId, $tier);
    }
   
    // FINANCING AND DRO METHODS REMAIN UNCHANGED...
    // (Including all the financing and DRO methods from your original file)
    
    public function financingForm($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('sign', $contract);

        if (!$contract->requiresFinancing()) {
            return redirect()->route('contracts.view', $id)
                ->with('error', 'This contract does not require a financing form.');
        }
        
        return view('contracts.financing', compact('contract'));
    }
    
    public function signFinancing($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('sign', $contract);

        if (!$contract->requiresFinancing()) {
            return redirect()->route('contracts.view', $id)
                ->with('error', 'This contract does not require a financing form.');
        }
        
        if ($contract->financing_status !== 'pending') {
            return redirect()->route('contracts.financing.index', $id)
                ->with('error', 'Financing form cannot be signed at this time.');
        }
        
        return view('contracts.sign-financing', compact('contract'));
    }
    
    public function storeFinancingSignature(Request $request, $id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('sign', $contract);

        if ($contract->financing_status !== 'pending') {
            Log::warning('Financing form cannot be signed - wrong status', [
                'contract_id' => $id,
                'current_status' => $contract->financing_status
            ]);
            return redirect()->route('contracts.financing.index', $id)
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
            
            $contract->refresh();
            $this->generateFinancingPdf($contract);

            return redirect()->route('contracts.financing.csr-initial', $id)
                ->with('success', 'Financing form signed successfully. Please initial as CSR.');
               
        } catch (\Exception $e) {
            Log::error('Error in storeFinancingSignature', ['contract_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save signature: ' . $e->getMessage());
        }
    }
    
    public function finalizeFinancing($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('finalize', $contract);

        if ($contract->financing_status !== 'csr_initialed') {
            return redirect()->route('contracts.financing.index', $id)
                ->with('error', 'Financing form must be initialed by CSR before finalizing.');
        }

        $contract->update([
            'financing_status' => 'finalized',
            'updated_by' => auth()->id(),
        ]);

        $this->generateFinancingPdf($contract);

        Log::info('Financing form finalized', ['contract_id' => $id]);

        // Auto-redirect to next step based on what's required
        if ($contract->requiresDro() && $contract->dro_status === 'pending') {
            // Redirect to DRO signing
            return redirect()->route('contracts.dro.sign', $id)
                ->with('success', 'Financing form finalized. Please proceed with DRO signing.');
        } else {
            // Redirect to main contract signing
            return redirect()->route('contracts.sign', $id)
                ->with('success', 'Financing form finalized. Please proceed with contract signing.');
        }
    }
   
    public function signCsrFinancing($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('sign', $contract);

        if (!$contract->requiresFinancing() || $contract->financing_status !== 'customer_signed') {
            return redirect()->route('contracts.financing.index', $id)
                ->with('error', 'Financing form cannot be initialed at this time.');
        }
        
        return view('contracts.sign-financing-csr', compact('contract'));
    }
    
    public function storeCsrFinancingInitials(Request $request, $id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('sign', $contract);

        if ($contract->financing_status !== 'customer_signed') {
            return redirect()->route('contracts.financing.index', $id)
                ->with('error', 'Financing form cannot be initialed at this time.');
        }
        
        $request->validate([
            'initials' => 'required|string|regex:/^data:image\/png;base64,/',
        ]);
        
        try {
            $initials = $request->initials;
            $initialsPath = "initials/financing_contract_{$contract->id}_csr.png";
            $initialsData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $initials));
           
            if ($initialsData === false) {
                return redirect()->back()->with('error', 'Failed to process initials.');
            }
            
            Storage::disk('public')->put($initialsPath, $initialsData);
            
            $contract->update([
                'financing_csr_initials_path' => 'storage/' . $initialsPath,
                'financing_status' => 'csr_initialed',
                'financing_csr_initialed_at' => now(),
                'updated_by' => auth()->id(),
            ]);
            
            $contract->refresh();
            $this->generateFinancingPdf($contract);
            
            return redirect()->route('contracts.financing.index', $id)
                ->with('success', 'Financing form initialed successfully');
        } catch (\Exception $e) {
            Log::error('Error in storeCsrFinancingInitials', ['contract_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save initials: ' . $e->getMessage());
        }
    }
   
    public function downloadFinancing($id)
    {
        $contract = Contract::with([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'bellDevice'
        ])->findOrFail($id);

        // Authorization check - prevent IDOR vulnerability
        $this->authorize('download', $contract);

        if ($contract->financing_status !== 'finalized') {
            return redirect()->back()->with('error', 'Financing form must be finalized to download.');
        }
        
        if (!$contract->financing_pdf_path || !Storage::disk('public')->exists($contract->financing_pdf_path)) {
            return redirect()->back()->with('error', 'Financing PDF not found.');
        }
        
        $pdfContent = Storage::disk('public')->get($contract->financing_pdf_path);
        $fileName = str_replace('.pdf', '_financing.pdf',
            $contract->subscriber->last_name . '_' .
            $contract->subscriber->first_name . '_' .
            $contract->id . '.pdf'
        );
        
        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $fileName, ['Content-Type' => 'application/pdf']);
    }
    
    private function generateFinancingPdf($contract)
    {
        $html = view('contracts.financing', [
            'contract' => $contract,
            'pdf' => true
        ])->render();
        
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('dpi', 150);
        $options->set('defaultFont', 'sans-serif');
        $options->set('memory_limit', '512M');
        $options->set('chroot', base_path());
        $options->set('isPhpEnabled', false); // SECURITY: Never enable PHP in PDFs (prevents RCE)
        $options->set('margin_top', 10);
        $options->set('margin_bottom', 10);
        $options->set('margin_left', 10);
        $options->set('margin_right', 10);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();
        
        $canvas = $dompdf->getCanvas();
        $height = $canvas->get_height() + 80;
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 595, $height]);
        $dompdf->render();
        
        $pdfPath = "contracts/financing_contract_{$contract->id}.pdf";
        Storage::disk('public')->put($pdfPath, $dompdf->output());
        $contract->update(['financing_pdf_path' => $pdfPath]);
        
        Log::info('Financing PDF generated', ['contract_id' => $contract->id, 'path' => $pdfPath]);
    }
	   
	public function droForm($id)
	{
		$contract = Contract::with([
			'subscriber.mobilityAccount.ivueAccount.customer',
			'bellDevice'
		])->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('sign', $contract);

		if (!$contract->requiresDro()) {
			return redirect()->route('contracts.view', $id)
				->with('error', 'This contract does not require a DRO form.');
		}
		
		return view('contracts.dro', compact('contract'));
	}

	public function signDro($id)
	{
		$contract = Contract::with([
			'subscriber.mobilityAccount.ivueAccount.customer',
			'bellDevice'
		])->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('sign', $contract);

		if (!$contract->requiresDro()) {
			return redirect()->route('contracts.view', $id)
				->with('error', 'This contract does not require a DRO form.');
		}
		
		if ($contract->dro_status !== 'pending') {
			return redirect()->route('contracts.dro.index', $id)
				->with('error', 'DRO form cannot be signed at this time.');
		}
		
		return view('contracts.sign-dro', compact('contract'));
	}

	public function storeDroSignature(Request $request, $id)
	{
		$contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('sign', $contract);

		if ($contract->dro_status !== 'pending') {
			return redirect()->route('contracts.dro.index', $id)
				->with('error', 'DRO form cannot be signed at this time.');
		}
		
		$request->validate([
			'signature' => 'required|string|regex:/^data:image\/png;base64,/',
		]);
		
		try {
			$signature = $request->signature;
			$signaturePath = "signatures/dro_contract_{$contract->id}.png";
			$signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature));
			
			if ($signatureData === false) {
				return redirect()->back()->with('error', 'Failed to process signature.');
			}
			
			Storage::disk('public')->put($signaturePath, $signatureData);
			
			$contract->update([
				'dro_signature_path' => 'storage/' . $signaturePath,
				'dro_status' => 'customer_signed',
				'dro_signed_at' => now(),
				'updated_by' => auth()->id(),
			]);
			
			$contract->refresh();
			$this->generateDroPdf($contract);

			return redirect()->route('contracts.dro.csr-initial', $id)
				->with('success', 'DRO form signed successfully. Please initial as CSR.');
				
		} catch (\Exception $e) {
			Log::error('Error in storeDroSignature', ['contract_id' => $id, 'error' => $e->getMessage()]);
			return redirect()->back()->with('error', 'Failed to save signature: ' . $e->getMessage());
		}
	}

	public function signCsrDro($id)
	{
		$contract = Contract::with([
			'subscriber.mobilityAccount.ivueAccount.customer',
			'bellDevice'
		])->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('sign', $contract);

		if (!$contract->requiresDro() || $contract->dro_status !== 'customer_signed') {
			return redirect()->route('contracts.dro.index', $id)
				->with('error', 'DRO form cannot be initialed at this time.');
		}
		
		return view('contracts.sign-dro-csr', compact('contract'));
	}

	public function storeCsrDroInitials(Request $request, $id)
	{
		$contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('sign', $contract);

		if ($contract->dro_status !== 'customer_signed') {
			return redirect()->route('contracts.dro.index', $id)
				->with('error', 'DRO form cannot be initialed at this time.');
		}
		
		$request->validate([
			'initials' => 'required|string|regex:/^data:image\/png;base64,/',
		]);
		
		try {
			$initials = $request->initials;
			$initialsPath = "initials/dro_contract_{$contract->id}_csr.png";
			$initialsData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $initials));
			
			if ($initialsData === false) {
				return redirect()->back()->with('error', 'Failed to process initials.');
			}
			
			Storage::disk('public')->put($initialsPath, $initialsData);
			
			$contract->update([
				'dro_csr_initials_path' => 'storage/' . $initialsPath,
				'dro_status' => 'csr_initialed',
				'dro_csr_initialed_at' => now(),
				'updated_by' => auth()->id(),
			]);
			
			$contract->refresh();
			$this->generateDroPdf($contract);
			
			return redirect()->route('contracts.dro.index', $id)
				->with('success', 'DRO form initialed successfully');
		} catch (\Exception $e) {
			Log::error('Error in storeCsrDroInitials', ['contract_id' => $id, 'error' => $e->getMessage()]);
			return redirect()->back()->with('error', 'Failed to save initials: ' . $e->getMessage());
		}
	}

	public function finalizeDro($id)
	{
		$contract = Contract::with([
			'subscriber.mobilityAccount.ivueAccount.customer',
			'bellDevice'
		])->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('finalize', $contract);

		if ($contract->dro_status !== 'csr_initialed') {
			return redirect()->route('contracts.dro.index', $id)
				->with('error', 'DRO form must be initialed by CSR before finalizing.');
		}

		$contract->update([
			'dro_status' => 'finalized',
			'updated_by' => auth()->id(),
		]);

		$this->generateDroPdf($contract);

		Log::info('DRO form finalized', ['contract_id' => $id]);

		// Auto-redirect to main contract signing
		return redirect()->route('contracts.sign', $id)
			->with('success', 'DRO form finalized. Please proceed with contract signing.');
	}

	public function downloadDro($id)
	{
		$contract = Contract::with([
			'subscriber.mobilityAccount.ivueAccount.customer',
			'bellDevice'
		])->findOrFail($id);

		// Authorization check - prevent IDOR vulnerability
		$this->authorize('download', $contract);

		if ($contract->dro_status !== 'finalized') {
			return redirect()->back()->with('error', 'DRO form must be finalized to download.');
		}
		
		if (!$contract->dro_pdf_path || !Storage::disk('public')->exists($contract->dro_pdf_path)) {
			return redirect()->back()->with('error', 'DRO PDF not found.');
		}
		
		$pdfContent = Storage::disk('public')->get($contract->dro_pdf_path);
		$fileName = str_replace('.pdf', '_dro.pdf',
			$contract->subscriber->last_name . '_' .
			$contract->subscriber->first_name . '_' .
			$contract->id . '.pdf'
		);
		
		return response()->streamDownload(function () use ($pdfContent) {
			echo $pdfContent;
		}, $fileName, ['Content-Type' => 'application/pdf']);
	}

	private function generateDroPdf($contract)
	{
		$html = view('contracts.dro-pdf', [
			'contract' => $contract
		])->render();
		
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$options->set('dpi', 150);
		$options->set('defaultFont', 'sans-serif');
		$options->set('memory_limit', '512M');
		$options->set('chroot', base_path());
		$options->set('isPhpEnabled', false); // SECURITY: Never enable PHP in PDFs (prevents RCE)
		$options->set('margin_top', 10);
		$options->set('margin_bottom', 10);
		$options->set('margin_left', 10);
		$options->set('margin_right', 10);
		
		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html);
		$dompdf->setPaper('a4', 'portrait');
		$dompdf->render();
		
		$canvas = $dompdf->getCanvas();
		$height = $canvas->get_height() + 80;
		
		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html);
		$dompdf->setPaper([0, 0, 595, $height]);
		$dompdf->render();
		
		$pdfPath = "contracts/dro_contract_{$contract->id}.pdf";
		Storage::disk('public')->put($pdfPath, $dompdf->output());
		$contract->update(['dro_pdf_path' => $pdfPath]);
		
		Log::info('DRO PDF generated', ['contract_id' => $contract->id, 'path' => $pdfPath]);
	}
}