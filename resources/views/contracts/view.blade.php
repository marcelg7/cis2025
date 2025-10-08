@php
    $isPdf = request()->is('contracts/*/download') || request()->is('contracts/*/download/*');
    $layout = $isPdf ? 'layouts.pdf' : 'layouts.app';
    \Illuminate\Support\Facades\Log::debug('View layout selection', ['isPdf' => $isPdf, 'layout' => $layout, 'path' => request()->path()]);
    // Calculate financial variables at the top to ensure availability
    $deviceAmount = ($contract->device_price ?? 0) - ($contract->agreement_credit_amount ?? 0);
    $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
    $monthlyDevicePayment = ($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0)) / 24;
    $earlyCancellationFee = $totalFinancedAmount + ($contract->device_return_amount ?? 0);
    $monthlyReduction = $monthlyDevicePayment;
@endphp
@extends($layout)
@section('content')
    @if ($isPdf)
        <style>
            .signature-wrapper { width: 100mm; height: auto; }
            .signature-wrapper img { width: 100%; height: auto !important; max-width: none; image-rendering: optimizeQuality; }
        </style>
    @endif
    @if (!$isPdf)
        <style>
            @media print {
                .no-print { display: none; }
                img { max-width: none !important; object-fit: contain; }
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
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 container">
        @if (!$isPdf && session('success'))
            <div class="bg-green-50 p-3 rounded-lg shadow-sm mb-6 no-print">
                {{ session('success') }}
            </div>
        @endif
        @if (!$isPdf && session('error'))
            <div class="bg-red-50 p-3 rounded-lg shadow-sm mb-6 no-print">
                {{ session('error') }}
            </div>
        @endif
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <!-- Header -->
            <div class="px-4 py-3 sm:px-4 flex justify-between items-start" style="{{ $isPdf ? 'padding: 0.5rem;' : '' }}">
                <div>
                    @php
                        $logoPath = $isPdf ? public_path('images/hayLogo.png') : asset('images/hayLogo.png');
                        $logoExists = $isPdf ? file_exists($logoPath) : true;
                        \Illuminate\Support\Facades\Log::debug('Logo path check in view', [
                            'contract_id' => $contract->id,
                            'logo_path' => $logoPath,
                            'logo_exists' => $logoExists,
                            'logo_readable' => $isPdf && $logoExists ? is_readable($logoPath) : true,
                            'logo_permissions' => $isPdf && $logoExists ? substr(sprintf('%o', fileperms($logoPath)), -4) : null,
                            'logo_file_size' => $isPdf && $logoExists ? filesize($logoPath) : null,
                        ]);
                    @endphp
                    @if ($logoExists)
                        <img src="{{ $logoPath }}" alt="Hay Communications" class="h-12" style="{{ $isPdf ? 'max-height: 150px; width: auto;' : '' }}">
                    @else
                        <p class="text-sm text-red-600">Logo not found at {{ public_path('images/hayLogo.png') }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-600" style="{{ $isPdf ? 'font-size: 9pt; margin-bottom: 0.25rem;' : '' }}">
                        <strong>Date:</strong> {{ $contract->start_date->format('M d, Y') }}<br>
                        <strong>Activity:</strong> {{ $contract->activityType->activity ?? 'Hardware Upgrade' }}<br>
                        <strong>Consultant:</strong> {{ auth()->user()->name ?? 'Marcel Gelinas' }}<br>
                        <strong>Store Phone Number:</strong> {{ $contract->location === 'zurich' ? '519-236-4333' : ($contract->location === 'exeter' ? '519-235-1234' : '519-238-5678') }}
                    </p>
                </div>
            </div>
            <div class="px-4 sm:px-4 text-center" style="{{ $isPdf ? 'padding: 0.5rem 0.5rem 0 0.5rem;' : '' }}">
                <h2 class="text-lg font-semibold text-gray-900" style="{{ $isPdf ? 'font-size: 14pt; margin-bottom: 0.25rem;' : '' }}">CRITICAL INFORMATION SUMMARY</h2>
                <h3 class="text-md font-medium text-gray-900" style="{{ $isPdf ? 'font-size: 12pt;' : '' }}">Wireless Mobility Agreement</h3>
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            <!-- Your Information -->
            <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem;' : '' }}">
                <h4 class="text-md font-medium text-gray-900" style="{{ $isPdf ? 'font-size: 12pt; margin-bottom: 0.25rem;' : '' }}">Your Information</h4>
                @if($isPdf)
                    <table width="100%" style="table-layout: fixed; font-size: 9pt; color: #333;">
                        <tr>
                            <td width="50%" style="padding-right: 0.5rem;">
                                <p><strong>Account Name:</strong> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                                <p><strong>Company Name:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->is_individual ? 'N/A' : $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name }}</p>
                                <p><strong>Mobile Account #:</strong> {{ $contract->subscriber->mobilityAccount->mobility_account }}</p>
                                <p><strong>Contact Number:</strong> {{ $contract->subscriber->mobile_number }}</p>
                                <p><strong>Mobile Number:</strong> {{ $contract->subscriber->mobile_number }}</p>
                                <p><strong>Subscriber:</strong> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                                <p><strong>Hay Account #:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->ivue_account }}</p>
                                <p><strong>Email:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->email ?? 'N/A' }}</p>
                                <p><strong>Address:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->address }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->city }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->state }} {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->zip_code }}</p>
                            </td>
                            <td width="50%" style="padding-left: 0.5rem;">
                                <p><strong>Monthly Payment Method:</strong> Pre-Authorized</p>
                                <p><strong>First Bill Date:</strong> {{ $contract->first_bill_date->format('M d, Y') }}</p>
                                <p><strong>To view your monthly usage register at:</strong><br>https://mybell.bell.ca/registration</p>
                                <p><strong>Bill Date for My Bell registration:</strong> {{ $contract->first_bill_date->day }}th</p>
                            </td>
                        </tr>
                    </table>
                @else
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
                                <p><strong>Bill Date for My Bell registration:</strong> {{ $contract->first_bill_date->day }}th</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            <!-- Device Details -->
            <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem;' : '' }}">
                <h4 class="text-md font-medium text-gray-900" style="{{ $isPdf ? 'font-size: 12pt; margin-bottom: 0.25rem;' : '' }}">Your Device Details</h4>
                @if($isPdf)
                    <table width="100%" style="table-layout: fixed; font-size: 9pt; color: #333;">
                        <tr>
                            <td width="50%" style="padding-right: 0.5rem;">
                                <p><strong>Model:</strong> {{ collect([
                                    $contract->manufacturer ? ucfirst($contract->manufacturer) : null,
                                    $contract->model ? ($contract->model === 'iphone' ? 'iPhone' : ucfirst($contract->model)) : null,
                                    $contract->version,
                                    $contract->device_storage ? str_replace('gb', 'GB', $contract->device_storage) : null,
                                    $contract->extra_info ? ucfirst($contract->extra_info) : null,
                                ])->filter()->implode(' ') }}</p>
                                <p style="font-style: italic; font-size: 8pt;">All amounts are before taxes.</p>
                                <p><strong>Device Retail Price:</strong> ${{ number_format($contract->device_price ?? 0, 2) }}</p>
                                <p><strong>Agreement Credit:</strong> ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }}</p>
                                <p><strong>Device Amount:</strong> ${{ number_format($deviceAmount, 2) }}</p>
                                <p><strong>Up-front Payment Required:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
                                <p><strong>Optional Up-front Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
                                <p><strong>Total Financed Amount (before tax):</strong> ${{ number_format($totalFinancedAmount, 2) }}</p>
                                <p><strong>Deferred Payment Amount:</strong> ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}</p>
                                <p><strong>Amount for Monthly Payment Calculation:</strong> ${{ number_format($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0), 2) }}</p>
                            </td>
                            <td width="50%" style="padding-left: 0.5rem;">
                                <h4 style="font-size: 12pt; font-weight: bold; margin-bottom: 0.25rem;">Monthly Device Payment: ${{ number_format($monthlyDevicePayment, 2) }}</h4>
                                <p><strong>Commitment Period:</strong> {{ $contract->commitmentPeriod->name ?? '2 Year Term Smart Pay' }}</p>
                                <p><strong>Remaining Device Balance:</strong> ${{ number_format($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0), 2) }}</p>
                                <p><strong>Start Date:</strong> {{ $contract->start_date->format('M d, Y') }}</p>
                                <p><strong>End Date:</strong> {{ $contract->end_date->format('M d, Y') }}</p>
                                <p>Your service will continue month-to-month after this end date.</p>
                                <p style="font-size: 8pt; margin-top: 0.5rem;">
                                    Early Cancellation Fee is the remaining balance of your device plus the full Deferred Return Option amount. In this case, your Buyout Cost would be ${{ number_format($monthlyDevicePayment, 2) }} per month left on the term plus the Device Return Option of ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}.<br>
                                    Fee will be $0 on {{ $contract->end_date->format('M d, Y') }} and will decrease each month by: ${{ number_format($monthlyReduction, 2) }}
                                </p>
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="mt-2 text-sm text-gray-600">
                                <p><strong>Model:</strong> {{ collect([
                                    $contract->manufacturer ? ucfirst($contract->manufacturer) : null,
                                    $contract->model ? ($contract->model === 'iphone' ? 'iPhone' : ucfirst($contract->model)) : null,
                                    $contract->version,
                                    $contract->device_storage ? str_replace('gb', 'GB', $contract->device_storage) : null,
                                    $contract->extra_info ? ucfirst($contract->extra_info) : null,
                                ])->filter()->implode(' ') }}</p>
                                <p class="mt-2 italic">All amounts are before taxes.</p>
                                <p><strong>Device Retail Price:</strong> ${{ number_format($contract->device_price ?? 0, 2) }}</p>
                                <p><strong>Agreement Credit:</strong> ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }}</p>
                                <p><strong>Device Amount:</strong> ${{ number_format($deviceAmount, 2) }}</p>
                                <p><strong>Up-front Payment Required:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
                                <p><strong>Optional Up-front Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
                                <p><strong>Total Financed Amount (before tax):</strong> ${{ number_format($totalFinancedAmount, 2) }}</p>
                                <p><strong>Deferred Payment Amount:</strong> ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}</p>
                                <p><strong>Amount for Monthly Payment Calculation:</strong> ${{ number_format($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0), 2) }}</p>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">Monthly Device Payment: ${{ number_format($monthlyDevicePayment, 2) }}</h4>
                            <div class="mt-2 text-sm text-gray-600">
                                <p><strong>Commitment Period:</strong> {{ $contract->commitmentPeriod->name ?? '2 Year Term Smart Pay' }}</p>
                                <p class="mt-2"><strong>Remaining Device Balance:</strong> ${{ number_format($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0), 2) }}</p>
                                <p><strong>Start Date:</strong> {{ $contract->start_date->format('M d, Y') }}</p>
                                <p><strong>End Date:</strong> {{ $contract->end_date->format('M d, Y') }}</p>
                                <p>Your service will continue month-to-month after this end date.</p>
                                <p class="mt-2 text-xs">
                                    Early Cancellation Fee is the remaining balance of your device plus the full Deferred Return Option amount. In this case, your Buyout Cost would be ${{ number_format($monthlyDevicePayment, 2) }} per month left on the term plus the Device Return Option of ${{ number_format($contract->device_return_amount ?? 0, 2) }}.<br>
                                    Fee will be $0 on {{ $contract->end_date->format('M d, Y') }} and will decrease each month by: ${{ number_format($monthlyReduction, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            <!-- Return Policy -->
            <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem; font-size: 8pt; color: #333;' : '' }}">
                <p class="text-xs text-gray-600">
                    Taxes not included. If you purchase a device from Hay which does not meet your needs, you may return the device if it is <strong>(a)</strong> returned within <strong>15</strong> calendar days of the commitment start date; <strong>(b)</strong> in "like new" condition with the original packaging, manuals, and accessories; and <strong>(c)</strong> returned with original receipt to the location. You are responsible for all service charges incurred prior to your return of the device. SIM Cards are not returnable. Postpaid Accounts: Hay will not accept devices with excessive usage in violation of our Responsible Use of Services Policy. Prepaid Accounts: The device has not exceeded <strong>30</strong> minutes of voice usage or <strong>50 MB</strong> of data usage. Funds added to your account are non-refundable. If you are a person with a disability, the same conditions apply; however, you may return your device within <strong>30</strong> calendar days of the commitment start date and, if in a Prepaid Account, double the corresponding permitted usage set out above.
                </p>
                @if ($isPdf)
                    <div style="page-break-after: always;"></div>
                @endif
            </div>
            <!-- Rate Plan Details -->
            <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem;' : '' }}">
                <h4 class="text-md font-medium text-gray-900" style="{{ $isPdf ? 'font-size: 12pt; margin-bottom: 0.25rem;' : '' }}">Your Rate Plan Details</h4>
                @if($isPdf)
                    <table width="100%" style="table-layout: fixed; font-size: 9pt; color: #333;">
                        <tr>
                            <td width="50%" style="padding-right: 0.5rem;">
                                <p><strong>Plan:</strong> {{ $contract->plan->name }}</p>
                            </td>
                            <td width="50%" style="padding-left: 0.5rem;">
                                <p><strong>Monthly Rate Plan Charge:</strong> ${{ number_format($contract->plan->price, 2) }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 0.25rem;">
                                <p>Unlimited Canada-wide calling</p>
                                <p>Unlimited Canada-wide text, picture, and video messages</p>
                                <p>50 GB unlimited non-shareable data at speeds up to 250 Mbps, after 75 GB reduced speeds are up to 512 Kbps.</p>
                                <p>Standard Definition Video Streaming (480p)</p>
                                <p>5G network access</p>
                                <p>Call Display | Message Centre | Call Waiting | Conference Calling</p>
                                <p>CAN-U.S Calling = $0.75/minute | CAN-U.S Texting = $0.40/text</p>
                                <p class="font-semibold">Note: This plan may be subject to rate increases by the provider, which will apply during your term.</p>
                                <p class="text-xs mt-2">If you exceed the usage allowed in your rate plan, additional usage charges may apply. See <a href="https://hay.net/cellular-service" class="text-indigo-600 hover:underline">hay.net/cellular-service</a> for current charges.</p>
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="mt-2 text-sm text-gray-600">
                                <p><strong>Plan:</strong> {{ $contract->plan->name }}</p>
                            </div>
                        </div>
                        <div>
                            <div class="mt-2 text-sm text-gray-600">
                                <p><strong>Monthly Rate Plan Charge:</strong> ${{ number_format($contract->plan->price, 2) }}</p>
                            </div>
                        </div>
                        <div class="col-span-2 text-sm text-gray-600">
                            <p>Unlimited Canada-wide calling</p>
                            <p>Unlimited Canada-wide text, picture, and video messages</p>
                            <p>50 GB unlimited non-shareable data at speeds up to 250 Mbps, after 75 GB reduced speeds are up to 512 Kbps.</p>
                            <p>Standard Definition Video Streaming (480p)</p>
                            <p>5G network access</p>
                            <p>Call Display | Message Centre | Call Waiting | Conference Calling</p>
                            <p>CAN-U.S Calling = $0.75/minute | CAN-U.S Texting = $0.40/text</p>
                            <p class="font-semibold">Note: This plan may be subject to rate increases by the provider, which will apply during your term.</p>
                            <p class="text-xs mt-2">If you exceed the usage allowed in your rate plan, additional usage charges may apply. See <a href="https://hay.net/cellular-service" class="text-indigo-600 hover:underline">hay.net/cellular-service</a> for current charges.</p>
                        </div>
                    </div>
                @endif
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            <!-- Minimum Monthly Charge -->
            <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem; font-size: 10pt; font-weight: bold;' : '' }}">
                @php
                    $minimumMonthlyCharge = ($contract->plan->price ?? 0) + $monthlyDevicePayment;
                @endphp
                Minimum Monthly Charge: ${{ number_format($minimumMonthlyCharge, 2) }}
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            <!-- Total Monthly Charges -->
            <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem;' : '' }}">
                @if($isPdf)
                    <table width="100%" style="table-layout: fixed; font-size: 10pt; font-weight: bold;">
                        <tr>
                            <td width="50%" style="padding-right: 0.5rem;">
                                Total Monthly Charges:
                                <span style="display: block; font-size: 8pt; font-weight: normal;">(Taxes and additional usage charges are extra.)</span>
                            </td>
                            <td width="50%" style="padding-left: 0.5rem; text-align: right;">
                                ${{ number_format($minimumMonthlyCharge + $totalAddOnCost, 2) }}
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-base font-semibold text-gray-900">
                                Total Monthly Charges:
                                <span class="text-xs text-gray-600 block">(Taxes and additional usage charges are extra.)</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-base font-semibold text-gray-900">
                                ${{ number_format($minimumMonthlyCharge + $totalAddOnCost, 2) }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            <!-- Add-ons -->
            @if($contract->addOns->count())
                <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem;' : '' }}">
                    <h4 class="text-md font-medium text-gray-900" style="{{ $isPdf ? 'font-size: 12pt; margin-bottom: 0.25rem;' : '' }}">Add-ons</h4>
                    <ul class="list-disc pl-4 text-xs text-gray-600" style="{{ $isPdf ? 'font-size: 9pt; padding-left: 1rem;' : '' }}">
                        @foreach($contract->addOns as $addOn)
                            <li>{{ $addOn->name }} ({{ $addOn->code }}): ${{ number_format($addOn->cost, 2) }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-1 text-xs text-gray-600" style="{{ $isPdf ? 'font-size: 9pt; margin-top: 0.25rem;' : '' }}"><strong>Total Add-on Cost:</strong> ${{ number_format($totalAddOnCost, 2) }}</p>
                </div>
                <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            @endif
            <!-- One-Time Fees -->
            @if($contract->oneTimeFees->count())
                <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem;' : '' }}">
                    <h4 class="text-md font-medium text-gray-900" style="{{ $isPdf ? 'font-size: 12pt; margin-bottom: 0.25rem;' : '' }}">One-Time Fees</h4>
                    <ul class="list-disc pl-4 text-xs text-gray-600" style="{{ $isPdf ? 'font-size: 9pt; padding-left: 1rem;' : '' }}">
                        @foreach($contract->oneTimeFees as $fee)
                            <li>{{ $fee->name }}: ${{ number_format($fee->cost, 2) }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-1 text-xs text-gray-600" style="{{ $isPdf ? 'font-size: 9pt; margin-top: 0.25rem;' : '' }}"><strong>Total One-Time Fee Cost:</strong> ${{ number_format($totalOneTimeFeeCost, 2) }}</p>
                </div>
                <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            @endif
            <!-- One-Time Charges -->
            <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem;' : '' }}">
                <h4 class="text-md font-medium text-gray-900" style="{{ $isPdf ? 'font-size: 12pt; margin-bottom: 0.25rem;' : '' }}">One-Time Charges</h4>
                @php
                    $subtotal = ($contract->amount_paid_for_device ?? 0) + ($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0);
                    $taxes = $subtotal * 0.13;
                    $total = $subtotal + $taxes;
                @endphp
                @if($isPdf)
                    <table width="100%" style="table-layout: fixed; font-size: 9pt; color: #333;">
                        <tr>
                            <td width="50%" style="padding-right: 0.5rem;">
                                <p><strong>Up-front Payment Requirement:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
                                <p><strong>Optional Up-front Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
                            </td>
                            <td width="50%" style="padding-left: 0.5rem; text-align: right;">
                                <table style="margin-left: auto;">
                                    <tr style="text-align: right;">
                                        <td style="padding-right: 0.5rem;">Subtotal:</td>
                                        <td>${{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                    <tr style="text-align: right;">
                                        <td style="padding-right: 0.5rem;">Taxes (13% HST):</td>
                                        <td>${{ number_format($taxes, 2) }}</td>
                                    </tr>
                                    <tr style="text-align: right; font-weight: bold;">
                                        <td style="padding-right: 0.5rem;">Total:</td>
                                        <td>${{ number_format($total, 2) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="text-sm text-gray-600">
                            <p><strong>Up-front Payment Requirement:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
                            <p><strong>Optional Up-front Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
                        </div>
                        <div class="text-sm text-gray-600">
                            <table class="ml-auto">
                                <tr class="text-right">
                                    <td class="pr-4">Subtotal:</td>
                                    <td>${{ number_format($subtotal, 2) }}</td>
                                </tr>
                                <tr class="text-right">
                                    <td class="pr-4">Taxes (13% HST):</td>
                                    <td>${{ number_format($taxes, 2) }}</td>
                                </tr>
                                <tr class="text-right">
                                    <td class="pr-4"><strong>Total:</strong></td>
                                    <td><strong>${{ number_format($total, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            <!-- Total Cost -->
            <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem; font-size: 10pt; font-weight: bold;' : '' }}">
                <p class="text-base font-semibold text-gray-900">
                    Total Contract Cost: ${{ number_format($totalCost, 2) }}
                </p>
            </div>
            <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            <!-- Signature -->
            @if ($contract->signature_path)
                @php
                    $signaturePath = trim($contract->signature_path);
                    $checkPath = str_replace('storage/', '', $signaturePath);
                    $signatureFullPath = storage_path('app/public/' . $checkPath);
                    $signatureExists = file_exists($signatureFullPath);
                    $signatureBase64 = null;
                    $signatureSrc = $isPdf ? 'file://' . $signatureFullPath : null;
                    $desiredWidthMm = 100;
                    $signatureHeightMm = 'auto';
                    if ($signatureExists && $isPdf) {
                        $imageSize = @getimagesize($signatureFullPath); // Suppress errors if GD fails
                        if ($imageSize && isset($imageSize[0]) && $imageSize[0] > 0) {
                            list($origWidth, $origHeight) = $imageSize;
                            $signatureHeightMm = ($origHeight / $origWidth) * $desiredWidthMm . 'mm';
                        }
                    }
                    $signatureStyle = $isPdf ? 'width: ' . $desiredWidthMm . 'mm; height: ' . $signatureHeightMm . '; display: block; margin-top: 0.25rem; image-rendering: optimizeQuality;' : '';
                    if ($signatureExists && !$isPdf) {
                        try {
							$signatureData = file_get_contents($signatureFullPath);
							$mime = pathinfo($signatureFullPath, PATHINFO_EXTENSION) === 'jpg' ? 'jpeg' : 'png';
							$signatureBase64 = "data:image/$mime;base64," . base64_encode($signatureData);
							$signatureSrc = $signatureBase64;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to encode signature to base64', [
                                'contract_id' => $contract->id,
                                'signature_path' => $signatureFullPath,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                    \Illuminate\Support\Facades\Log::debug('Signature path check in view', [
                        'contract_id' => $contract->id,
                        'signature_path' => $signaturePath,
                        'check_path' => $checkPath,
                        'signature_full_path' => $signatureFullPath,
                        'signature_exists' => $signatureExists,
                        'signature_readable' => $signatureExists ? is_readable($signatureFullPath) : false,
                        'signature_permissions' => $signatureExists ? substr(sprintf('%o', fileperms($signatureFullPath)), -4) : null,
                        'signature_file_size' => $signatureExists ? filesize($signatureFullPath) : null,
                        'signature_base64_available' => !empty($signatureBase64),
                        'signature_style' => $signatureStyle,
                    ]);
                @endphp
                <div class="px-4 py-3 sm:px-4" style="{{ $isPdf ? 'padding: 0.5rem;' : '' }}">
                    <h4 class="text-md font-medium text-gray-900" style="{{ $isPdf ? 'font-size: 12pt; margin-bottom: 0.25rem;' : '' }}">Signature</h4>
                    @if ($signatureExists && !empty($signatureSrc))
                        <div class="signature-wrapper">
                            <img src="{{ $signatureSrc }}" alt="Signature" class="mt-2" style="{{ $signatureStyle }}">
                        </div>
                    @elseif (!$isPdf)
                        <p class="text-sm text-red-600">Signature file not found or failed to load at {{ $checkPath }}</p>
                    @else
                        <p style="font-size: 9pt; color: #ff0000;">Signature not available</p> <!-- Silent fallback for PDF -->
                    @endif
                </div>
                <hr class="border-gray-200" style="{{ $isPdf ? 'margin: 0.5rem 0;' : '' }}">
            @endif
            <!-- Buttons -->
            @if (!$isPdf)
                <div class="px-4 py-5 sm:px-6 flex items-center space-x-4 no-print">
                    <a href="{{ route('contracts.download', $contract->id) }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 {{ $contract->status !== 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}"
                       style="background-color: #16a34a !important; color: #ffffff !important;"
                       {{ $contract->status !== 'finalized' ? 'disabled' : '' }}>
                        Download PDF
                    </a>
                    <form action="{{ route('contracts.email', $contract->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $contract->status !== 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}"
                                style="background-color: #2563eb !important; color: #ffffff !important;"
                                {{ $contract->status !== 'finalized' ? 'disabled' : '' }}>
                            Email Contract
                        </button>
                    </form>
                    <x-secondary-link href="{{ route('contracts.index') }}"
                                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                      style="background-color: #ffffff !important; color: #374151 !important;">
                        Back to Contracts
                    </x-secondary-link>
                    @if ($contract->status === 'draft')
                        <x-primary-link href="{{ route('contracts.edit', $contract->id) }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                        style="background-color: #ca8a04 !important; color: #ffffff !important;">
                            Edit
                        </x-primary-link>
                        <x-primary-link href="{{ route('contracts.sign', $contract->id) }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        style="background-color: #2563eb !important; color: #ffffff !important;">
                            Sign
                        </x-primary-link>
                    @endif
                    @if ($contract->status === 'signed')
                        <form action="{{ route('contracts.finalize', $contract->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    style="background-color: #16a34a !important; color: #ffffff !important;">
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
                                Create Revision
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection