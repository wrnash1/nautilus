<?php
/**
 * Nautilus Web-Based Installer
 *
 * Enterprise-grade installation wizard for multi-tenant deployment
 */

session_start();

// Check if already installed
if (file_exists(__DIR__ . '/../../.installed')) {
    header('Location: ../index.php');
    exit;
}

// Initialize installation session
if (!isset($_SESSION['install_step'])) {
    $_SESSION['install_step'] = 1;
    $_SESSION['install_data'] = [];
}

$step = $_SESSION['install_step'];
$errors = [];
$success = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle back button
    if (isset($_POST['back']) && $step > 1) {
        $_SESSION['install_step'] = $step - 1;
        $step = $step - 1;
    }
    // Handle next button
    elseif (isset($_POST['next'])) {
        // Validate and proceed to next step
        if ($step < 6) {
            $_SESSION['install_step'] = $step + 1;
            $step = $step + 1;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Installation Wizard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .installer-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #0066cc 0%, #004999 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .progress-bar {
            background: #f0f0f0;
            height: 6px;
        }

        .progress-fill {
            background: #0066cc;
            height: 100%;
            transition: width 0.3s ease;
        }

        .step-indicators {
            display: flex;
            justify-content: space-between;
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }

        .step-indicator {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 10px;
        }

        .step-indicator::before {
            content: attr(data-step);
            display: block;
            width: 35px;
            height: 35px;
            background: #ddd;
            border-radius: 50%;
            margin: 0 auto 8px;
            line-height: 35px;
            font-weight: bold;
            color: #666;
        }

        .step-indicator.active::before {
            background: #0066cc;
            color: white;
        }

        .step-indicator.completed::before {
            background: #28a745;
            color: white;
            content: '✓';
        }

        .step-indicator span {
            font-size: 0.85em;
            color: #666;
        }

        .step-indicator.active span {
            color: #0066cc;
            font-weight: 600;
        }

        .content {
            padding: 40px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #0066cc;
        }

        .form-help {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }

        .requirement-list {
            list-style: none;
            margin: 20px 0;
        }

        .requirement-item {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .requirement-item.pass {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .requirement-item.fail {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .requirement-item.warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #333;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #0066cc;
            color: white;
        }

        .btn-primary:hover {
            background: #0052a3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: #28a745;
            color: white;
            font-size: 18px;
            padding: 15px 40px;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .installation-log {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 400px;
            overflow-y: auto;
            margin: 20px 0;
        }

        .log-entry {
            padding: 5px 0;
            border-bottom: 1px solid #333;
        }

        .log-entry.success {
            color: #4ec9b0;
        }

        .log-entry.error {
            color: #f48771;
        }

        .log-entry.info {
            color: #569cd6;
        }

        .completion-icon {
            text-align: center;
            font-size: 80px;
            color: #28a745;
            margin: 30px 0;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .two-column {
                grid-template-columns: 1fr;
            }

            .step-indicator span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="header">
            <h1>🌊 Nautilus</h1>
            <p>Enterprise Dive Shop Management System</p>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= ($step / 6) * 100 ?>%"></div>
        </div>

        <div class="step-indicators">
            <div class="step-indicator <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>" data-step="1">
                <span>Requirements</span>
            </div>
            <div class="step-indicator <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>" data-step="2">
                <span>Database</span>
            </div>
            <div class="step-indicator <?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' ?>" data-step="3">
                <span>Configuration</span>
            </div>
            <div class="step-indicator <?= $step >= 4 ? ($step > 4 ? 'completed' : 'active') : '' ?>" data-step="4">
                <span>Admin Account</span>
            </div>
            <div class="step-indicator <?= $step >= 5 ? ($step > 5 ? 'completed' : 'active') : '' ?>" data-step="5">
                <span>Installation</span>
            </div>
            <div class="step-indicator <?= $step >= 6 ? 'active' : '' ?>" data-step="6">
                <span>Complete</span>
            </div>
        </div>

        <div class="content">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>⚠️ Error:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <strong>✓ Success:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach ($success as $msg): ?>
                            <li><?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php include __DIR__ . '/steps/step' . $step . '.php'; ?>
        </div>
    </div>
</body>
</html>
