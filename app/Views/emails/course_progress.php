<?php
/**
 * Progress Update Email Template
 * Variables: $first_name, $course_name, $instructor_first, $instructor_last
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
            style="background: linear-gradient(135deg, #4facfe, #00f2fe); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
            <div style="font-size: 50px; margin-bottom: 10px;">📊</div>
            <h1 style="color: white; margin: 0; font-size: 26px;">Your Progress Update</h1>
        </div>

        <!-- Content -->
        <div
            style="background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
                Hi
                <?= htmlspecialchars($first_name) ?>,
            </p>

            <p style="color: #555; line-height: 1.6;">
                Here's an update on your progress in <strong>
                    <?= htmlspecialchars($course_name) ?>
                </strong>!
            </p>

            <!-- Progress Section -->
            <div style="margin: 25px 0;">
                <h3 style="color: #333; font-size: 16px; margin: 0 0 15px;">Course Progress</h3>

                <!-- Knowledge Development -->
                <div style="background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #333; font-weight: 500;">📚 Knowledge Development</span>
                        <span
                            style="background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">IN
                            PROGRESS</span>
                    </div>
                </div>

                <!-- Confined Water -->
                <div style="background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #333; font-weight: 500;">🏊 Confined Water Skills</span>
                        <span
                            style="background: #e9ecef; color: #666; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">PENDING</span>
                    </div>
                </div>

                <!-- Open Water -->
                <div style="background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #333; font-weight: 500;">🌊 Open Water Dives</span>
                        <span
                            style="background: #e9ecef; color: #666; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">PENDING</span>
                    </div>
                </div>
            </div>

            <!-- Instructor Notes -->
            <div
                style="background: linear-gradient(135deg, #667eea15, #764ba215); border-left: 4px solid #667eea; border-radius: 0 8px 8px 0; padding: 20px; margin: 25px 0;">
                <h4 style="color: #667eea; margin: 0 0 10px;">📝 Instructor Notes</h4>
                <p style="color: #555; margin: 0; line-height: 1.6;">
                    Great job on your recent session! Keep up the good work. Remember to review your skills before our
                    next pool session.
                </p>
            </div>

            <p style="color: #555; line-height: 1.6;">
                If you have any questions about your progress or need additional practice time,
                don't hesitate to reach out. We're here to support you!
            </p>

            <p style="color: #333; margin-top: 25px;">
                See you at the next session!<br>
                <strong>
                    <?= htmlspecialchars($instructor_first . ' ' . $instructor_last) ?>
                </strong>
            </p>
        </div>

        <!-- Footer -->
        <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
            <p>©
                <?= date('Y') ?> Nautilus Dive Shop
            </p>
        </div>
    </div>
</body>

</html>