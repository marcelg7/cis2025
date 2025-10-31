@extends('layouts.pdf')

@section('content')
<div class="header">
    @php
        $logoPath = public_path('images/hayLogo.png');
        $logoExists = file_exists($logoPath);
    @endphp
    @if ($logoExists)
        <img src="{{ $logoPath }}" alt="Hay Communications" style="height: auto; width: auto; max-height: 10mm; float: left; margin-bottom: 0;">
    @endif
    <div style="float: right; width: 90mm; text-align: right; font-size: 7pt; color: #333; margin-top: 0;">
        <p><strong>Critical Information Summary</strong></p>
        <p>Wireless Mobility Agreement</p>
    </div>
    <div style="clear: both; height: 0;"></div>
</div>
<div class="footer">
    <p style="font-size: 7pt;">Hay Communications | www.hay.net | {{ $contract->locationModel?->phone ?? '519-238-5678' }}</p>
</div>
<div style="margin-top: 10mm; margin-bottom: 10mm; background: #fff; border: 1px solid #ccc; padding: 0;">
    <!-- Your Information Section -->
    <div style="padding: 0;">
        <h4 style="font-size: 10pt; margin: 0;">Your Information</h4>
        <table width="100%" style="table-layout: fixed; font-size: 7pt; color: #333; line-height: 1.0;">
            <tr>
                <td width="50%" style="padding-right: 0.1rem;">
                    <p><strong>Account Name:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->first_name }} {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->last_name }}</p>
                    <p><strong>Company Name:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->is_individual ? 'N/A' : $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name }}</p>
                    <p><strong>Mobile Account #:</strong> {{ $contract->subscriber->mobilityAccount->mobility_account }}</p>
                    <p><strong>Contact Number:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->phone ?? $contract->subscriber->mobile_number }}</p>
                    <p><strong>Mobile Number:</strong> {{ $contract->subscriber->mobile_number }}</p>
                    <p><strong>Subscriber:</strong> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                    <p><strong>Hay Account #:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->ivue_account }}</p>
                    <p><strong>Email:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->email ?? 'N/A' }}</p>
                    <p><strong>Address:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->address }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->city }}, {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->state }} {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->zip_code }}</p>
                </td>
                <td width="50%" style="padding-left: 0.1rem;">
                    <p><strong>Monthly Payment Method:</strong> Pre-Authorized</p>
                    <p><strong>First Bill Date:</strong> {{ $contract->first_bill_date->format('M d, Y') }}</p>
                    <p><strong>To view your monthly usage register at:</strong><br>https://mybell.bell.ca/registration</p>
                    <p><strong>Bill Date for My Bell registration:</strong> {{ $contract->start_date->day }}{{ $contract->start_date->day == 1 ? 'st' : ($contract->start_date->day == 2 ? 'nd' : ($contract->start_date->day == 3 ? 'rd' : 'th')) }}</p>
                    
                    @php
                        // Calculate proration details
                        $startDate = $contract->start_date;
                        $billingCycleStart = 11;
                        $billingCycleEnd = 10;
                        
                        $partialMonthEnd = $startDate->copy();
                        if ($startDate->day <= $billingCycleEnd) {
                            $partialMonthEnd->day($billingCycleEnd);
                        } else {
                            $partialMonthEnd->addMonth()->day($billingCycleEnd);
                        }
                        
                        $firstFullMonthStart = $partialMonthEnd->copy()->addDay();
                        $firstFullMonthEnd = $firstFullMonthStart->copy()->addMonth()->subDay();
                        
                        $partialDays = $startDate->diffInDays($partialMonthEnd) + 1;
                    @endphp
                    
                    <p style="margin-top: 0; font-size: 6pt;">
                        <strong>Proration Information:</strong><br>
                        Your first bill will include prorated charges for service from <strong>{{ $startDate->format('M d, Y') }}</strong> to <strong>{{ $partialMonthEnd->format('M d, Y') }}</strong> ({{ $partialDays }} days), plus your first full month from <strong>{{ $firstFullMonthStart->format('M d, Y') }}</strong> to <strong>{{ $firstFullMonthEnd->format('M d, Y') }}</strong>. After that, your bills will follow the regular monthly cycle (11th to 10th).
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <hr style="margin: 0; border-color: #ccc;">

    <!-- Device Details -->
    @if($contract->bell_pricing_type === 'byod')
        <!-- BYOD Plan - Simplified Display -->
        <div style="padding: 0;">
            <h3 style="font-size: 10pt; margin: 0;">Device Details</h3>
            <div style="font-size: 7pt; color: #333; background: #e3f2fd; border: 1px solid #90caf9; padding: 3mm; margin-top: 2mm;">
                <p style="font-weight: bold; margin: 0;">Bring Your Own Device (BYOD) Plan</p>
                <p style="margin: 2mm 0 0 0;">This is a Bring Your Own Device plan. The customer is using their own device. No device financing is required for this contract.</p>
            </div>
        </div>
    @else
        <!-- Regular Device Details (SmartPay/DRO) -->
        <div style="padding: 0;">
            <h3 style="font-size: 10pt; margin: 0;">Device Details</h3>
            @php
                // Use stored device_name instead of relationship to preserve historical data
                $deviceDisplayName = $contract->device_name ?? $contract->custom_device_name ?? 'N/A';
            @endphp
            <div style="font-size: 7pt; color: #333; line-height: 1.0;">
                <div style="float: left; width: 48%; margin-right: 2%;">
                    <p><strong>Model:</strong> {{ $deviceDisplayName }}</p>
                    
                    <!-- IMEI Display -->
                    @if($contract->imei)
                        <p><strong>IMEI:</strong> {{ $contract->imei }}</p>
                    @endif
                    
                    @if($contract->bell_device_id && $contract->bellDevice)
                        <p style="margin-top: 0;"><strong>Pricing Type:</strong> {{ $contract->bell_pricing_type === 'dro' ? 'DRO' : ucfirst($contract->bell_pricing_type ?? 'N/A') }}</p>
                        <p><strong>Plan Tier:</strong> {{ $contract->bell_tier ?? 'N/A' }}</p>
                        @if($contract->bell_dro_amount && $contract->bell_dro_amount > 0)
                            <p><strong>DRO Amount:</strong> ${{ number_format($contract->bell_dro_amount, 2) }}</p>
                        @endif
                    @endif
                    <p style="font-style: italic; font-size: 6pt; margin-top: 0;">All amounts are before taxes.</p>
                    <p><strong>Device Retail Price:</strong> ${{ number_format($devicePrice, 2) }}</p>
                    <p><strong>Agreement Credit:</strong> ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }}</p>
                    <!-- REMOVED: Device Amount line -->
                    <p><strong>Deferred Payment Amount:</strong> ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}</p>
                    <p><strong>Up-front Payment Required:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
                    <p><strong>Optional Up-front Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
                    <p><strong>Total Financed Amount (before tax):</strong> ${{ number_format($totalFinancedAmount, 2) }}</p>
                    <p><strong>Remaining Device Balance:</strong> ${{ number_format($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0), 2) }}</p>
                </div>
                <div style="float: left; width: 48%;">
                    <p><strong>Monthly Device Payment:</strong> ${{ number_format($monthlyDevicePayment, 2) }}</p>
                    @if($contract->bell_device_id && $contract->bell_monthly_device_cost)
                        <p style="font-size: 6pt; color: #666;">(Bell Calculated: ${{ number_format($contract->bell_monthly_device_cost, 2) }})</p>
                    @endif

                    <p style="font-size: 6pt; margin-top: 0;">
						@if($cancellationPolicy)
							{!! nl2br(e($cancellationPolicy)) !!}
						@else
							Early Cancellation Fee: ${{ number_format($buyoutCost, 2) }} per month left plus Device Return Option of ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}.<br>
							Fee will be $0 on {{ $contract->end_date->format('M d, Y') }}; decreases monthly by: ${{ number_format($buyoutCost, 2) }}
						@endif
											</p>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>
    @endif
    <hr style="margin: 0; border-color: #ccc;">

    <!-- Return Policy -->
    @if($contract->bell_pricing_type !== 'byod')
        <div style="padding: 0; font-size: 6pt; color: #333; background: #fff; border-bottom: 1px solid #ccc;">
            <h3 style="font-size: 8pt; margin: 0;">Return Policy</h3>
            <p style="line-height: 1.0;">
                Taxes not included. If you purchase a device from Hay which does not meet your needs, you may return the device if it is (a) returned within 15 calendar days of the commitment start date; (b) in "like new" condition with the original packaging, manuals, and accessories; and (c) returned with original receipt to the location. You are responsible for all service charges incurred prior to your return of the device. SIM Cards are not returnable. Postpaid Accounts: Hay will not accept devices with excessive usage in violation of our Responsible Use of Services Policy. Prepaid Accounts: The device has not exceeded 30 minutes of voice usage or 50 MB of data usage. Funds added to your account are non-refundable. If you are a person with a disability, the same conditions apply; however, you may return your device within 30 calendar days of the commitment start date and, if in a Prepaid Account, double the corresponding permitted usage set out above.
            </p>
        </div>
        <hr style="margin: 0; border-color: #ccc;">
    @endif

    <!-- Rate Plan Details -->
    <div style="padding: 0; background: #fff; border-bottom: 1px solid #ccc;">
        <h3 style="font-size: 8pt; margin: 0;">Rate Plan Details</h3>
        <div style="font-size: 6pt; color: #333; line-height: 1.0;">
            <p><strong>Rate Plan:</strong> {{ $contract->ratePlan?->plan_name ?? 'N/A' }}</p>
            <p><strong>Monthly Rate Plan Charge:</strong> ${{ number_format($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0, 2) }}</p>
            @if($contract->ratePlan && $contract->ratePlan->features)
                {!! \App\Helpers\MarkdownHelper::sanitize(Str::markdown($contract->ratePlan->features)) !!}
            @endif
            <p style="font-weight: bold;">Note: This plan may be subject to rate increases by the provider, which will apply during your term.</p>
            <p style="font-size: 5pt;">See hay.net/cellular-service for additional usage charges.</p>
        </div>
    </div>

    @if($contract->mobileInternetPlan)
        <hr style="margin: 0; border-color: #ccc;">
        <div style="padding: 0; background: #fff; border-bottom: 1px solid #ccc;">
            <h3 style="font-size: 8pt; margin: 0;">Mobile Internet Plan Details</h3>
            <div style="font-size: 6pt; color: #333; line-height: 1.0;">
                <p><strong>Plan:</strong> {{ $contract->mobileInternetPlan->plan_name }}</p>
                <p><strong>Monthly Charge:</strong> ${{ number_format($contract->mobile_internet_price ?? 0, 2) }}</p>
                @if($contract->mobileInternetPlan->description)
                    {!! \App\Helpers\MarkdownHelper::sanitize(Str::markdown($contract->mobileInternetPlan->description)) !!}
                @endif
            </div>
        </div>
    @endif

    <hr style="margin: 0; border-color: #ccc;">

    <!-- Minimum Monthly Charge -->
    <div style="padding: 0; font-size: 8pt; font-weight: bold; background: #fff; border-bottom: 1px solid #ccc;">
        @php
            $minimumMonthlyCharge = ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) + $monthlyDevicePayment;
        @endphp
        <p style="margin: 0;">Minimum Monthly Charge: ${{ number_format($minimumMonthlyCharge, 2) }}</p>
    </div>
    <hr style="margin: 0; border-color: #ccc;">

    <!-- Add-ons (MOVED BEFORE Total Monthly Charges) -->
    @if($contract->addOns->count())
        <div style="padding: 0; background: #fff; border-bottom: 1px solid #ccc;">
            <h3 style="font-size: 8pt; margin: 0;">Add-ons</h3>
            <ul style="font-size: 6pt; color: #333; list-style-type: disc; padding-left: 4mm; margin: 0; line-height: 1.0;">
                @foreach($contract->addOns as $addOn)
                    <li>{{ $addOn->name }} ({{ $addOn->code }}): ${{ number_format($addOn->cost, 2) }}</li>
                @endforeach
            </ul>
            <p style="font-size: 6pt; font-weight: bold; color: #333; margin: 0;">Total Add-on Cost: ${{ number_format($totalAddOnCost, 2) }}</p>
        </div>
        <hr style="margin: 0; border-color: #ccc;">
    @endif

    <!-- Total Monthly Charges (MOVED AFTER Add-ons) -->
    <div style="padding: 0; background: #fff; border-bottom: 1px solid #ccc;">
        <h3 style="font-size: 8pt; margin: 0;">Total Monthly Charges</h3>
        <div style="font-size: 8pt; font-weight: bold; color: #333;">
            <p>Total Monthly Charges: ${{ number_format($minimumMonthlyCharge + $totalAddOnCost, 2) }}</p>
            <p style="font-size: 6pt; font-weight: normal; margin: 0;">(Taxes and additional usage charges are extra.)</p>
        </div>
    </div>
    <hr style="margin: 0; border-color: #ccc;">

    <!-- One-Time Fees -->
    @if($contract->oneTimeFees->count())
        <div style="padding: 0; background: #fff; border-bottom: 1px solid #ccc;">
            <h3 style="font-size: 8pt; margin: 0;">One-Time Fees</h3>
            <ul style="font-size: 6pt; color: #333; list-style-type: disc; padding-left: 4mm; margin: 0; line-height: 1.0;">
                @foreach($contract->oneTimeFees as $fee)
                    <li>{{ $fee->name }}: ${{ number_format($fee->cost, 2) }}</li>
                @endforeach
            </ul>
            <p style="font-size: 6pt; font-weight: bold; color: #333; margin: 0;">Total One-Time Fee Cost: ${{ number_format($totalOneTimeFeeCost, 2) }}</p>
        </div>
        <hr style="margin: 0; border-color: #ccc;">
    @endif

    <!-- One-Time Charges (UPDATED CALCULATION) -->
    <div style="padding: 0; background: #fff; border-bottom: 1px solid #ccc;">
        <h3 style="font-size: 8pt; margin: 0;">One-Time Charges</h3>
        @php
            // Updated: One-time fees + upfront payments
            $subtotal = $totalOneTimeFeeCost + ($contract->required_upfront_payment ?? 0) + ($contract->optional_down_payment ?? 0);
            $taxes = $subtotal * 0.13;
            $total = $subtotal + $taxes;
        @endphp
        <div style="font-size: 6pt; color: #333; line-height: 1.0;">
            <div style="float: left; width: 48%; margin-right: 2%;">
                <p><strong>One-Time Fees:</strong> ${{ number_format($totalOneTimeFeeCost, 2) }}</p>
                <p><strong>Up-front Payment Requirement:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
                <p><strong>Optional Up-front Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
            </div>
            <div style="float: left; width: 48%; text-align: right;">
                <p><strong>Subtotal:</strong> ${{ number_format($subtotal, 2) }}</p>
                <p><strong>Taxes (13% HST):</strong> ${{ number_format($taxes, 2) }}</p>
                <p><strong>Total:</strong> ${{ number_format($total, 2) }}</p>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    <hr style="margin: 0; border-color: #ccc;">

    <!-- Total Contract Cost -->
    @if(\App\Helpers\SettingsHelper::enabled('show_contract_cost_breakdown'))
    <div style="padding: 0; background: #fff; border-bottom: 1px solid #ccc;">
        <h3 style="font-size: 8pt; margin: 0;">Total Contract Cost Breakdown</h3>
        <div style="font-size: 6pt; color: #333; line-height: 1.0;">
            <ul style="list-style-type: disc; padding-left: 4mm; margin: 0;">
                <li>Device Cost (after ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }} credit): ${{ number_format($deviceAmount, 2) }}</li>
                <li>Rate Plan (24 months): ${{ number_format(($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) * 24, 2) }}</li>
                <li>Add-ons (24 months): ${{ number_format($totalAddOnCost * 24, 2) }}</li>
                <li>One-Time Fees: ${{ number_format($totalOneTimeFeeCost, 2) }}</li>
            </ul>
            <div style="float: left; width: 48%; margin-right: 2%;">
                <p style="font-weight: bold;">Total Contract Cost (before taxes):</p>
                <p style="font-size: 5pt;">Estimated taxes (13% HST):</p>
                <p style="font-size: 5pt;">Total with estimated taxes:</p>
            </div>
            <div style="float: left; width: 48%; text-align: right;">
                <p style="font-weight: bold;">${{ number_format($totalCost, 2) }}</p>
                <p style="font-size: 5pt;">${{ number_format($totalCost * 0.13, 2) }}</p>
                <p style="font-size: 5pt;">${{ number_format($totalCost * 1.13, 2) }}</p>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    <hr style="margin: 0; border-color: #ccc;">
    @endif

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
        <div style="padding: 0; background: #fff; border-bottom: 1px solid #ccc; page-break-before: avoid; margin-top: 0;">
            <h3 style="font-size: 8pt; margin: 0;">Signature</h3>
            @if ($signatureExists && !empty($signatureSrc))
                <div class="signature-wrapper">
                    <img src="{{ $signatureSrc }}" alt="Signature" style="max-height: 25mm;">
                </div>
            @else
                <p style="font-size: 6pt; color: #ff0000;">Signature not available</p>
            @endif
        </div>
    @endif
</div>
@endsection