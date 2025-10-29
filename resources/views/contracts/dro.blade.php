@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 page-container">
    <div class="mb-6 no-print">
        <a href="{{ route('contracts.view', $contract->id) }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Contract
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 no-print">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-gray-900">DRO Form</h2>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        {{ $contract->dro_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $contract->dro_status === 'customer_signed' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $contract->dro_status === 'csr_initialed' ? 'bg-indigo-100 text-indigo-800' : '' }}
                        {{ $contract->dro_status === 'finalized' ? 'bg-green-100 text-green-800' : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $contract->dro_status)) }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-600">
                    Contract #{{ $contract->id }} - {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        @if(!isset($pdf) || !$pdf)
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 no-print">
            <div class="flex flex-wrap gap-3">
                @if($contract->dro_status === 'pending')
                    <a href="{{ route('contracts.dro.sign', $contract->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Customer Sign Form
                    </a>
                @endif

                @if($contract->dro_status === 'customer_signed')
                    <a href="{{ route('contracts.dro.csr-initial', $contract->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        CSR Initial Form
                    </a>
                @endif

                @if($contract->dro_status === 'csr_initialed')
                    <form action="{{ route('contracts.dro.finalize', $contract->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Finalize DRO & Continue
                        </button>
                    </form>
                @endif

                @if($contract->dro_status === 'finalized')
                    <a href="{{ route('contracts.dro.download', $contract->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download PDF
                    </a>
                @endif
            </div>
        </div>
        @endif

        <!-- DRO Form Content -->
        <div class="px-6 py-6">
            <style>
                .signing {
                    border-top: 2px solid black;
                    padding-top: 5px;
                    width: 400px;
                }
                
                p, li {
                    font-size: 12px;
                }
                
                @media print {
                    .no-print { display: none !important; }
                }
            </style>

            <div class="mb-6">
                <img src="{{ asset('images/hayLogo.png') }}" alt="Hay Communications" class="h-16" />
            </div>

            <div class="mb-6">
                <h2 class="text-xl font-bold text-center">DEVICE RETURN (DEFERRED PAYMENT) OPTION AGREEMENT</h2>
            </div>

            <div class="mb-6">
                <p class="text-sm">
                    This Device Return (Deferred Payment) Option Agreement ("<b>Agreement</b>") sets out the terms under which Hay Communications Co-operative Limited ("<b>Hay</b>", "<b>we</b>", "<b>us</b>", or "<b>our</b>") has agreed to allow you to defer the payment or a portion of the purchase price (and associated taxes charged upfront) in respect of the Device and the terms for the Return Option (Buyback) of the Device by Hay. You will own the Device once it has been delivered to you.
                </p>
            </div>

            <div class="mb-6">
                <p class="text-sm"><b>Name:</b> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
                <p class="text-sm"><b>Address:</b> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->address }}</p>
                <p class="text-sm"><b>Account Number:</b> {{ $contract->subscriber->mobilityAccount->ivueAccount->ivue_account }}</p>
            </div>

            <div class="mb-6">
				<h3 class="text-lg font-bold mb-2">AGREEMENT DETAILS</h3>
				<p class="text-sm"><b>Effective Date:</b> {{ $contract->start_date->format('F d, Y') }}</p>
				<p class="text-sm"><b>Device Description:</b> {{ $contract->bellDevice ? $contract->bellDevice->name : 'N/A' }}</p>
				<p class="text-sm"><b>Device IMEI:</b> {{ $contract->imei ?? 'To be provided' }}</p>
                
                <div class="mt-4">
                    <p class="text-sm font-bold">Device Return or Deferred Payment</p>
                    <p class="text-sm mt-2">
                        <b>Deferred Payment Option:</b> Pay the Deferred Payment Amount of <b>${{ number_format($contract->bell_dro_amount ?? 0, 2) }}</b> on the Due Date unless Hay, in its discretion, buys back your device for the full amount or allows you to pay on a different payment schedule.
                    </p>
                    <p class="text-sm mt-2">or</p>
                    <p class="text-sm mt-2">
                        <b>Device Return Option:</b> Sell your Device back to Hay, subject to the terms set out in Sections 2 through 7 below. If you do so, no further payment will be due under this Agreement if you meet the conditions described in Section 2 below. This option will expire on the <b>Due Date</b> at which time Hay will have no obligation to purchase your Device.
                    </p>
                </div>

                <p class="text-sm mt-4">
                    <b>Due Date:</b> The earlier of {{ $contract->end_date->format('F d, Y') }} and the date your service arrangement for the line associated with the Device ends. Upon the ending of such service arrangement, if Hay has not agreed to a Device Buyback (as described in Section 2 below), the Deferred Payment Amount will become due immediately, or Hay, in its discretion allows you to pay on a different payment schedule.
                </p>
            </div>

            <hr class="my-6">

            <ol class="list-decimal list-inside space-y-4 text-sm">
                <li><b>What is the purpose of this Agreement?</b> Hay has agreed to defer a portion of the purchase price of your Device (and associated taxes charged upfront) until the Due Date at which time you can pay the Deferred Payment Amount or sell your Device to Hay, at it's discretion (subject to the conditions described below). You may pay the full Deferred Payment Amount at any time without any prepayment charge or penalty. This Agreement will terminate upon Device Buyback (as described below) or once you have paid the full Deferred Payment Amount. The termination of this Agreement will not affect your existing Service Agreement obligations.</li>

                <li><b>What terms apply to Hay's Buyback of my Device?</b> If your device meets the following conditions, Hay may choose to buy the Device from you for a purchase price equal to the Deferred Payment Amount if ALL of the following conditions are met (as determined by Hay, acting reasonably):
                    <ol type="a" class="list-inside ml-6 mt-2 space-y-2">
                        <li>You must be an active Hay Communications customer with an account in good standing.</li>
                        <li>At least 12 months must have passed from the Effective Date.</li>
                        <li>The Due Date must NOT have passed.</li>
                        <li>You must pay any amounts owing or outstanding (including your Device obligation) under your Service Agreement.</li>
                        <li>You must own your Device.</li>
                        <li>Your Device must match the Description of the Device set out above. In addition, it must be in good working condition, which means that it powers on and navigates properly to the home screen, the keyboard and/or touchscreen is responsive and functions properly, the display and body of the Device is free of any visible chips, cracks, scratches, missing parts, dead pixels or dark spots, and the battery and battery cover must be included. For Apple Devices with "iCloud Find my iPhone Activation Lock", the lock must be turned off and no longer linked to your account and for Android Devices. "Activation Lock Protection" must be disabled.</li>
                    </ol>
                </li>

                <li><b>What if the conditions for Device Return are not met?</b> If any of the above conditions for Device Return are not met, Hay may still agree, in our sole discretion, to Buyback the Device from you. The purchase price that we set for the Device Return (the "Device Return Amount" will likely be lower than the Deferred Payment Amount. In these circumstances, you will be responsible for the difference between the Deferred Payment Amount and the Device Return Price. We will advise you of the amount owing and you will have the option whether to proceed with the Device Buyback.</li>

                <li><b>How do I arrange for a Device Buyback?</b> If you would like Hay to buy the Device from you, please bring your Device into one of the three Hay locations, where a Cell Specialist will assess whether you are eligible for a Device Buyback.</li>

                <li><b>How will Hay pay me for my Device?</b> You will not receive any cash payment from Hay as a result of a Device Buyback. Hay will pay the purchase price of the Device by off-setting it against the Device Return (Deferred Payment) Option Amount you owe to Hay. Generally, the result of this transaction will be that you do not owe any further amount to Hay under this Agreement, except in the circumstance identified in Section 3 above.</li>

                <li><b>What happens to my content if Hay buys back the Device from me?</b> Before you provide your Device to Hay to Buyback, you are responsible for deleting all stored data and personal information. Once you provide the Device to Hay for buyback, it cannot be returned to you and any information stored on the Device may be accessible to others. Hay is not responsible for the loss, safekeeping or security of any data or personal information remaining on your Device once it is transferred to Hay. If you are unsure of how to remove all data or personal information from your Device, consult your Device's product information manual or visit us at https://hay.net/support/#mobile.</li>

                <li><b>What happens once I have provided my Device to Hay for buyback?</b> Once the Device Buyback transaction concludes, the transaction will be final and Hay will own the Device. This Agreement will terminate once the Device Buyback transaction is concluded.</li>

                <li><b>What if I do not sell my Device to Hay?</b> If Hay does not buy or make an agreement to buy your Device before the Due Date, you must pay the Deferred Payment Amount on the Due Date. However, at our discretion, we may allow you to pay the Deferred Payment Amount on a payment schedule beginning on or following the Due Date. If we make any such allowance, we will communicate this to you on or before the Due Date.</li>

                <li><b>What are the additional payment terms on the Deferred Payment Amount?</b> The annual percentage rate (credit rate) under this Agreement is 0%. Hay will not charge interest on the Deferred Payment Amount provided that (i) we agree to a Device Buyback before the Due Date; or (ii) you pay the Deferred Payment Amount on or before the Due Date (or in accordance with a different payment schedule that we have agreed upon). The Deferred Payment Amount is payable in accordance with the payment options set out in your 90 day "end of term" notice. You agree to pay interest on the Deferred Payment Amount if the arrangement for payments is not met, at the rate of 1.25% per month, compounded monthly.</li>

                <li><b>What is the term length of this Agreement?</b> Unless terminated earlier as provided in this Agreement, the term of this Agreement begins on the Effective Date and ends on the Due Date. Your obligation to pay the full Deferred Payment Amount will continue until it is paid in full, unless this Agreement has been terminated.</li>

                <li><b>How does Hay limit its liability?</b> To the extent permitted by applicable law, Hay's Liability to you for negligence, breach of contract, tort or other causes of action, including fundamental breach, in connection with this Agreement is limited to the Deferred Payment Amount. Other than this amount and to the extent permitted by applicable law, Hay is not responsible to anyone for any damages, including direct, indirect, special, consequential, incidental, economic, exemplary or punitive damages.</li>

                <li><b>Can this Agreement be transferred?</b> Hay may transfer or assign all or part of this Agreement (including any rights in accounts receivable) at any time without prior notice or your consent). You may not transfer or assign this Agreement without Hay's prior written consent.</li>

                <li><b>What laws apply to this Agreement?</b> Hay is federally regulated. This Agreement is governed by the federal laws and regulations of Canada.</li>

                <li><b>What if I prefer this Agreement to be in French?</b> You are receiving this Agreement in English because you requested a copy in English. Vous avez demandé que cette entente ainsi que tous les documents en faisant partie soient rédigés dans la langue anglaise mais si vous souhaitez que votre entente soit en français, veuillez communiquer avec nous, aux coordonnées indiquées à la fin de ce document.</li>
            </ol>

            <div class="mt-8 mb-6">
                <p class="text-sm">
                    By signing below, you confirm that you have read, understood and agree to be bound by all of the terms and conditions set out in this Agreement
                </p>
            </div>

            <!-- Signature Section -->
            <div class="mt-8 space-y-8">
                @if($contract->dro_csr_initials_path)
                    <div>
                        <p class="text-sm font-semibold mb-2">Sales Consultant's Initials</p>
                        <div class="border-t-2 border-black pt-2" style="width: 400px;">
                            <img src="{{ asset($contract->dro_csr_initials_path) }}" alt="CSR Initials" class="h-16">
                        </div>
                    </div>
                @else
                    <div>
                        <p class="text-sm signing">Sales Consultant's Initials</p>
                    </div>
                @endif

                @if($contract->dro_signature_path)
                    <div>
                        <p class="text-sm font-semibold mb-2">Customer's Signature</p>
                        <div class="border-t-2 border-black pt-2" style="width: 400px;">
                            <img src="{{ asset($contract->dro_signature_path) }}" alt="Customer Signature" class="h-20">
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Signed on: {{ $contract->dro_signed_at->format('F d, Y g:i A') }}</p>
                    </div>
                @else
                    <div>
                        <p class="text-sm signing">Customer's Signature</p>
                    </div>
                @endif

                <div>
                    <p class="text-sm signing">Date of Agreement</p>
                </div>
            </div>

            <div class="mt-8">
                <p class="text-sm">
                    Contact us online at hay@hay.net, or call 519-236-4333 Monday to Friday from 9:00 a.m. to 4:30 p.m. and Saturday 9:00 a.m. to 12:00 p.m. Our mailing address is: P.O. Box 99 Zurich, ON N0M 2T0.
                </p>
            </div>

            <div class="mt-4">
                <p class="text-sm text-right text-gray-600">rev. Mar 2022</p>
            </div>
        </div>
    </div>
</div>
@endsection