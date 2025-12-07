<?php
// Email template for instructor notification of new enrollment
$logoPath = $_ENV['APP_URL'] ?? 'https://nautilus.local';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject ?? 'New Enrollment') ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">
                                👨‍🏫 New Student Enrollment
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333333; margin-top: 0;">Hello <?= htmlspecialchars($instructor_name) ?>!</h2>

                            <p style="color: #666666; line-height: 1.6; font-size: 16px;">
                                You have a new student enrolled in your upcoming course. Here are the details:
                            </p>

                            <!-- Course Details Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; border-radius: 8px; margin: 20px 0; padding: 20px;">
                                <tr>
                                    <td>
                                        <h3 style="color: #28a745; margin-top: 0;">📚 Course Information</h3>
                                        <table width="100%" cellpadding="5" cellspacing="0">
                                            <tr>
                                                <td style="color: #666; font-weight: bold; width: 30%;">Course:</td>
                                                <td style="color: #333;"><?= htmlspecialchars($course_name) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666; font-weight: bold;">Start Date:</td>
                                                <td style="color: #333;"><?= htmlspecialchars($start_date) ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Student Details Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #e7f4ff; border-radius: 8px; margin: 20px 0; padding: 20px;">
                                <tr>
                                    <td>
                                        <h3 style="color: #0066cc; margin-top: 0;">👤 Student Information</h3>
                                        <table width="100%" cellpadding="5" cellspacing="0">
                                            <tr>
                                                <td style="color: #666; font-weight: bold; width: 30%;">Name:</td>
                                                <td style="color: #333;"><strong><?= htmlspecialchars($student_name) ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666; font-weight: bold;">Email:</td>
                                                <td style="color: #333;"><a href="mailto:<?= htmlspecialchars($student_email) ?>" style="color: #0066cc;"><?= htmlspecialchars($student_email) ?></a></td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666; font-weight: bold;">Phone:</td>
                                                <td style="color: #333;"><?= htmlspecialchars($student_phone ?? 'Not provided') ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Next Steps -->
                            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #155724;">✓ Next Steps:</strong>
                                <ul style="color: #155724; margin: 10px 0 0 0; line-height: 1.8;">
                                    <li>The student has been sent a welcome email with course details</li>
                                    <li>They will complete required prerequisites before the course</li>
                                    <li>You'll be notified when all requirements are completed</li>
                                    <li>Review the roster before the course start date</li>
                                </ul>
                            </div>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= $logoPath ?>/courses/schedules/<?= $schedule_id ?>/roster"
                                           style="background-color: #28a745; color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; margin-right: 10px;">
                                            View Course Roster
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #666666; line-height: 1.6; margin-top: 30px;">
                                If you have any questions or concerns, please contact the dive shop staff.
                            </p>

                            <p style="color: #666666; line-height: 1.6;">
                                Happy teaching!<br>
                                <strong>Nautilus Dive Shop</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                This is an automated notification from Nautilus Dive Shop<br>
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
