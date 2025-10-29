@extends('layouts.app')

@section('content')
<style>
    /* Plan features and return policy formatting */
    .prose p {
        margin-bottom: 0.75rem;
    }
    .prose ul, .prose ol {
        margin-bottom: 0.75rem;
        padding-left: 1.5rem;
    }
    .prose li {
        margin-bottom: 0.375rem;
    }

    @media print {
        .no-print { display: none; }
        img { max-height: 50px; }
        body { font-size: 12pt; margin: 0; }
        .container { width: 100%; max-width: 100%; }
        .grid { display: block; }
        .grid > div { margin-bottom: 1rem; }
        .grid-cols-2 > div { width: 100%; }
        table { width: auto; }
        hr { margin: 1rem 0; }
        h2, h3, h4 { font-size: 14pt; margin-bottom: 0.5rem; }
        p { margin: 0.25rem 0; }
        .text-sm { font-size: 10pt; }
        .text-xs { font-size: 8pt; }
        .font-semibold { font-weight: bold; }
        .font-medium { font-weight: 500; }
        .text-gray-900 { color: #000; }
        .text-gray-600 { color: #333; }
        .bg-white { background: #fff; }
        .border-gray-200 { border-color: #ccc; }
        .shadow { box-shadow: none; }
        .rounded-lg { border-radius: 0; }
    }
</style>
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8 px-4 container page-container">
    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 no-print">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 no-print">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-b border-gray-200">
            <div>
                @php
                    $logoPath = asset('images/hayLogo.png');
                @endphp
                <img src="{{ $logoPath }}" alt="Hay Communications" class="h-14">
            </div>
            <div class="text-right text-sm text-gray-700">
                <p><span class="font-semibold">Date:</span> {{ $contract->start_date->format('M d, Y') }}</p>
                <p><span class="font-semibold">Activity:</span> {{ $contract->activityType->activity ?? 'Hardware Upgrade' }}</p>
                <p><span class="font-semibold">Consultant:</span> {{ $contract->updatedBy->name ?? 'N/A' }}</p>
                <p><span class="font-semibold">Location:</span> {{ $contract->locationModel->name ?? 'N/A' }} - {{ $contract->locationModel->phone ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="px-6 py-4 text-center">
            <h2 class="text-2xl font-bold text-gray-900">Critical Information Summary</h2>
            <h3 class="text-lg font-semibold text-gray-800">Wireless Mobility Agreement</h3>
        </div>
        <hr class="border-gray-200">

        <!-- Your Information Section -->
        <div class="px-4 py-3 sm:px-4">
            <h4 class="text-md font-medium text-gray-900">Your Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="mt-2 text-sm text-gray-600">
                        <p><strong>Account Name:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->first_name }} {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->last_name }}</p>
                        <p><strong>Company Name:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->is_individual ? 'N/A' : $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name }}</p>
                        <p><strong>Mobile Account #:</strong> {{ $contract->subscriber->mobilityAccount->mobility_account }}</p>
                        <p><strong>Contact Number:</strong> {{ \App\Helpers\PhoneHelper::formatDisplay($contract->customer_phone ?? $contract->subscriber->mobile_number) }}</p>
                        <p><strong>Mobile Number:</strong> {{ \App\Helpers\PhoneHelper::formatDisplay($contract->subscriber->mobile_number) }}</p>
                        <p><strong>Subscriber:</strong> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                        <p><strong>Hay Account #:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->ivue_account }}</p>
                        <p><strong>Email:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->email ?? 'N/A' }}</p>
                        <p><strong>Address:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->address }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->city }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->state }} {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->zip_code }}</p>
                    </div>
                </div>
                <div>
                    <div class="mt-2 text-sm text-gray-600">
                        <p><strong>Monthly Payment Method:</strong> Pre-Authorized</p>
                        <p><strong>First Bill Date:</strong> {{ $contract->first_bill_date->format('M d, Y') }}</p>
                        <p class="mt-2"><strong>To view your monthly usage register at:</strong><br><a href="https://mybell.bell.ca/registration" class="text-indigo-600 hover:underline">https://mybell.bell.ca/registration</a></p>
                        <p><strong>Bill Date for My Bell registration:</strong> {{ $contract->start_date->format('M d, Y') }}</p>
                        
                        @php
                            // Calculate proration details
                            $startDate = $contract->start_date;
                            $billingCycleStart = 11;
                            $billingCycleEnd = 10;
                            
                            // Find the end of the partial month (next 10th)
                            $partialMonthEnd = $startDate->copy();
                            if ($startDate->day <= $billingCycleEnd) {
                                $partialMonthEnd->day($billingCycleEnd);
                            } else {
                                $partialMonthEnd->addMonth()->day($billingCycleEnd);
                            }
                            
                            // Find the start of first full month (next 11th)
                            $firstFullMonthStart = $partialMonthEnd->copy()->addDay();
                            $firstFullMonthEnd = $firstFullMonthStart->copy()->addMonth()->subDay();
                            
                            // Calculate days in partial month
                            $partialDays = $startDate->diffInDays($partialMonthEnd) + 1;
                        @endphp
                        
                        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-indigo-500 rounded">
                            <p class="text-sm font-semibold text-indigo-900 mb-2">Proration Information</p>
                            <p class="text-xs text-gray-700">
                                Your first bill will include prorated charges for service from <strong>{{ $startDate->format('M d, Y') }}</strong> to <strong>{{ $partialMonthEnd->format('M d, Y') }}</strong> ({{ $partialDays }} days), plus your first full month from <strong>{{ $firstFullMonthStart->format('M d, Y') }}</strong> to <strong>{{ $firstFullMonthEnd->format('M d, Y') }}</strong>.
                            </p>
                            <p class="text-xs text-gray-600 mt-2">
                                After that, your bills will follow the regular monthly cycle (11th to 10th of each month).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-gray-200">

