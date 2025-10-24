@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Contract Analytics</h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                All Contracts
            </span>
        </div>

        <!-- Time Period Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Contracts Today -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Contracts Today</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $contractsToday }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts This Week -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Contracts This Week</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $contractsThisWeek }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts This Month -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Contracts This Month</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $contractsThisMonth }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Revenue Projection</h3>
                        <p class="text-sm text-gray-500">From finalized contracts only</p>
                    </div>
                    <form method="GET" action="{{ route('analytics') }}">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="include_taxes" value="1" {{ $includeTaxes ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Include estimated taxes (13%)</span>
                        </label>
                    </form>
                </div>
                <div class="text-3xl font-bold text-gray-900">
                    ${{ number_format($includeTaxes ? $revenueWithTaxes : $totalRevenue, 2) }}
                </div>
                @if($includeTaxes)
                    <p class="text-sm text-gray-500 mt-1">
                        Base: ${{ number_format($totalRevenue, 2) }} + Tax: ${{ number_format($revenueWithTaxes - $totalRevenue, 2) }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Contracts by Status Chart -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contracts by Status</h3>
                    <div style="height: 300px; position: relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Contracts Trend Chart -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contracts Trend (Last 30 Days)</h3>
                    <div style="height: 300px; position: relative;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Items Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Most Popular Devices -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Most Popular Devices (This Month)</h3>
                    <div class="space-y-3">
                        @forelse($popularDevices as $device)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">{{ $device['name'] }}</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $device['count'] }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No device data available</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Most Popular Rate Plans -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Most Popular Rate Plans (This Month)</h3>
                    <div class="space-y-3">
                        @forelse($popularPlans as $plan)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">{{ $plan['name'] }}</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $plan['count'] }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No plan data available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- CSR Performance -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">CSR Activity (Contracts Modified)</h3>
                <p class="text-sm text-gray-500 mb-4">Based on last user to update each contract</p>
                <div style="height: 300px; position: relative;">
                    <canvas id="csrChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Ensure this only runs once
        if (!window.analyticsChartsLoaded) {
            window.analyticsChartsLoaded = true;

        document.addEventListener('DOMContentLoaded', function() {
            // Status Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const statusData = @json($contractsByStatus);
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(statusData).map(status => status.charAt(0).toUpperCase() + status.slice(1)),
                        datasets: [{
                            data: Object.values(statusData),
                            backgroundColor: [
                                '#F59E0B', // draft - amber
                                '#3B82F6', // signed - blue
                                '#10B981', // finalized - green
                                '#EF4444', // cancelled - red
                                '#6B7280'  // other - gray
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 0
                        }
                    }
                });
            }

            // Trend Chart
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                const trendData = @json($contractsTrend);

                // Fill in missing dates with 0 contracts
                const last30Days = [];
                const counts = [];
                const today = new Date();

                for (let i = 29; i >= 0; i--) {
                    const date = new Date(today);
                    date.setDate(date.getDate() - i);
                    const dateStr = date.toISOString().split('T')[0];
                    last30Days.push(dateStr);
                    counts.push(trendData[dateStr] || 0);
                }

                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: last30Days.map(date => {
                            const d = new Date(date);
                            return (d.getMonth() + 1) + '/' + d.getDate();
                        }),
                        datasets: [{
                            label: 'Contracts Created',
                            data: counts,
                            borderColor: '#6366F1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        },
                        animation: {
                            duration: 0
                        }
                    }
                });
            }

            // CSR Performance Chart
            const csrCtx = document.getElementById('csrChart');
            if (csrCtx) {
                const csrData = @json($csrPerformance);
                new Chart(csrCtx, {
                    type: 'bar',
                    data: {
                        labels: csrData.map(item => item.name),
                        datasets: [{
                            label: 'Contracts Created',
                            data: csrData.map(item => item.count),
                            backgroundColor: '#8B5CF6',
                            borderColor: '#7C3AED',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        animation: {
                            duration: 0
                        }
                    }
                });
            }
        });

        } // End of analyticsChartsLoaded check
    </script>
@endsection
