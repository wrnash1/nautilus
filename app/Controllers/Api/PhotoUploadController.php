<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;

/**
 * Photo Upload API Controller
 * Handles photo uploads from camera capture component
 */
class PhotoUploadController extends Controller
{
    private string $uploadDir = 'public/uploads/photos/';
    private array $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    private int $maxFileSize = 5242880; // 5MB

    public function __construct()
    {
        parent::__construct();

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }

    /**
     * Upload customer photo
     */
    public function uploadCustomerPhoto(int $customerId): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }

        try {
            // Verify customer exists
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, photo_path FROM customers WHERE id = ?");
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$customer) {
                $this->jsonResponse(['error' => 'Customer not found'], 404);
                return;
            }

            // Process upload
            $photoPath = $this->processPhotoUpload('photo', 'customer_' . $customerId);

            if (!$photoPath) {
                $this->jsonResponse(['error' => 'Failed to process photo'], 500);
                return;
            }

            // Delete old photo if exists
            if (!empty($customer['photo_path']) && file_exists($customer['photo_path'])) {
                unlink($customer['photo_path']);
            }

            // Update customer record
            $stmt = $db->prepare("UPDATE customers SET photo_path = ? WHERE id = ?");
            $stmt->execute([$photoPath, $customerId]);

            $this->jsonResponse([
                'success' => true,
                'photo_path' => '/' . $photoPath,
                'message' => 'Photo uploaded successfully'
            ]);

        } catch (\Exception $e) {
            $this->logError('Photo upload error: ' . $e->getMessage());
            $this->jsonResponse(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete customer photo
     */
    public function deleteCustomerPhoto(int $customerId): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, photo_path FROM customers WHERE id = ?");
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$customer) {
                $this->jsonResponse(['error' => 'Customer not found'], 404);
                return;
            }

            // Delete photo file
            if (!empty($customer['photo_path']) && file_exists($customer['photo_path'])) {
                unlink($customer['photo_path']);
            }

            // Update customer record
            $stmt = $db->prepare("UPDATE customers SET photo_path = NULL WHERE id = ?");
            $stmt->execute([$customerId]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Photo deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->logError('Photo delete error: ' . $e->getMessage());
            $this->jsonResponse(['error' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload incident photo
     */
    public function uploadIncidentPhoto(int $incidentId): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }

        try {
            // Verify incident exists
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, photos FROM incident_reports WHERE id = ?");
            $stmt->execute([$incidentId]);
            $incident = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$incident) {
                $this->jsonResponse(['error' => 'Incident not found'], 404);
                return;
            }

            // Process upload
            $photoPath = $this->processPhotoUpload('photo', 'incident_' . $incidentId . '_' . time());

            if (!$photoPath) {
                $this->jsonResponse(['error' => 'Failed to process photo'], 500);
                return;
            }

            // Add to photos JSON array
            $photos = json_decode($incident['photos'] ?? '[]', true);
            $photos[] = $photoPath;

            // Update incident record
            $stmt = $db->prepare("UPDATE incident_reports SET photos = ? WHERE id = ?");
            $stmt->execute([json_encode($photos), $incidentId]);

            $this->jsonResponse([
                'success' => true,
                'photo_path' => '/' . $photoPath,
                'message' => 'Photo uploaded successfully'
            ]);

        } catch (\Exception $e) {
            $this->logError('Incident photo upload error: ' . $e->getMessage());
            $this->jsonResponse(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generic photo upload (for equipment, etc.)
     */
    public function uploadGenericPhoto(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }

        try {
            $category = $_POST['category'] ?? 'general';
            $identifier = $_POST['identifier'] ?? time();

            $photoPath = $this->processPhotoUpload('photo', $category . '_' . $identifier);

            if (!$photoPath) {
                $this->jsonResponse(['error' => 'Failed to process photo'], 500);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'photo_path' => '/' . $photoPath,
                'message' => 'Photo uploaded successfully'
            ]);

        } catch (\Exception $e) {
            $this->logError('Generic photo upload error: ' . $e->getMessage());
            $this->jsonResponse(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process photo upload
     */
    private function processPhotoUpload(string $fieldName, string $prefix): ?string
    {
        // Check if file was uploaded
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$fieldName];

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new \Exception('Invalid file type. Only JPEG, PNG, and WebP images allowed.');
        }

        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception('File too large. Maximum size is 5MB.');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . '_' . uniqid() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new \Exception('Failed to save file');
        }

        // Optional: Resize/optimize image
        $this->optimizeImage($filepath, $mimeType);

        return $filepath;
    }

    /**
     * Optimize uploaded image
     */
    private function optimizeImage(string $filepath, string $mimeType): void
    {
        $maxWidth = 1200;
        $maxHeight = 1200;
        $quality = 85;

        // Load image based on type
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filepath);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($filepath);
                break;
            default:
                return;
        }

        if (!$image) {
            return;
        }

        // Get original dimensions
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        // Calculate new dimensions
        if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
            $newWidth = (int)($origWidth * $ratio);
            $newHeight = (int)($origHeight * $ratio);

            // Create new image
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG
            if ($mimeType === 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }

            // Resize
            imagecopyresampled(
                $newImage, $image,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $origWidth, $origHeight
            );

            // Save optimized image
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    imagejpeg($newImage, $filepath, $quality);
                    break;
                case 'image/png':
                    imagepng($newImage, $filepath, 9);
                    break;
                case 'image/webp':
                    imagewebp($newImage, $filepath, $quality);
                    break;
            }

            imagedestroy($newImage);
        }

        imagedestroy($image);
    }

    /**
     * Log error
     */
    private function logError(string $message): void
    {
        error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, 3, 'storage/logs/photo_upload.log');
    }
}
