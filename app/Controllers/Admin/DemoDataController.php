<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class DemoDataController extends Controller
{
    public function index()
    {
        $this->checkPermission('settings.edit');

        // Check if demo data is already loaded
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $demoDataLoaded = Database::fetchOne(
            "SELECT setting_value FROM system_settings WHERE tenant_id = ? AND setting_key = 'demo_data_loaded'",
            [$tenantId]
        );

        $isLoaded = ($demoDataLoaded['setting_value'] ?? 'false') === 'true';

        // Get counts
        $counts = [
            'customers' => Database::fetchOne("SELECT COUNT(*) as count FROM customers WHERE tenant_id = ?", [$tenantId])['count'] ?? 0,
            'products' => Database::fetchOne("SELECT COUNT(*) as count FROM products WHERE tenant_id = ?", [$tenantId])['count'] ?? 0,
            'courses' => Database::fetchOne("SELECT COUNT(*) as count FROM courses WHERE tenant_id = ?", [$tenantId])['count'] ?? 0,
        ];

        $data = [
            'demo_data_loaded' => $isLoaded,
            'counts' => $counts,
            'page_title' => 'Demo Data Management'
        ];

        $this->view('admin/demo-data/index', $data);
    }

    public function load()
    {
        $this->checkPermission('settings.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/admin/demo-data');
            return;
        }

        try {
            $tenantId = $_SESSION['tenant_id'] ?? 1;

            // Check if already loaded
            $alreadyLoaded = Database::fetchOne(
                "SELECT setting_value FROM system_settings WHERE tenant_id = ? AND setting_key = 'demo_data_loaded'",
                [$tenantId]
            );

            if (($alreadyLoaded['setting_value'] ?? 'false') === 'true') {
                $_SESSION['flash_warning'] = 'Demo data has already been loaded!';
                $this->redirect('/store/admin/demo-data');
                return;
            }

            // Load the demo data SQL script
            $sqlFile = __DIR__ . '/../../../database/demo-data.sql';

            if (!file_exists($sqlFile)) {
                throw new \Exception('Demo data SQL file not found');
            }

            // Read and execute SQL file
            $sql = file_get_contents($sqlFile);

            // Execute the SQL
            Database::query("USE nautilus");

            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (empty($statement) || strpos($statement, '--') === 0) continue;
                Database::query($statement);
            }

            // Mark as loaded
            Database::query(
                "UPDATE system_settings SET setting_value = 'true' WHERE tenant_id = ? AND setting_key = 'demo_data_loaded'",
                [$tenantId]
            );

            $_SESSION['flash_success'] = 'Demo data loaded successfully! Check your customers, products, and courses.';

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to load demo data: ' . $e->getMessage();
        }

        $this->redirect('/store/admin/demo-data');
    }

    public function clear()
    {
        $this->checkPermission('settings.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/admin/demo-data');
            return;
        }

        try {
            $tenantId = $_SESSION['tenant_id'] ?? 1;

            // Only delete demo data (customers with IDs 1-8, products with specific demo SKUs)
            Database::query("DELETE FROM customer_tag_assignments WHERE customer_id BETWEEN 1 AND 8");
            Database::query("DELETE FROM customers WHERE id BETWEEN 1 AND 8 AND tenant_id = ?", [$tenantId]);
            Database::query("DELETE FROM products WHERE sku LIKE 'REG-%' OR sku LIKE 'BCD-%' OR sku LIKE 'WET-%' OR sku LIKE 'FIN-%' OR sku LIKE 'MSK-%' OR sku LIKE 'COM-%'");
            Database::query("DELETE FROM courses WHERE price IN (399.99, 349.99, 450.00, 850.00, 200.00)");

            // Mark as not loaded
            Database::query(
                "UPDATE system_settings SET setting_value = 'false' WHERE tenant_id = ? AND setting_key = 'demo_data_loaded'",
                [$tenantId]
            );

            $_SESSION['flash_success'] = 'Demo data cleared successfully!';

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to clear demo data: ' . $e->getMessage();
        }

        $this->redirect('/store/admin/demo-data');
    }
}
