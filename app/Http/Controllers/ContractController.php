<?php
namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\Device;
use App\Models\Plan;
use App\Models\ActivityType;
use App\Models\CommitmentPeriod;
use App\Models\Contract;
use App\Models\ContractAddOn;
use App\Models\ContractOneTimeFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContractController extends Controller {
    public function create($subscriberId): View {
        $subscriber = Subscriber::findOrFail($subscriberId);
        $devices = Device::where('is_active', true)->get();
        $plans = Plan::where('is_active', true)->get();
        $activityTypes = ActivityType::where('is_active', true)->get();
        $commitmentPeriods = CommitmentPeriod::where('is_active', true)->get();
        $defaultFirstBillDate = now()->day >= 11 ? now()->addMonth()->startOfMonth()->addDays(10) : now()->startOfMonth()->addDays(10);
        return view('contracts.create', compact('subscriber', 'devices', 'plans', 'activityTypes', 'commitmentPeriods', 'defaultFirstBillDate'));
    }
	
public function ftp($id)
{
    $contract = Contract::findOrFail($id);
    
    // This is a placeholder for future implementation
    // In the future, you'll add code here to send the contract to your storage vault via FTP
    
    // For now, just redirect back with a message
    return redirect()->back()->with('info', 'FTP to storage vault functionality will be implemented soon.');
}

public function store(Request $request, $subscriberId) {
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'activity_type_id' => 'required|exists:activity_types,id',
        'contract_date' => 'required|date',
        'location' => 'required|in:zurich,exeter,grand_bend',
        'device_id' => 'nullable|exists:devices,id',
        'sim_number' => 'nullable|string|max:50',
        'imei_number' => 'nullable|string|max:50',
        'amount_paid_for_device' => 'required|numeric|min:0',
        'agreement_credit_amount' => 'required|numeric|min:0',
        'plan_id' => 'required|exists:plans,id',
        'commitment_period_id' => 'required|exists:commitment_periods,id',
        'first_bill_date' => 'required|date',
        'add_ons' => 'nullable|array',
        'add_ons.*.name' => 'required_with:add_ons.*.code|required_with:add_ons.*.cost|string|max:100',
        'add_ons.*.code' => 'required_with:add_ons.*.name|required_with:add_ons.*.cost|string|max:50',
        'add_ons.*.cost' => 'required_with:add_ons.*.name|required_with:add_ons.*.code|numeric|min:0',
        'one_time_fees' => 'nullable|array',
        'one_time_fees.*.name' => 'required_with:one_time_fees.*.cost|string|max:100',
        'one_time_fees.*.cost' => 'required_with:one_time_fees.*.name|numeric|min:0',
    ]);

    $contract = Contract::create([
        'subscriber_id' => $subscriberId,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'activity_type_id' => $request->activity_type_id,
        'contract_date' => $request->contract_date,
        'location' => $request->location,
        'device_id' => $request->device_id,
        'sim_number' => $request->sim_number,
        'imei_number' => $request->imei_number,
        'amount_paid_for_device' => $request->amount_paid_for_device,
        'agreement_credit_amount' => $request->agreement_credit_amount,
        'plan_id' => $request->plan_id,
        'commitment_period_id' => $request->commitment_period_id,
        'first_bill_date' => $request->first_bill_date,
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

    // Generate PDF
    $pdf = Pdf::loadView('contracts.pdf', ['contract' => $contract->load('addOns', 'oneTimeFees')]);
    $pdfPath = "contracts/contract_{$contract->id}.pdf";
    Storage::disk('public')->put($pdfPath, $pdf->output());
    $contract->update(['pdf_path' => $pdfPath]);

    // Redirect to the customer show page using the correct customer ID
    $customerId = $contract->subscriber->mobilityAccount->ivueAccount->customer_id;
    return redirect()->route('customers.show', $customerId)->with('success', 'Contract created successfully.');
}
    public function download($id) {
        $contract = Contract::findOrFail($id);
        if ($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path)) {
            return Storage::disk('public')->download($contract->pdf_path);
        }
        return redirect()->back()->with('error', 'PDF not found.');
    }

    public function view($id): View {
        $contract = Contract::with('subscriber', 'activityType', 'device', 'plan', 'commitmentPeriod', 'addOns', 'oneTimeFees')->findOrFail($id);
        return view('contracts.view', compact('contract'));
    }

    public function email($id) {
        $contract = Contract::with('subscriber')->findOrFail($id);
        if (!$contract->pdf_path || !Storage::disk('public')->exists($contract->pdf_path)) {
            return redirect()->back()->with('error', 'PDF not found.');
        }

        $email = $contract->subscriber->email ?? 'default@example.com'; // Fallback if email is missing
        Mail::raw('Please find your contract attached.', function ($message) use ($contract, $email) {
            $message->to($email)
                    ->subject('Your Contract')
                    ->attach(storage_path('app/public/' . $contract->pdf_path));
        });

        return redirect()->back()->with('success', 'Contract emailed successfully.');
    }
}