<?php

namespace App\Core;

/**
 * Redis Cache Adapter
 *
 * High-performance caching layer using Redis
 * Falls back to file-based caching if Redis is unavailable
 */
class RedisCache
{
    private static $instance = null;
    private $redis = null;
    private bool $isAvailable = false;
    private string $prefix;

    private function __construct()
    {
        $this->prefix = $_ENV['CACHE_PREFIX'] ?? 'nautilus:';

        if (extension_loaded('redis')) {
            try {
                $this->redis = new \Redis();

                $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
                $port = $_ENV['REDIS_PORT'] ?? 6379;
                $password = $_ENV['REDIS_PASSWORD'] ?? null;
                $database = $_ENV['REDIS_DATABASE'] ?? 0;

                $connected = $this->redis->connect($host, $port, 2.5);

                if ($connected) {
                    if ($password) {
                        $this->redis->auth($password);
                    }

                    $this->redis->select($database);
                    $this->isAvailable = true;
                }
            } catch (\Exception $e) {
                error_log("Redis connection failed: " . $e->getMessage());
                $this->isAvailable = false;
            }
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get value from cache
     */
    public function get(string $key)
    {
        if (!$this->isAvailable) {
            return $this->getFromFile($key);
        }

        try {
            $value = $this->redis->get($this->prefix . $key);

            if ($value === false) {
                return false;
            }

            return unserialize($value);
        } catch (\Exception $e) {
            error_log("Redis get failed: " . $e->getMessage());
            return $this->getFromFile($key);
        }
    }

    /**
     * Set value in cache
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        if (!$this->isAvailable) {
            return $this->setToFile($key, $value, $ttl);
        }

        try {
            $serialized = serialize($value);

            if ($ttl > 0) {
                return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
            } else {
                return $this->redis->set($this->prefix . $key, $serialized);
            }
        } catch (\Exception $e) {
            error_log("Redis set failed: " . $e->getMessage());
            return $this->setToFile($key, $value, $ttl);
        }
    }

    /**
     * Delete from cache
     */
    public function delete(string $key): bool
    {
        if (!$this->isAvailable) {
            return $this->deleteFromFile($key);
        }

        try {
            return $this->redis->del($this->prefix . $key) > 0;
        } catch (\Exception $e) {
            error_log("Redis delete failed: " . $e->getMessage());
            return $this->deleteFromFile($key);
        }
    }

    /**
     * Check if key exists
     */
    public function exists(string $key): bool
    {
        if (!$this->isAvailable) {
            return $this->existsInFile($key);
        }

        try {
            return $this->redis->exists($this->prefix . $key) > 0;
        } catch (\Exception $e) {
            return $this->existsInFile($key);
        }
    }

    /**
     * Increment value
     */
    public function increment(string $key, int $value = 1): int
    {
        if (!$this->isAvailable) {
            return $this->incrementInFile($key, $value);
        }

        try {
            return $this->redis->incrBy($this->prefix . $key, $value);
        } catch (\Exception $e) {
            return $this->incrementInFile($key, $value);
        }
    }

    /**
     * Decrement value
     */
    public function decrement(string $key, int $value = 1): int
    {
        if (!$this->isAvailable) {
            return $this->decrementInFile($key, $value);
        }

        try {
            return $this->redis->decrBy($this->prefix . $key, $value);
        } catch (\Exception $e) {
            return $this->decrementInFile($key, $value);
        }
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        if (!$this->isAvailable) {
            return $this->flushFiles();
        }

        try {
            // Only flush keys with our prefix
            $keys = $this->redis->keys($this->prefix . '*');

            if (!empty($keys)) {
                return $this->redis->del($keys) > 0;
            }

            return true;
        } catch (\Exception $e) {
            error_log("Redis flush failed: " . $e->getMessage());
            return $this->flushFiles();
        }
    }

    /**
     * Get multiple keys
     */
    public function getMultiple(array $keys): array
    {
        if (!$this->isAvailable) {
            return $this->getMultipleFromFile($keys);
        }

        try {
            $prefixedKeys = array_map(fn($k) => $this->prefix . $k, $keys);
            $values = $this->redis->mGet($prefixedKeys);

            $result = [];
            foreach ($keys as $index => $key) {
                $result[$key] = $values[$index] !== false ? unserialize($values[$index]) : false;
            }

            return $result;
        } catch (\Exception $e) {
            return $this->getMultipleFromFile($keys);
        }
    }

    /**
     * Set multiple keys
     */
    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        if (!$this->isAvailable) {
            return $this->setMultipleToFile($values, $ttl);
        }

        try {
            $prefixedValues = [];
            foreach ($values as $key => $value) {
                $prefixedValues[$this->prefix . $key] = serialize($value);
            }

            $result = $this->redis->mSet($prefixedValues);

            if ($ttl > 0) {
                foreach (array_keys($prefixedValues) as $key) {
                    $this->redis->expire($key, $ttl);
                }
            }

            return $result;
        } catch (\Exception $e) {
            return $this->setMultipleToFile($values, $ttl);
        }
    }

    /**
     * Remember: Get from cache or execute callback and store result
     */
    public function remember(string $key, int $ttl, callable $callback)
    {
        $value = $this->get($key);

        if ($value !== false) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        if (!$this->isAvailable) {
            return [
                'backend' => 'file',
                'available' => false
            ];
        }

        try {
            $info = $this->redis->info();

            return [
                'backend' => 'redis',
                'available' => true,
                'version' => $info['redis_version'] ?? 'unknown',
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'backend' => 'redis',
                'available' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // File-based cache fallback methods

    private function getCacheDir(): string
    {
        $dir = __DIR__ . '/../../storage/cache';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    private function getCacheFilePath(string $key): string
    {
        return $this->getCacheDir() . '/' . md5($key) . '.cache';
    }

    private function getFromFile(string $key)
    {
        $filepath = $this->getCacheFilePath($key);

        if (!file_exists($filepath)) {
            return false;
        }

        $data = unserialize(file_get_contents($filepath));

        if ($data['expires'] > 0 && time() > $data['expires']) {
            unlink($filepath);
            return false;
        }

        return $data['value'];
    }

    private function setToFile(string $key, $value, int $ttl): bool
    {
        $filepath = $this->getCacheFilePath($key);

        $data = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : 0
        ];

        return file_put_contents($filepath, serialize($data)) !== false;
    }

    private function deleteFromFile(string $key): bool
    {
        $filepath = $this->getCacheFilePath($key);

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return true;
    }

    private function existsInFile(string $key): bool
    {
        return $this->getFromFile($key) !== false;
    }

    private function incrementInFile(string $key, int $value): int
    {
        $current = (int) $this->getFromFile($key);
        $new = $current + $value;
        $this->setToFile($key, $new, 3600);

        return $new;
    }

    private function decrementInFile(string $key, int $value): int
    {
        $current = (int) $this->getFromFile($key);
        $new = $current - $value;
        $this->setToFile($key, $new, 3600);

        return $new;
    }

    private function flushFiles(): bool
    {
        $dir = $this->getCacheDir();
        $files = glob($dir . '/*.cache');

        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }

    private function getMultipleFromFile(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->getFromFile($key);
        }

        return $result;
    }

    private function setMultipleToFile(array $values, int $ttl): bool
    {
        foreach ($values as $key => $value) {
            $this->setToFile($key, $value, $ttl);
        }

        return true;
    }

    public function isRedisAvailable(): bool
    {
        return $this->isAvailable;
    }
}
