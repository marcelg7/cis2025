@extends('layouts.app')

@section('content')
<div class="py-12">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <a href="{{ route('bell-pricing.index') }}" class="text-indigo-600 hover:text-indigo-900">
            ← Back to Devices
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">{{ $device->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $device->manufacturer }} • {{ $device->model }} • {{ $device->storage }}</p>
        </div>
    </div>

    <!-- SmartPay Pricing -->
    @if($device->currentPricing->count() > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">SmartPay Pricing</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monthly Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan + Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">w/ $10 Credit</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($device->currentPricing as $pricing)
                            <tr>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    ${{ number_format($pricing->hay_credit_plus_device_pre_tax, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- DRO Pricing -->
    @if($device->currentDroPricing->count() > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">DRO (Device Return Option) Pricing</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DRO Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monthly Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan + Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">w/ $10 Credit</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($device->currentDroPricing as $pricing)
                            <tr>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    ${{ number_format($pricing->hay_credit_plus_device_pre_tax, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
</div>
@endsection