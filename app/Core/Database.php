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
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $port = $_ENV['DB_PORT'] ?? '3306';
                $database = $_ENV['DB_DATABASE'] ?? 'nautilus';
                $username = $_ENV['DB_USERNAME'] ?? 'root';
                $password = $_ENV['DB_PASSWORD'] ?? '';
                
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new \RuntimeException("Database connection failed");
            }
        }
        
        return self::$instance;
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
}
