<?php

namespace App\Core;

/**
 * Encryption utility for securing sensitive data at rest
 * Uses AES-256-CBC encryption with APP_KEY as the master key
 */
class Encryption
{
    /**
     * Get the encryption key from environment
     *
     * @return string 32-byte binary key
     * @throws \RuntimeException if APP_KEY is not set or too short
     */
    private static function getKey(): string
    {
        $key = $_ENV['APP_KEY'] ?? null;

        if (!$key || strlen($key) < 32) {
            throw new \RuntimeException(
                'APP_KEY must be at least 32 characters. Run installation to generate a secure key.'
            );
        }

        // Convert to 32-byte binary key using SHA-256
        return hash('sha256', $key, true);
    }

    /**
     * Encrypt a plaintext string
     *
     * @param string $plaintext The data to encrypt
     * @return string Base64-encoded encrypted data with IV prepended
     * @throws \RuntimeException if encryption fails
     */
    public static function encrypt(string $plaintext): string
    {
        if (empty($plaintext)) {
            return '';
        }

        $key = self::getKey();

        // Generate random IV (Initialization Vector) - 16 bytes for AES-256-CBC
        $iv = random_bytes(16);

        $ciphertext = openssl_encrypt(
            $plaintext,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed: ' . openssl_error_string());
        }

        // Prepend IV to ciphertext (IV doesn't need to be secret)
        // Format: [16-byte IV][encrypted data]
        return base64_encode($iv . $ciphertext);
    }

    /**
     * Decrypt an encrypted string
     *
     * @param string $encrypted Base64-encoded encrypted data with IV prepended
     * @return string Decrypted plaintext
     * @throws \RuntimeException if decryption fails or data is invalid
     */
    public static function decrypt(string $encrypted): string
    {
        if (empty($encrypted)) {
            return '';
        }

        $key = self::getKey();
        $data = base64_decode($encrypted, true);

        if ($data === false || strlen($data) < 16) {
            throw new \RuntimeException('Invalid encrypted data: unable to decode or too short');
        }

        // Extract IV (first 16 bytes) and ciphertext (remaining bytes)
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);

        $plaintext = openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed: ' . openssl_error_string());
        }

        return $plaintext;
    }

    /**
     * Mask a sensitive value for display (show last N characters)
     *
     * @param string $value The value to mask
     * @param int $showLast Number of characters to show at the end
     * @return string Masked value (e.g., "••••••••1234")
     */
    public static function mask(string $value, int $showLast = 4): string
    {
        if (empty($value)) {
            return '';
        }

        $length = strlen($value);

        if ($length <= $showLast) {
            // Value is too short to mask meaningfully
            return str_repeat('•', $length);
        }

        $masked = str_repeat('•', $length - $showLast);
        $visible = substr($value, -$showLast);

        return $masked . $visible;
    }

    /**
     * Check if encryption is properly configured
     *
     * @return bool True if encryption is available
     */
    public static function isConfigured(): bool
    {
        try {
            $key = $_ENV['APP_KEY'] ?? null;
            return !empty($key) && strlen($key) >= 32;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test encryption/decryption (useful for diagnostics)
     *
     * @return bool True if encryption round-trip succeeds
     */
    public static function test(): bool
    {
        try {
            $testData = 'test_encryption_' . time();
            $encrypted = self::encrypt($testData);
            $decrypted = self::decrypt($encrypted);

            return $testData === $decrypted;
        } catch (\Exception $e) {
            return false;
        }
    }
}
