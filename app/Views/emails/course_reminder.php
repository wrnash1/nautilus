<?php
/**
 * Class Reminder Email Template
 * Variables: $first_name, $course_name, $start_date, $location, $instructor_first, $instructor_last, $days_until
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
            style="background: linear-gradient(135deg, #f093fb, #f5576c); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
            <div
                style="background: white; border-radius: 50%; width: 80px; height: 80px; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 40px;">⏰</span>
            </div>
            <h1 style="color: white; margin: 0; font-size: 26px;">Your Class Starts in
                <?= (int) $days_until ?> Day
                <?= (int) $days_until !== 1 ? 's' : '' ?>!
            </h1>
        </div>

        <!-- Content -->
        <div
            style="background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
                Hi
                <?= htmlspecialchars($first_name) ?>,
            </p>

            <p style="color: #555; line-height: 1.6;">
                Just a friendly reminder that your <strong>
                    <?= htmlspecialchars($course_name) ?>
                </strong> is coming up soon!
            </p>

            <!-- Details Card -->
            <div
                style="background: linear-gradient(135deg, #667eea15, #764ba215); border-left: 4px solid #667eea; border-radius: 0 8px 8px 0; padding: 20px; margin: 25px 0;">
                <p style="margin: 0 0 10px; color: #333;">
                    <strong style="font-size: 24px;">📅
                        <?= date('l, F j, Y', strtotime($start_date)) ?>
                    </strong>
                </p>
                <?php if (!empty($location)): ?>
                    <p style="margin: 0; color: #666;">
                        📍
                        <?= htmlspecialchars($location) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Checklist -->
            <h3 style="color: #333; font-size: 18px; margin: 25px 0 15px;">Quick Checklist</h3>
            <div style="color: #555; line-height: 2;">
                <div>☑️ Complete any outstanding eLearning modules</div>
                <div>☑️ Fill out medical and liability forms</div>
                <div>☑️ Pack your swimsuit, towel, and sunscreen</div>
                <div>☑️ Get a good night's sleep!</div>
            </div>

            <p style="color: #555; line-height: 1.6; margin-top: 25px;">
                If you need to reschedule or have any questions, please contact us as soon as possible.
            </p>

            <p style="color: #333; margin-top: 25px;">
                See you soon!<br>
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