<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DRO Form - Contract #{{ $contract->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 15px;
        }
        
        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }
        
        h2 {
            font-size: 14px;
            text-align: center;
            margin: 10px 0;
        }
        
        h3 {
            font-size: 11px;
            margin: 10px 0 5px 0;
        }
        
        p, li {
            font-size: 10px;
            margin: 3px 0;
        }
        
        .logo {
            margin-bottom: 10px;
        }
        
        .signing {
            border-top: 2px solid black;
            padding-top: 3px;
            width: 350px;
            min-height: 40px;
            margin: 10px 0;
        }
        
        .signature-image {
            max-height: 50px;
            margin-top: 5px;
        }
        
        .initials-image {
            max-height: 40px;
            margin-top: 5px;
        }
        
        ol {
            margin: 10px 0;
            padding-left: 18px;
        }
        
        ol li {
            margin-bottom: 8px;
        }
        
        ol ol {
            list-style-type: lower-alpha;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        
        ol ol li {
            margin-bottom: 4px;
        }
        
        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 10px 0;
        }
        
        .section {
            margin-bottom: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ public_path('images/hayLogo.png') }}" alt="Hay Communications" style="height: 45px;" />
    </div>

    <h2>DEVICE RETURN (DEFERRED PAYMENT) OPTION AGREEMENT</h2>

    <div class="section">
        <p>
            This Device Return (Deferred Payment) Option Agreement ("<b>Agreement</b>") sets out the terms under which Hay Communications Co-operative Limited ("<b>Hay</b>", "<b>we</b>", "<b>us</b>", or "<b>our</b>") has agreed to allow you to defer the payment or a portion of the purchase price (and associated taxes charged upfront) in respect of the Device and the terms for the Return Option (Buyback) of the Device by Hay. You will own the Device once it has been delivered to you.
        </p>
    </div>

    <div class="section">
        <p><b>Name:</b> {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
        <p><b>Address:</b> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->address }}</p>
        <p><b>Account Number:</b> {{ $contract->subscriber->mobilityAccount->ivueAccount->ivue_account }}</p>
    </div>

    <div class="section">
        <h3>AGREEMENT DETAILS</h3>
        <p><b>Effective Date:</b> {{ $contract->start_date->format('F d, Y') }}</p>
        <p><b>Device Description:</b> {{ $contract->bellDevice ? $contract->bellDevice->name : 'N/A' }}</p>
        <p><b>Device IMEI:</b> {{ $contract->imei ?? 'To be provided' }}</p>
        
        <div style="margin-top: 8px;">
            <p class="font-bold">Device Return or Deferred Payment</p>
            <p style="margin-top: 5px;">
                <b>Deferred Payment Option:</b> Pay the Deferred Payment Amount of <b>${{ number_format($contract->bell_dro_amount ?? 0, 2) }}</b> on the Due Date unless Hay, in its discretion, buys back your device for the full amount or allows you to pay on a different payment schedule.
            </p>
            <p style="margin-top: 5px;">or</p>
            <p style="margin-top: 5px;">
                <b>Device Return Option:</b> Sell your Device back to Hay, subject to the terms set out in Sections 2 through 7 below. If you do so, no further payment will be due under this Agreement if you meet the conditions described in Section 2 below. This option will expire on the <b>Due Date</b> at which time Hay will have no obligation to purchase your Device.
            </p>
        </div>

        <p style="margin-top: 8px;">
            <b>Due Date:</b> The earlier of {{ $contract->end_date->format('F d, Y') }} and the date your service arrangement for the line associated with the Device ends. Upon the ending of such service arrangement, if Hay has not agreed to a Device Buyback (as described in Section 2 below), the Deferred Payment Amount will become due immediately, or Hay, in its discretion allows you to pay on a different payment schedule.
        </p>
    </div>

    <hr>

    <ol>
        <li><b>What is the purpose of this Agreement?</b> Hay has agreed to defer a portion of the purchase price of your Device (and associated taxes charged upfront) until the Due Date at which time you can pay the Deferred Payment Amount or sell your Device to Hay, at it's discretion (subject to the conditions described below). You may pay the full Deferred Payment Amount at any time without any prepayment charge or penalty. This Agreement will terminate upon Device Buyback (as described below) or once you have paid the full Deferred Payment Amount. The termination of this Agreement will not affect your existing Service Agreement obligations.</li>

        <li><b>What terms apply to Hay's Buyback of my Device?</b> If your device meets the following conditions, Hay may choose to buy the Device from you for a purchase price equal to the Deferred Payment Amount if ALL of the following conditions are met (as determined by Hay, acting reasonably):
            <ol type="a">
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

    <div style="margin-top: 15px;">
        <p>
            By signing below, you confirm that you have read, understood and agree to be bound by all of the terms and conditions set out in this Agreement
        </p>
    </div>

    <!-- Signature Section -->
    <div style="margin-top: 20px;">
        @if($contract->dro_csr_initials_path)
            <div style="margin-bottom: 15px;">
                <p class="font-bold" style="margin-bottom: 2px;">Sales Consultant's Initials</p>
                <div class="signing">
                    <img src="{{ storage_path('app/public/' . str_replace('storage/', '', $contract->dro_csr_initials_path)) }}" alt="CSR Initials" class="initials-image">
                </div>
            </div>
        @else
            <div style="margin-bottom: 15px;">
                <p class="signing">Sales Consultant's Initials</p>
            </div>
        @endif

        @if($contract->dro_signature_path)
            <div style="margin-bottom: 15px;">
                <p class="font-bold" style="margin-bottom: 2px;">Customer's Signature</p>
                <div class="signing">
                    <img src="{{ storage_path('app/public/' . str_replace('storage/', '', $contract->dro_signature_path)) }}" alt="Customer Signature" class="signature-image">
                </div>
            </div>
        @else
            <div style="margin-bottom: 15px;">
                <p class="signing">Customer's Signature</p>
            </div>
        @endif

        @if($contract->dro_signed_at)
            <div style="margin-bottom: 15px;">
                <p class="signing">Date of Agreement - {{ $contract->dro_signed_at->format('F d, Y') }}</p>
            </div>
        @else
            <div style="margin-bottom: 15px;">
                <p class="signing">Date of Agreement</p>
            </div>
        @endif
    </div>

    <div style="margin-top: 15px;">
        <p style="font-size: 9px;">
            Contact us online at hay@hay.net, or call 519-236-4333 Monday to Friday from 9:00 a.m. to 4:30 p.m. and Saturday 9:00 a.m. to 12:00 p.m. Our mailing address is: P.O. Box 99 Zurich, ON N0M 2T0.
        </p>
    </div>

    <div style="margin-top: 8px;">
        <p class="text-right" style="font-size: 8px; color: #666;">rev. Mar 2022</p>
    </div>
</body>
</html>