<!-- Device Details -->
@if($contract->bell_pricing_type === 'byod')
    <!-- BYOD Plan - Simplified Display -->
    <div class="section px-6 py-4 bg-white border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Device Details</h3>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h4 class="text-md font-semibold text-blue-900">Bring Your Own Device (BYOD) Plan</h4>
                    <p class="mt-2 text-sm text-blue-800">
                        This is a Bring Your Own Device plan. The customer is using their own device. No device financing is required for this contract.
                    </p>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Regular Device Details (SmartPay/DRO) - TWO COLUMNS -->
    <div class="section px-6 py-4 bg-white border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Device Details</h3>
        @php
            $deviceDisplayName = $contract->bell_device_id && $contract->bellDevice ? $contract->bellDevice->name : 'N/A';
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <!-- LEFT COLUMN: Device & Pricing Info -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-700"><span class="font-semibold">Model:</span> {{ $deviceDisplayName }}</p>
                
                @if($contract->imei)
                    <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">IMEI:</span> {{ $contract->imei }}</p>
                @endif
                
                @if($contract->bell_device_id && $contract->bellDevice)
                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-xs font-semibold text-blue-900 mb-1">Bell Pricing Details</p>
                        <p class="text-xs"><span class="font-semibold">Pricing Type:</span> {{ $contract->bell_pricing_type === 'dro' ? 'DRO' : ucfirst($contract->bell_pricing_type ?? 'N/A') }}</p>
                        <p class="text-xs"><span class="font-semibold">Plan Tier:</span> {{ $contract->bell_tier ?? 'N/A' }}</p>
                        @if($contract->bell_dro_amount && $contract->bell_dro_amount > 0)
                            <p class="text-xs"><span class="font-semibold">DRO Amount:</span> ${{ number_format($contract->bell_dro_amount, 2) }}</p>
                        @endif
                        @if($contract->bell_plan_cost)
                            <p class="text-xs"><span class="font-semibold">Bell Plan Cost:</span> ${{ number_format($contract->bell_plan_cost, 2) }}</p>
                        @endif
                    </div>
                @endif
                
                <p class="mt-2 italic text-xs text-gray-600">All amounts are before taxes.</p>
                <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Device Retail Price:</span> ${{ number_format($devicePrice, 2) }}</p>
                <p class="text-sm text-gray-700"><span class="font-semibold">Agreement Credit:</span> ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }}</p>
                <p class="text-sm text-gray-700"><span class="font-semibold">Deferred Payment Amount:</span> ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}</p>
                <p class="text-sm text-gray-700"><span class="font-semibold">Up-front Payment Required:</span> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
                <p class="text-sm text-gray-700"><span class="font-semibold">Optional Up-front Payment:</span> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
                <p class="text-sm text-gray-700"><span class="font-semibold">Total Financed Amount:</span> ${{ number_format($totalFinancedAmount, 2) }}</p>
                <p class="text-sm text-gray-700"><span class="font-semibold">Remaining Device Balance:</span> ${{ number_format($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0), 2) }}</p>
            </div>
            
            <!-- RIGHT COLUMN: Payment & Term Info -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-lg font-semibold text-gray-900">Monthly Device Payment: ${{ number_format($monthlyDevicePayment, 2) }}</p>
                @if($contract->bell_device_id && $contract->bell_monthly_device_cost)
                    <p class="text-xs text-gray-500 mt-1">(Bell Calculated: ${{ number_format($contract->bell_monthly_device_cost, 2) }})</p>
                @endif
                <p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Commitment Period:</span> {{ $contract->commitmentPeriod->name ?? '2 Year Term Smart Pay' }}</p>
                
                <div class="mt-3 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-r">
                    <p class="text-xs font-semibold text-gray-900 mb-1">Early Cancellation Policy</p>
                    <p class="text-xs text-gray-700 leading-relaxed">
                        @if($cancellationPolicy)
                            {!! nl2br(e($cancellationPolicy)) !!}
                        @else
                            Early Cancellation Fee: ${{ number_format($monthlyDevicePayment, 2) }} per month left plus Device Return Option of ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}.<br>
                            Fee will be $0 on {{ $contract->end_date->format('M d, Y') }}; decreases monthly by: ${{ number_format($monthlyDevicePayment, 2) }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif
<hr class="border-gray-200">

		<!-- Return Policy -->
		@if($contract->bell_pricing_type !== 'byod')
			<div class="section px-6 py-4 bg-white border-b border-gray-200">
				<h3 class="text-lg font-semibold text-gray-900">Return Policy</h3>
				<p class="text-sm text-gray-600 prose prose-sm max-w-none">
					Taxes not included. If you purchase a device from Hay which does not meet your needs, you may return the device if it is <strong>(a)</strong> returned within <strong>15</strong> calendar days of the commitment start date; <strong>(b)</strong> in "like new" condition with the original packaging, manuals, and accessories; and <strong>(c)</strong> returned with original receipt to the location. You are responsible for all service charges incurred prior to your return of the device. SIM Cards are not returnable. Postpaid Accounts: Hay will not accept devices with excessive usage in violation of our Responsible Use of Services Policy. Prepaid Accounts: The device has not exceeded <strong>30</strong> minutes of voice usage or <strong>50 MB</strong> of data usage. Funds added to your account are non-refundable. If you are a person with a disability, the same conditions apply; however, you may return your device within <strong>30</strong> calendar days of the commitment start date and, if in a Prepaid Account, double the corresponding permitted usage set out above.
				</p>
			</div>
			<hr class="border-gray-200">
		@endif

        <!-- Rate Plan Details -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Rate Plan Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700"><span class="font-semibold">Rate Plan:</span> {{ $contract->ratePlan?->plan_name ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700"><span class="font-semibold">Monthly Rate Plan Charge:</span> ${{ number_format($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0, 2) }}</p>
                </div>
                <div class="col-span-1 md:col-span-2 bg-gray-50 p-4 rounded-lg">
                    @if($contract->ratePlan && $contract->ratePlan->features)
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! \App\Helpers\MarkdownHelper::sanitize(Str::markdown($contract->ratePlan->features)) !!}
                        </div>
                    @else
                        <p class="text-sm text-gray-700">No rate plan features have been entered for this plan.</p>
                    @endif
                    <p class="text-sm font-semibold text-gray-700 mt-2">Note: This plan may be subject to rate increases by the provider, which will apply during your term.</p>
                    <p class="text-xs text-gray-600 mt-2">If you exceed the usage allowed in your rate plan, additional usage charges may apply. See <a href="https://hay.net/cellular-service" class="text-indigo-600 hover:underline" rel="noopener noreferrer">hay.net/cellular-service</a> for current charges.</p>
                </div>
            </div>
        </div>

        <!-- Mobile Internet Plan Details -->
        @if($contract->mobileInternetPlan)
            <hr class="border-gray-200">
            <div class="section px-6 py-4 bg-white border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Mobile Internet Plan Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700"><span class="font-semibold">Plan:</span> {{ $contract->mobileInternetPlan->plan_name }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700"><span class="font-semibold">Monthly Charge:</span> ${{ number_format($contract->mobile_internet_price ?? 0, 2) }}</p>
                    </div>
                    @if($contract->mobileInternetPlan->description)
                        <div class="col-span-1 md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <div class="prose prose-sm max-w-none text-gray-700">
                                {!! \App\Helpers\MarkdownHelper::sanitize(Str::markdown($contract->mobileInternetPlan->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <hr class="border-gray-200">

        <!-- Minimum Monthly Charge -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200">
            @php
                $minimumMonthlyCharge = ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) + $monthlyDevicePayment;
            @endphp
            <p class="text-lg font-semibold text-gray-900">Minimum Monthly Charge: ${{ number_format($minimumMonthlyCharge, 2) }}</p>
        </div>
        <hr class="border-gray-200">
		
		
        <!-- Add-ons -->
        @if($contract->addOns->count())
            <div class="section px-6 py-4 bg-white border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Add-ons</h3>
                <ul class="list-disc pl-6 text-sm text-gray-700 mt-2">
                    @foreach($contract->addOns as $addOn)
                        <li>{{ $addOn->name }} ({{ $addOn->code }}): ${{ number_format($addOn->cost, 2) }}</li>
                    @endforeach
                </ul>
                <p class="text-sm font-semibold text-gray-700 mt-2">Total Add-on Cost: ${{ number_format($totalAddOnCost, 2) }}</p>
            </div>
            <hr class="border-gray-200">
        @endif		

        <!-- Total Monthly Charges -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Total Monthly Charges</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-xs text-gray-600">(Taxes and additional usage charges are extra.)</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg text-right">
                    <p class="text-lg font-semibold text-gray-900">${{ number_format($minimumMonthlyCharge + $totalAddOnCost, 2) }}</p>
                </div>
            </div>
        </div>
        <hr class="border-gray-200">



        <!-- One-Time Fees -->
        @if($contract->oneTimeFees->count())
            <div class="section px-6 py-4 bg-white border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">One-Time Fees</h3>
                <ul class="list-disc pl-6 text-sm text-gray-700 mt-2">
                    @foreach($contract->oneTimeFees as $fee)
                        <li>{{ $fee->name }}: ${{ number_format($fee->cost, 2) }}</li>
                    @endforeach
                </ul>
                <p class="text-sm font-semibold text-gray-700 mt-2">Total One-Time Fee Cost: ${{ number_format($totalOneTimeFeeCost, 2) }}</p>
            </div>
            <hr class="border-gray-200">
        @endif

		<!-- One-Time Charges -->
		<div class="section px-6 py-4 bg-white border-b border-gray-200">
			<h3 class="text-lg font-semibold text-gray-900">One-Time Charges</h3>
			@php
				$subtotal = $totalOneTimeFeeCost + ($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0);
				$taxes = $subtotal * 0.13;
				$total = $subtotal + $taxes;
			@endphp
			<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
				<div class="bg-gray-50 p-4 rounded-lg">
					<p class="text-sm text-gray-700"><span class="font-semibold">One-Time Fees:</span> ${{ number_format($totalOneTimeFeeCost, 2) }}</p>
					<p class="text-sm text-gray-700"><span class="font-semibold">Up-front Payment Requirement:</span> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
					<p class="text-sm text-gray-700"><span class="font-semibold">Optional Up-front Payment:</span> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
				</div>
				<div class="bg-gray-50 p-4 rounded-lg text-right">
					<p class="text-sm text-gray-700">Subtotal: ${{ number_format($subtotal, 2) }}</p>
					<p class="text-sm text-gray-700">Taxes (13% HST): ${{ number_format($taxes, 2) }}</p>
					<p class="text-sm font-semibold text-gray-700">Total: ${{ number_format($total, 2) }}</p>
				</div>
			</div>
		</div>
		<hr class="border-gray-200">

		<!-- Total Contract Cost Breakdown (Admin Setting) -->
		@if(\App\Helpers\SettingsHelper::enabled('show_contract_cost_breakdown'))
			<div class="section px-6 py-4 bg-white border-b border-gray-200">
				<h3 class="text-lg font-semibold text-gray-900 mb-4">Total Contract Cost Breakdown</h3>
				<div class="text-sm text-gray-700 space-y-2">
					<ul class="list-disc list-inside space-y-1 mb-4">
						<li>Device Cost (after ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }} credit): ${{ number_format($deviceAmount, 2) }}</li>
						<li>Rate Plan (24 months): ${{ number_format(($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) * 24, 2) }}</li>
						<li>Add-ons (24 months): ${{ number_format($totalAddOnCost * 24, 2) }}</li>
						<li>One-Time Fees: ${{ number_format($totalOneTimeFeeCost, 2) }}</li>
					</ul>
					<div class="bg-gray-50 p-4 rounded-lg">
						<div class="flex justify-between items-center mb-2">
							<span class="font-semibold">Total Contract Cost (before taxes):</span>
							<span class="font-semibold">${{ number_format($totalCost, 2) }}</span>
						</div>
						<div class="flex justify-between items-center text-xs mb-2">
							<span>Estimated taxes (13% HST):</span>
							<span>${{ number_format($totalCost * 0.13, 2) }}</span>
						</div>
						<div class="flex justify-between items-center text-xs border-t border-gray-300 pt-2">
							<span>Total with estimated taxes:</span>
							<span>${{ number_format($totalCost * 1.13, 2) }}</span>
						</div>
					</div>
				</div>
			</div>
			<hr class="border-gray-200">
		@endif

		@if((auth()->user()->show_development_info ?? false) || \App\Helpers\SettingsHelper::enabled('show_development_info'))
			<!-- Total Contract Cost Breakdown - Development/Calculation Check -->
			<div class="section px-6 py-4 bg-yellow-50 border-b border-yellow-200">
				<div class="flex items-start mb-2">
					<svg class="h-5 w-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
					</svg>
					<div>
						<h3 class="text-lg font-semibold text-gray-900">Complete Contract Calculations</h3>
						<p class="text-xs text-yellow-700 mt-1">Development/Verification - All formulas and calculations shown for accuracy checking.</p>
					</div>
				</div>
				
				<div class="bg-yellow-100 p-6 rounded-lg mt-4 space-y-6">
					
					<!-- DEVICE CALCULATIONS -->
					@php
						$retailPrice = $contract->bell_retail_price ?? 0;
						$agreementCredit = $contract->agreement_credit_amount ?? 0;
						$deviceAmount = $deviceAmount; // Already calculated in controller
						$requiredUpfront = $contract->required_upfront_payment ?? 0;
						$optionalUpfront = $contract->optional_down_payment ?? 0;
						$deferredPayment = $contract->deferred_payment_amount ?? 0;
						$totalFinanced = $totalFinancedAmount; // Already calculated
						$remainingBalance = $totalFinanced - $deferredPayment;
						$monthlyDevicePayment = $monthlyDevicePayment; // Already calculated
						$monthlyReduction = $monthlyReduction; // Already calculated
					@endphp
					
					<div class="border-l-4 border-blue-500 pl-4">
						<h4 class="text-md font-semibold text-gray-900 mb-3">💰 Device Pricing Calculations</h4>
						<div class="space-y-2 text-sm font-mono">
							<p class="text-gray-700">
								<span class="text-blue-600">Device Retail Price:</span> 
								<span class="float-right">${{ number_format($retailPrice, 2) }}</span>
							</p>
							<p class="text-gray-700">
								<span class="text-blue-600">Agreement Credit:</span> 
								<span class="float-right text-green-600">-${{ number_format($agreementCredit, 2) }}</span>
							</p>
							<hr class="border-gray-300 my-1">
							<p class="text-gray-900 font-bold">
								<span class="text-blue-600">Device Amount = Retail - Credit:</span> 
								<span class="float-right">${{ number_format($deviceAmount, 2) }}</span>
							</p>
							
							<div class="mt-3 pt-3 border-t border-gray-300">
								<p class="text-gray-700">
									<span class="text-blue-600">Required Upfront Payment:</span> 
									<span class="float-right">${{ number_format($requiredUpfront, 2) }}</span>
								</p>
								<p class="text-gray-700">
									<span class="text-blue-600">Optional Upfront Payment:</span> 
									<span class="float-right">${{ number_format($optionalUpfront, 2) }}</span>
								</p>
								<p class="text-gray-700">
									<span class="text-blue-600">Deferred Payment (DRO):</span> 
									<span class="float-right">${{ number_format($deferredPayment, 2) }}</span>
								</p>
								<hr class="border-gray-300 my-1">
								<p class="text-gray-900 font-bold">
									<span class="text-blue-600">Total Financed = Device - Upfronts:</span> 
									<span class="float-right">${{ number_format($totalFinanced, 2) }}</span>
								</p>
								<p class="text-gray-900 font-bold">
									<span class="text-blue-600">Remaining Balance = Financed - DRO:</span> 
									<span class="float-right">${{ number_format($remainingBalance, 2) }}</span>
								</p>
							</div>
							
							<div class="mt-3 pt-3 border-t-2 border-gray-400">
								<p class="text-gray-900 font-bold text-base">
									<span class="text-blue-600">Monthly Device Payment = Balance ÷ 24:</span> 
									<span class="float-right">${{ number_format($monthlyDevicePayment, 2) }}</span>
								</p>
								<p class="text-xs text-gray-600 mt-1">
									Formula: ${{ number_format($remainingBalance, 2) }} ÷ 24 months = ${{ number_format($monthlyDevicePayment, 2) }}/month
								</p>
								<p class="text-xs text-gray-600">
									Monthly reduction in buyout: ${{ number_format($monthlyReduction, 2) }}
								</p>
							</div>
						</div>
					</div>
					
					<!-- PLAN CALCULATIONS -->
					@php
						$ratePlanPrice = $contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0;
						$mobileInternetPrice = $contract->mobile_internet_price ?? 0;
						$totalPlanMonthly = $ratePlanPrice + $mobileInternetPrice;
					@endphp
					
					<div class="border-l-4 border-green-500 pl-4">
						<h4 class="text-md font-semibold text-gray-900 mb-3">📱 Monthly Plan Calculations</h4>
						<div class="space-y-2 text-sm font-mono">
							<p class="text-gray-700">
								<span class="text-green-600">Rate Plan Cost:</span> 
								<span class="float-right">${{ number_format($ratePlanPrice, 2) }}/mo</span>
							</p>
							<p class="text-gray-700">
								<span class="text-green-600">Mobile Internet Cost:</span> 
								<span class="float-right">${{ number_format($mobileInternetPrice, 2) }}/mo</span>
							</p>
							<hr class="border-gray-300 my-1">
							<p class="text-gray-900 font-bold">
								<span class="text-green-600">Total Plan Cost = Rate + Internet:</span> 
								<span class="float-right">${{ number_format($totalPlanMonthly, 2) }}/mo</span>
							</p>
						</div>
					</div>
					
					<!-- ADD-ONS CALCULATIONS -->
					@php
						$addOnsList = $contract->addOns;
						$totalAddOnsMonthly = $totalAddOnCost; // Already calculated
					@endphp
					
					<div class="border-l-4 border-purple-500 pl-4">
						<h4 class="text-md font-semibold text-gray-900 mb-3">➕ Add-ons Calculations</h4>
						<div class="space-y-2 text-sm font-mono">
							@if($addOnsList->count() > 0)
								@foreach($addOnsList as $addon)
									<p class="text-gray-700">
										<span class="text-purple-600">{{ $addon->name }} ({{ $addon->code }}):</span> 
										<span class="float-right {{ $addon->cost < 0 ? 'text-green-600' : '' }}">
											{{ $addon->cost < 0 ? '-' : '' }}${{ number_format(abs($addon->cost), 2) }}/mo
										</span>
									</p>
								@endforeach
								<hr class="border-gray-300 my-1">
							@else
								<p class="text-gray-500 italic">No add-ons selected</p>
							@endif
							<p class="text-gray-900 font-bold">
								<span class="text-purple-600">Total Add-ons:</span> 
								<span class="float-right">${{ number_format($totalAddOnsMonthly, 2) }}/mo</span>
							</p>
						</div>
					</div>
					
					<!-- MONTHLY TOTAL CALCULATIONS -->
					@php
						$minimumMonthly = $minimumMonthlyCharge; // Already calculated
						$totalMonthly = $minimumMonthly + $totalAddOnsMonthly;
					@endphp
					
					<div class="border-l-4 border-indigo-500 pl-4">
						<h4 class="text-md font-semibold text-gray-900 mb-3">📊 Total Monthly Calculations</h4>
						<div class="space-y-2 text-sm font-mono">
							<p class="text-gray-700">
								<span class="text-indigo-600">Monthly Device Payment:</span> 
								<span class="float-right">${{ number_format($monthlyDevicePayment, 2) }}/mo</span>
							</p>
							<p class="text-gray-700">
								<span class="text-indigo-600">Monthly Plan Cost:</span> 
								<span class="float-right">${{ number_format($totalPlanMonthly, 2) }}/mo</span>
							</p>
							<p class="text-gray-700">
								<span class="text-indigo-600">Monthly Add-ons:</span> 
								<span class="float-right">${{ number_format($totalAddOnsMonthly, 2) }}/mo</span>
							</p>
							<hr class="border-gray-300 my-1">
							<p class="text-gray-900 font-bold text-base">
								<span class="text-indigo-600">Total Monthly Charge = Device + Plan + Add-ons:</span> 
								<span class="float-right">${{ number_format($totalMonthly, 2) }}/mo</span>
							</p>
							<p class="text-xs text-gray-600 mt-1">
								Formula: ${{ number_format($monthlyDevicePayment, 2) }} + ${{ number_format($totalPlanMonthly, 2) }} + ${{ number_format($totalAddOnsMonthly, 2) }} = ${{ number_format($totalMonthly, 2) }}/mo
							</p>
						</div>
					</div>
					
					<!-- ONE-TIME FEES -->
					@php
						$oneTimeFeesList = $contract->oneTimeFees;
						$totalOneTimeFees = $totalOneTimeFeeCost; // Already calculated
					@endphp
					
					<div class="border-l-4 border-orange-500 pl-4">
						<h4 class="text-md font-semibold text-gray-900 mb-3">🔔 One-Time Fees</h4>
						<div class="space-y-2 text-sm font-mono">
							@if($oneTimeFeesList->count() > 0)
								@foreach($oneTimeFeesList as $fee)
									<p class="text-gray-700">
										<span class="text-orange-600">{{ $fee->name }}:</span> 
										<span class="float-right">${{ number_format($fee->cost, 2) }}</span>
									</p>
								@endforeach
								<hr class="border-gray-300 my-1">
							@else
								<p class="text-gray-500 italic">No one-time fees</p>
							@endif
							<p class="text-gray-900 font-bold">
								<span class="text-orange-600">Total One-Time Fees:</span> 
								<span class="float-right">${{ number_format($totalOneTimeFees, 2) }}</span>
							</p>
						</div>
					</div>
					
					<!-- 24-MONTH CONTRACT TOTAL -->
					@php
						$device24Month = $monthlyDevicePayment * 24;
						$plan24Month = $totalPlanMonthly * 24;
						$addons24Month = $totalAddOnsMonthly * 24;
						$totalRecurring24 = $device24Month + $plan24Month + $addons24Month;
						$contractSubtotal = $totalRecurring24 + $totalOneTimeFees;
						$upfrontTotal = $requiredUpfront + $optionalUpfront;
						$grandSubtotal = $contractSubtotal + $upfrontTotal;
						$taxAmount = $grandSubtotal * 0.13;
						$grandTotal = $grandSubtotal + $taxAmount;
					@endphp
					
					<div class="border-l-4 border-red-500 pl-4">
						<h4 class="text-md font-semibold text-gray-900 mb-3">📅 24-Month Contract Totals</h4>
						<div class="space-y-2 text-sm font-mono">
							<p class="text-gray-700">
								<span class="text-red-600">Device Cost (24 × ${{ number_format($monthlyDevicePayment, 2) }}):</span> 
								<span class="float-right">${{ number_format($device24Month, 2) }}</span>
							</p>
							<p class="text-gray-700">
								<span class="text-red-600">Plan Cost (24 × ${{ number_format($totalPlanMonthly, 2) }}):</span> 
								<span class="float-right">${{ number_format($plan24Month, 2) }}</span>
							</p>
							<p class="text-gray-700">
								<span class="text-red-600">Add-ons (24 × ${{ number_format($totalAddOnsMonthly, 2) }}):</span> 
								<span class="float-right">${{ number_format($addons24Month, 2) }}</span>
							</p>
							<hr class="border-gray-300 my-1">
							<p class="text-gray-900 font-bold">
								<span class="text-red-600">Recurring Charges (24 months):</span> 
								<span class="float-right">${{ number_format($totalRecurring24, 2) }}</span>
							</p>
							<p class="text-gray-700 mt-2">
								<span class="text-red-600">One-Time Fees:</span> 
								<span class="float-right">${{ number_format($totalOneTimeFees, 2) }}</span>
							</p>
							<p class="text-gray-700">
								<span class="text-red-600">Upfront Payments:</span> 
								<span class="float-right">${{ number_format($upfrontTotal, 2) }}</span>
							</p>
							<hr class="border-gray-400 my-2">
							<p class="text-gray-900 font-bold text-base">
								<span class="text-red-600">Contract Subtotal (before tax):</span> 
								<span class="float-right">${{ number_format($grandSubtotal, 2) }}</span>
							</p>
							<p class="text-xs text-gray-600 mt-1">
								Formula: ${{ number_format($totalRecurring24, 2) }} + ${{ number_format($totalOneTimeFees, 2) }} + ${{ number_format($upfrontTotal, 2) }} = ${{ number_format($grandSubtotal, 2) }}
							</p>
						</div>
					</div>
					
					<!-- FINAL TOTALS WITH TAX -->
					<div class="border-l-4 border-gray-700 pl-4 bg-gray-50 p-4 rounded">
						<h4 class="text-md font-semibold text-gray-900 mb-3">💵 Final Contract Totals</h4>
						<div class="space-y-2 text-sm font-mono">
							<p class="text-gray-900 font-bold text-base">
								<span>Subtotal (before tax):</span> 
								<span class="float-right">${{ number_format($grandSubtotal, 2) }}</span>
							</p>
							<p class="text-gray-700">
								<span>HST (13% × ${{ number_format($grandSubtotal, 2) }}):</span> 
								<span class="float-right">${{ number_format($taxAmount, 2) }}</span>
							</p>
							<hr class="border-gray-700 my-2">
							<p class="text-gray-900 font-bold text-lg">
								<span>GRAND TOTAL (with tax):</span> 
								<span class="float-right">${{ number_format($grandTotal, 2) }}</span>
							</p>
						</div>
					</div>
					
					<!-- NOTES -->
					<div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
						<p class="text-xs text-blue-900">
							<strong>Note:</strong> All calculations shown exclude actual taxes charged by the provider on monthly bills. 
							The 13% HST shown is an estimate for contract total comparison purposes only. 
							Actual taxes will vary based on your location and applicable tax rates at time of billing.
						</p>
					</div>
					
				</div>
			</div>
			<hr class="border-gray-200">
				
		@endif
		

		<!-- Signature -->
		@if ($contract->signature_path || $contract->status !== 'draft')
			@php
				$signaturePath = trim($contract->signature_path ?? '');
				$checkPath = str_replace('storage/', '', $signaturePath);
				$signatureFullPath = $signaturePath ? storage_path('app/public/' . $checkPath) : null;
				$signatureExists = $signatureFullPath ? file_exists($signatureFullPath) : false;
				$signatureBase64 = null;
				$signatureSrc = null;
				
				if ($signatureExists) {
					try {
						$signatureData = file_get_contents($signatureFullPath);
						$signatureBase64 = 'data:image/png;base64,' . base64_encode($signatureData);
						$signatureSrc = $signatureBase64;
					} catch (\Exception $e) {
						Log::error('Failed to process signature', [
							'contract_id' => $contract->id,
							'error' => $e->getMessage()
						]);
					}
				}
			@endphp
			
			<div class="section px-6 py-4 bg-white border-b border-gray-200">
				<h3 class="text-lg font-semibold text-gray-900">Signature</h3>
				
				@if ($signatureExists && !empty($signatureSrc))
					<!-- Signature file exists - show it -->
					<div class="signature-wrapper border border-gray-200 rounded-lg p-2 bg-gray-50 mt-4">
						<img src="{{ $signatureSrc }}" alt="Signature" style="max-height: 128px; width: auto;">
					</div>
					<p class="text-xs text-gray-500 mt-2">
						Contract signed on {{ $contract->updated_at->format('M d, Y \a\t g:i A') }}
					</p>
				@elseif ($contract->ftp_to_vault && $contract->vault_path)
					<!-- Contract uploaded to vault - signature removed for security -->
					<div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-4">
						<div class="flex items-start">
							<div class="flex-shrink-0">
								<svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
								</svg>
							</div>
							<div class="ml-3 flex-1">
								<h4 class="text-sm font-semibold text-blue-900">Signature Securely Archived</h4>
								<p class="mt-2 text-sm text-blue-800">
									This contract was signed on <strong>{{ $contract->updated_at->format('M d, Y \a\t g:i A') }}</strong>.
								</p>
								<p class="mt-2 text-sm text-blue-800">
									For security purposes, signature files are automatically removed from the server after successful upload to the vault. 
									The complete signed contract can be accessed in the <strong>NISC iVue Vault</strong>.
								</p>
								<div class="mt-4 flex items-center text-xs text-blue-700">
									<svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
									</svg>
									<span>Uploaded to vault: {{ $contract->ftp_at->format('M d, Y \a\t g:i A') }}</span>
								</div>
								@if($contract->vault_path)
									<div class="mt-1 flex items-center text-xs text-blue-700">
										<svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
										</svg>
										<span class="font-mono">{{ basename($contract->vault_path) }}</span>
									</div>
								@endif
							</div>
						</div>
					</div>
				@elseif ($contract->status === 'signed' || $contract->status === 'finalized')
					<!-- Contract is signed but signature file is missing (edge case) -->
					<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mt-4">
						<div class="flex items-start">
							<div class="flex-shrink-0">
								<svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
								</svg>
							</div>
							<div class="ml-3">
								<h4 class="text-sm font-semibold text-yellow-900">Signature File Not Available</h4>
								<p class="mt-2 text-sm text-yellow-800">
									This contract was signed on <strong>{{ $contract->updated_at->format('M d, Y \a\t g:i A') }}</strong>, 
									but the signature file is not currently available on this server.
								</p>
								@if(!$contract->ftp_to_vault)
									<p class="mt-2 text-sm text-yellow-800">
										The contract has not yet been uploaded to the vault. Please contact support if you need access to the signed copy.
									</p>
								@endif
							</div>
						</div>
					</div>
				@else
					<!-- Contract not yet signed -->
					<div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-4">
						<p class="text-sm text-gray-600 italic">This contract has not been signed yet.</p>
					</div>
				@endif
			</div>
			<hr class="border-gray-200">
		@endif

        <!-- Contract Timeline -->
        <div class="px-6 py-6 no-print">
            <x-contract-timeline :contract="$contract" />
        </div>
        <hr class="border-gray-200">

        <!-- Buttons -->
        <div class="px-6 py-6 flex flex-wrap gap-4 no-print">
            @if($contract->bell_pricing_type !== 'byod')
                <a href="{{ route('contracts.financing.index', $contract->id) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                   title="View Financing Agreement">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Financing Form
                </a>
                @if($contract->bell_pricing_type === 'dro' || $contract->deferred_payment_amount > 0)
                    <a href="{{ route('contracts.dro.index', $contract->id) }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                       title="View DRO Agreement">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        DRO Form
                    </a>
                @endif
            @endif
            <a href="{{ route('contracts.download', $contract->id) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 {{ $contract->status !== 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}"
               title="Download PDF"
               {{ $contract->status !== 'finalized' ? 'disabled' : '' }}>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                PDF
            </a>
            <form action="{{ route('contracts.email', $contract->id) }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $contract->status !== 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}"
                        title="Email Contract"
                        {{ $contract->status !== 'finalized' ? 'disabled' : '' }}>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Email
                </button>
            </form>
            <a href="{{ route('contracts.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
               title="Back to Contracts">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Contracts
            </a>
            @if ($contract->status === 'draft')
                <a href="{{ route('contracts.edit', $contract->id) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit
                </a>
                <a href="{{ route('contracts.sign', $contract->id) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Sign
                </a>
            @endif
            @if ($contract->status === 'signed')
                <form action="{{ route('contracts.finalize', $contract->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Finalize Contract & Upload
                    </button>
                </form>
            @endif
            @if ($contract->status === 'finalized')
                <form action="{{ route('contracts.revision', $contract->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            style="background-color: #2563eb !important; color: #ffffff !important;"
                            title="Create Revision">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Revision
                    </button>
                </form>
            @endif

            @if($contract->ftp_to_vault)
                <div class="inline-flex items-center px-4 py-2 text-sm text-green-700 bg-green-100 rounded-md">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Uploaded to Vault {{ $contract->ftp_at->diffForHumans() }}
                </div>
            @elseif($contract->status === 'finalized')
                <form action="{{ route('contracts.ftp', $contract->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700"
                            style="background-color: #9333ea !important; color: #ffffff !important;"
                            title="Upload to Vault">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Vault
                    </button>
                </form>
                @if($contract->ftp_error)
                    <span class="text-xs text-red-600 block mt-1">Last error: {{ $contract->ftp_error }}</span>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection