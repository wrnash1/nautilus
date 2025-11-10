<?php
// Email template for course enrollment welcome
$logoPath = $_ENV['APP_URL'] ?? 'https://nautilus.local';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject ?? 'Course Enrollment') ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0066cc 0%, #004999 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">
                                🤿 Welcome to Your Course!
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333333; margin-top: 0;">Hello <?= htmlspecialchars($customer_name) ?>!</h2>

                            <p style="color: #666666; line-height: 1.6; font-size: 16px;">
                                Congratulations! You're enrolled in <strong><?= htmlspecialchars($course_name) ?></strong>. We're excited to help you advance your diving skills and knowledge.
                            </p>

                            <!-- Course Details Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; border-radius: 8px; margin: 20px 0; padding: 20px;">
                                <tr>
                                    <td>
                                        <h3 style="color: #0066cc; margin-top: 0;">📚 Course Details</h3>
                                        <table width="100%" cellpadding="5" cellspacing="0">
                                            <tr>
                                                <td style="color: #666; font-weight: bold; width: 30%;">Course:</td>
                                                <td style="color: #333;"><?= htmlspecialchars($course_name) ?> (<?= htmlspecialchars($course_code) ?>)</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666; font-weight: bold;">Start Date:</td>
                                                <td style="color: #333;"><?= htmlspecialchars($start_date) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666; font-weight: bold;">End Date:</td>
                                                <td style="color: #333;"><?= htmlspecialchars($end_date) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666; font-weight: bold;">Instructor:</td>
                                                <td style="color: #333;"><?= htmlspecialchars($instructor_name) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666; font-weight: bold;">Location:</td>
                                                <td style="color: #333;"><?= htmlspecialchars($location) ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Requirements Section -->
                            <?php if (!empty($requirements)): ?>
                            <h3 style="color: #0066cc; margin-top: 30px;">✅ What You Need to Complete</h3>
                            <p style="color: #666666; line-height: 1.6;">
                                Before your course starts, please complete the following requirements:
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                                <?php foreach ($requirements as $req): ?>
                                <?php if ($req['is_mandatory']): ?>
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee;">
                                        <table width="100%">
                                            <tr>
                                                <td width="30">
                                                    <span style="color: #dc3545; font-size: 18px;">●</span>
                                                </td>
                                                <td>
                                                    <strong style="color: #333;"><?= htmlspecialchars($req['name']) ?></strong>
                                                    <?php if (!empty($req['instructions'])): ?>
                                                    <br>
                                                    <small style="color: #888;"><?= htmlspecialchars($req['instructions']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td width="80" align="right">
                                                    <span style="background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">REQUIRED</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                            <?php endif; ?>

                            <!-- Important Notes -->
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #856404;">⚠️ Important:</strong>
                                <p style="color: #856404; margin: 10px 0 0 0; line-height: 1.6;">
                                    Please complete all required items at least 3 days before your course starts. If you have any questions or need assistance, don't hesitate to contact us!
                                </p>
                            </div>

                            <!-- What to Bring -->
                            <h3 style="color: #0066cc; margin-top: 30px;">🎒 What to Bring</h3>
                            <ul style="color: #666666; line-height: 1.8;">
                                <li>Swimming attire and towel</li>
                                <li>Mask, snorkel, and fins (if you have your own)</li>
                                <li>Logbook (if you have one)</li>
                                <li>Current certification card (for advanced courses)</li>
                                <li>Positive attitude and enthusiasm!</li>
                            </ul>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= $logoPath ?>/courses/enrollments/<?= $enrollment_id ?>"
                                           style="background-color: #0066cc; color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                                            View My Enrollment
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #666666; line-height: 1.6; margin-top: 30px;">
                                If you have any questions, please don't hesitate to reach out. We're here to help make your diving education experience amazing!
                            </p>

                            <p style="color: #666666; line-height: 1.6;">
                                See you in the water!<br>
                                <strong>The Nautilus Dive Shop Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                This email was sent by Nautilus Dive Shop<br>
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
