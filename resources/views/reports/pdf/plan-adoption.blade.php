<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Plan Adoption Report</title>
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
        .summary-stats {
            background-color: #f3f4f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .stat-label {
            font-weight: bold;
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
        <h1>Plan Adoption Report</h1>
        <p class="subtitle">{{ $startDate->format('F Y') }}</p>
        <p class="subtitle">Generated: {{ now()->format('F d, Y g:i A') }}</p>
    </div>

    <div class="summary-stats">
        <div class="stat-row">
            <span class="stat-label">Total Contracts:</span>
            <span>{{ $totalContracts }}</span>
        </div>
        <div class="stat-row">
            <span class="stat-label">BYOD Contracts:</span>
            <span>{{ $byodVsDevice['BYOD'] }} ({{ $totalContracts > 0 ? number_format(($byodVsDevice['BYOD'] / $totalContracts) * 100, 1) : 0 }}%)</span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Device Contracts:</span>
            <span>{{ $byodVsDevice['Device'] }} ({{ $totalContracts > 0 ? number_format(($byodVsDevice['Device'] / $totalContracts) * 100, 1) : 0 }}%)</span>
        </div>
    </div>

    <h2>Rate Plan Adoption</h2>
    @if($ratePlanAdoption->isEmpty())
        <p>No rate plan data available for this period.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th>Subscriptions</th>
                    <th>Revenue</th>
                    <th>Avg Price</th>
                    <th>Market Share</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratePlanAdoption as $planName => $stats)
                    <tr>
                        <td>{{ $planName }}</td>
                        <td>{{ $stats['count'] }}</td>
                        <td>${{ number_format($stats['revenue'], 2) }}</td>
                        <td>${{ number_format($stats['revenue'] / $stats['count'], 2) }}</td>
                        <td>{{ number_format(($stats['count'] / $totalContracts) * 100, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Mobile Internet Plan Adoption</h2>
    @if($internetAdoption->isEmpty())
        <p>No mobile internet plan data available for this period.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th>Subscriptions</th>
                    <th>Revenue</th>
                    <th>Avg Price</th>
                    <th>Market Share</th>
                </tr>
            </thead>
            <tbody>
                @foreach($internetAdoption as $planName => $stats)
                    <tr>
                        <td>{{ $planName }}</td>
                        <td>{{ $stats['count'] }}</td>
                        <td>${{ number_format($stats['revenue'], 2) }}</td>
                        <td>${{ number_format($stats['revenue'] / $stats['count'], 2) }}</td>
                        <td>{{ number_format(($stats['count'] / $totalContracts) * 100, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Hay Communications - Contract Information System</p>
        <p>This report is confidential and intended for internal use only.</p>
    </div>
</body>
</html>
