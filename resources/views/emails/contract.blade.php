<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #1a73e8;
        }
        p {
            line-height: 1.6;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hay CIS Contract #{{ $contract->id }}</h1>
        <p>Dear {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name ?? 'Customer' }},</p>
        <p>Thank you for choosing Hay Communications. Attached is your contract (#{{ $contract->id }}) for your records.</p>
        <p><strong>Summary:</strong></p>
        <ul>
            <li><strong>Plan:</strong> {{ $contract->plan->name ?? 'N/A' }} (${{ number_format($contract->plan->price ?? 0, 2) }})</li>
            <li><strong>Device:</strong> {{ implode(', ', array_filter([
                $contract->manufacturer ? "Manufacturer: " . $contract->manufacturer : null,
                $contract->model ? "Model: " . $contract->model : null,
                $contract->version ? "Version: " . $contract->version : null,
                $contract->device_storage ? "Storage: " . $contract->device_storage : null,
                $contract->extra_info ? "Extra: " . $contract->extra_info : null,
            ])) }}</li>
            <li><strong>Start Date:</strong> {{ $contract->start_date }}</li>
            <li><strong>Total Cost:</strong> ${{ number_format(($contract->device_price ?? 0) + $contract->addOns->sum('cost') + $contract->oneTimeFees->sum('cost') + ($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0) + ($contract->deferred_payment_amount ?? 0), 2) }}</li>
        </ul>
        <p>Please review the attached PDF for full details. Contact us at support@hay.net if you have any questions.</p>
        <div class="footer">
            <p>Hay Communications<br>1234 Example St, Zurich, ON<br>Email: support@hay.net</p>
        </div>
    </div>
</body>
</html>