<?php
$pageTitle = 'Feedback Submitted Successfully';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Nautilus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
        }

        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 24px;
        }

        .ticket-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            font-size: 1.5rem;
            font-weight: bold;
            display: inline-block;
            margin: 24px 0;
        }

        .next-steps {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            text-align: left;
        }

        .next-steps h5 {
            color: #667eea;
            margin-bottom: 16px;
        }

        .next-steps ul {
            margin-bottom: 0;
        }

        .next-steps li {
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <i class="bi bi-check-circle-fill success-icon"></i>
        <h1>Thank You!</h1>
        <p class="lead">Your feedback has been submitted successfully.</p>

        <div class="ticket-number">
            Ticket <?= htmlspecialchars($ticket_number) ?>
        </div>

        <div class="next-steps">
            <h5><i class="bi bi-info-circle"></i> What Happens Next?</h5>
            <ul>
                <li><strong>Email Confirmation:</strong> You'll receive a confirmation email with your ticket details</li>
                <li><strong>Review:</strong> Our team will review your feedback within 1-2 business days</li>
                <li><strong>Updates:</strong> We'll email you with any status changes or questions</li>
                <li><strong>Resolution:</strong> Once resolved, you'll be notified via email</li>
            </ul>
        </div>

        <p class="text-muted mb-4">
            <i class="bi bi-envelope"></i> Check your email for updates about this ticket
        </p>

        <div class="d-grid gap-2 d-md-block">
            <a href="/feedback/ticket/<?= htmlspecialchars($ticket_number) ?>" class="btn btn-primary">
                <i class="bi bi-eye"></i> View Ticket
            </a>
            <a href="/" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>
