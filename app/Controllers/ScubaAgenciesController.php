<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

/**
 * Scuba Agencies Controller
 * Manages diving agency logos, certification types, and cross-agency mappings
 */
class ScubaAgenciesController extends Controller
{
    /**
     * List all diving agencies
     */
    public function index()
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();

        // Get all agencies
        $stmt = $db->query("
            SELECT * FROM diving_agency_logos 
            WHERE is_active = 1 
            ORDER BY sort_order
        ");
        $agencies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Count certifications per agency
        $stmt = $db->query("
            SELECT typical_agencies, COUNT(*) as cert_count
            FROM certification_type_master
            GROUP BY typical_agencies
        ");
        $certCounts = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->view('admin/agencies/index', [
            'pageTitle' => 'Diving Agencies',
            'agencies' => $agencies,
            'certCounts' => $certCounts
        ]);
    }

    /**
     * Certification types list
     */
    public function certifications($category = null)
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();

        $sql = "SELECT * FROM certification_type_master";
        $params = [];

        if ($category) {
            $sql .= " WHERE category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY category, level, name";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $certifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group by category
        $grouped = [];
        foreach ($certifications as $cert) {
            $grouped[$cert['category']][] = $cert;
        }

        $this->view('admin/agencies/certifications', [
            'pageTitle' => 'Certification Types',
            'certifications' => $certifications,
            'grouped' => $grouped,
            'selectedCategory' => $category
        ]);
    }

    /**
     * Get certification levels for a specific agency (JSON API)
     */
    public function agencyCertifications($agencyCode)
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT * FROM certification_type_master 
            WHERE typical_agencies LIKE ?
            ORDER BY category, level
        ");
        $stmt->execute(['%' . $agencyCode . '%']);
        $certifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'certifications' => $certifications]);
    }

    /**
     * Upload/update agency logo
     */
    public function updateLogo($agencyCode)
    {
        $this->requireAuth();
        $this->requirePermission('settings.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/admin/agencies');
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Handle file upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/png', 'image/jpeg', 'image/svg+xml'];

            if (!in_array($_FILES['logo']['type'], $allowedTypes)) {
                $_SESSION['error'] = 'Invalid file type. Please upload PNG, JPG, or SVG.';
                redirect('/store/admin/agencies');
                return;
            }

            $uploadDir = APP_ROOT . '/public/assets/images/agencies/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = strtolower($agencyCode) . '.' . $extension;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename)) {
                $logoPath = '/assets/images/agencies/' . $filename;

                $stmt = $db->prepare("
                    UPDATE diving_agency_logos 
                    SET logo_url = ?
                    WHERE code = ?
                ");
                $stmt->execute([$logoPath, $agencyCode]);

                $_SESSION['success'] = 'Logo updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to save logo file.';
            }
        }

        // Handle SVG content
        if (!empty($_POST['logo_svg'])) {
            $stmt = $db->prepare("
                UPDATE diving_agency_logos 
                SET logo_svg = ?
                WHERE code = ?
            ");
            $stmt->execute([$_POST['logo_svg'], $agencyCode]);
            $_SESSION['success'] = 'SVG logo saved.';
        }

        redirect('/store/admin/agencies');
    }

    /**
     * Add custom agency
     */
    public function store()
    {
        $this->requireAuth();
        $this->requirePermission('settings.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/admin/agencies');
            return;
        }

        $db = Database::getInstance()->getConnection();

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $isRecreational = isset($_POST['is_recreational']) ? 1 : 0;
        $isTechnical = isset($_POST['is_technical']) ? 1 : 0;

        if (empty($code) || empty($name)) {
            $_SESSION['error'] = 'Code and name are required.';
            redirect('/store/admin/agencies');
            return;
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO diving_agency_logos 
                (code, name, website, is_recreational, is_technical)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$code, $name, $website, $isRecreational, $isTechnical]);
            $_SESSION['success'] = 'Agency added successfully.';
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['error'] = 'Agency code already exists.';
            } else {
                $_SESSION['error'] = 'Failed to add agency.';
            }
        }

        redirect('/store/admin/agencies');
    }

    /**
     * Add custom certification type
     */
    public function storeCertification()
    {
        $this->requireAuth();
        $this->requirePermission('settings.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/admin/agencies/certifications');
            return;
        }

        $db = Database::getInstance()->getConnection();

        $code = strtoupper(str_replace(' ', '_', trim($_POST['code'] ?? '')));
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'specialty';
        $level = intval($_POST['level'] ?? 1);
        $minAge = intval($_POST['min_age'] ?? 10);
        $minDives = intval($_POST['min_dives'] ?? 0);
        $typicalAgencies = trim($_POST['typical_agencies'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($code) || empty($name)) {
            $_SESSION['error'] = 'Code and name are required.';
            redirect('/store/admin/agencies/certifications');
            return;
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO certification_type_master 
                (code, name, category, level, min_age, min_dives, typical_agencies, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$code, $name, $category, $level, $minAge, $minDives, $typicalAgencies, $description]);
            $_SESSION['success'] = 'Certification type added successfully.';
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['error'] = 'Certification code already exists.';
            } else {
                $_SESSION['error'] = 'Failed to add certification type.';
            }
        }

        redirect('/store/admin/agencies/certifications');
    }

    /**
     * Get agency logo for display
     */
    public function logo($agencyCode)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT logo_url, logo_svg 
            FROM diving_agency_logos 
            WHERE code = ?
        ");
        $stmt->execute([$agencyCode]);
        $agency = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$agency) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        // Return SVG if available
        if (!empty($agency['logo_svg'])) {
            header('Content-Type: image/svg+xml');
            echo $agency['logo_svg'];
            exit;
        }

        // Redirect to file if exists
        if (!empty($agency['logo_url'])) {
            header('Location: ' . $agency['logo_url']);
            exit;
        }

        // Return placeholder
        header('Content-Type: image/svg+xml');
        echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect fill="#ddd" width="100" height="100"/><text x="50" y="55" text-anchor="middle" fill="#666" font-size="14">' . htmlspecialchars($agencyCode) . '</text></svg>';
    }
}
