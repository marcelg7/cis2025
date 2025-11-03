@extends('layouts.app')

@section('content')
<div class="py-12">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">Device Comparison - {{ ucfirst($pricingType) }} / {{ $tier }}</h1>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                    @foreach($devices as $device)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $device->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @php
                    $fields = [
                        'Retail Price' => 'retail_price',
                        'Upfront Payment' => 'upfront_payment',
                        'Agreement Credit' => 'agreement_credit',
                        'Monthly Device Cost (Pre-Tax)' => 'monthly_device_cost_pre_tax',
                        'Plan Cost' => 'plan_cost',
                        'Plan + Device (Pre-Tax)' => 'plan_plus_device_pre_tax',
                    ];
                    if ($pricingType === 'dro') {
                        $fields['DRO Amount'] = 'dro_amount';
                    }
                @endphp
                
                @foreach($fields as $label => $field)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $label }}</td>
                        @foreach($devices as $device)
                            @php
                                $pricing = $pricingType === 'smartpay' ? $device->currentPricing->first() : $device->currentDroPricing->first();
                                $value = $pricing ? ($pricing->{$field} ?? 'N/A') : 'N/A';
                                $formatted = is_numeric($value) ? '$' . number_format($value, 2) : $value;
                            @endphp
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $formatted }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <a href="{{ route('bell-pricing.compare') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            Back to Compare
        </a>
    </div>
</div>
</div>
@endsection