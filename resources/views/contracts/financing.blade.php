@php
    $isPdf = isset($pdf) ? $pdf : request()->get('pdf', false);
@endphp

@extends($isPdf ? 'layouts.pdf' : 'layouts.app')

@section('content')

@if(!$isPdf)
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
@endif
    @if(!$isPdf)
    <!-- Header with Back Button - ONLY FOR WEB -->
    <div class="mb-6 flex justify-between items-center no-print">
        <a href="{{ route('contracts.view', $contract->id) }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Contract
        </a>
        
        @if($contract->financing_status === 'pending')
            <a href="{{ route('contracts.financing.sign', $contract->id) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Sign as Customer
            </a>
        @elseif($contract->financing_status === 'customer_signed')
            <a href="{{ route('contracts.financing.csr-initial', $contract->id) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Initial as CSR
            </a>
        @elseif($contract->financing_status === 'csr_initialed')
            <form action="{{ route('contracts.financing.finalize', $contract->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @if($contract->requiresDro() && $contract->dro_status === 'pending')
                        Finalize Financing & Continue to DRO
                    @else
                        Finalize Financing & Continue
                    @endif
                </button>
            </form>
        @elseif($contract->financing_status === 'finalized')
            <a href="{{ route('contracts.financing.download', $contract->id) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Financing Form
            </a>
        @endif
    </div>
    @endif

	<div class="{{ $isPdf ? '' : 'bg-white shadow rounded-lg overflow-hidden' }}" style="{{ $isPdf ? 'padding: 5px 10px;' : '' }}">		
		<!-- Logo -->
		<div class="{{ $isPdf ? '' : 'px-6 py-4 border-b border-gray-200' }}" style="{{ $isPdf ? 'text-align: center; margin-bottom: 5px;' : '' }}">
			<img src="{{ public_path('images/hayLogo.png') }}" alt="Hay Communications" style="{{ $isPdf ? 'max-width: 120px; height: auto;' : 'max-width: 250px; height: auto;' }}">
		</div>

		<!-- Title -->
		<div class="{{ $isPdf ? '' : 'px-6 py-4' }}" style="{{ $isPdf ? 'text-align: center; margin-bottom: 5px;' : '' }}">
			<h2 class="{{ $isPdf ? '' : 'text-2xl font-bold text-gray-900' }}" style="{{ $isPdf ? 'font-size: 14pt; font-weight: bold; margin: 0;' : '' }}">HAY FINANCE INSTALLATION AGREEMENT</h2>
		</div>

		<!-- Warning Notice -->
		<div class="{{ $isPdf ? '' : 'px-6 py-4' }}" style="{{ $isPdf ? 'margin-bottom: 5px;' : '' }}">
			<p class="{{ $isPdf ? '' : 'font-bold text-gray-900' }}" style="{{ $isPdf ? 'font-weight: bold; font-size: 10pt; margin: 3px 0;' : '' }}">
				THE HAY COMMUNICATIONS CO-OPERATIVE LIMITED FINANCE INSTALLMENT AGREEMENT GOVERNS YOUR PURCHASE OF THE DEVICE ONLY AND NOT YOUR SERVICE AGREEMENT WITH HAY COMMUNICATIONS CO-OPERATIVE LIMITED (HAY).
			</p>
		</div>

		<!-- Introductory Paragraph -->
		<div class="{{ $isPdf ? '' : 'px-6 py-4' }}" style="{{ $isPdf ? 'margin-bottom: 5px;' : '' }}">
			<p class="{{ $isPdf ? '' : 'text-sm text-gray-700' }}" style="{{ $isPdf ? 'font-size: 9pt; line-height: 1.3; margin: 3px 0;' : '' }}">
				You have agreed to purchase and make monthly payments on your Device as set out below. You will own the Device once it has been delivered to you.
				Delivery of the Device will take place once you enter into the Service Agreement, or if the Device is shipped to you, within 30 days of you entering into
				the Service Agreement. Any additional charges (set out below) must be paid by you to Hay upfront. The total deferred tax amount does not represent,
				and is not charged on account of, tax. Your total obligation is payable to Hay in accordance with the payment options set out in your monthly bill.
			</p>
		</div>

        @php
            $devicePrice = $contract->bell_retail_price ?? 0;
            $credit = $contract->agreement_credit_amount ?? 0;
            $upfront = $contract->required_upfront_payment ?? 0;
            $downPayment = $contract->optional_down_payment ?? 0;
            $totalFinanced = $contract->getTotalFinancedAmount();
            $monthlyPayment = $contract->getMonthlyDevicePayment();
        @endphp

        <!-- Two Column Section -->
		<div class="{{ $isPdf ? '' : 'px-6 py-4' }}" style="{{ $isPdf ? 'font-size: 10pt; margin-top: 5px;' : '' }}">
            @if($isPdf)
                <!-- PDF Layout - Using table for fixed columns -->
                <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 10pt; table-layout: fixed; border-collapse: collapse;">
                    <tr>
                        <td width="50%" style="vertical-align: top; padding-right: 10px; page-break-inside: auto;">
                            <!-- Left Column Content -->
                            <div style="margin-bottom: 10px;">
                                <p style="margin: 3px 0;"><strong>Effective Date:</strong> {{ $contract->start_date->format('M j, Y') }}</p>
                                <p style="margin: 3px 0;"><strong>Description of Device:</strong> {{ $contract->bellDevice->name ?? 'N/A' }}</p>
                                <p style="margin: 3px 0;"><strong>Device Retail (before tax):</strong> ${{ number_format($devicePrice, 2) }}</p>
                                <p style="margin: 3px 0;"><strong>Finance Agreement Credit:</strong> ${{ number_format($credit, 2) }}</p>
                                <p style="margin: 3px 0;"><strong>Up-front Charges/Payments:</strong> ${{ number_format($upfront + $downPayment, 2) }}</p>
                                <p style="margin: 3px 0;"><strong>Total Financed Amount (before tax):</strong> ${{ number_format($totalFinanced, 2) }}</p>
                                <p style="margin: 3px 0;"><strong>Remaining Device Payments:</strong> ${{ number_format($monthlyPayment, 2) }}</p>
                                <p style="margin: 3px 0;"><strong>Installment Term:</strong> 24 months</p>
                                <p style="margin: 3px 0;"><strong>Payment Schedule:</strong> Monthly for a maximum of 24 months</p>
                                <p style="margin: 3px 0;"><strong>Annual Percentage Rate (credit rate):</strong> 0%</p>
                            </div>

                            <div style="margin-top: 10px; margin-bottom: 10px;">
                                <p style="margin: 5px 0; line-height: 1.4;">By signing below, you confirm that you have read, understood and agree to be bound by all of the terms and conditions set out in this Agreement</p>
                            </div>

                            @if($contract->financing_status !== 'pending')
                                <!-- Signature Section -->
                                <div style="margin-top: 10px;">
                                    <!-- CSR Initials -->
                                    <div style="margin-bottom: 10px;">
                                        @if($contract->financing_csr_initials_path)
                                            <img src="{{ public_path($contract->financing_csr_initials_path) }}" 
                                                 alt="CSR Initials" 
                                                 style="max-width: 280px; max-height: 60px; height: auto; display: block; margin-bottom: 3px;">
                                        @endif
                                        <div style="border-top: 2px solid black; padding-top: 3px; width: 280px; margin-top: 10px;">
                                            Sales Consultant's Initials
                                        </div>
                                    </div>

                                    <div style="margin-bottom: 10px;">
                                        @if($contract->financing_signature_path)
                                            <img src="{{ public_path($contract->financing_signature_path) }}" 
                                                 alt="Customer Signature" 
                                                 style="max-width: 280px; max-height: 60px; height: auto; display: block; margin-bottom: 3px;">
                                        @endif
                                        <div style="border-top: 2px solid black; padding-top: 3px; width: 280px;">
                                            Customer Signature
                                        </div>
                                    </div>

                                    <div style="margin-bottom: 10px;">
                                        <p style="margin: 0 0 3px 0; font-weight: bold;">{{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                                        <div style="border-top: 2px solid black; padding-top: 3px; width: 280px;">
                                            Customer Name (Printed)
                                        </div>
                                    </div>

                                    <div style="margin-bottom: 10px;">
                                        <p style="margin: 0 0 3px 0; font-weight: bold;">{{ $contract->subscriber->mobilityAccount->ivueAccount->customer->address }}</p>
                                        <div style="border-top: 2px solid black; padding-top: 3px; width: 280px;">
                                            Address
                                        </div>
                                    </div>

                                    <div style="margin-bottom: 10px;">
                                        <p style="margin: 0 0 3px 0; font-weight: bold;">{{ $contract->financing_signed_at ? $contract->financing_signed_at->format('Y-m-d') : now()->format('Y-m-d') }}</p>
                                        <div style="border-top: 2px solid black; padding-top: 3px; width: 280px;">
                                            Date of Agreement
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                        
                        <td width="50%" style="vertical-align: top; padding-left: 10px; page-break-inside: auto;">
                            <!-- Right Column Content -->
                            <ol style="margin: 0; padding-left: 20px; line-height: 1.6;">
                                <li style="margin-bottom: 10px;">
                                    <strong>Total Cash Price of the Device (total obligation) including down payment, but excluding taxes):</strong> ${{ number_format($totalFinanced, 2) }}
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <strong>Outstanding Balance:</strong> ${{ number_format($totalFinanced, 2) }}
                                    <ol type="a" style="margin-top: 5px; padding-left: 20px;">
                                        <li style="margin-bottom: 5px;">Outstanding balance after payment made by you on all amounts owing on the Device on or before the Effective Date. $0.00</li>
                                        <li style="margin-bottom: 5px;">Outstanding balance at the end of the Installment Term if all scheduled payments are made: $0.00</li>
                                    </ol>
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <strong>Default Charges:</strong> Interest on all amounts owing in your current bill which are not paid by you or received by Hay by the following Billing Date will be charged at the rate of 1.25% calculated and compounded monthly
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <strong>Prepayment Rights:</strong>
                                    <ol type="a" style="margin-top: 5px; padding-left: 20px;">
                                        <li style="margin-bottom: 5px;">You may pay the full outstanding balance on your Device at any time without any prepayment charge or penalty. If you prepay the full outstanding balance, it will be credited to your Hay account and the amount outstanding will be $0.</li>
                                        <li style="margin-bottom: 5px;">If you make any payment which changes the billing schedule set out above in "Remaining Device Payments", you agree that an updated installment agreement will not be issued by Hay.</li>
                                    </ol>
                                </li>
                                <li style="margin-bottom: 10px;"><strong>Optional Services:</strong> None</li>
                                <li style="margin-bottom: 10px;"><strong>Additional Charges that may be applicable under the Agreement (other than interest):</strong> $0.00</li>
                                <li style="margin-bottom: 10px;"><strong>Early Termination:</strong> If this Hay Finance Installment Agreement or your Service Agreement is terminated prior to the end of the Finance Term, then your Remaining Device Payments, plus any applicable taxes, will become due immediately and any promotional discounts on such Remaining Device Payments will no longer apply.</li>
                                <li style="margin-bottom: 10px;"><strong>General:</strong> Capitalized words used in this Hay Finance Installation Agreement which are not defined have the same meaning as they do in your Service Agreement.</li>
                            </ol>
                        </td>
                    </tr>
                </table>
            @else
                <!-- Web Layout - Using grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div>
                        <div class="text-sm space-y-2">
                            <p><strong>Effective Date:</strong> {{ $contract->start_date->format('M j, Y') }}</p>
                            <p><strong>Description of Device:</strong> {{ $contract->bellDevice->name ?? 'N/A' }}</p>
                            <p><strong>Device Retail (before tax):</strong> ${{ number_format($devicePrice, 2) }}</p>
                            <p><strong>Finance Agreement Credit:</strong> ${{ number_format($credit, 2) }}</p>
                            <p><strong>Up-front Charges/Payments:</strong> ${{ number_format($upfront + $downPayment, 2) }}</p>
                            <p><strong>Total Financed Amount (before tax):</strong> ${{ number_format($totalFinanced, 2) }}</p>
                            <p><strong>Remaining Device Payments:</strong> ${{ number_format($monthlyPayment, 2) }}</p>
                            <p><strong>Installment Term:</strong> 24 months</p>
                            <p><strong>Payment Schedule:</strong> Monthly for a maximum of 24 months</p>
                            <p><strong>Annual Percentage Rate (credit rate):</strong> 0%</p>
                        </div>

                        <div class="mt-6 text-sm">
                            <p>By signing below, you confirm that you have read, understood and agree to be bound by all of the terms and conditions set out in this Agreement</p>
                        </div>

                        @if($contract->financing_status !== 'pending')
                        <!-- Signature Section -->
                        <div class="mt-8 space-y-6">
                            <!-- CSR Initials -->
                            <div>
                                @if($contract->financing_csr_initials_path)
                                    <img src="{{ asset($contract->financing_csr_initials_path) }}" 
                                         alt="CSR Initials" 
                                         class="max-w-xs h-auto mb-2">
                                @endif
                                <div class="border-t-2 border-gray-900 pt-2 w-80">
                                    Sales Consultant's Initials
                                </div>
                            </div>

                            <div>
                                @if($contract->financing_signature_path)
                                    <img src="{{ asset($contract->financing_signature_path) }}" 
                                         alt="Customer Signature" 
                                         class="max-w-xs h-auto mb-2">
                                @endif
                                <div class="border-t-2 border-gray-900 pt-2 w-80">
                                    Customer Signature
                                </div>
                            </div>

                            <div>
                                <p class="font-medium">{{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                                <div class="border-t-2 border-gray-900 pt-2 w-80">
                                    Customer Name (Printed)
                                </div>
                            </div>

                            <div>
                                <p class="font-medium">{{ $contract->subscriber->mobilityAccount->ivueAccount->customer->address }}</p>
                                <div class="border-t-2 border-gray-900 pt-2 w-80">
                                    Address
                                </div>
                            </div>

                            <div>
                                <p class="font-medium">{{ $contract->financing_signed_at ? $contract->financing_signed_at->format('Y-m-d') : now()->format('Y-m-d') }}</p>
                                <div class="border-t-2 border-gray-900 pt-2 w-80">
                                    Date of Agreement
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right Column -->
                    <div>
                        <div class="text-sm">
                            <ol class="list-decimal list-outside ml-4 space-y-2">
                                <li><strong>Total Cash Price of the Device (total obligation) including down payment, but excluding taxes):</strong> ${{ number_format($totalFinanced, 2) }}</li>
                                <li><strong>Outstanding Balance:</strong> ${{ number_format($totalFinanced, 2) }}
                                    <ol type="a" class="ml-4 mt-1">
                                        <li>Outstanding balance after payment made by you on all amounts owing on the Device on or before the Effective Date. $0.00</li>
                                        <li>Outstanding balance at the end of the Installment Term if all scheduled payments are made: $0.00</li>
                                    </ol>
                                </li>
                                <li>Default Charges: Interest on all amounts owing in your current bill which are not paid by you or received by Hay by the following Billing Date will be charged at the rate of 1.25% calculated and compounded monthly</li>
                                <li><strong>Prepayment Rights:</strong>
                                    <ol type="a" class="ml-4 mt-1">
                                        <li>You may pay the full outstanding balance on your Device at any time without any prepayment charge or penalty. If you prepay the full outstanding balance, it will be credited to your Hay account and the amount outstanding will be $0.</li>
                                        <li>If you make any payment which changes the billing schedule set out above in "Remaining Device Payments", you agree that an updated installment agreement will not be issued by Hay.</li>
                                    </ol>
                                </li>
                                <li><strong>Optional Services:</strong> None</li>
                                <li><strong>Additional Charges that may be applicable under the Agreement (other than interest):</strong> $0.00</li>
                                <li><strong>Early Termination:</strong> If this Hay Finance Installment Agreement or your Service Agreement is terminated prior to the end of the Finance Term, then your Remaining Device Payments, plus any applicable taxes, will become due immediately and any promotional discounts on such Remaining Device Payments will no longer apply.</li>
                                <li><strong>General:</strong> Capitalized words used in this Hay Finance Installation Agreement which are not defined have the same meaning as they do in your Service Agreement.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="{{ $isPdf ? '' : 'px-6 py-4 text-right text-xs text-gray-500' }}" style="{{ $isPdf ? 'text-align: right; font-size: 8pt; margin-top: 10px;' : '' }}">
            <p style="{{ $isPdf ? 'margin: 0;' : '' }}">rev. Mar 2022</p>
        </div>
    </div>
@if(!$isPdf)
</div>
@endif

@endsection