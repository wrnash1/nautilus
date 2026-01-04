<?php

namespace App\Core;

/**
 * Credential Manager
 *
 * Securely manages database credentials, API keys, and secrets
 * Supports encryption/decryption of sensitive values
 */
class CredentialManager
{
    private static $encryptionKey = null;
    private static $currentEnvironment = null;

    /**
     * Initialize encryption key from environment
     */
    private static function init()
    {
        if (self::$encryptionKey === null) {
            // Use APP_KEY from .env or generate temporary one
            self::$encryptionKey = $_ENV['APP_KEY'] ?? hash('sha256', 'nautilus_temp_key');
            self::$currentEnvironment = $_ENV['APP_ENV'] ?? 'development';
        }
    }

    /**
     * Encrypt sensitive value
     */
    public static function encrypt($value)
    {
        self::init();

        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $value,
            'AES-256-CBC',
            self::$encryptionKey,
            0,
            $iv
        );

        // Return base64 encoded: iv + encrypted data
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt sensitive value
     */
    public static function decrypt($encrypted)
    {
        self::init();

        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);

        return openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            self::$encryptionKey,
            0,
            $iv
        );
    }

    /**
     * Get tenant database credentials
     * Returns decrypted credentials for the specified tenant and environment
     */
    public static function getTenantDatabaseCredentials($tenantId, $environment = null)
    {
        self::init();
        $environment = $environment ?? self::$currentEnvironment;

        $db = Database::getPdo();
        $stmt = $db->prepare("
            SELECT use_dedicated_db, db_host, db_port, db_database,
                   db_username, db_password, connection_options
            FROM tenant_database_credentials
            WHERE tenant_id = ? AND environment = ? AND is_active = 1
        ");
        $stmt->execute([$tenantId, $environment]);
        $config = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$config) {
            // Return default/shared database config
            return [
                'use_dedicated_db' => false,
                'host' => $_ENV['DB_HOST'] ?? 'database',
                'port' => $_ENV['DB_PORT'] ?? 3306,
                'database' => $_ENV['DB_DATABASE'] ?? 'nautilus',
                'username' => $_ENV['DB_USERNAME'] ?? 'nautilus',
                'password' => $_ENV['DB_PASSWORD'] ?? 'nautilus123',
            ];
        }

        // If using shared DB, return default credentials
        if (!$config['use_dedicated_db']) {
            return [
                'use_dedicated_db' => false,
                'host' => $_ENV['DB_HOST'],
                'port' => $_ENV['DB_PORT'],
                'database' => $_ENV['DB_DATABASE'],
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
            ];
        }

        // Decrypt password for dedicated database
        $config['password'] = self::decrypt($config['db_password']);

        return [
            'use_dedicated_db' => true,
            'host' => $config['db_host'],
            'port' => $config['db_port'],
            'database' => $config['db_database'],
            'username' => $config['db_username'],
            'password' => $config['password'],
            'options' => json_decode($config['connection_options'] ?? '{}', true),
        ];
    }

    /**
     * Store or update tenant database credentials
     */
    public static function setTenantDatabaseCredentials($tenantId, $config, $environment = null)
    {
        self::init();
        $environment = $environment ?? self::$currentEnvironment;

        $db = Database::getInstance();

        // Encrypt password
        $encryptedPassword = self::encrypt($config['password']);

        $stmt = $db->prepare("
            INSERT INTO tenant_database_credentials
                (tenant_id, environment, use_dedicated_db, db_host, db_port,
                 db_database, db_username, db_password, connection_options)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                use_dedicated_db = VALUES(use_dedicated_db),
                db_host = VALUES(db_host),
                db_port = VALUES(db_port),
                db_database = VALUES(db_database),
                db_username = VALUES(db_username),
                db_password = VALUES(db_password),
                connection_options = VALUES(connection_options),
                updated_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            $tenantId,
            $environment,
            $config['use_dedicated_db'] ?? 1,
            $config['host'],
            $config['port'] ?? 3306,
            $config['database'],
            $config['username'],
            $encryptedPassword,
            json_encode($config['options'] ?? []),
        ]);

        // Log credential rotation
        self::logCredentialRotation($tenantId, 'database', $environment);

        return true;
    }

    /**
     * Get environment setting
     */
    public static function getEnvironmentSetting($key, $default = null, $environment = null)
    {
        self::init();
        $environment = $environment ?? self::$currentEnvironment;

        $db = Database::getPdo();
        $stmt = $db->prepare("
            SELECT setting_value, is_encrypted
            FROM environment_settings
            WHERE environment = ? AND setting_key = ?
        ");
        $stmt->execute([$environment, $key]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return $default;
        }

        $value = $result['setting_value'];
        if ($result['is_encrypted']) {
            $value = self::decrypt($value);
        }

        return $value;
    }

    /**
     * Set environment setting
     */
    public static function setEnvironmentSetting($key, $value, $isSensitive = false, $description = null, $environment = null)
    {
        self::init();
        $environment = $environment ?? self::$currentEnvironment;

        $db = Database::getInstance();

        // Encrypt if sensitive
        $storedValue = $isSensitive ? self::encrypt($value) : $value;

        $stmt = $db->prepare("
            INSERT INTO environment_settings
                (environment, setting_key, setting_value, is_encrypted, is_sensitive, description)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                is_encrypted = VALUES(is_encrypted),
                is_sensitive = VALUES(is_sensitive),
                description = VALUES(description),
                updated_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            $environment,
            $key,
            $storedValue,
            $isSensitive ? 1 : 0,
            $isSensitive ? 1 : 0,
            $description,
        ]);

        return true;
    }

    /**
     * Get tenant secret (API keys, tokens, etc.)
     */
    public static function getTenantSecret($tenantId, $serviceName, $keyName, $environment = null)
    {
        self::init();
        $environment = $environment ?? self::$currentEnvironment;

        $db = Database::getPdo();
        $stmt = $db->prepare("
            SELECT key_value, is_encrypted
            FROM tenant_secrets
            WHERE tenant_id = ? AND environment = ?
              AND service_name = ? AND key_name = ?
              AND is_active = 1
        ");
        $stmt->execute([$tenantId, $environment, $serviceName, $keyName]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        return $result['is_encrypted'] ? self::decrypt($result['key_value']) : $result['key_value'];
    }

    /**
     * Set tenant secret
     */
    public static function setTenantSecret($tenantId, $serviceName, $keyName, $keyValue, $keyType = 'api_key', $rotationDays = 90, $environment = null)
    {
        self::init();
        $environment = $environment ?? self::$currentEnvironment;

        $db = Database::getInstance();

        // Always encrypt secrets
        $encryptedValue = self::encrypt($keyValue);

        $stmt = $db->prepare("
            INSERT INTO tenant_secrets
                (tenant_id, environment, service_name, key_name, key_value, key_type, rotation_days)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                key_value = VALUES(key_value),
                key_type = VALUES(key_type),
                rotation_days = VALUES(rotation_days),
                last_rotated_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            $tenantId,
            $environment,
            $serviceName,
            $keyName,
            $encryptedValue,
            $keyType,
            $rotationDays,
        ]);

        return true;
    }

    /**
     * Log credential rotation for audit
     */
    private static function logCredentialRotation($tenantId, $credentialType, $environment, $reason = 'Manual rotation')
    {
        $db = Database::getPdo();
        $stmt = $db->prepare("
            INSERT INTO credential_rotation_log
                (tenant_id, credential_type, environment, rotation_reason, rotation_status)
            VALUES (?, ?, ?, ?, 'success')
        ");
        $stmt->execute([$tenantId, $credentialType, $environment, $reason]);
    }

    /**
     * Test database connection
     */
    public static function testDatabaseConnection($tenantId, $environment = null)
    {
        try {
            $credentials = self::getTenantDatabaseCredentials($tenantId, $environment);

            $pdo = new \PDO(
                "mysql:host={$credentials['host']};port={$credentials['port']};dbname={$credentials['database']}",
                $credentials['username'],
                $credentials['password']
            );

            // Update last connection test
            $db = Database::getInstance();
            $stmt = $db->prepare("
                UPDATE tenant_database_credentials
                SET last_connection_test = CURRENT_TIMESTAMP,
                    last_connection_status = 'success',
                    last_connection_error = NULL
                WHERE tenant_id = ? AND environment = ?
            ");
            $stmt->execute([$tenantId, $environment ?? self::$currentEnvironment]);

            return ['success' => true, 'message' => 'Connection successful'];

        } catch (\Exception $e) {
            // Update last connection test with error
            $db = Database::getInstance();
            $stmt = $db->prepare("
                UPDATE tenant_database_credentials
                SET last_connection_test = CURRENT_TIMESTAMP,
                    last_connection_status = 'failed',
                    last_connection_error = ?
                WHERE tenant_id = ? AND environment = ?
            ");
            $stmt->execute([$e->getMessage(), $tenantId, $environment ?? self::$currentEnvironment]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
