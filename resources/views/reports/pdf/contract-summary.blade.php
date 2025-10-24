<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contract Summary Report</title>
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
        <h1>Contract Summary Report</h1>
        <p class="subtitle">{{ $startDate->format('F Y') }}</p>
        <p class="subtitle">Generated: {{ now()->format('F d, Y g:i A') }}</p>
    </div>

    <div class="summary-stats">
        <div class="stat-row">
            <span class="stat-label">Total Contracts:</span>
            <span>{{ $totalContracts }}</span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Total Monthly Revenue:</span>
            <span>${{ number_format($totalRevenue, 2) }}</span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Total Device Revenue:</span>
            <span>${{ number_format($totalDeviceRevenue, 2) }}</span>
        </div>
    </div>

    <h2>Contracts by Activity Type</h2>
    <div class="breakdown-section">
        @foreach($contractsByType as $type => $count)
            <div class="breakdown-item">
                <strong>{{ $type }}:</strong> {{ $count }} ({{ number_format(($count / $totalContracts) * 100, 1) }}%)
            </div>
        @endforeach
    </div>

    <h2>Contracts by Location</h2>
    <div class="breakdown-section">
        @foreach($contractsByLocation as $location => $count)
            <div class="breakdown-item">
                <strong>{{ $location ?? 'Unknown' }}:</strong> {{ $count }} ({{ number_format(($count / $totalContracts) * 100, 1) }}%)
            </div>
        @endforeach
    </div>

    <h2>Contract Details</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Activity</th>
                <th>Device</th>
                <th>Plan Revenue</th>
                <th>Device Revenue</th>
                <th>Location</th>
                <th>CSR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contracts as $contract)
                @php
                    $customer = $contract->subscriber->mobilityAccount->ivueAccount->customer ?? null;
                @endphp
                <tr>
                    <td>{{ $contract->contract_date->format('M d, Y') }}</td>
                    <td>{{ $customer ? $customer->display_name : 'N/A' }}</td>
                    <td>{{ $contract->activityType->name ?? 'N/A' }}</td>
                    <td>{{ $contract->bellDevice->device_name ?? 'BYOD' }}</td>
                    <td>${{ number_format(($contract->rate_plan_price ?? 0) + ($contract->mobile_internet_price ?? 0), 2) }}</td>
                    <td>${{ number_format($contract->bell_retail_price ?? 0, 2) }}</td>
                    <td>{{ $contract->locationModel->name ?? 'N/A' }}</td>
                    <td>{{ $contract->updatedBy->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Hay Communications - Contract Information System</p>
        <p>This report is confidential and intended for internal use only.</p>
    </div>
</body>
</html>
