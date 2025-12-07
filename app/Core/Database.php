<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    
    public static function getInstance(): self
    {
        static $selfInstance = null;
        if ($selfInstance === null) {
            $selfInstance = new self();
        }
        return $selfInstance;
    }

    public function getConnection(): PDO
    {
        return self::getPdo();
    }

    public static function getPdo(): PDO
    {
        if (self::$instance === null) {
            try {
                $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $port = $_ENV['DB_PORT'] ?? self::getDefaultPort($connection);
                $database = $_ENV['DB_DATABASE'] ?? 'nautilus';
                $username = $_ENV['DB_USERNAME'] ?? 'root';
                $password = $_ENV['DB_PASSWORD'] ?? '';
                
                // Build DSN based on database type
                $dsn = self::buildDsn($connection, $host, $port, $database);
                
                // Build options based on database type
                $options = self::buildOptions($connection);
                
                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Get default port for database type
     */
    private static function getDefaultPort(string $connection): string
    {
        return match($connection) {
            'mysql' => '3306',
            'pgsql' => '5432',
            default => '3306'
        };
    }
    
    /**
     * Build DSN string for database connection
     */
    private static function buildDsn(string $connection, string $host, string $port, string $database): string
    {
        return match($connection) {
            'mysql' => "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
            'pgsql' => "pgsql:host={$host};port={$port};dbname={$database}",
            default => "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4"
        };
    }
    
    /**
     * Build PDO options for database connection
     */
    private static function buildOptions(string $connection): array
    {
        $baseOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        // Add MySQL-specific options
        if ($connection === 'mysql') {
            $baseOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
        }
        
        return $baseOptions;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $db = self::getPdo();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = self::query($sql, $params);
            $result = $stmt->fetch();
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            // Log error but return null to prevent crashes
            error_log("Database fetchOne error: " . $e->getMessage() . " SQL: " . substr($sql, 0, 100));
            return null;
        }
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = self::query($sql, $params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Log error but return empty array to prevent crashes
            error_log("Database fetchAll error: " . $e->getMessage() . " SQL: " . substr($sql, 0, 100));
            return [];
        }
    }
    
    public static function lastInsertId(): string
    {
        return self::getPdo()->lastInsertId();
    }

    /**
     * Begin a database transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getPdo()->beginTransaction();
    }

    /**
     * Commit the active database transaction
     */
    public static function commit(): bool
    {
        return self::getPdo()->commit();
    }

    /**
     * Rollback the active database transaction
     */
    public static function rollBack(): bool
    {
        return self::getPdo()->rollBack();
    }

    /**
     * Check if currently in a transaction
     */
    public static function inTransaction(): bool
    {
        return self::getPdo()->inTransaction();
    }

    /**
     * Execute a callback within a database transaction
     * Automatically commits on success or rolls back on exception
     *
     * @param callable $callback The callback to execute
     * @return mixed The callback's return value
     * @throws \Exception If the callback throws an exception
     */
    public static function transaction(callable $callback)
    {
        self::beginTransaction();

        try {
            $result = $callback();
            self::commit();
            return $result;
        } catch (\Exception $e) {
            self::rollBack();
            error_log("Transaction failed: " . $e->getMessage());
            throw $e;
        }
    }
}
