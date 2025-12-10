<?php

namespace App\Services\Security;

/**
 * Data Encryption Service
 * Handles encryption/decryption of sensitive data
 */
class EncryptionService
{
    private string $encryptionKey;
    private string $cipher = 'aes-256-gcm';

    public function __construct()
    {
        // Load encryption key from environment
        $this->encryptionKey = $_ENV['APP_ENCRYPTION_KEY'] ?? $this->generateKey();

        if (strlen($this->encryptionKey) < 32) {
            throw new \Exception('Encryption key must be at least 32 characters');
        }
    }

    /**
     * Encrypt data
     */
    public function encrypt(string $data): string
    {
        if (empty($data)) {
            return '';
        }

        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $tag = '';

        $encrypted = openssl_encrypt(
            $data,
            $this->cipher,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }

        // Combine IV + Tag + Encrypted Data
        $result = base64_encode($iv . $tag . $encrypted);

        return $result;
    }

    /**
     * Decrypt data
     */
    public function decrypt(string $encryptedData): string
    {
        if (empty($encryptedData)) {
            return '';
        }

        $data = base64_decode($encryptedData);

        if ($data === false) {
            throw new \Exception('Invalid encrypted data format');
        }

        $ivLength = openssl_cipher_iv_length($this->cipher);
        $tagLength = 16;

        if (strlen($data) < ($ivLength + $tagLength)) {
            throw new \Exception('Encrypted data is too short');
        }

        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, $tagLength);
        $ciphertext = substr($data, $ivLength + $tagLength);

        $decrypted = openssl_decrypt(
            $ciphertext,
            $this->cipher,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new \Exception('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * Encrypt array (JSON encode then encrypt)
     */
    public function encryptArray(array $data): string
    {
        $json = json_encode($data);
        return $this->encrypt($json);
    }

    /**
     * Decrypt to array
     */
    public function decryptArray(string $encryptedData): array
    {
        $json = $this->decrypt($encryptedData);
        $data = json_decode($json, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to decode decrypted data');
        }

        return $data;
    }

    /**
     * Hash data (one-way)
     */
    public function hash(string $data): string
    {
        return password_hash($data, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify hash
     */
    public function verifyHash(string $data, string $hash): bool
    {
        return password_verify($data, $hash);
    }

    /**
     * Generate secure random token
     */
    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate encryption key
     */
    public function generateKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Mask sensitive data for display (e.g., credit card)
     */
    public function maskData(string $data, int $visibleChars = 4, string $maskChar = '*'): string
    {
        $length = strlen($data);

        if ($length <= $visibleChars) {
            return str_repeat($maskChar, $length);
        }

        $masked = str_repeat($maskChar, $length - $visibleChars);
        $visible = substr($data, -$visibleChars);

        return $masked . $visible;
    }

    /**
     * Sanitize credit card number (encrypt and return masked version for display)
     */
    public function sanitizeCreditCard(string $cardNumber): array
    {
        // Remove spaces and dashes
        $clean = preg_replace('/[\s\-]/', '', $cardNumber);

        return [
            'encrypted' => $this->encrypt($clean),
            'masked' => $this->maskData($clean, 4),
            'last4' => substr($clean, -4)
        ];
    }

    /**
     * Encrypt database field value
     */
    public function encryptField(string $value, string $fieldName): array
    {
        return [
            'encrypted_value' => $this->encrypt($value),
            'field_name' => $fieldName,
            'encrypted_at' => date('Y-m-d H:i:s')
        ];
    }
}
