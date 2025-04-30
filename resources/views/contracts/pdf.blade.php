<!DOCTYPE html>
<html>
<head>
    <title>Contract #{{ $contract->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { font-size: 24px; color: #333; }
        h2 { font-size: 18px; color: #555; margin-top: 20px; }
        p, li { font-size: 14px; color: #666; }
        ul { margin: 10px 0; padding-left: 20px; }
        .section { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Contract #{{ $contract->id }}</h1>
    <div class="section">
        <h2>Contract Details</h2>
        <p><strong>Start Date:</strong> {{ $contract->start_date->format('Y-m-d') }}</p>
        <p><strong>End Date:</strong> {{ $contract->end_date->format('Y-m-d') }}</p>
        <p><strong>Activity Type:</strong> {{ $contract->activityType->name }}</p>
        <p><strong>Contract Date:</strong> {{ $contract->contract_date->format('Y-m-d') }}</p>
        <p><strong>Location:</strong> {{ ucfirst($contract->location) }}</p>
    </div>
    <div class="section">
        <h2>Device Details</h2>
        <p><strong>Device:</strong> {{ $contract->device ? $contract->device->manufacturer . ' ' . $contract->device->model : 'None' }}</p>
        <p><strong>SIM #:</strong> {{ $contract->sim_number ?? 'N/A' }}</p>
        <p><strong>IMEI #:</strong> {{ $contract->imei_number ?? 'N/A' }}</p>
        <p><strong>Amount Paid:</strong> ${{ number_format($contract->amount_paid_for_device, 2) }}</p>
        <p><strong>Agreement Credit:</strong> ${{ number_format($contract->agreement_credit_amount, 2) }}</p>
    </div>
    <div class="section">
        <h2>Plan Section</h2>
        <p><strong>Plan:</strong> {{ $contract->plan->name }}</p>
        <p><strong>Commitment Period:</strong> {{ $contract->commitmentPeriod->name }}</p>
        <p><strong>First Bill Date:</strong> {{ $contract->first_bill_date->format('Y-m-d') }}</p>
    </div>
    <div class="section">
        <h2>Plan Add-ons</h2>
        @if ($contract->addOns->isEmpty())
            <p>No add-ons</p>
        @else
            <ul>
                @foreach ($contract->addOns as $addOn)
                    <li>{{ $addOn->name }} ({{ $addOn->code }}): ${{ number_format($addOn->cost, 2) }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="section">
        <h2>One Time Fees</h2>
        @if ($contract->oneTimeFees->isEmpty())
            <p>No fees</p>
        @else
            <ul>
                @foreach ($contract->oneTimeFees as $fee)
                    <li>{{ $fee->name }}: ${{ number_format($fee->cost, 2) }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="section">
        <h2>Cancellation Policy</h2>
        <p>{{ $contract->commitmentPeriod->cancellation_policy ?? 'N/A' }}</p>
    </div>
</body>
</html>