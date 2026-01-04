<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Installation - Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-container {
            max-width: 700px;
            width: 100%;
            padding: 20px;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .install-header i {
            font-size: 5rem;
            animation: successPulse 2s infinite;
        }
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .install-body {
            padding: 40px;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 20px;
            margin-bottom: 20px;
        }
        .next-steps {
            list-style: none;
            padding: 0;
            counter-reset: step-counter;
        }
        .next-steps li {
            position: relative;
            padding: 15px 15px 15px 50px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            counter-increment: step-counter;
        }
        .next-steps li::before {
            content: counter(step-counter);
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 25px;
            height: 25px;
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <i class="bi bi-check-circle-fill"></i>
                <h1 class="mt-3 mb-2">Installation Complete!</h1>
                <p class="lead mb-0">Nautilus has been successfully installed</p>
            </div>

            <div class="install-body">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Success!</strong> Your Nautilus installation is complete and ready to use.
                </div>

                <div class="info-box">
                    <h5><i class="bi bi-info-circle"></i> Important Information</h5>
                    <p class="mb-2"><strong>Installation Summary:</strong></p>
                    <ul class="mb-0">
                        <li>Database configured and migrations completed</li>
                        <li>Administrator account created</li>
                        <li>Security keys generated</li>
                        <li>System initialized and ready</li>
                    </ul>
                </div>

                <h4 class="mb-3">Next Steps</h4>
                <ul class="next-steps">
                    <li>
                        <strong>Login to your account</strong><br>
                        <small class="text-muted">Use the email and password you created during installation</small>
                    </li>
                    <li>
                        <strong>Configure system settings</strong><br>
                        <small class="text-muted">Visit Admin > Settings to configure payment gateways, email, and integrations</small>
                    </li>
                    <li>
                        <strong>Add your products</strong><br>
                        <small class="text-muted">Set up your product catalog in Inventory > Products</small>
                    </li>
                    <li>
                        <strong>Create user accounts</strong><br>
                        <small class="text-muted">Add staff members in Admin > User Management</small>
                    </li>
                    <li>
                        <strong>Customize your store</strong><br>
                        <small class="text-muted">Update the online store appearance in CMS > Pages</small>
                    </li>
                </ul>

                <div class="alert alert-warning mt-4">
                    <i class="bi bi-shield-exclamation"></i>
                    <strong>Security Reminder:</strong> For production environments, make sure to:
                    <ul class="mb-0 mt-2">
                        <li>Set <code>APP_DEBUG=false</code> in your .env file</li>
                        <li>Set <code>APP_ENV=production</code> in your .env file</li>
                        <li>Configure SSL/HTTPS for secure connections</li>
                        <li>Set up regular database backups</li>
                    </ul>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="/login" class="btn btn-success btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login to Nautilus
                    </a>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted mb-0">
                        <small>
                            Need help? Check out the documentation or contact support.<br>
                            Thank you for choosing Nautilus!
                        </small>
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center text-white mt-3">
            <small>Nautilus v6.0.0 | Dive Shop Management System</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
