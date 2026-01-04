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

        // Try system_settings first, fall back to settings table
        $isLoaded = false;
        try {
            $demoDataLoaded = Database::fetchOne(
                "SELECT setting_value FROM system_settings WHERE setting_key = 'demo_data_loaded' LIMIT 1"
            );
            $isLoaded = ($demoDataLoaded['setting_value'] ?? 'false') === 'true';
        } catch (\Exception $e) {
            // Try settings table instead
            try {
                $demoDataLoaded = Database::fetchOne(
                    "SELECT setting_value FROM settings WHERE setting_key = 'demo_data_loaded' LIMIT 1"
                );
                $isLoaded = ($demoDataLoaded['setting_value'] ?? 'false') === 'true';
            } catch (\Exception $e2) {
                $isLoaded = false;
            }
        }

        // Get counts - handle missing tenant_id column gracefully
        $counts = [
            'customers' => 0,
            'products' => 0,
            'courses' => 0,
        ];

        try {
            $counts['customers'] = Database::fetchOne("SELECT COUNT(*) as count FROM customers")['count'] ?? 0;
            $counts['products'] = Database::fetchOne("SELECT COUNT(*) as count FROM products")['count'] ?? 0;
            $counts['courses'] = Database::fetchOne("SELECT COUNT(*) as count FROM courses")['count'] ?? 0;
        } catch (\Exception $e) {
            // Tables might not exist yet
        }

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

            // Load the demo data SQL script
            $sqlFile = __DIR__ . '/../../../database/seeds/002_seed_demo_data.sql';

            if (!file_exists($sqlFile)) {
                // Try alternate location
                $sqlFile = __DIR__ . '/../../../database/demo-data.sql';
                if (!file_exists($sqlFile)) {
                    throw new \Exception('Demo data SQL file not found');
                }
            }

            // Read and execute SQL file
            $sql = file_get_contents($sqlFile);

            // Remove comments
            $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (empty($statement)) continue;
                try {
                    Database::query($statement);
                } catch (\Exception $e) {
                    // Continue on errors (duplicates, etc.)
                }
            }

            // Mark as loaded in system_settings
            try {
                Database::query(
                    "INSERT INTO system_settings (setting_key, setting_value) VALUES ('demo_data_loaded', 'true') ON DUPLICATE KEY UPDATE setting_value = 'true'"
                );
            } catch (\Exception $e) {
                // Try settings table instead
                Database::query(
                    "INSERT INTO settings (setting_key, setting_value, setting_group) VALUES ('demo_data_loaded', 'true', 'system') ON DUPLICATE KEY UPDATE setting_value = 'true'"
                );
            }

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
            try { Database::query("DELETE FROM customer_tag_assignments WHERE customer_id BETWEEN 1 AND 8"); } catch (\Exception $e) {}
            try { Database::query("DELETE FROM customers WHERE id BETWEEN 1 AND 8"); } catch (\Exception $e) {}
            try { Database::query("DELETE FROM products WHERE sku LIKE 'REG-%' OR sku LIKE 'BCD-%' OR sku LIKE 'WET-%' OR sku LIKE 'FIN-%' OR sku LIKE 'MSK-%' OR sku LIKE 'COM-%'"); } catch (\Exception $e) {}
            try { Database::query("DELETE FROM courses WHERE price IN (399.99, 349.99, 450.00, 850.00, 200.00)"); } catch (\Exception $e) {}

            // Mark as not loaded
            try {
                Database::query(
                    "UPDATE system_settings SET setting_value = 'false' WHERE setting_key = 'demo_data_loaded'"
                );
            } catch (\Exception $e) {
                Database::query(
                    "UPDATE settings SET setting_value = 'false' WHERE setting_key = 'demo_data_loaded'"
                );
            }

            $_SESSION['flash_success'] = 'Demo data cleared successfully!';

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to clear demo data: ' . $e->getMessage();
        }

        $this->redirect('/store/admin/demo-data');
    }
}
