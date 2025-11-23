<?php
/**
 * Configuration Saver
 * Saves application configuration and generates .env file
 */

header('Content-Type: application/json');

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid input data');
    }

    // Validate required fields
    $required = ['app_url', 'business_name', 'admin_email', 'timezone', 'db_host', 'db_name', 'db_user', 'db_password'];
    foreach ($required as $field) {
        if (empty($input[$field]) && $input[$field] !== '0') {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // Validate email
    if (!filter_var($input['admin_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // Validate timezone
    if (!in_array($input['timezone'], timezone_identifiers_list())) {
        throw new Exception('Invalid timezone');
    }

    // Generate application key (32 random bytes, base64 encoded)
    $appKey = base64_encode(random_bytes(32));

    // Prepare .env content
    $envContent = <<<ENV
# Nautilus Dive Shop - Environment Configuration
# Generated: {DATE}

# Application
APP_NAME="{BUSINESS_NAME}"
APP_ENV=production
APP_DEBUG=false
APP_URL={APP_URL}
APP_KEY={APP_KEY}
APP_TIMEZONE={TIMEZONE}

# Database
DB_CONNECTION=mysql
DB_HOST={DB_HOST}
DB_PORT=3306
DB_DATABASE={DB_NAME}
DB_USERNAME={DB_USER}
DB_PASSWORD={DB_PASSWORD}

# Admin
ADMIN_EMAIL={ADMIN_EMAIL}

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Security
CSRF_ENABLED=true
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=60
RATE_LIMIT_WINDOW=60

# File Uploads
MAX_UPLOAD_SIZE=10485760
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx

# Logging
LOG_LEVEL=warning
LOG_CHANNEL=daily

# Mail (Configure later in admin panel)
MAIL_DRIVER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS={ADMIN_EMAIL}
MAIL_FROM_NAME="{BUSINESS_NAME}"
ENV;

    // Replace placeholders
    $replacements = [
        '{DATE}' => date('Y-m-d H:i:s'),
        '{BUSINESS_NAME}' => str_replace('"', '\\"', $input['business_name']),
        '{APP_URL}' => rtrim($input['app_url'], '/'),
        '{APP_KEY}' => $appKey,
        '{TIMEZONE}' => $input['timezone'],
        '{DB_HOST}' => $input['db_host'],
        '{DB_NAME}' => $input['db_name'],
        '{DB_USER}' => $input['db_user'],
        '{DB_PASSWORD}' => $input['db_password'],
        '{ADMIN_EMAIL}' => $input['admin_email']
    ];

    $envContent = str_replace(array_keys($replacements), array_values($replacements), $envContent);

    // Write .env file
    $envFile = dirname(__DIR__, 2) . '/.env';

    if (file_exists($envFile)) {
        // Backup existing .env
        $backupFile = dirname(__DIR__, 2) . '/.env.backup.' . date('YmdHis');
        copy($envFile, $backupFile);
    }

    if (!file_put_contents($envFile, $envContent)) {
        throw new Exception('Failed to write .env file. Check permissions.');
    }

    // Store configuration in session for database installation step
    session_start();
    $_SESSION['install_config'] = [
        'business_name' => $input['business_name'],
        'admin_email' => $input['admin_email'],
        'timezone' => $input['timezone'],
        'db_host' => $input['db_host'],
        'db_name' => $input['db_name'],
        'db_user' => $input['db_user'],
        'db_password' => $input['db_password']
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Configuration saved successfully',
        'env_file' => $envFile,
        'app_key' => substr($appKey, 0, 10) . '...' // Show partial key for confirmation
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
