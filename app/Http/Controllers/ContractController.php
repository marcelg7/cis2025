<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\Shortcode;
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

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contract::with('subscriber.mobilityAccount.ivueAccount.customer', 'plan');

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
        if ($request->filled('plan')) {
            $query->whereHas('plan', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->input('plan') . '%');
            });
        }
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', $request->input('start_date'));
        }

        $contracts = $query->latest()->paginate(10)->appends($request->query());

        return view('contracts.index', compact('contracts'));
    }

    public function create($subscriberId): View
    {
        $subscriber = Subscriber::with('mobilityAccount.ivueAccount.customer')->findOrFail($subscriberId);
        $shortcodes = Shortcode::where('disabled', false)->where('slug', 'LIKE', 'cis-%')->get()->map(function($shortcode) {
            Log::info('Shortcode data: ', ['slug' => $shortcode->slug, 'data' => $shortcode->data]);
            $parts = explode('-', $shortcode->slug);
            array_shift($parts); // Remove 'cis'
            $deviceData = $shortcode->toArray();
            $deviceData['parsed'] = [
                'manufacturer' => $parts[0] ?? null,
                'model' => $parts[1] ?? null,
                'version' => $parts[2] ?? null,
                'device_storage' => isset($parts[3]) && !str_contains($parts[3], 'retail') ? $parts[3] : null,
                'extra_info' => isset($parts[4]) ? $parts[4] : (isset($parts[3]) && str_contains($parts[3], 'retail') ? $parts[3] : null),
            ];
            $cleanPrice = preg_replace('/[^0-9.]/', '', $shortcode->data ?? '0');
            $deviceData['price'] = is_numeric($cleanPrice) ? (float) $cleanPrice : 0;
            $deviceData['display'] = implode(', ', array_filter([
                $deviceData['parsed']['manufacturer'] ? "Manufacturer: " . $deviceData['parsed']['manufacturer'] : null,
                $deviceData['parsed']['model'] ? "Model: " . $deviceData['parsed']['model'] : null,
                $deviceData['parsed']['version'] ? "Version: " . $deviceData['parsed']['version'] : null,
                $deviceData['parsed']['device_storage'] ? "Storage: " . $deviceData['parsed']['device_storage'] : null,
                $deviceData['parsed']['extra_info'] ? "Extra: " . $deviceData['parsed']['extra_info'] : null,
                "Price: $" . number_format($deviceData['price'], 2),
            ]));
            return $deviceData;
        });
        $plans = Plan::where('is_active', true)->get();
        $activityTypes = ActivityType::where('is_active', true)->get();
        $commitmentPeriods = CommitmentPeriod::where('is_active', true)->get();
        $defaultFirstBillDate = now()->day >= 11 ? now()->addMonth()->startOfMonth()->addDays(10) : now()->startOfMonth()->addDays(10);

        return view('contracts.create', compact('subscriber', 'shortcodes', 'plans', 'activityTypes', 'commitmentPeriods', 'defaultFirstBillDate'));
    }

    public function store(Request $request, $subscriberId)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'activity_type_id' => 'required|exists:activity_types,id',
            'contract_date' => 'required|date',
            'location' => 'required|in:zurich,exeter,grand_bend',
            'shortcode_id' => 'nullable|exists:shortcodes,id',
            'sim_number' => 'nullable|string|max:50',
            'imei_number' => 'nullable|string|max:50',
            'amount_paid_for_device' => 'required|numeric|min:0',
            'agreement_credit_amount' => 'required|numeric|min:0',
            'required_upfront_payment' => 'required|numeric|min:0',
            'optional_down_payment' => 'nullable|numeric|min:0',
            'deferred_payment_amount' => 'nullable|numeric|min:0',
            'dro_amount' => 'nullable|numeric|min:0',
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

        $subscriber = Subscriber::with('mobilityAccount.ivueAccount.customer')->findOrFail($subscriberId);
        $shortcode = $request->shortcode_id ? Shortcode::find($request->shortcode_id) : null;
        $deviceData = $shortcode ? collect(explode('-', $shortcode->slug))->slice(1)->toArray() : [];

        $contract = Contract::create([
            'subscriber_id' => $subscriberId,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'activity_type_id' => $request->activity_type_id,
            'contract_date' => $request->contract_date,
            'location' => $request->location,
            'device_id' => null,
            'manufacturer' => $deviceData[0] ?? null,
            'model' => $deviceData[1] ?? null,
            'version' => $deviceData[2] ?? null,
            'device_storage' => isset($deviceData[3]) && !str_contains($deviceData[3], 'retail') ? $deviceData[3] : null,
            'extra_info' => isset($deviceData[4]) ? $deviceData[4] : (isset($deviceData[3]) && str_contains($deviceData[3], 'retail') ? $deviceData[3] : null),
            'device_price' => $shortcode ? (is_numeric($cleanPrice = preg_replace('/[^0-9.]/', '', $shortcode->data ?? '0')) ? (float) $cleanPrice : 0) : 0,
            'sim_number' => $request->sim_number,
            'imei_number' => $request->imei_number,
            'amount_paid_for_device' => $request->amount_paid_for_device,
            'agreement_credit_amount' => $request->agreement_credit_amount,
            'required_upfront_payment' => $request->required_upfront_payment,
            'optional_down_payment' => $request->optional_down_payment,
            'deferred_payment_amount' => $request->deferred_payment_amount,
            'dro_amount' => $request->dro_amount,
            'plan_id' => $request->plan_id,
            'commitment_period_id' => $request->commitment_period_id,
            'first_bill_date' => $request->first_bill_date,
            'status' => 'draft',
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

        $pdf = Pdf::loadView('contracts.pdf', ['contract' => $contract->load('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer')]);
        $pdfPath = "contracts/contract_{$contract->id}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());
        $contract->update(['pdf_path' => $pdfPath]);

        $customerId = $contract->subscriber->mobilityAccount->ivueAccount->customer_id;
        return redirect()->route('customers.show', $customerId)->with('success', 'Contract created successfully.');
    }

    public function edit($id): View
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        if ($contract->status !== 'draft') {
            return redirect()->route('contracts.view', $id)->with('error', 'Contract is not editable.');
        }
        $shortcodes = Shortcode::where('disabled', false)->where('slug', 'LIKE', 'cis-%')->get()->map(function($shortcode) {
            Log::info('Shortcode data: ', ['slug' => $shortcode->slug, 'data' => $shortcode->data]);
            $parts = explode('-', $shortcode->slug);
            array_shift($parts); // Remove 'cis'
            $deviceData = $shortcode->toArray();
            $deviceData['parsed'] = [
                'manufacturer' => $parts[0] ?? null,
                'model' => $parts[1] ?? null,
                'version' => $parts[2] ?? null,
                'device_storage' => isset($parts[3]) && !str_contains($parts[3], 'retail') ? $parts[3] : null,
                'extra_info' => isset($parts[4]) ? $parts[4] : (isset($parts[3]) && str_contains($parts[3], 'retail') ? $parts[3] : null),
            ];
            $cleanPrice = preg_replace('/[^0-9.]/', '', $shortcode->data ?? '0');
            $deviceData['price'] = is_numeric($cleanPrice) ? (float) $cleanPrice : 0;
            $deviceData['display'] = implode(', ', array_filter([
                $deviceData['parsed']['manufacturer'] ? "Manufacturer: " . $deviceData['parsed']['manufacturer'] : null,
                $deviceData['parsed']['model'] ? "Model: " . $deviceData['parsed']['model'] : null,
                $deviceData['parsed']['version'] ? "Version: " . $deviceData['parsed']['version'] : null,
                $deviceData['parsed']['device_storage'] ? "Storage: " . $deviceData['parsed']['device_storage'] : null,
                $deviceData['parsed']['extra_info'] ? "Extra: " . $deviceData['parsed']['extra_info'] : null,
                "Price: $" . number_format($deviceData['price'], 2),
            ]));
            return $deviceData;
        });
        $plans = Plan::where('is_active', true)->get();
        $activityTypes = ActivityType::where('is_active', true)->get();
        $commitmentPeriods = CommitmentPeriod::where('is_active', true)->get();
        $defaultFirstBillDate = now()->day >= 11 ? now()->addMonth()->startOfMonth()->addDays(10) : now()->startOfMonth()->addDays(10);

        return view('contracts.edit', compact('contract', 'shortcodes', 'plans', 'activityTypes', 'commitmentPeriods', 'defaultFirstBillDate'));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        if ($contract->status !== 'draft') {
            return redirect()->route('contracts.view', $id)->with('error', 'Contract is not editable.');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'activity_type_id' => 'required|exists:activity_types,id',
            'contract_date' => 'required|date',
            'location' => 'required|in:zurich,exeter,grand_bend',
            'shortcode_id' => 'nullable|exists:shortcodes,id',
            'sim_number' => 'nullable|string|max:50',
            'imei_number' => 'nullable|string|max:50',
            'amount_paid_for_device' => 'required|numeric|min:0',
            'agreement_credit_amount' => 'required|numeric|min:0',
            'required_upfront_payment' => 'required|numeric|min:0',
            'optional_down_payment' => 'nullable|numeric|min:0',
            'deferred_payment_amount' => 'nullable|numeric|min:0',
            'dro_amount' => 'nullable|numeric|min:0',
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

        $shortcode = $request->shortcode_id ? Shortcode::find($request->shortcode_id) : null;
        $deviceData = $shortcode ? collect(explode('-', $shortcode->slug))->slice(1)->toArray() : [];

        $contract->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'activity_type_id' => $request->activity_type_id,
            'contract_date' => $request->contract_date,
            'location' => $request->location,
            'manufacturer' => $deviceData[0] ?? null,
            'model' => $deviceData[1] ?? null,
            'version' => $deviceData[2] ?? null,
            'device_storage' => isset($deviceData[3]) && !str_contains($deviceData[3], 'retail') ? $deviceData[3] : null,
            'extra_info' => isset($deviceData[4]) ? $deviceData[4] : (isset($deviceData[3]) && str_contains($deviceData[3], 'retail') ? $deviceData[3] : null),
            'device_price' => $shortcode ? (is_numeric($cleanPrice = preg_replace('/[^0-9.]/', '', $shortcode->data ?? '0')) ? (float) $cleanPrice : 0) : 0,
            'sim_number' => $request->sim_number,
            'imei_number' => $request->imei_number,
            'amount_paid_for_device' => $request->amount_paid_for_device,
            'agreement_credit_amount' => $request->agreement_credit_amount,
            'required_upfront_payment' => $request->required_upfront_payment,
            'optional_down_payment' => $request->optional_down_payment,
            'deferred_payment_amount' => $request->deferred_payment_amount,
            'dro_amount' => $request->dro_amount,
            'plan_id' => $request->plan_id,
            'commitment_period_id' => $request->commitment_period_id,
            'first_bill_date' => $request->first_bill_date,
        ]);

        $contract->addOns()->delete();
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

        $contract->oneTimeFees()->delete();
        if ($request->has('one_time_fees')) {
            foreach ($request->one_time_fees as $fee) {
                ContractOneTimeFee::create([
                    'contract_id' => $contract->id,
                    'name' => $fee['name'],
                    'cost' => $fee['cost'],
                ]);
            }
        }

        $pdf = Pdf::loadView('contracts.pdf', ['contract' => $contract->load('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer')]);
        $pdfPath = "contracts/contract_{$contract->id}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());
        $contract->update(['pdf_path' => $pdfPath]);

        return redirect()->route('contracts.view', $contract->id)->with('success', 'Contract updated successfully.');
    }

    public function sign($id): View
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        if ($contract->status !== 'draft') {
            return redirect()->route('contracts.view', $id)->with('error', 'Contract cannot be signed.');
        }
        return view('contracts.sign', compact('contract'));
    }

    public function storeSignature(Request $request, $id)
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        if ($contract->status !== 'draft') {
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
                'signature_path' => $signaturePath,
                'status' => 'signed',
            ]);

            $pdf = Pdf::loadView('contracts.pdf', ['contract' => $contract->load('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer')]);
            $pdfPath = "contracts/contract_{$contract->id}.pdf";
            Storage::disk('public')->put($pdfPath, $pdf->output());
            $contract->update(['pdf_path' => $pdfPath]);
            Log::info('PDF regenerated with signature', ['contract_id' => $id, 'pdf_path' => $pdfPath]);

            return redirect()->route('contracts.view', $id)->with('success', 'Contract signed successfully.');
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

        $contract->update(['status' => 'finalized']);

        $pdf = Pdf::loadView('contracts.pdf', ['contract' => $contract->load('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer')]);
        $pdfPath = "contracts/contract_{$contract->id}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());
        $contract->update(['pdf_path' => $pdfPath]);

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
        $contract = Contract::with('addOns', 'oneTimeFees', 'subscriber.mobilityAccount.ivueAccount.customer')->findOrFail($id);
        if ($contract->status !== 'finalized') {
            return redirect()->back()->with('error', 'Contract must be finalized to download.');
        }
        if ($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path)) {
            return Storage::disk('public')->download($contract->pdf_path);
        }
        return redirect()->back()->with('error', 'PDF not found.');
    }

    public function view($id): View
    {
        $contract = Contract::with('subscriber.mobilityAccount.ivueAccount.customer', 'activityType', 'plan', 'commitmentPeriod', 'addOns', 'oneTimeFees')->findOrFail($id);
        $totalAddOnCost = $contract->addOns->sum('cost');
        $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost');
        $totalFinancingCost = ($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0) + ($contract->deferred_payment_amount ?? 0);
        $totalCost = ($contract->device_price ?? 0) + $totalAddOnCost + $totalOneTimeFeeCost + $totalFinancingCost;

        return view('contracts.view', compact('contract', 'totalAddOnCost', 'totalOneTimeFeeCost', 'totalFinancingCost', 'totalCost'));
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
}