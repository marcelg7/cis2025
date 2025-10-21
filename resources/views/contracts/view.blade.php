@php
    // Removed dynamic layout - this is now web-only, always use layouts.app
@endphp

@extends('layouts.app')

@section('content')
<style>
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
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8 px-4 container">
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
                <p><span class="font-semibold">Consultant:</span> {{ auth()->user()->name ?? 'Marcel Gelinas' }}</p>
                <p><span class="font-semibold">Store Phone Number:</span> {{ $contract->location === 'zurich' ? '519-236-4333' : ($contract->location === 'exeter' ? '519-235-1234' : '519-238-5678') }}</p>
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
                        <p><strong>Account Name:</strong> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                        <p><strong>Company Name:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->is_individual ? 'N/A' : $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name }}</p>
                        <p><strong>Mobile Account #:</strong> {{ $contract->subscriber->mobilityAccount->mobility_account }}</p>
                        <p><strong>Contact Number:</strong> {{ $contract->subscriber->mobile_number }}</p>
                        <p><strong>Mobile Number:</strong> {{ $contract->subscriber->mobile_number }}</p>
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
                        <p><strong>Bill Date for My Bell registration:</strong> {{ $contract->first_bill_date->day }}{{ $contract->first_bill_date->day == 1 ? 'st' : ($contract->first_bill_date->day == 2 ? 'nd' : ($contract->first_bill_date->day == 3 ? 'rd' : 'th')) }}</p>
                        
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
			<!-- Regular Device Details (SmartPay/DRO) -->
			<div class="section px-6 py-4 bg-white border-b border-gray-200">
				<h3 class="text-lg font-semibold text-gray-900">Device Details</h3>
				@php
					$deviceDisplayName = $contract->bell_device_id && $contract->bellDevice ? $contract->bellDevice->name : 'N/A';
				@endphp
				<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
					<div class="bg-gray-50 p-4 rounded-lg">
						<p class="text-sm text-gray-700"><span class="font-semibold">Model:</span> {{ $deviceDisplayName }}</p>
						@if($contract->bell_device_id && $contract->bellDevice)
							<div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded">
								<p class="text-xs font-semibold text-blue-900 mb-1">Bell Pricing Details</p>
								<p class="text-xs"><span class="font-semibold">Pricing Type:</span> {{ ucfirst($contract->bell_pricing_type ?? 'N/A') }}</p>
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
						<p class="text-sm text-gray-700"><span class="font-semibold">Device Retail Price:</span> ${{ number_format($devicePrice, 2) }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">Agreement Credit:</span> ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">Device Amount:</span> ${{ number_format($deviceAmount, 2) }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">Up-front Payment Required:</span> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">Optional Up-front Payment:</span> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">Total Financed Amount (before tax):</span> ${{ number_format($totalFinancedAmount, 2) }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">Deferred Payment Amount:</span> ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">Amount for Monthly Payment Calculation:</span> ${{ number_format($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0), 2) }}</p>
					</div>
					<div class="bg-gray-50 p-4 rounded-lg">
						<p class="text-lg font-semibold text-gray-900">Monthly Device Payment: ${{ number_format($monthlyDevicePayment, 2) }}</p>
						@if($contract->bell_device_id && $contract->bell_monthly_device_cost)
							<p class="text-xs text-gray-500 mt-1">(Bell Calculated: ${{ number_format($contract->bell_monthly_device_cost, 2) }})</p>
						@endif
						<p class="text-sm text-gray-700 mt-2"><span class="font-semibold">Commitment Period:</span> {{ $contract->commitmentPeriod->name ?? '2 Year Term Smart Pay' }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">Remaining Device Balance:</span> ${{ number_format($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0), 2) }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">Start Date:</span> {{ $contract->start_date->format('M d, Y') }}</p>
						<p class="text-sm text-gray-700"><span class="font-semibold">End Date:</span> {{ $contract->end_date->format('M d, Y') }}</p>
						<p class="text-sm text-gray-700">Your service will continue month-to-month after this end date.</p>
						<p class="text-xs text-gray-600 mt-2">
							Early Cancellation Fee is the remaining balance of your device plus the full Deferred Return Option amount. In this case, your Buyout Cost would be ${{ number_format($monthlyDevicePayment, 2) }} per month left on the term plus the Device Return Option of ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}.<br>
							Fee will be $0 on {{ $contract->end_date->format('M d, Y') }} and will decrease each month by: ${{ number_format($monthlyReduction, 2) }}
						</p>
					</div>
				</div>
			</div>
		@endif
		<hr class="border-gray-200">

        @include('contracts.partials._cellular_pricing_display')
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
                    <p class="text-sm text-gray-700"><span class="font-semibold">Plan:</span> {{ $contract->bell_tier ?? 'N/A' }}</p>
                    @if($contract->ratePlan && $contract->ratePlan->tier)
                        <p class="text-sm text-gray-700"><span class="font-semibold">Tier:</span> {{ $contract->ratePlan->tier }} Tier</p>
                    @endif
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700"><span class="font-semibold">Monthly Rate Plan Charge:</span> ${{ number_format($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0, 2) }}</p>
                </div>
                <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                    @if($contract->ratePlan && $contract->ratePlan->features)
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! $contract->ratePlan->features ?? '<p>No features available</p>' !!}
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
                        <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                            <div class="prose prose-sm max-w-none text-gray-700">
                                {!! $contract->mobileInternetPlan->description !!}
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

        <!-- Total Monthly Charges -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Total Monthly Charges</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-lg font-semibold text-gray-900">Total Monthly Charges</p>
                    <p class="text-xs text-gray-600 mt-1">(Taxes and additional usage charges are extra.)</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg text-right">
                    <p class="text-lg font-semibold text-gray-900">${{ number_format($minimumMonthlyCharge + $totalAddOnCost, 2) }}</p>
                </div>
            </div>
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
                $subtotal = $deviceAmount + ($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0);
                $taxes = $subtotal * 0.13;
                $total = $subtotal + $taxes;
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div class="bg-gray-50 p-4 rounded-lg">
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

        <!-- Total Contract Cost -->
        <div class="section px-6 py-4 bg-blue-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Total Contract Cost Breakdown</h3>
            <div class="bg-blue-100 p-4 rounded-lg mt-4">
                <ul class="list-disc pl-6 text-sm text-gray-700">
                    <li>Device Cost (after ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }} credit): ${{ number_format($deviceAmount, 2) }}</li>
                    <li>Rate Plan (24 months): ${{ number_format(($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) * 24, 2) }}</li>
                    <li>Add-ons (24 months): ${{ number_format($totalAddOnCost * 24, 2) }}</li>
                    <li>One-Time Fees: ${{ number_format($totalOneTimeFeeCost, 2) }}</li>
                </ul>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <p class="text-lg font-semibold text-gray-900">Total Contract Cost (before taxes):</p>
                    <p class="text-lg font-semibold text-gray-900 text-right">${{ number_format($totalCost, 2) }}</p>
                    <p class="text-sm text-gray-600">Estimated taxes (13% HST):</p>
                    <p class="text-sm text-gray-600 text-right">${{ number_format($totalCost * 0.13, 2) }}</p>
                    <p class="text-sm text-gray-600">Total with estimated taxes:</p>
                    <p class="text-sm text-gray-600 text-right">${{ number_format($totalCost * 1.13, 2) }}</p>
                </div>
            </div>
        </div>
        <hr class="border-gray-200">

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
 
        <!-- Buttons -->
        <div class="px-6 py-6 flex flex-wrap gap-4 no-print">
            <a href="{{ route('contracts.download', $contract->id) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 {{ $contract->status !== 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}"
               {{ $contract->status !== 'finalized' ? 'disabled' : '' }}>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Download PDF
            </a>
            <form action="{{ route('contracts.email', $contract->id) }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $contract->status !== 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ $contract->status !== 'finalized' ? 'disabled' : '' }}>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Email Contract
                </button>
            </form>
            <a href="{{ route('contracts.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Contracts
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
                        Finalize
                    </button>
                </form>
            @endif
            @if ($contract->status === 'finalized')
                <form action="{{ route('contracts.revision', $contract->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            style="background-color: #2563eb !important; color: #ffffff !important;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Create Revision
                    </button>
                </form>
            @endif
                            
            <!-- Financing Form Button -->
            @if($contract->requiresFinancing())
                @if($contract->financing_status === 'pending')
                    <a href="{{ route('contracts.financing.index', $contract->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                       style="background-color: #ea580c !important; color: #ffffff !important;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Financing Form (Pending)
                    </a>
                @elseif($contract->financing_status === 'signed')
                    <a href="{{ route('contracts.financing.index', $contract->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                       style="background-color: #2563eb !important; color: #ffffff !important;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Financing Form (Signed)
                    </a>
                @elseif($contract->financing_status === 'finalized')
                    <a href="{{ route('contracts.financing.index', $contract->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                       style="background-color: #16a34a !important; color: #ffffff !important;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Financing Form (Finalized)
                    </a>
                @endif
            @endif    

			<!-- DRO Form Button -->
			@if($contract->requiresDro())
				<a href="{{ route('contracts.dro.index', $contract->id) }}"
				   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white 
				   {{ $contract->dro_status === 'finalized' ? 'bg-green-600 hover:bg-green-700' : 'bg-orange-600 hover:bg-orange-700' }}">
					<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
					</svg>
					DRO Form
					@if($contract->dro_status === 'pending')
						<span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-white text-orange-600">Pending</span>
					@elseif($contract->dro_status === 'customer_signed')
						<span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-white text-blue-600">Needs CSR</span>
					@elseif($contract->dro_status === 'csr_initialed')
						<span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-white text-indigo-600">Ready</span>
					@elseif($contract->dro_status === 'finalized')
						<span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-white text-green-600">Complete</span>
					@endif
				</a>
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
                            style="background-color: #9333ea !important; color: #ffffff !important;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Upload to Vault
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