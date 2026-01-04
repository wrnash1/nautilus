<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $subject ?? 'Nautilus Dive Shop' ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #0077be 0%, #005a8c 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #0077be;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
        }
        .btn:hover {
            background-color: #005a8c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌊 Nautilus Dive Shop</h1>
        </div>
        <div class="content">
            <?= $content ?? '' ?>
        </div>
        <div class="footer">
            <p>© <?= date('Y') ?> Nautilus Dive Shop. All rights reserved.</p>
            <p>If you have any questions, please contact us at <?= $_ENV['MAIL_FROM_ADDRESS'] ?? 'info@nautilus.com' ?></p>
        </div>
    </div>
</body>
</html>
