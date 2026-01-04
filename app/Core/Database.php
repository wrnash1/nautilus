<?php

namespace App\Core;

use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;
use PDOException;

class Database
{
    private static ?Capsule $capsule = null;

    /**
     * Initialize Eloquent Capsule
     */
    public static function init(): void
    {
        if (self::$capsule !== null) {
            return;
        }

        // Ensure Environment is loaded
        if (empty($_ENV['DB_DATABASE']) && empty(getenv('DB_DATABASE'))) {
            if (file_exists(dirname(__DIR__, 2) . '/vendor/autoload.php')) {
                require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
                if (class_exists('Dotenv\Dotenv')) {
                    try {
                        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
                        $dotenv->safeLoad();
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }
            }
        }

        $connection = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: 'sqlite';
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
        $database = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: dirname(__DIR__, 2) . '/database/database.sqlite';
        $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
        $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

        self::$capsule = new Capsule;

        $config = [
            'driver' => $connection,
            'host' => $host,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ];

        if ($connection === 'sqlite') {
            $config['database'] = $database;
            $config['foreign_key_constraints'] = true;
        } elseif ($connection === 'pgsql') {
            $config['port'] = $port;
            $config['schema'] = 'public';
        } else {
            // mysql
            $config['port'] = $port;
            $config['strict'] = true;
        }

        self::$capsule->addConnection($config);
        self::$capsule->setAsGlobal();
        self::$capsule->bootEloquent();
    }

    public static function getCapsule(): Capsule
    {
        if (self::$capsule === null) {
            self::init();
        }
        return self::$capsule;
    }

    public static function getPdo(): PDO
    {
        return self::getCapsule()->getConnection()->getPdo();
    }

    public static function getConnection()
    {
        return self::getCapsule()->getConnection();
    }

    public static function getInstance(): PDO
    {
        return self::getPdo();
    }

    // Legacy wrappers for backward compatibility

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function execute(string $sql, array $params = []): \PDOStatement
    {
        return self::query($sql, $params);
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = self::query($sql, $params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log("Database fetchOne error: " . $e->getMessage());
            return null;
        }
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = self::query($sql, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database fetchAll error: " . $e->getMessage());
            return [];
        }
    }

    public static function lastInsertId(): string
    {
        return self::getPdo()->lastInsertId();
    }

    public static function beginTransaction(): void
    {
        self::getConnection()->beginTransaction();
    }

    public static function commit(): void
    {
        self::getConnection()->commit();
    }

    public static function rollBack(): void
    {
        self::getConnection()->rollBack();
    }

    public static function inTransaction(): bool
    {
        // Eloquent doesn't expose inTransaction directly on Connection Interface easily without getPdo
        return self::getPdo()->inTransaction();
    }

    public static function transaction(callable $callback)
    {
        return self::getConnection()->transaction($callback);
    }
}
