<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class CompanySettingsController extends Controller
{
    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('admin.settings');

        $tenantId = $_SESSION['tenant_id'];

        // Get company settings
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM company_settings
            WHERE tenant_id = ?
            LIMIT 1
        ");
        $stmt->execute([$tenantId]);
        $settings = $stmt->fetch(\PDO::FETCH_ASSOC);

        // If no settings exist, create default
        if (!$settings) {
            $stmt = $db->prepare("
                INSERT INTO company_settings (tenant_id, created_at)
                VALUES (?, NOW())
            ");
            $stmt->execute([$tenantId]);

            $stmt = $db->prepare("
                SELECT * FROM company_settings
                WHERE tenant_id = ?
                LIMIT 1
            ");
            $stmt->execute([$tenantId]);
            $settings = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $this->view('admin/settings/company', [
            'settings' => $settings,
            'pageTitle' => 'Company Information'
        ]);
    }

    public function update()
    {
        $this->requireAuth();
        $this->requirePermission('admin.settings');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/admin/settings/company');
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        try {
            // Handle logo upload
            $logoUrl = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../public/uploads/logos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $filename = 'logo_' . $tenantId . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                    $logoUrl = '/uploads/logos/' . $filename;
                }
            }

            // Update company settings
            $stmt = $db->prepare("
                UPDATE company_settings SET
                    company_name = ?,
                    legal_name = ?,
                    tax_id = ?,
                    address_line1 = ?,
                    address_line2 = ?,
                    city = ?,
                    state = ?,
                    postal_code = ?,
                    country = ?,
                    phone = ?,
                    fax = ?,
                    email = ?,
                    website = ?,
                    logo_url = COALESCE(?, logo_url),
                    business_hours = ?,
                    timezone = ?,
                    currency = ?,
                    updated_at = NOW()
                WHERE tenant_id = ?
            ");

            $stmt->execute([
                $_POST['company_name'] ?? '',
                $_POST['legal_name'] ?? '',
                $_POST['tax_id'] ?? '',
                $_POST['address_line1'] ?? '',
                $_POST['address_line2'] ?? '',
                $_POST['city'] ?? '',
                $_POST['state'] ?? '',
                $_POST['postal_code'] ?? '',
                $_POST['country'] ?? 'US',
                $_POST['phone'] ?? '',
                $_POST['fax'] ?? '',
                $_POST['email'] ?? '',
                $_POST['website'] ?? '',
                $logoUrl,
                $_POST['business_hours'] ?? '',
                $_POST['timezone'] ?? 'America/New_York',
                $_POST['currency'] ?? 'USD',
                $tenantId
            ]);

            $_SESSION['success'] = 'Company settings updated successfully!';
            redirect('/store/admin/settings/company');

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to update settings: ' . $e->getMessage();
            redirect('/store/admin/settings/company');
        }
    }

    public function getSettings()
    {
        $this->requireAuth();

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT * FROM company_settings
            WHERE tenant_id = ?
            LIMIT 1
        ");
        $stmt->execute([$tenantId]);
        $settings = $stmt->fetch(\PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($settings ?: []);
    }
}
