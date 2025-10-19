<?php

namespace App\Controllers\Install;

use App\Services\Install\InstallService;

class InstallController
{
    private InstallService $installService;

    public function __construct()
    {
        $this->installService = new InstallService();
    }

    /**
     * Show installation welcome page
     */
    public function index()
    {
        // Check if already installed
        if ($this->installService->isInstalled()) {
            $_SESSION['flash_error'] = 'Application is already installed';
            redirect('/login');
        }

        require __DIR__ . '/../../Views/install/welcome.php';
    }

    /**
     * Test database connection
     */
    public function testDatabase()
    {
        header('Content-Type: application/json');

        $host = $_POST['db_host'] ?? '';
        $port = $_POST['db_port'] ?? '';
        $database = $_POST['db_database'] ?? '';
        $username = $_POST['db_username'] ?? '';
        $password = $_POST['db_password'] ?? '';

        $result = $this->installService->testDatabaseConnection(
            $host,
            $port,
            $database,
            $username,
            $password
        );

        echo json_encode($result);
        exit;
    }

    /**
     * Show configuration step
     */
    public function configure()
    {
        if ($this->installService->isInstalled()) {
            $_SESSION['flash_error'] = 'Application is already installed';
            redirect('/login');
        }

        require __DIR__ . '/../../Views/install/configure.php';
    }

    /**
     * Process installation
     */
    public function install()
    {
        if ($this->installService->isInstalled()) {
            echo json_encode([
                'success' => false,
                'message' => 'Application is already installed'
            ]);
            exit;
        }

        header('Content-Type: application/json');

        // Validate input
        $config = [
            'app_name' => sanitizeInput($_POST['app_name'] ?? 'Nautilus'),
            'app_url' => sanitizeInput($_POST['app_url'] ?? ''),
            'app_timezone' => sanitizeInput($_POST['app_timezone'] ?? 'America/Chicago'),
            'db_host' => sanitizeInput($_POST['db_host'] ?? 'localhost'),
            'db_port' => sanitizeInput($_POST['db_port'] ?? '3306'),
            'db_database' => sanitizeInput($_POST['db_database'] ?? ''),
            'db_username' => sanitizeInput($_POST['db_username'] ?? ''),
            'db_password' => $_POST['db_password'] ?? '',
            'admin_email' => sanitizeInput($_POST['admin_email'] ?? ''),
            'admin_password' => $_POST['admin_password'] ?? '',
            'admin_first_name' => sanitizeInput($_POST['admin_first_name'] ?? ''),
            'admin_last_name' => sanitizeInput($_POST['admin_last_name'] ?? ''),
            'install_demo_data' => isset($_POST['install_demo_data']) && $_POST['install_demo_data'] === 'true',
        ];

        // Validate required fields
        $errors = [];

        if (empty($config['app_url'])) {
            $errors[] = 'Application URL is required';
        }

        if (empty($config['db_database'])) {
            $errors[] = 'Database name is required';
        }

        if (empty($config['db_username'])) {
            $errors[] = 'Database username is required';
        }

        if (empty($config['admin_email']) || !filter_var($config['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid admin email is required';
        }

        if (empty($config['admin_password']) || strlen($config['admin_password']) < 8) {
            $errors[] = 'Admin password must be at least 8 characters';
        }

        if (empty($config['admin_first_name'])) {
            $errors[] = 'Admin first name is required';
        }

        if (empty($config['admin_last_name'])) {
            $errors[] = 'Admin last name is required';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors)
            ]);
            exit;
        }

        // Run installation
        $result = $this->installService->runInstallation($config);

        echo json_encode($result);
        exit;
    }

    /**
     * Show installation complete page
     */
    public function complete()
    {
        if (!$this->installService->isInstalled()) {
            redirect('/install');
        }

        require __DIR__ . '/../../Views/install/complete.php';
    }

    /**
     * Get installation progress (for AJAX polling)
     */
    public function progress()
    {
        header('Content-Type: application/json');

        $progress = $this->installService->getProgress();
        echo json_encode($progress);
        exit;
    }
}
