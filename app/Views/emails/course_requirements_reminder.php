<?php
// Email template for course requirements reminder
$logoPath = $_ENV['APP_URL'] ?? 'https://nautilus.local';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject ?? 'Course Requirements') ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">
                                ⏰ Action Required
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333333; margin-top: 0;">Hello <?= htmlspecialchars($customer_name) ?>!</h2>

                            <p style="color: #666666; line-height: 1.6; font-size: 16px;">
                                Your course <strong><?= htmlspecialchars($course_name) ?></strong> starts on <strong><?= htmlspecialchars($start_date) ?></strong>. To ensure you're ready, please complete the following requirements:
                            </p>

                            <!-- Requirements List -->
                            <?php if (!empty($requirements)): ?>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                                <?php foreach ($requirements as $req): ?>
                                <?php if ($req['is_mandatory'] && !$req['is_completed']): ?>
                                <tr>
                                    <td style="padding: 15px 0; border-bottom: 1px solid #eeeeee;">
                                        <table width="100%">
                                            <tr>
                                                <td width="30">
                                                    <span style="color: <?= $req['is_completed'] ? '#28a745' : '#ffc107' ?>; font-size: 24px;">
                                                        <?= $req['is_completed'] ? '✓' : '○' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong style="color: #333; font-size: 16px;"><?= htmlspecialchars($req['name']) ?></strong>
                                                    <?php if (!empty($req['instructions'])): ?>
                                                    <br>
                                                    <small style="color: #888; line-height: 1.6;"><?= htmlspecialchars($req['instructions']) ?></small>
                                                    <?php endif; ?>
                                                    <br>
                                                    <span style="background-color: <?= $req['is_completed'] ? '#28a745' : '#ffc107' ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; margin-top: 5px; display: inline-block;">
                                                        <?= $req['is_completed'] ? 'COMPLETED' : 'REQUIRED' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                            <?php endif; ?>

                            <!-- Urgent Notice -->
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #856404;">⚠️ Important Deadline:</strong>
                                <p style="color: #856404; margin: 10px 0 0 0; line-height: 1.6;">
                                    Please complete these requirements at least <strong>3 days before your course starts</strong>. Incomplete requirements may delay your certification or prevent you from participating.
                                </p>
                            </div>

                            <!-- How to Complete -->
                            <h3 style="color: #0066cc; margin-top: 30px;">How to Complete Requirements:</h3>
                            <ol style="color: #666666; line-height: 1.8;">
                                <li>Visit our dive shop to sign waivers and submit documents</li>
                                <li>Upload photos and documents through your student portal</li>
                                <li>Complete e-learning modules online</li>
                                <li>Contact us if you need assistance with any requirement</li>
                            </ol>

                            <!-- CTA Buttons -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= $logoPath ?>/courses/enrollments/<?= $enrollment_id ?>"
                                           style="background-color: #0066cc; color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; margin: 0 5px;">
                                            View My Requirements
                                        </a>
                                        <a href="tel:<?= $_ENV['PHONE'] ?? '' ?>"
                                           style="background-color: #28a745; color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; margin: 0 5px;">
                                            Call Us for Help
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #666666; line-height: 1.6; margin-top: 30px;">
                                Need help? Don't hesitate to reach out! We're here to make sure you're fully prepared for your course.
                            </p>

                            <p style="color: #666666; line-height: 1.6;">
                                Looking forward to seeing you soon!<br>
                                <strong>The Nautilus Dive Shop Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                This is a reminder email from Nautilus Dive Shop<br>
                                Questions? Contact us at <?= $_ENV['MAIL_FROM_ADDRESS'] ?? 'info@nautilus.local' ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
