<?php
/**
 * Nautilus Database Configuration
 *
 * Hardcoded for container deployment - no .env files needed
 * Database credentials are set via Docker environment variables
 */

// Database connection constants
define('DB_HOST', getenv('DB_HOST') ?: 'database');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_DATABASE') ?: 'nautilus');
define('DB_USER', getenv('DB_USERNAME') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'Frogman09!');

/**
 * Get database connection
 * @return PDO
 */
function get_database_connection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST, DB_PORT, DB_NAME);

            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Get application settings from database
 * @return array
 */
function get_app_settings() {
    static $settings = null;

    if ($settings === null) {
        try {
            $pdo = get_database_connection();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            // Settings table might not exist yet during installation
            $settings = [];
        }
    }

    return $settings;
}

/**
 * Get a specific setting value
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_setting($key, $default = null) {
    $settings = get_app_settings();
    return $settings[$key] ?? $default;
}

/**
 * Update a setting value
 * @param string $key
 * @param mixed $value
 * @return bool
 */
function update_setting($key, $value) {
    try {
        $pdo = get_database_connection();
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        error_log("Failed to update setting $key: " . $e->getMessage());
        return false;
    }
}
