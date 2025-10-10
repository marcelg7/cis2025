@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <a href="{{ route('bell-pricing.index') }}" class="text-indigo-600 hover:text-indigo-900">
            ← Back to Devices
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Compare Device Pricing</h1>
    
    <!-- Selection Form -->
    <form method="GET" action="{{ route('bell-pricing.compare') }}" class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Devices Multi-Select -->
            <div class="col-span-2">
                <label for="devices" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Devices to Compare (Hold Ctrl/Cmd to select multiple)
                </label>
                <select name="devices[]" 
                        id="devices" 
                        multiple 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                        size="10"
                        required>
                    @foreach($allDevices as $device)
                        <option value="{{ $device->id }}" 
                                {{ in_array($device->id, request('devices', [])) ? 'selected' : '' }}>
                            {{ $device->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Select 2-5 devices to compare</p>
            </div>
            
            <div class="space-y-4">
                <!-- Tier Selection -->
                <div>
                    <label for="tier" class="block text-sm font-medium text-gray-700 mb-2">Plan Tier</label>
                    <select name="tier" 
                            id="tier" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="Ultra" {{ $tier === 'Ultra' ? 'selected' : '' }}>Ultra</option>
                        <option value="Max" {{ $tier === 'Max' ? 'selected' : '' }}>Max</option>
                        <option value="Select" {{ $tier === 'Select' ? 'selected' : '' }}>Select</option>
                        <option value="Lite" {{ $tier === 'Lite' ? 'selected' : '' }}>Lite</option>
                    </select>
                </div>

                <!-- Pricing Type Selection -->
                <div>
                    <label for="pricing_type" class="block text-sm font-medium text-gray-700 mb-2">Pricing Type</label>
                    <select name="pricing_type" 
                            id="pricing_type" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="smartpay" {{ $pricingType === 'smartpay' ? 'selected' : '' }}>SmartPay</option>
                        <option value="dro" {{ $pricingType === 'dro' ? 'selected' : '' }}>DRO (Device Return Option)</option>
                    </select>
                </div>

                <!-- Compare Button -->
                <button type="submit" 
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Compare Devices
                </button>
            </div>
        </div>
    </form>

    <!-- Comparison Results -->
    @if($selectedDevices->count() > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    Comparison Results - {{ $tier }} Tier ({{ ucfirst($pricingType) }})
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Device
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Retail Price
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Plan Cost
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Monthly Device
                            </th>
                            @if($pricingType === 'dro')
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    DRO Amount
                                </th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Plan + Device
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                w/ $10 Credit
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($selectedDevices as $device)
                            @php
                                $pricing = null;
                                if ($pricingType === 'dro') {
                                    $pricing = $device->currentDroPricing->firstWhere('tier', $tier);
                                } else {
                                    $pricing = $device->currentPricing->firstWhere('tier', $tier);
                                }
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $device->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $device->manufacturer }}</div>
                                </td>
                                @if($pricing)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->retail_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->plan_cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->monthly_device_cost_pre_tax, 2) }}
                                    </td>
                                    @if($pricingType === 'dro')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-600">
                                            ${{ number_format($pricing->dro_amount, 2) }}
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pricing->plan_plus_device_pre_tax, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                        ${{ number_format($pricing->hay_credit_plus_device_pre_tax, 2) }}
                                    </td>
                                @else
                                    <td colspan="{{ $pricingType === 'dro' ? '6' : '5' }}" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        No pricing available for {{ $tier }} tier
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Section -->
            @if($selectedDevices->count() >= 2)
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Price Range Summary</h3>
                    @php
                        $prices = $selectedDevices->map(function($device) use ($tier, $pricingType) {
                            if ($pricingType === 'dro') {
                                $pricing = $device->currentDroPricing->firstWhere('tier', $tier);
                            } else {
                                $pricing = $device->currentPricing->firstWhere('tier', $tier);
                            }
                            return $pricing ? $pricing->retail_price : null;
                        })->filter();
                        
                        $monthlyPrices = $selectedDevices->map(function($device) use ($tier, $pricingType) {
                            if ($pricingType === 'dro') {
                                $pricing = $device->currentDroPricing->firstWhere('tier', $tier);
                            } else {
                                $pricing = $device->currentPricing->firstWhere('tier', $tier);
                            }
                            return $pricing ? $pricing->plan_plus_device_pre_tax : null;
                        })->filter();
                    @endphp
                    
                    @if($prices->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Lowest Retail:</span>
                                <span class="font-medium text-gray-900">${{ number_format($prices->min(), 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Highest Retail:</span>
                                <span class="font-medium text-gray-900">${{ number_format($prices->max(), 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Lowest Monthly:</span>
                                <span class="font-medium text-gray-900">${{ number_format($monthlyPrices->min(), 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Highest Monthly:</span>
                                <span class="font-medium text-gray-900">${{ number_format($monthlyPrices->max(), 2) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
            <p class="text-blue-800">Select devices from the list above to compare their pricing.</p>
        </div>
    @endif
</div>
@endsection