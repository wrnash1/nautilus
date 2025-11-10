<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 30px; }
        .appointment-details { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #007bff; }
        .detail-row { margin: 10px 0; }
        .label { font-weight: bold; color: #666; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Appointment Reminder</h1>
        </div>
        <div class="content">
            <p>Hello <?= htmlspecialchars($customer_name) ?>,</p>

            <p>This is a friendly reminder about your upcoming appointment with Nautilus Dive Shop.</p>

            <div class="appointment-details">
                <h2>Appointment Details</h2>

                <div class="detail-row">
                    <span class="label">Type:</span>
                    <?= htmlspecialchars($appointment_type) ?>
                </div>

                <div class="detail-row">
                    <span class="label">Date & Time:</span>
                    <?= date('l, F j, Y', strtotime($start_time)) ?><br>
                    <?= date('g:i A', strtotime($start_time)) ?> - <?= date('g:i A', strtotime($end_time)) ?>
                </div>

                <div class="detail-row">
                    <span class="label">Location:</span>
                    <?= htmlspecialchars($location) ?>
                </div>

                <div class="detail-row">
                    <span class="label">With:</span>
                    <?= htmlspecialchars($assigned_to_name) ?>
                </div>

                <?php if (!empty($notes)): ?>
                <div class="detail-row">
                    <span class="label">Notes:</span><br>
                    <?= nl2br(htmlspecialchars($notes)) ?>
                </div>
                <?php endif; ?>
            </div>

            <p>If you need to reschedule or cancel, please contact us as soon as possible.</p>

            <p>We look forward to seeing you!</p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> Nautilus Dive Shop. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
