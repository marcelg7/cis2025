@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <a href="{{ route('bell-pricing.index') }}" class="text-indigo-600 hover:text-indigo-900">
            ← Back to Devices
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Pricing History</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $device->name }}</p>
        </div>
    </div>

    <!-- SmartPay Pricing History -->
    @if($device->pricing->count() > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">SmartPay Pricing History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retail Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monthly Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan + Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($device->pricing->groupBy('effective_date') as $date => $pricingGroup)
                            @foreach($pricingGroup as $pricing)
                                <tr class="{{ $pricing->is_current ? 'bg-green-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $pricing->tier }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->retail_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->plan_cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->monthly_device_cost_pre_tax, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->plan_plus_device_pre_tax, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($pricing->is_current)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Current
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Historical
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-6 py-5 text-center text-gray-500">
                No SmartPay pricing history available for this device.
            </div>
        </div>
    @endif

    <!-- DRO Pricing History -->
    @if($device->droPricing->count() > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">DRO Pricing History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retail Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DRO Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monthly Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan + Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($device->droPricing->groupBy('effective_date') as $date => $pricingGroup)
                            @foreach($pricingGroup as $pricing)
                                <tr class="{{ $pricing->is_current ? 'bg-green-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $pricing->tier }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->retail_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-600">
                                        ${{ number_format($pricing->dro_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->plan_cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->monthly_device_cost_pre_tax, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->plan_plus_device_pre_tax, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($pricing->is_current)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Current
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Historical
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-5 text-center text-gray-500">
                No DRO pricing history available for this device.
            </div>
        </div>
    @endif
</div>
@endsection