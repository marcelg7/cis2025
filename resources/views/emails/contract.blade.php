<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Hay Communications Contract</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Your Hay Communications Contract</h2>
        
        <p>Dear {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }},</p>
        
        <p>Thank you for choosing Hay Communications! Please find your contract documents attached to this email.</p>
        
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #1f2937;">Contract Details:</h3>
            <p style="margin: 5px 0;"><strong>Contract #:</strong> {{ $contract->id }}</p>
            <p style="margin: 5px 0;"><strong>Mobile Number:</strong> {{ $contract->subscriber->mobile_number }}</p>
            <p style="margin: 5px 0;"><strong>Start Date:</strong> {{ $contract->start_date->format('F j, Y') }}</p>
            @if($contract->bellDevice)
                <p style="margin: 5px 0;"><strong>Device:</strong> {{ $contract->bellDevice->name }}</p>
            @endif
        </div>
        
        <p><strong>Attached Documents:</strong></p>
        <ul>
            <li>Service Agreement Contract</li>
            @if($contract->requiresFinancing() && $contract->financing_status === 'finalized')
                <li>Device Financing Agreement</li>
            @endif
        </ul>
        
        <p>If you have any questions about your contract, please don't hesitate to contact us.</p>
        
        <p style="margin-top: 30px;">
            Best regards,<br>
            <strong>Hay Communications Team</strong>
        </p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6b7280;">
            This is an automated message. Please do not reply to this email.
        </p>
    </div>
</body>
</html>