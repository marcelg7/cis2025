<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Plan Comparison</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 5px;
        }
        h2 {
            color: #4b5563;
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .subtitle {
            color: #6b7280;
            font-size: 14px;
        }
        .best-value {
            background-color: #d1fae5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 2px solid #10b981;
        }
        .best-value h3 {
            color: #065f46;
            margin: 0 0 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        th {
            background-color: #e5e7eb;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .highlight {
            background-color: #dbeafe !important;
            font-weight: bold;
        }
        .savings {
            color: #059669;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Contract Calculator - Plan Comparison</h1>
        <p class="subtitle">Generated: {{ now()->format('F d, Y g:i A') }}</p>
    </div>

    <div class="best-value">
        <h3>Recommended: {{ $bestValue['plan_name'] }}</h3>
        <p><strong>Total 24-Month Cost:</strong> ${{ number_format($bestValue['costs']['total_24_months'], 2) }}</p>
        <p><strong>Average Monthly Cost:</strong> ${{ number_format($bestValue['costs']['avg_monthly'], 2) }}</p>
    </div>

    <h2>Plan Comparison</h2>
    <table>
        <thead>
            <tr>
                <th>Feature</th>
                @foreach($calculations as $calc)
                    <th>{{ $calc['plan_name'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Data Allowance</strong></td>
                @foreach($calculations as $calc)
                    <td>{{ $calc['features']['data'] }}</td>
                @endforeach
            </tr>

            <tr class="highlight">
                <td><strong>Monthly Cost (Avg)</strong></td>
                @foreach($calculations as $calc)
                    <td>${{ number_format($calc['costs']['avg_monthly'], 2) }}</td>
                @endforeach
            </tr>

            @if(collect($calculations)->some(fn($c) => $c['costs']['upfront'] > 0))
            <tr>
                <td><strong>Upfront Costs</strong></td>
                @foreach($calculations as $calc)
                    <td>${{ number_format($calc['costs']['upfront'], 2) }}</td>
                @endforeach
            </tr>
            @endif

            <tr class="highlight">
                <td><strong>24-Month Total</strong></td>
                @foreach($calculations as $calc)
                    <td>${{ number_format($calc['costs']['total_24_months'], 2) }}</td>
                @endforeach
            </tr>

            @if(collect($calculations)->some(fn($c) => $c['costs']['credit_total'] > 0))
            <tr>
                <td><strong>Credit Savings</strong></td>
                @foreach($calculations as $calc)
                    <td class="savings">
                        @if($calc['costs']['credit_total'] > 0)
                            -${{ number_format($calc['costs']['credit_total'], 2) }}
                            ({{ $calc['costs']['credit_duration'] }} months)
                        @else
                            -
                        @endif
                    </td>
                @endforeach
            </tr>
            @endif

            <tr>
                <td><strong>Savings vs Most Expensive</strong></td>
                @php
                    $maxCost = collect($calculations)->max('costs.total_24_months');
                @endphp
                @foreach($calculations as $calc)
                    @php
                        $savings = $maxCost - $calc['costs']['total_24_months'];
                    @endphp
                    <td class="savings">
                        @if($savings > 0)
                            -${{ number_format($savings, 2) }}
                        @else
                            $0.00
                        @endif
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <h2>Plan Details</h2>
    @foreach($calculations as $calc)
        <h3>{{ $calc['plan_name'] }}</h3>
        <ul>
            <li><strong>Data:</strong> {{ $calc['features']['data'] }}</li>
            <li><strong>Canada-Wide Calling:</strong> {{ $calc['features']['canada_wide'] ? 'Yes' : 'No' }}</li>
            <li><strong>Unlimited Text:</strong> {{ $calc['features']['text'] ? 'Yes' : 'No' }}</li>
            <li><strong>Unlimited Voice:</strong> {{ $calc['features']['voice'] ? 'Yes' : 'No' }}</li>
            @if($calc['costs']['credit_total'] > 0)
                <li><strong>Credit Savings:</strong> ${{ number_format($calc['costs']['credit_total'], 2) }} over {{ $calc['costs']['credit_duration'] }} months</li>
            @endif
        </ul>
    @endforeach

    <div class="footer">
        <p>Hay Communications - Contract Information System</p>
        <p>This comparison is for informational purposes only. Final pricing may vary.</p>
    </div>
</body>
</html>
