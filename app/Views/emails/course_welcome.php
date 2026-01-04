<?php
/**
 * Welcome to Class Email Template
 * Variables: $first_name, $last_name, $course_name, $start_date, $end_date, $location, $instructor_first, $instructor_last
 */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #667eea, #764ba2); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
            <h1 style="color: white; margin: 0; font-size: 28px;">Welcome to Your Dive Course! 🌊</h1>
        </div>

        <!-- Content -->
        <div
            style="background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
                Hi
                <?= htmlspecialchars($first_name) ?>,
            </p>

            <p style="color: #555; line-height: 1.6;">
                We're thrilled to have you enrolled in <strong>
                    <?= htmlspecialchars($course_name) ?>
                </strong>!
                This is the beginning of an incredible underwater adventure.
            </p>

            <!-- Course Details Card -->
            <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 25px 0;">
                <h3 style="color: #667eea; margin: 0 0 15px; font-size: 16px; text-transform: uppercase;">Course Details
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666;">📅 Dates:</td>
                        <td style="padding: 8px 0; color: #333; font-weight: 600;">
                            <?= date('F j', strtotime($start_date)) ?> -
                            <?= date('F j, Y', strtotime($end_date)) ?>
                        </td>
                    </tr>
                    <?php if (!empty($location)): ?>
                        <tr>
                            <td style="padding: 8px 0; color: #666;">📍 Location:</td>
                            <td style="padding: 8px 0; color: #333; font-weight: 600;">
                                <?= htmlspecialchars($location) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">👨‍🏫 Instructor:</td>
                        <td style="padding: 8px 0; color: #333; font-weight: 600;">
                            <?= htmlspecialchars($instructor_first . ' ' . $instructor_last) ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- What to Bring -->
            <h3 style="color: #333; font-size: 18px; margin: 25px 0 15px;">What to Bring</h3>
            <ul style="color: #555; line-height: 1.8; padding-left: 20px;">
                <li>Swimsuit</li>
                <li>Towel</li>
                <li>Sunscreen (reef-safe preferred)</li>
                <li>Comfortable clothes for classroom sessions</li>
                <li>Your PADI eLearning confirmation (if applicable)</li>
                <li>A positive attitude! 😊</li>
            </ul>

            <!-- Before Your Course -->
            <h3 style="color: #333; font-size: 18px; margin: 25px 0 15px;">Before Your Course</h3>
            <p style="color: #555; line-height: 1.6;">
                Make sure to complete any required paperwork and medical forms before your first session.
                If you have any medical conditions, please let us know in advance.
            </p>

            <!-- CTA Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="#"
                    style="display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 14px 35px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">
                    View My Enrollment
                </a>
            </div>

            <p style="color: #555; line-height: 1.6;">
                If you have any questions before the course, don't hesitate to reach out!
            </p>

            <p style="color: #333; margin-top: 25px;">
                See you soon,<br>
                <strong>
                    <?= htmlspecialchars($instructor_first . ' ' . $instructor_last) ?>
                </strong><br>
                <span style="color: #667eea;">Your Instructor</span>
            </p>
        </div>

        <!-- Footer -->
        <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
            <p>©
                <?= date('Y') ?> Nautilus Dive Shop. All rights reserved.
            </p>
            <p>You're receiving this because you enrolled in a course with us.</p>
        </div>
    </div>
</body>

</html>