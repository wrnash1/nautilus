<?php

namespace App\Core;

/**
 * Cache Manager
 * Supports multiple cache drivers: File, Redis, Memcached
 */
class Cache
{
    private static ?Cache $instance = null;
    private string $driver;
    private ?object $connection = null;
    private string $prefix;
    private int $defaultTTL;

    private function __construct()
    {
        $this->driver = $_ENV['CACHE_DRIVER'] ?? 'file';
        $this->prefix = $_ENV['CACHE_PREFIX'] ?? 'nautilus_';
        $this->defaultTTL = (int)($_ENV['CACHE_TTL'] ?? 3600);

        $this->connect();
    }

    public static function getInstance(): Cache
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Connect to cache backend
     */
    private function connect(): void
    {
        switch ($this->driver) {
            case 'redis':
                if (class_exists('\Redis')) {
                    $this->connection = new \Redis();
                    $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
                    $port = (int)($_ENV['REDIS_PORT'] ?? 6379);
                    $this->connection->connect($host, $port);

                    if (!empty($_ENV['REDIS_PASSWORD'])) {
                        $this->connection->auth($_ENV['REDIS_PASSWORD']);
                    }
                }
                break;

            case 'memcached':
                if (class_exists('\Memcached')) {
                    $this->connection = new \Memcached();
                    $host = $_ENV['MEMCACHED_HOST'] ?? '127.0.0.1';
                    $port = (int)($_ENV['MEMCACHED_PORT'] ?? 11211);
                    $this->connection->addServer($host, $port);
                }
                break;

            case 'file':
            default:
                // File cache doesn't need connection
                $this->driver = 'file';
                break;
        }
    }

    /**
     * Get value from cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->prefix . $key;

        try {
            switch ($this->driver) {
                case 'redis':
                    $value = $this->connection?->get($key);
                    return $value === false ? $default : unserialize($value);

                case 'memcached':
                    $value = $this->connection?->get($key);
                    return $value === false ? $default : $value;

                case 'file':
                    return $this->fileGet($key, $default);

                default:
                    return $default;
            }
        } catch (\Exception $e) {
            (new Logger())->warning('Cache get failed', ['key' => $key, 'error' => $e->getMessage()]);
            return $default;
        }
    }

    /**
     * Set value in cache
     */
    public function set(string $key, mixed $value, ??int $ttl = null): bool
    {
        $key = $this->prefix . $key;
        $ttl = $ttl ?? $this->defaultTTL;

        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->connection?->setex($key, $ttl, serialize($value)) ?? false;

                case 'memcached':
                    return $this->connection?->set($key, $value, $ttl) ?? false;

                case 'file':
                    return $this->fileSet($key, $value, $ttl);

                default:
                    return false;
            }
        } catch (\Exception $e) {
            (new Logger())->warning('Cache set failed', ['key' => $key, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        $key = $this->prefix . $key;

        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->connection?->exists($key) ?? false;

                case 'memcached':
                    $this->connection?->get($key);
                    return $this->connection?->getResultCode() !== \Memcached::RES_NOTFOUND;

                case 'file':
                    return $this->fileHas($key);

                default:
                    return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete key from cache
     */
    public function delete(string $key): bool
    {
        $key = $this->prefix . $key;

        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->connection?->del($key) > 0;

                case 'memcached':
                    return $this->connection?->delete($key) ?? false;

                case 'file':
                    return $this->fileDelete($key);

                default:
                    return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->connection?->flushDB() ?? false;

                case 'memcached':
                    return $this->connection?->flush() ?? false;

                case 'file':
                    return $this->fileClear();

                default:
                    return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get or set cache value
     */
    public function remember(string $key, callable $callback, ??int $ttl = null): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Increment cache value
     */
    public function increment(string $key, int $value = 1): int|false
    {
        $key = $this->prefix . $key;

        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->connection?->incrBy($key, $value) ?? false;

                case 'memcached':
                    return $this->connection?->increment($key, $value) ?? false;

                case 'file':
                    $current = (int)$this->get($key, 0);
                    $new = $current + $value;
                    $this->set($key, $new);
                    return $new;

                default:
                    return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Decrement cache value
     */
    public function decrement(string $key, int $value = 1): int|false
    {
        $key = $this->prefix . $key;

        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->connection?->decrBy($key, $value) ?? false;

                case 'memcached':
                    return $this->connection?->decrement($key, $value) ?? false;

                case 'file':
                    $current = (int)$this->get($key, 0);
                    $new = $current - $value;
                    $this->set($key, $new);
                    return $new;

                default:
                    return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    // ==================== File Cache Methods ====================

    private function getCachePath(): string
    {
        $path = BASE_PATH . '/storage/cache';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    private function fileGet(string $key, mixed $default): mixed
    {
        $filepath = $this->getCachePath() . '/' . md5($key) . '.cache';

        if (!file_exists($filepath)) {
            return $default;
        }

        $data = unserialize(file_get_contents($filepath));

        // Check if expired
        if ($data['expires'] < time()) {
            unlink($filepath);
            return $default;
        }

        return $data['value'];
    }

    private function fileSet(string $key, mixed $value, int $ttl): bool
    {
        $filepath = $this->getCachePath() . '/' . md5($key) . '.cache';

        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        return file_put_contents($filepath, serialize($data), LOCK_EX) !== false;
    }

    private function fileHas(string $key): bool
    {
        $filepath = $this->getCachePath() . '/' . md5($key) . '.cache';

        if (!file_exists($filepath)) {
            return false;
        }

        $data = unserialize(file_get_contents($filepath));

        // Check if expired
        if ($data['expires'] < time()) {
            unlink($filepath);
            return false;
        }

        return true;
    }

    private function fileDelete(string $key): bool
    {
        $filepath = $this->getCachePath() . '/' . md5($key) . '.cache';

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return true;
    }

    private function fileClear(): bool
    {
        $path = $this->getCachePath();
        $files = glob($path . '/*.cache');

        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }

    /**
     * Clean expired cache files
     */
    public function cleanExpired(): int
    {
        if ($this->driver !== 'file') {
            return 0;
        }

        $cleaned = 0;
        $path = $this->getCachePath();
        $files = glob($path . '/*.cache');

        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}
