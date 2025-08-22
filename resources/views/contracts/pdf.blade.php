<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contract #{{ $contract->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        h1, h2 {
            color: #1a73e8;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            border-bottom: 2px solid #1a73e8;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .total {
            font-weight: bold;
        }
        img.signature {
            max-width: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Contract #{{ $contract->id }}</h1>

    <div class="section">
        <h2>Contract Details</h2>
        <p><strong>Customer:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name ?? 'N/A' }}</p>
        <p><strong>Start Date:</strong> {{ $contract->start_date }}</p>
        <p><strong>End Date:</strong> {{ $contract->end_date ?? 'N/A' }}</p>
        <p><strong>Location:</strong> {{ ucfirst($contract->location) }}</p>
        <p><strong>Plan:</strong> {{ $contract->plan->name ?? 'N/A' }} (${{ number_format($contract->plan->price ?? 0, 2) }})</p>
        <p><strong>Commitment Period:</strong> {{ $contract->commitmentPeriod->name ?? 'N/A' }}</p>
        <p><strong>First Bill Date:</strong> {{ $contract->first_bill_date }}</p>
        <p><strong>Activity Type:</strong> {{ $contract->activityType->name ?? 'N/A' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($contract->status) }}</p>
    </div>

    <div class="section">
        <h2>Device Details</h2>
        <p><strong>Device:</strong> {{ implode(', ', array_filter([
            $contract->manufacturer ? "Manufacturer: " . $contract->manufacturer : null,
            $contract->model ? "Model: " . $contract->model : null,
            $contract->version ? "Version: " . $contract->version : null,
            $contract->device_storage ? "Storage: " . $contract->device_storage : null,
            $contract->extra_info ? "Extra: " . $contract->extra_info : null,
        ])) }}</p>
        <p><strong>Device Retail Price:</strong> ${{ number_format($contract->device_price ?? 0, 2) }}</p>
        <p><strong>SIM #:</strong> {{ $contract->sim_number ?? 'N/A' }}</p>
        <p><strong>IMEI #:</strong> {{ $contract->imei_number ?? 'N/A' }}</p>
        <p><strong>Amount Paid for Device:</strong> ${{ number_format($contract->amount_paid_for_device ?? 0, 2) }}</p>
        <p><strong>Agreement Credit Amount:</strong> ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }}</p>
    </div>

    <div class="section">
        <h2>Hay Financing</h2>
        <p><strong>Required Up-front Payment:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
        <p><strong>Optional Down Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
        <p><strong>Deferred Payment Amount:</strong> ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}</p>
        <p><strong>DRO Amount:</strong> ${{ number_format($contract->dro_amount ?? 0, 2) }}</p>
        <p><strong>Total Financing Cost:</strong> ${{ number_format(($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0) + ($contract->deferred_payment_amount ?? 0), 2) }}</p>
    </div>

    @if($contract->addOns->count())
        <div class="section">
            <h2>Add-ons</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contract->addOns as $addOn)
                        <tr>
                            <td>{{ $addOn->name }}</td>
                            <td>{{ $addOn->code }}</td>
                            <td>${{ number_format($addOn->cost, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2" class="total">Total Add-on Cost</td>
                        <td class="total">${{ number_format($contract->addOns->sum('cost'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if($contract->oneTimeFees->count())
        <div class="section">
            <h2>One-Time Fees</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contract->oneTimeFees as $fee)
                        <tr>
                            <td>{{ $fee->name }}</td>
                            <td>${{ number_format($fee->cost, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="total">Total One-Time Fee Cost</td>
                        <td class="total">${{ number_format($contract->oneTimeFees->sum('cost'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <div class="section">
        <h2>Total Cost</h2>
        <p><strong>Total Contract Cost:</strong> ${{ number_format(($contract->device_price ?? 0) + $contract->addOns->sum('cost') + $contract->oneTimeFees->sum('cost') + ($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0) + ($contract->deferred_payment_amount ?? 0), 2) }}</p>
    </div>

    @php
        $signaturePath = $contract->signature_path ? storage_path('app/public/' . $contract->signature_path) : null;
        if ($signaturePath && !file_exists($signaturePath)) {
            \Illuminate\Support\Facades\Log::error('Signature file not found', ['contract_id' => $contract->id, 'path' => $signaturePath]);
        }
    @endphp
    @if($signaturePath && file_exists($signaturePath))
        <div class="section">
            <h2>Signature</h2>
            <img src="{{ $signaturePath }}" alt="Signature" class="signature">
        </div>
    @else
        <div class="section">
            <h2>Signature</h2>
            <p>No signature available.</p>
        </div>
    @endif
</body>
</html>