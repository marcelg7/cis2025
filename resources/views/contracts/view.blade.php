@php
    $isPdf = request()->is('contracts/*/download') || request()->is('contracts/*/download/*');
    $layout = $isPdf ? 'layouts.pdf' : 'layouts.app';
    \Illuminate\Support\Facades\Log::debug('View layout selection', ['isPdf' => $isPdf, 'layout' => $layout, 'path' => request()->path()]);
@endphp

@extends($layout)

@section('content')
@if ($isPdf)
<style>
body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 9pt;
    color: #333;
    margin: 0;
    padding: 0;
}
.container {
    width: 100%;
    max-width: 190mm;
    margin: 0 auto;
}
.header {
    position: fixed;
    top: 5mm;
    left: 10mm;
    right: 10mm;
    height: 40mm;
}
.footer {
    position: fixed;
    bottom: 5mm;
    left: 10mm;
    right: 10mm;
    text-align: center;
    font-size: 7pt;
    color: #666;
}
.signature-wrapper {
    width: 100mm;
    height: auto;
}
.signature-wrapper img {
    width: 100%;
    height: auto;
    max-height: 50mm;
    image-rendering: optimizeQuality;
    object-fit: contain;
    display: block;
}
hr {
    border: 0;
    border-top: 1px solid #ccc;
    margin: 2mm 0;
}
.section {
    page-break-inside: avoid;
    page-break-before: auto;
}
</style>
<script type="text/php">
if (isset($pdf)) {
    $x = 500;
    $y = 10;
    $font = $fontMetrics->getFont('Helvetica');
    $pdf->page_text($x, $y, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 7, [0.4, 0.4, 0.4]);
}
</script>
@endif
@if (!$isPdf)
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
@endif
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8 px-4 container">
    @if (!$isPdf && session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 no-print">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if (!$isPdf && session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 no-print">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif
    @if ($isPdf)
        <div class="header">
            @php
                $logoPath = public_path('images/hayLogo.png');
                $logoExists = file_exists($logoPath);
            @endphp
            @if ($logoExists)
                <img src="{{ $logoPath }}" alt="Hay Communications" style="height: 40mm; width: auto; float: left;">
            @endif
            <div style="float: right; width: 90mm; text-align: right; font-size: 8pt; color: #333;">
                <p><strong>Critical Information Summary</strong></p>
                <p>Wireless Mobility Agreement</p>
            </div>
            <div style="clear: both;"></div>
        </div>
        <div class="footer">
            <p>Hay Communications | www.hay.net | {{ $contract->location === 'zurich' ? '519-236-4333' : ($contract->location === 'exeter' ? '519-235-1234' : '519-238-5678') }}</p>
        </div>
    @endif
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200" style="{{ $isPdf ? 'margin-top: 45mm; margin-bottom: 15mm; background: #fff; border: 1px solid #ccc; padding: 4mm;' : '' }}">
        @if (!$isPdf)
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
        @endif

        <!-- Your Information -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
            <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 10pt; margin-bottom: 1mm;' : '' }}">Your Information</h3>
            @if($isPdf)
                <div style="font-size: 8pt; color: #333; line-height: 1.3;">
                    <div style="float: left; width: 48%; margin-right: 2%;">
                        <p><strong>Account Name:</strong> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                        <p><strong>Company Name:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->is_individual ? 'N/A' : $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name }}</p>
                        <p><strong>Mobile Account #:</strong> {{ $contract->subscriber->mobilityAccount->mobility_account }}</p>
                        <p><strong>Contact Number:</strong> {{ $contract->subscriber->mobile_number }}</p>
                        <p><strong>Hay Account #:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->ivue_account }}</p>
                        <p><strong>Email:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->email ?? 'N/A' }}</p>
                        <p><strong>Address:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->address }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->city }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->state }} {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->zip_code }}</p>
                    </div>
                    <div style="float: left; width: 48%;">
                        <p><strong>Monthly Payment Method:</strong> Pre-Authorized</p>
                        <p><strong>First Bill Date:</strong> {{ $contract->first_bill_date->format('M d, Y') }}</p>
                        <p><strong>My Bell Registration:</strong> https://mybell.bell.ca/registration</p>
                        <p><strong>Bill Date:</strong> {{ $contract->first_bill_date->day }}th</p>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700"><span class="font-semibold">Account Name:</span> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Company Name:</span> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->is_individual ? 'N/A' : $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Mobile Account #:</span> {{ $contract->subscriber->mobilityAccount->mobility_account }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Contact Number:</span> {{ $contract->subscriber->mobile_number }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Mobile Number:</span> {{ $contract->subscriber->mobile_number }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Subscriber:</span> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Hay Account #:</span> {{ $contract->subscriber->mobilityAccount->ivueAccount->ivue_account }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Email:</span> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->email ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Address:</span> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->address }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->city }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->state }} {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->zip_code }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700"><span class="font-semibold">Monthly Payment Method:</span> Pre-Authorized</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">First Bill Date:</span> {{ $contract->first_bill_date->format('M d, Y') }}</p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">To view your monthly usage register at:</span><br><a href="https://mybell.bell.ca/registration" class="text-indigo-600 hover:underline">https://mybell.bell.ca/registration</a></p>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Bill Date for My Bell registration:</span> {{ $contract->first_bill_date->day }}th</p>
                    </div>
                </div>
            @endif
        </div>
        <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">

        <!-- Device Details -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
            <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 10pt; margin-bottom: 1mm;' : '' }}">Device Details</h3>
            @php
                $deviceDisplayName = $contract->bell_device_id && $contract->bellDevice ? $contract->bellDevice->name : 'N/A';
            @endphp
            @if($isPdf)
                <div style="font-size: 8pt; color: #333; line-height: 1.3;">
                    <div style="float: left; width: 48%; margin-right: 2%;">
                        <p><strong>Model:</strong> {{ $deviceDisplayName }}</p>
                        @if($contract->bell_device_id && $contract->bellDevice)
                            <p style="margin-top: 1mm;"><strong>Pricing Type:</strong> {{ ucfirst($contract->bell_pricing_type ?? 'N/A') }}</p>
                            <p><strong>Plan Tier:</strong> {{ $contract->bell_tier ?? 'N/A' }}</p>
                            @if($contract->bell_dro_amount && $contract->bell_dro_amount > 0)
                                <p><strong>DRO Amount:</strong> ${{ number_format($contract->bell_dro_amount, 2) }}</p>
                            @endif
                        @endif
                        <p style="font-style: italic; font-size: 7pt; margin-top: 1mm;">All amounts are before taxes.</p>
                        <p><strong>Device Retail Price:</strong> ${{ number_format($devicePrice, 2) }}</p>
                        <p><strong>Agreement Credit:</strong> ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }}</p>
                        <p><strong>Device Amount:</strong> ${{ number_format($deviceAmount, 2) }}</p>
                        <p><strong>Up-front Payment Required:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
                        <p><strong>Optional Up-front Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
                        <p><strong>Total Financed Amount:</strong> ${{ number_format($totalFinancedAmount, 2) }}</p>
                        <p><strong>Deferred Payment Amount:</strong> ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}</p>
                    </div>
                    <div style="float: left; width: 48%;">
                        <p><strong>Monthly Device Payment:</strong> ${{ number_format($monthlyDevicePayment, 2) }}</p>
                        @if($contract->bell_device_id && $contract->bell_monthly_device_cost)
                            <p style="font-size: 7pt; color: #666;">(Bell Calculated: ${{ number_format($contract->bell_monthly_device_cost, 2) }})</p>
                        @endif
                        <p><strong>Commitment Period:</strong> {{ $contract->commitmentPeriod->name ?? '2 Year Term Smart Pay' }}</p>
                        <p><strong>Remaining Device Balance:</strong> ${{ number_format($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0), 2) }}</p>
                        <p><strong>Start Date:</strong> {{ $contract->start_date->format('M d, Y') }}</p>
                        <p><strong>End Date:</strong> {{ $contract->end_date->format('M d, Y') }}</p>
                        <p style="font-size: 7pt; margin-top: 1mm;">
                            Early Cancellation Fee: ${{ number_format($monthlyDevicePayment, 2) }} per month left plus Device Return Option of ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}.<br>
                            Fee will be $0 on {{ $contract->end_date->format('M d, Y') }}; decreases monthly by: ${{ number_format($monthlyReduction, 2) }}
                        </p>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            @else
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
            @endif
        </div>
        <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">

        @if (!$isPdf)
            @include('contracts.partials._cellular_pricing_display')
            <hr class="border-gray-200">
        @endif

        <!-- Return Policy -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; font-size: 7pt; color: #333; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
            <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 1mm;' : '' }}">Return Policy</h3>
            <p class="text-sm text-gray-600 prose prose-sm max-w-none" style="{{ $isPdf ? 'font-size: 7pt; line-height: 1.2;' : '' }}">
                Taxes not included. If you purchase a device from Hay which does not meet your needs, you may return the device if it is <strong>(a)</strong> returned within <strong>15</strong> calendar days of the commitment start date; <strong>(b)</strong> in "like new" condition with the original packaging, manuals, and accessories; and <strong>(c)</strong> returned with original receipt to the location. You are responsible for all service charges incurred prior to your return of the device. SIM Cards are not returnable. Postpaid Accounts: Hay will not accept devices with excessive usage in violation of our Responsible Use of Services Policy. Prepaid Accounts: The device has not exceeded <strong>30</strong> minutes of voice usage or <strong>50 MB</strong> of data usage. Funds added to your account are non-refundable. If you are a person with a disability, the same conditions apply; however, you may return your device within <strong>30</strong> calendar days of the commitment start date and, if in a Prepaid Account, double the corresponding permitted usage set out above.
            </p>
        </div>
        <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">

        <!-- Rate Plan Details -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
            <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 1mm;' : '' }}">Rate Plan Details</h3>
            @if($isPdf)
                <div style="font-size: 8pt; color: #333; line-height: 1.2;">
                    <div style="float: left; width: 48%; margin-right: 2%;">
                        <p><strong>Plan:</strong> {{ $contract->bell_tier ?? 'N/A' }}</p>
                        @if($contract->ratePlan && $contract->ratePlan->tier)
                            <p><strong>Tier:</strong> {{ $contract->ratePlan->tier }} Tier</p>
                        @endif
                    </div>
                    <div style="float: left; width: 48%;">
                        <p><strong>Monthly Rate Plan Charge:</strong> ${{ number_format($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0, 2) }}</p>
                    </div>
                    <div style="clear: both;"></div>
                    @if($contract->ratePlan && $contract->ratePlan->features)
                        <div style="margin-top: 1mm;">
                            {!! $contract->ratePlan->features !!}
                        </div>
                    @endif
                    <div style="margin-top: 1mm;">
                        <p style="font-weight: bold;">Note: This plan may be subject to rate increases by the provider, which will apply during your term.</p>
                        <p style="font-size: 6pt;">See <a href="https://hay.net/cellular-service">hay.net/cellular-service</a> for additional usage charges.</p>
                    </div>
                </div>
            @else
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
            @endif
        </div>

        <!-- Mobile Internet Plan Details -->
        @if($contract->mobileInternetPlan)
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">
            <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
                <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 1mm;' : '' }}">Mobile Internet Plan Details</h3>
                @if($isPdf)
                    <div style="font-size: 8pt; color: #333; line-height: 1.2;">
                        <div style="float: left; width: 48%; margin-right: 2%;">
                            <p><strong>Plan:</strong> {{ $contract->mobileInternetPlan->plan_name }}</p>
                        </div>
                        <div style="float: left; width: 48%;">
                            <p><strong>Monthly Charge:</strong> ${{ number_format($contract->mobile_internet_price ?? 0, 2) }}</p>
                        </div>
                        <div style="clear: both;"></div>
                        @if($contract->mobileInternetPlan->description)
                            <div style="margin-top: 1mm;">
                                {!! $contract->mobileInternetPlan->description !!}
                            </div>
                        @endif
                    </div>
                @else
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
                @endif
            </div>
        @endif

        <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">

        <!-- Minimum Monthly Charge -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; font-size: 9pt; font-weight: bold; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
            @php
                $minimumMonthlyCharge = ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) + $monthlyDevicePayment;
            @endphp
            <p class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; font-weight: bold; margin: 0;' : '' }}">Minimum Monthly Charge: ${{ number_format($minimumMonthlyCharge, 2) }}</p>
        </div>
        <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">

        <!-- Total Monthly Charges -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
            <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 1mm;' : '' }}">Total Monthly Charges</h3>
            @if($isPdf)
                <div style="font-size: 9pt; font-weight: bold; color: #333;">
                    <p>Total Monthly Charges: ${{ number_format($minimumMonthlyCharge + $totalAddOnCost, 2) }}</p>
                    <p style="font-size: 7pt; font-weight: normal; margin-top: 1mm;">(Taxes and additional usage charges are extra.)</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-lg font-semibold text-gray-900">Total Monthly Charges</p>
                        <p class="text-xs text-gray-600 mt-1">(Taxes and additional usage charges are extra.)</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg text-right">
                        <p class="text-lg font-semibold text-gray-900">${{ number_format($minimumMonthlyCharge + $totalAddOnCost, 2) }}</p>
                    </div>
                </div>
            @endif
        </div>
        <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">

        <!-- Add-ons -->
        @if($contract->addOns->count())
            <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
                <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 1mm;' : '' }}">Add-ons</h3>
                <ul style="{{ $isPdf ? 'font-size: 8pt; color: #333; list-style-type: disc; padding-left: 4mm; margin-bottom: 1mm;' : 'list-disc pl-6 text-sm text-gray-700 mt-2' }}">
                    @foreach($contract->addOns as $addOn)
                        <li>{{ $addOn->name }} ({{ $addOn->code }}): ${{ number_format($addOn->cost, 2) }}</li>
                    @endforeach
                </ul>
                <p style="{{ $isPdf ? 'font-size: 8pt; font-weight: bold; color: #333; margin-top: 1mm;' : 'text-sm font-semibold text-gray-700 mt-2' }}">Total Add-on Cost: ${{ number_format($totalAddOnCost, 2) }}</p>
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">
        @endif

        <!-- One-Time Fees -->
        @if($contract->oneTimeFees->count())
            <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
                <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 1mm;' : '' }}">One-Time Fees</h3>
                <ul style="{{ $isPdf ? 'font-size: 8pt; color: #333; list-style-type: disc; padding-left: 4mm; margin-bottom: 1mm;' : 'list-disc pl-6 text-sm text-gray-700 mt-2' }}">
                    @foreach($contract->oneTimeFees as $fee)
                        <li>{{ $fee->name }}: ${{ number_format($fee->cost, 2) }}</li>
                    @endforeach
                </ul>
                <p style="{{ $isPdf ? 'font-size: 8pt; font-weight: bold; color: #333; margin-top: 1mm;' : 'text-sm font-semibold text-gray-700 mt-2' }}">Total One-Time Fee Cost: ${{ number_format($totalOneTimeFeeCost, 2) }}</p>
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">
        @endif

        <!-- One-Time Charges -->
        <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
            <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 1mm;' : '' }}">One-Time Charges</h3>
            @php
                $subtotal = $deviceAmount + ($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0);
                $taxes = $subtotal * 0.13;
                $total = $subtotal + $taxes;
            @endphp
            @if($isPdf)
                <div style="font-size: 8pt; color: #333; line-height: 1.2;">
                    <div style="float: left; width: 48%; margin-right: 2%;">
                        <p><strong>Up-front Payment Requirement:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
                        <p><strong>Optional Up-front Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
                    </div>
                    <div style="float: left; width: 48%;">
                        <p><strong>Subtotal:</strong> ${{ number_format($subtotal, 2) }}</p>
                        <p><strong>Taxes (13% HST):</strong> ${{ number_format($taxes, 2) }}</p>
                        <p><strong>Total:</strong> ${{ number_format($total, 2) }}</p>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            @else
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
            @endif
        </div>
        <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">

        <!-- Total Contract Cost -->
        <div class="section px-6 py-4 bg-blue-50 border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #e6f0ff; border-bottom: 1px solid #ccc;' : '' }}">
            <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 1mm;' : '' }}">Total Contract Cost Breakdown</h3>
            @if($isPdf)
                <div style="font-size: 8pt; color: #333; line-height: 1.2;">
                    <ul style="list-style-type: disc; padding-left: 4mm; margin-bottom: 1mm;">
                        <li>Device Cost (after ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }} credit): ${{ number_format($deviceAmount, 2) }}</li>
                        <li>Rate Plan (24 months): ${{ number_format(($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) * 24, 2) }}</li>
                        <li>Add-ons (24 months): ${{ number_format($totalAddOnCost * 24, 2) }}</li>
                        <li>One-Time Fees: ${{ number_format($totalOneTimeFeeCost, 2) }}</li>
                    </ul>
                    <div style="float: left; width: 48%; margin-right: 2%;">
                        <p style="font-weight: bold;">Total Contract Cost (before taxes):</p>
                        <p style="font-size: 7pt;">Estimated taxes (13% HST):</p>
                        <p style="font-size: 7pt;">Total with estimated taxes:</p>
                    </div>
                    <div style="float: left; width: 48%; text-align: right;">
                        <p style="font-weight: bold;">${{ number_format($totalCost, 2) }}</p>
                        <p style="font-size: 7pt;">${{ number_format($totalCost * 0.13, 2) }}</p>
                        <p style="font-size: 7pt;">${{ number_format($totalCost * 1.13, 2) }}</p>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            @else
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
            @endif
        </div>
        <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">

        <!-- Signature -->
        @if ($contract->signature_path)
            @php
                $signaturePath = trim($contract->signature_path);
                $checkPath = str_replace('storage/', '', $signaturePath);
                $signatureFullPath = storage_path('app/public/' . $checkPath);
                $signatureExists = file_exists($signatureFullPath);
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
            <div class="section px-6 py-4 bg-white border-b border-gray-200" style="{{ $isPdf ? 'padding: 2mm; background: #fff; border-bottom: 1px solid #ccc;' : '' }}">
                <h3 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 1mm;' : '' }}">Signature</h3>
                @if ($signatureExists && !empty($signatureSrc))
                    <div class="signature-wrapper" style="{{ $isPdf ? '' : 'border border-gray-200 rounded-lg p-2 bg-gray-50 mt-4' }}">
                        <img src="{{ $signatureSrc }}" alt="Signature" style="{{ $isPdf ? 'max-height: 50mm;' : 'max-h-32 w-auto' }}">
                    </div>
                @elseif (!$isPdf)
                    <p class="text-sm text-red-600">Signature file not found at {{ $checkPath }}</p>
                @else
                    <p style="font-size: 7pt; color: #ff0000;">Signature not available</p>
                @endif
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 2mm 0; border-color: #ccc;' : '' }}">
        @endif

        <!-- Buttons -->
        @if (!$isPdf)
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
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5m-5 0a9 9 0 1112.728 2.314M12 12V9m0 0h3"></path></svg>
                            Create Revision
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection