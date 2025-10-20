<?php

namespace App\Services;

use App\Core\Database;

/**
 * File Upload Service
 * Handles secure file uploads with validation, storage, and tracking
 */
class FileUploadService
{
    private const UPLOAD_BASE_PATH = __DIR__ . '/../../public/uploads/';
    private const WEB_BASE_PATH = '/uploads/';

    /**
     * Allowed file types for different upload categories
     */
    private const ALLOWED_TYPES = [
        'logo' => ['jpg', 'jpeg', 'png', 'svg', 'webp'],
        'product_image' => ['jpg', 'jpeg', 'png', 'webp'],
        'customer_photo' => ['jpg', 'jpeg', 'png'],
        'document' => ['pdf', 'doc', 'docx'],
        'certification_card' => ['jpg', 'jpeg', 'png', 'pdf']
    ];

    /**
     * Maximum file sizes (in bytes) for different types
     */
    private const MAX_FILE_SIZES = [
        'logo' => 5242880,          // 5MB
        'product_image' => 10485760, // 10MB
        'customer_photo' => 5242880, // 5MB
        'document' => 20971520,      // 20MB
        'certification_card' => 10485760 // 10MB
    ];

    /**
     * Upload a file
     *
     * @param array $file $_FILES array element
     * @param string $type File type (logo, product_image, etc.)
     * @param string|null $customFilename Optional custom filename
     * @return array ['success' => bool, 'path' => string, 'error' => string]
     */
    public function upload(array $file, string $type = 'other', ?string $customFilename = null): array
    {
        // Validate file exists
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->getUploadErrorMessage($file['error'])];
        }

        // Validate file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = self::ALLOWED_TYPES[$type] ?? ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'error' => "Invalid file type. Allowed: " . implode(', ', $allowedExtensions)
            ];
        }

        // Validate file size
        $maxSize = self::MAX_FILE_SIZES[$type] ?? 10485760; // Default 10MB
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1048576, 1);
            return ['success' => false, 'error' => "File too large. Maximum size: {$maxSizeMB}MB"];
        }

        // Validate MIME type
        $mimeType = mime_content_type($file['tmp_name']);
        if (!$this->isValidMimeType($mimeType, $type)) {
            return ['success' => false, 'error' => 'Invalid file format'];
        }

        // Additional security: Check for valid image
        if (in_array($type, ['logo', 'product_image', 'customer_photo'])) {
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['success' => false, 'error' => 'File is not a valid image'];
            }
        }

        // Create upload directory if it doesn't exist
        $uploadDir = $this->getUploadDirectory($type);
        if (!$this->ensureDirectoryExists($uploadDir)) {
            return ['success' => false, 'error' => 'Failed to create upload directory'];
        }

        // Generate unique filename
        $filename = $customFilename ?? $this->generateUniqueFilename($file['name'], $extension);

        // Full path for saving
        $fullPath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['success' => false, 'error' => 'Failed to save file'];
        }

        // Set proper permissions
        chmod($fullPath, 0644);

        // Web-accessible path
        $webPath = $this->getWebPath($type, $filename);

        // Track upload in database
        $this->trackUpload($filename, $webPath, $file['size'], $mimeType, $type);

        return [
            'success' => true,
            'path' => $webPath,
            'filename' => $filename,
            'size' => $file['size'],
            'mime_type' => $mimeType
        ];
    }

    /**
     * Delete a file
     *
     * @param string $webPath Web path to the file
     * @return bool Success status
     */
    public function delete(string $webPath): bool
    {
        // Convert web path to filesystem path
        $fullPath = self::UPLOAD_BASE_PATH . ltrim(str_replace(self::WEB_BASE_PATH, '', $webPath), '/');

        if (file_exists($fullPath)) {
            if (unlink($fullPath)) {
                // Remove from database tracking
                Database::execute(
                    "DELETE FROM file_uploads WHERE file_path = ?",
                    [$webPath]
                );
                return true;
            }
        }

        return false;
    }

    /**
     * Get upload directory for a specific type
     *
     * @param string $type File type
     * @return string Full directory path
     */
    private function getUploadDirectory(string $type): string
    {
        $subdirs = [
            'logo' => 'logos/',
            'product_image' => 'products/',
            'customer_photo' => 'customers/',
            'certification_card' => 'certifications/',
            'document' => 'documents/'
        ];

        $subdir = $subdirs[$type] ?? 'other/';
        return self::UPLOAD_BASE_PATH . $subdir;
    }

    /**
     * Get web-accessible path
     *
     * @param string $type File type
     * @param string $filename Filename
     * @return string Web path
     */
    private function getWebPath(string $type, string $filename): string
    {
        $subdirs = [
            'logo' => 'logos/',
            'product_image' => 'products/',
            'customer_photo' => 'customers/',
            'certification_card' => 'certifications/',
            'document' => 'documents/'
        ];

        $subdir = $subdirs[$type] ?? 'other/';
        return self::WEB_BASE_PATH . $subdir . $filename;
    }

    /**
     * Ensure directory exists with proper permissions
     *
     * @param string $directory Directory path
     * @return bool Success status
     */
    private function ensureDirectoryExists(string $directory): bool
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                return false;
            }
        }

        return is_writable($directory);
    }

    /**
     * Generate unique filename
     *
     * @param string $originalName Original filename
     * @param string $extension File extension
     * @return string Unique filename
     */
    private function generateUniqueFilename(string $originalName, string $extension): string
    {
        // Sanitize original name
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
        $baseName = substr($baseName, 0, 50); // Limit length

        // Add timestamp and random string for uniqueness
        $timestamp = date('YmdHis');
        $random = bin2hex(random_bytes(4));

        return "{$baseName}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Validate MIME type
     *
     * @param string $mimeType Detected MIME type
     * @param string $fileType File type category
     * @return bool Valid status
     */
    private function isValidMimeType(string $mimeType, string $fileType): bool
    {
        $validMimeTypes = [
            'logo' => ['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
            'product_image' => ['image/jpeg', 'image/png', 'image/webp'],
            'customer_photo' => ['image/jpeg', 'image/png'],
            'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'certification_card' => ['image/jpeg', 'image/png', 'application/pdf']
        ];

        $allowed = $validMimeTypes[$fileType] ?? [];
        return in_array($mimeType, $allowed);
    }

    /**
     * Track uploaded file in database
     *
     * @param string $filename Original filename
     * @param string $path Web path
     * @param int $size File size in bytes
     * @param string $mimeType MIME type
     * @param string $type File type
     * @return void
     */
    private function trackUpload(string $filename, string $path, int $size, string $mimeType, string $type): void
    {
        $userId = currentUser()['id'] ?? null;

        try {
            Database::execute(
                "INSERT INTO file_uploads (file_name, file_path, file_size, mime_type, file_type, uploaded_by, uploaded_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$filename, $path, $size, $mimeType, $type, $userId]
            );
        } catch (\Exception $e) {
            // Log error but don't fail upload
            error_log("Failed to track upload in database: " . $e->getMessage());
        }
    }

    /**
     * Get human-readable upload error message
     *
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by PHP extension'
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Get all uploads of a specific type
     *
     * @param string $type File type
     * @param int $limit Number of results
     * @return array List of uploads
     */
    public function getUploadsByType(string $type, int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT fu.*, u.email as uploaded_by_email
             FROM file_uploads fu
             LEFT JOIN users u ON fu.uploaded_by = u.id
             WHERE fu.file_type = ?
             ORDER BY fu.uploaded_at DESC
             LIMIT ?",
            [$type, $limit]
        ) ?? [];
    }
}
