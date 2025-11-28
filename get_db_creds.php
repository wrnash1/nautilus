<?php
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "Error: .env file not found\n";
    exit(1);
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (in_array($key, ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'])) {
            echo "$key=$value\n";
        }
    }
}
