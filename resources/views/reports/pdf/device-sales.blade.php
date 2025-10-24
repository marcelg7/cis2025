<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Device Sales Report</title>
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
        .breakdown-section {
            margin-bottom: 20px;
        }
        .breakdown-item {
            margin-bottom: 5px;
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
        <h1>Device Sales Report</h1>
        <p class="subtitle">{{ $startDate->format('F Y') }}</p>
        <p class="subtitle">Generated: {{ now()->format('F d, Y g:i A') }}</p>
    </div>

    <div class="summary-stats">
        <div class="stat-row">
            <span class="stat-label">Total Devices Sold:</span>
            <span>{{ $totalDevicesSold }}</span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Total Revenue:</span>
            <span>${{ number_format($totalRevenue, 2) }}</span>
        </div>
        @if($totalDevicesSold > 0)
            <div class="stat-row">
                <span class="stat-label">Average Device Price:</span>
                <span>${{ number_format($totalRevenue / $totalDevicesSold, 2) }}</span>
            </div>
        @endif
    </div>

    <h2>Sales by Device</h2>
    <table>
        <thead>
            <tr>
                <th>Device Name</th>
                <th>Units Sold</th>
                <th>Total Revenue</th>
                <th>Avg Price</th>
                <th>% of Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesByDevice as $deviceName => $stats)
                <tr>
                    <td>{{ $deviceName }}</td>
                    <td>{{ $stats['count'] }}</td>
                    <td>${{ number_format($stats['total_revenue'], 2) }}</td>
                    <td>${{ number_format($stats['avg_price'], 2) }}</td>
                    <td>{{ number_format(($stats['count'] / $totalDevicesSold) * 100, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Sales by Pricing Type</h2>
    <div class="breakdown-section">
        @foreach($salesByPricingType as $type => $count)
            <div class="breakdown-item">
                <strong>{{ ucfirst($type) }}:</strong> {{ $count }} devices ({{ number_format(($count / $totalDevicesSold) * 100, 1) }}%)
            </div>
        @endforeach
    </div>

    <h2>Sales by CSR</h2>
    <div class="breakdown-section">
        @foreach($salesByUser->sortDesc() as $user => $count)
            <div class="breakdown-item">
                <strong>{{ $user ?? 'Unknown' }}:</strong> {{ $count }} devices ({{ number_format(($count / $totalDevicesSold) * 100, 1) }}%)
            </div>
        @endforeach
    </div>

    <div class="footer">
        <p>Hay Communications - Contract Information System</p>
        <p>This report is confidential and intended for internal use only.</p>
    </div>
</body>
</html>
