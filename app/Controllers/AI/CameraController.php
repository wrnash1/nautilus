<?php

namespace App\Controllers\AI;

use App\Core\Controller;
use App\Core\Database;

/**
 * AI Camera Controller
 * Handles product recognition, cert card OCR, and serial number scanning
 */
class CameraController extends Controller
{
    /**
     * Camera interface for product scanning
     */
    public function index()
    {
        $this->requireAuth();

        $this->view('ai/camera', [
            'pageTitle' => 'AI Camera Scanner'
        ]);
    }

    /**
     * Process captured image for product recognition
     */
    public function recognizeProduct()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'POST required']);
            return;
        }

        $imageData = $_POST['image'] ?? '';

        if (empty($imageData)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No image provided']);
            return;
        }

        // Decode base64 image
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = base64_decode($imageData);

        // Save temp file for processing
        $tempFile = tempnam(sys_get_temp_dir(), 'nautilus_cam_');
        file_put_contents($tempFile, $imageData);

        // Try barcode first (most reliable)
        $barcode = $this->detectBarcode($tempFile);

        if ($barcode) {
            $product = $this->lookupByBarcode($barcode);
            unlink($tempFile);

            if ($product) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'method' => 'barcode',
                    'barcode' => $barcode,
                    'product' => $product
                ]);
                return;
            }
        }

        // Fall back to AI visual recognition
        $result = $this->aiRecognition($tempFile);
        unlink($tempFile);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * OCR certification card
     */
    public function scanCertCard()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'POST required']);
            return;
        }

        $imageData = $_POST['image'] ?? '';

        if (empty($imageData)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No image provided']);
            return;
        }

        // Decode and save
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $imageData = base64_decode($imageData);
        $tempFile = tempnam(sys_get_temp_dir(), 'nautilus_cert_');
        file_put_contents($tempFile, $imageData);

        // Extract text using Tesseract OCR (if available) or Google Vision API
        $extractedData = $this->ocrCertCard($tempFile);
        unlink($tempFile);

        header('Content-Type: application/json');
        echo json_encode($extractedData);
    }

    /**
     * Scan serial number from equipment
     */
    public function scanSerialNumber()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'POST required']);
            return;
        }

        $imageData = $_POST['image'] ?? '';

        if (empty($imageData)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No image provided']);
            return;
        }

        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $imageData = base64_decode($imageData);
        $tempFile = tempnam(sys_get_temp_dir(), 'nautilus_serial_');
        file_put_contents($tempFile, $imageData);

        $result = $this->ocrSerialNumber($tempFile);
        unlink($tempFile);

        // If serial found, look up in database
        if ($result['success'] && !empty($result['serial_number'])) {
            $equipment = $this->lookupSerialNumber($result['serial_number']);
            $result['equipment'] = $equipment;
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Export training data for community AI model
     */
    public function exportTrainingData()
    {
        $this->requireAuth();
        $this->requirePermission('settings.edit');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Get anonymized product data
        $stmt = $db->prepare("
            SELECT 
                p.name, p.sku, p.barcode, c.name as category,
                p.description, p.brand
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.tenant_id = ?
        ");
        $stmt->execute([$tenantId]);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get course structures
        $stmt = $db->prepare("
            SELECT name, description, duration_hours, category
            FROM courses
            WHERE tenant_id = ?
        ");
        $stmt->execute([$tenantId]);
        $courses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get certification types
        $stmt = $db->prepare("
            SELECT name, agency, level
            FROM certification_types
            WHERE tenant_id = ?
        ");
        $stmt->execute([$tenantId]);
        $certTypes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $trainingData = [
            'exported_at' => date('Y-m-d H:i:s'),
            'tenant_hash' => md5($tenantId . $_SERVER['HTTP_HOST']), // Anonymous ID
            'products' => $products,
            'courses' => $courses,
            'certification_types' => $certTypes,
            'product_count' => count($products),
            'course_count' => count($courses)
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="nautilus_training_data_' . date('Y-m') . '.json"');
        echo json_encode($trainingData, JSON_PRETTY_PRINT);
    }

    /**
     * Detect barcode in image
     */
    private function detectBarcode($imagePath)
    {
        // Try using zbarimg command line tool if available
        $output = [];
        $returnCode = 0;
        exec("zbarimg -q --raw " . escapeshellarg($imagePath) . " 2>/dev/null", $output, $returnCode);

        if ($returnCode === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        return null;
    }

    /**
     * Lookup product by barcode
     */
    private function lookupByBarcode($barcode)
    {
        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT id, name, sku, price, barcode, 
                   (SELECT name FROM categories WHERE id = products.category_id) as category
            FROM products 
            WHERE tenant_id = ? AND (barcode = ? OR sku = ?)
            LIMIT 1
        ");
        $stmt->execute([$tenantId, $barcode, $barcode]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * AI visual product recognition
     */
    private function aiRecognition($imagePath)
    {
        // Check for Google Cloud Vision API key
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT setting_value FROM settings 
            WHERE tenant_id = ? AND setting_key = 'google_vision_api_key'
        ");
        $stmt->execute([$tenantId]);
        $apiKey = $stmt->fetchColumn();

        if (empty($apiKey)) {
            return [
                'success' => false,
                'method' => 'ai',
                'error' => 'AI vision not configured. Please set up Google Cloud Vision API key.'
            ];
        }

        // Read image and encode
        $imageContent = base64_encode(file_get_contents($imagePath));

        // Call Google Cloud Vision API
        $requestData = [
            'requests' => [
                [
                    'image' => ['content' => $imageContent],
                    'features' => [
                        ['type' => 'LABEL_DETECTION', 'maxResults' => 10],
                        ['type' => 'TEXT_DETECTION', 'maxResults' => 5],
                        ['type' => 'LOGO_DETECTION', 'maxResults' => 5]
                    ]
                ]
            ]
        ];

        $ch = curl_init("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Vision API request failed'];
        }

        $visionResult = json_decode($response, true);

        // Extract labels and text
        $labels = [];
        $text = '';
        $logos = [];

        if (isset($visionResult['responses'][0]['labelAnnotations'])) {
            foreach ($visionResult['responses'][0]['labelAnnotations'] as $label) {
                $labels[] = [
                    'description' => $label['description'],
                    'score' => $label['score']
                ];
            }
        }

        if (isset($visionResult['responses'][0]['textAnnotations'][0])) {
            $text = $visionResult['responses'][0]['textAnnotations'][0]['description'];
        }

        if (isset($visionResult['responses'][0]['logoAnnotations'])) {
            foreach ($visionResult['responses'][0]['logoAnnotations'] as $logo) {
                $logos[] = $logo['description'];
            }
        }

        // Try to match with products in database
        $product = $this->matchProductByAI($labels, $text, $logos);

        return [
            'success' => true,
            'method' => 'ai',
            'labels' => $labels,
            'text' => $text,
            'logos' => $logos,
            'product' => $product
        ];
    }

    /**
     * Match product based on AI recognition
     */
    private function matchProductByAI($labels, $text, $logos)
    {
        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Try matching by brand (logos)
        foreach ($logos as $logo) {
            $stmt = $db->prepare("
                SELECT id, name, sku, price 
                FROM products 
                WHERE tenant_id = ? AND brand LIKE ?
                LIMIT 5
            ");
            $stmt->execute([$tenantId, '%' . $logo . '%']);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($products)) {
                return ['matches' => $products, 'matched_by' => 'brand'];
            }
        }

        // Try matching by text in product name
        $words = str_word_count($text, 1);
        foreach ($words as $word) {
            if (strlen($word) < 4)
                continue;

            $stmt = $db->prepare("
                SELECT id, name, sku, price 
                FROM products 
                WHERE tenant_id = ? AND (name LIKE ? OR description LIKE ?)
                LIMIT 5
            ");
            $pattern = '%' . $word . '%';
            $stmt->execute([$tenantId, $pattern, $pattern]);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($products)) {
                return ['matches' => $products, 'matched_by' => 'text'];
            }
        }

        return null;
    }

    /**
     * OCR certification card
     */
    private function ocrCertCard($imagePath)
    {
        // Try Tesseract first
        $output = [];
        $returnCode = 0;
        exec("tesseract " . escapeshellarg($imagePath) . " stdout 2>/dev/null", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            $text = implode("\n", $output);

            // Parse common cert card fields
            return [
                'success' => true,
                'raw_text' => $text,
                'parsed' => $this->parseCertCardText($text)
            ];
        }

        return [
            'success' => false,
            'error' => 'OCR not available. Install tesseract-ocr.'
        ];
    }

    /**
     * Parse cert card text for common fields
     */
    private function parseCertCardText($text)
    {
        $result = [
            'name' => null,
            'cert_number' => null,
            'agency' => null,
            'cert_date' => null,
            'cert_level' => null
        ];

        // Look for agency
        $agencies = ['PADI', 'SSI', 'NAUI', 'SDI', 'TDI', 'IANTD', 'GUE', 'CMAS', 'BSAC'];
        foreach ($agencies as $agency) {
            if (stripos($text, $agency) !== false) {
                $result['agency'] = $agency;
                break;
            }
        }

        // Look for cert number patterns
        if (preg_match('/\b(\d{6,12})\b/', $text, $matches)) {
            $result['cert_number'] = $matches[1];
        }

        // Look for dates
        if (preg_match('/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/', $text, $matches)) {
            $result['cert_date'] = $matches[1];
        }

        // Look for cert levels
        $levels = ['Open Water', 'Advanced', 'Rescue', 'Divemaster', 'Instructor', 'Nitrox', 'Deep'];
        foreach ($levels as $level) {
            if (stripos($text, $level) !== false) {
                $result['cert_level'] = $level;
                break;
            }
        }

        return $result;
    }

    /**
     * OCR serial number
     */
    private function ocrSerialNumber($imagePath)
    {
        $output = [];
        $returnCode = 0;
        exec("tesseract " . escapeshellarg($imagePath) . " stdout 2>/dev/null", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            $text = implode(' ', $output);

            // Serial numbers are usually alphanumeric, 6-20 chars
            if (preg_match('/\b([A-Z0-9]{6,20})\b/i', $text, $matches)) {
                return [
                    'success' => true,
                    'serial_number' => strtoupper($matches[1]),
                    'raw_text' => $text
                ];
            }
        }

        return ['success' => false, 'error' => 'Could not read serial number'];
    }

    /**
     * Lookup serial number in database
     */
    private function lookupSerialNumber($serialNumber)
    {
        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Check rental equipment
        $stmt = $db->prepare("
            SELECT id, name, equipment_code, status, 'rental' as type
            FROM rental_equipment 
            WHERE tenant_id = ? AND serial_number = ?
        ");
        $stmt->execute([$tenantId, $serialNumber]);
        $equipment = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($equipment) {
            return $equipment;
        }

        // Check customer equipment
        $stmt = $db->prepare("
            SELECT ce.*, c.first_name, c.last_name
            FROM customer_equipment ce
            JOIN customers c ON ce.customer_id = c.id
            WHERE c.tenant_id = ? AND ce.serial_number = ?
        ");
        $stmt->execute([$tenantId, $serialNumber]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
