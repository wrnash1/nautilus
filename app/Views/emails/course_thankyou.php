<?php
/**
 * Thank You / Completion Email Template
 * Variables: $first_name, $last_name, $course_name, $instructor_first, $instructor_last
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
            style="background: linear-gradient(135deg, #11998e, #38ef7d); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
            <div style="font-size: 60px; margin-bottom: 10px;">🎉</div>
            <h1 style="color: white; margin: 0; font-size: 28px;">Congratulations,
                <?= htmlspecialchars($first_name) ?>!
            </h1>
            <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 18px;">You've completed
                <?= htmlspecialchars($course_name) ?>!
            </p>
        </div>

        <!-- Content -->
        <div
            style="background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
                Hi
                <?= htmlspecialchars($first_name) ?>,
            </p>

            <p style="color: #555; line-height: 1.6;">
                Thank you for completing your <strong>
                    <?= htmlspecialchars($course_name) ?>
                </strong> with us!
                We're so proud of your accomplishment and excited to welcome you to the diving community.
            </p>

            <!-- Achievement Badge -->
            <div style="text-align: center; margin: 30px 0;">
                <div
                    style="display: inline-block; background: linear-gradient(135deg, #f5af19, #f12711); padding: 30px 40px; border-radius: 12px; color: white;">
                    <div style="font-size: 50px; margin-bottom: 10px;">🏆</div>
                    <div style="font-size: 20px; font-weight: bold;">
                        <?= htmlspecialchars($course_name) ?>
                    </div>
                    <div style="font-size: 14px; opacity: 0.9; margin-top: 5px;">COMPLETED</div>
                </div>
            </div>

            <!-- What's Next -->
            <h3 style="color: #333; font-size: 18px; margin: 25px 0 15px;">What's Next?</h3>
            <ul style="color: #555; line-height: 1.8; padding-left: 20px;">
                <li><strong>Your certification card:</strong> You'll receive your digital certification within 7-10
                    business days</li>
                <li><strong>Continue diving:</strong> Book fun dives with us to practice your new skills!</li>
                <li><strong>Keep learning:</strong> Ask about our Advanced Open Water or specialty courses</li>
                <li><strong>Join our community:</strong> Follow us on social media for dive tips and trip announcements
                </li>
            </ul>

            <!-- Stay Connected -->
            <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: center;">
                <h4 style="color: #667eea; margin: 0 0 10px;">Ready for Your Next Adventure?</h4>
                <p style="color: #666; margin: 0 0 15px; font-size: 14px;">Check out our upcoming dive trips and
                    courses!</p>
                <a href="#"
                    style="display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    View Upcoming Trips
                </a>
            </div>

            <!-- Review Request -->
            <p style="color: #555; line-height: 1.6;">
                We'd love to hear about your experience! If you have a moment, please consider leaving us a review.
                Your feedback helps other divers find us! ⭐
            </p>

            <p style="color: #333; margin-top: 25px;">
                Safe diving!<br>
                <strong>
                    <?= htmlspecialchars($instructor_first . ' ' . $instructor_last) ?>
                </strong><br>
                <span style="color: #11998e;">& The Nautilus Team</span>
            </p>
        </div>

        <!-- Footer -->
        <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
            <p>©
                <?= date('Y') ?> Nautilus Dive Shop. Happy bubbles! 🫧
            </p>
        </div>
    </div>
</body>

</html>