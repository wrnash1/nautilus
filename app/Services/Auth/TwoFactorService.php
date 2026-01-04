<?php

namespace App\Services\Auth;

use App\Core\Database;
use PDO;
use App\Core\Logger;

/**
 * Two-Factor Authentication Service
 * Implements TOTP-based 2FA using Google Authenticator compatible format
 */
class TwoFactorService
{
    private PDO $db;
    private Logger $logger;
    private int $codeLength = 6;
    private int $period = 30; // seconds
    private int $window = 1; // Allow 1 period before/after for clock drift

    public function __construct()
    {
        $this->db = Database::getPdo();
        $this->logger = new Logger();
    }

    /**
     * Generate a secret key for 2FA
     */
    public function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $secret;
    }

    /**
     * Enable 2FA for a user
     */
    public function enable(int $userId, string $secret): bool
    {
        try {
            // Generate backup codes
            $backupCodes = $this->generateBackupCodes();

            $sql = "INSERT INTO user_two_factor (user_id, secret, backup_codes, enabled, created_at)
                    VALUES (?, ?, ?, 1, NOW())
                    ON DUPLICATE KEY UPDATE
                        secret = VALUES(secret),
                        backup_codes = VALUES(backup_codes),
                        enabled = 1,
                        updated_at = NOW()";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $this->encrypt($secret),
                $this->encrypt(json_encode($backupCodes))
            ]);

            $this->logger->info('2FA enabled for user', ['user_id' => $userId]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to enable 2FA', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Disable 2FA for a user
     */
    public function disable(int $userId): bool
    {
        try {
            $sql = "UPDATE user_two_factor SET enabled = 0, updated_at = NOW() WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            $this->logger->info('2FA disabled for user', ['user_id' => $userId]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to disable 2FA', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if 2FA is enabled for user
     */
    public function isEnabled(int $userId): bool
    {
        $sql = "SELECT enabled FROM user_two_factor WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result && $result['enabled'] == 1;
    }

    /**
     * Verify 2FA code
     */
    public function verify(int $userId, string $code): bool
    {
        try {
            // Get user's secret
            $sql = "SELECT secret, backup_codes FROM user_two_factor WHERE user_id = ? AND enabled = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            $twoFactor = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$twoFactor) {
                return false;
            }

            $secret = $this->decrypt($twoFactor['secret']);

            // Verify TOTP code
            if ($this->verifyTOTP($secret, $code)) {
                $this->logVerificationAttempt($userId, true);
                return true;
            }

            // Check if it's a backup code
            $backupCodes = json_decode($this->decrypt($twoFactor['backup_codes']), true);

            if (in_array($code, $backupCodes)) {
                // Remove used backup code
                $backupCodes = array_diff($backupCodes, [$code]);
                $this->updateBackupCodes($userId, $backupCodes);

                $this->logVerificationAttempt($userId, true, 'backup_code');
                return true;
            }

            $this->logVerificationAttempt($userId, false);
            return false;

        } catch (\Exception $e) {
            $this->logger->error('2FA verification failed', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verify TOTP code
     */
    private function verifyTOTP(string $secret, string $code): bool
    {
        $currentTime = time();

        // Check current period and adjacent periods (for clock drift)
        for ($i = -$this->window; $i <= $this->window; $i++) {
            $timestamp = $currentTime + ($i * $this->period);
            $generatedCode = $this->generateTOTP($secret, $timestamp);

            if (hash_equals($generatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate TOTP code
     */
    private function generateTOTP(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeCounter = floor($timestamp / $this->period);

        // Decode base32 secret
        $secret = $this->base32Decode($secret);

        // Pack time counter
        $time = pack('N*', 0, $timeCounter);

        // Generate HMAC hash
        $hash = hash_hmac('sha1', $time, $secret, true);

        // Dynamic truncation
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, $this->codeLength);

        return str_pad((string)$code, $this->codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Get QR code URL for Google Authenticator
     */
    public function getQRCodeUrl(string $username, string $secret, ?string $issuer = null): string
    {
        $issuer = $issuer ?? ($_ENV['APP_NAME'] ?? 'Nautilus');
        $label = urlencode($issuer . ':' . $username);

        $params = [
            'secret' => $secret,
            'issuer' => urlencode($issuer),
            'algorithm' => 'SHA1',
            'digits' => $this->codeLength,
            'period' => $this->period
        ];

        $query = http_build_query($params);
        $otpauthUrl = "otpauth://totp/{$label}?{$query}";

        // Use Google Charts API for QR code
        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($otpauthUrl);
    }

    /**
     * Generate backup codes
     */
    private function generateBackupCodes(int $count = 10): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }

        return $codes;
    }

    /**
     * Get backup codes for user
     */
    public function getBackupCodes(int $userId): array
    {
        $sql = "SELECT backup_codes FROM user_two_factor WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return [];
        }

        return json_decode($this->decrypt($result['backup_codes']), true) ?? [];
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(int $userId): array
    {
        $newCodes = $this->generateBackupCodes();

        $sql = "UPDATE user_two_factor SET backup_codes = ?, updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $this->encrypt(json_encode($newCodes)),
            $userId
        ]);

        return $newCodes;
    }

    /**
     * Update backup codes
     */
    private function updateBackupCodes(int $userId, array $codes): void
    {
        $sql = "UPDATE user_two_factor SET backup_codes = ?, updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $this->encrypt(json_encode($codes)),
            $userId
        ]);
    }

    /**
     * Log verification attempt
     */
    private function logVerificationAttempt(int $userId, bool $success, string $method = 'totp'): void
    {
        $sql = "INSERT INTO two_factor_logs (user_id, success, method, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $success ? 1 : 0,
            $method,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    /**
     * Base32 decode
     */
    private function base32Decode(string $secret): string
    {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));

        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = [6, 4, 3, 1, 0];

        if (!in_array($paddingCharCount, $allowedValues)) {
            return '';
        }

        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])) {
                return '';
            }
        }

        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';

        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) {
                return '';
            }

            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }

            $eightBits = str_split($x, 8);

            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }

    /**
     * Encrypt sensitive data
     */
    private function encrypt(string $data): string
    {
        $key = $_ENV['ENCRYPTION_KEY'] ?? 'default-key-change-this';
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt sensitive data
     */
    private function decrypt(string $data): string
    {
        $key = $_ENV['ENCRYPTION_KEY'] ?? 'default-key-change-this';
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}
