<?php

namespace App\Controllers\Install;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class DeploymentController extends Controller
{
    private $output = [];
    private $errors = [];

    public function run()
    {
        // Simple security check
        if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
            if (!isset($_GET['force'])) {
                die('Access denied. Add ?force=1 to the URL to run from remote.');
            }
        }

        $this->addOutput("Database Deployment Started", 'info');

        try {
            $db = Database::getInstance();
            $this->addOutput("✓ Database connection established", 'success');

            // Run migrations
            $this->runMigrations($db);

            // Run seeders
            $this->runSeeders($db);

            // Get statistics
            $stats = $this->getStatistics($db);

            // Render output
            $this->render($stats);

        } catch (\Exception $e) {
            $this->addError("Fatal error: " . $e->getMessage());
            $this->render([]);
        }
    }

    private function runMigrations($db)
    {
        $this->addOutput("=== Running Migrations ===", 'info');

        $migrations = [
            '039_create_customer_enhanced_tables.sql',
            '040_create_cash_drawer_system.sql',
            '041_add_customer_certifications.sql'
        ];

        $migrationDir = BASE_PATH . '/database/migrations';
        $migrationsRun = 0;
        $migrationsSkipped = 0;

        foreach ($migrations as $migrationFile) {
            $this->addOutput("Checking migration: {$migrationFile}", 'info');

            // Check if already run
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM migrations WHERE filename = ? AND status = 'completed'");
            $stmt->execute([$migrationFile]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                $this->addOutput("✓ Already completed - skipping", 'success');
                $migrationsSkipped++;
                continue;
            }

            // Find the migration file
            $files = glob($migrationDir . '/*' . $migrationFile);

            if (empty($files)) {
                $this->addError("✗ Migration file not found: {$migrationFile}");
                continue;
            }

            $fullPath = $files[0];

            try {
                // Read and execute SQL file
                $sql = file_get_contents($fullPath);
                $db->exec($sql);

                // Record migration
                $stmt = $db->prepare("
                    INSERT INTO migrations (filename, status, executed_at)
                    VALUES (?, 'completed', NOW())
                    ON DUPLICATE KEY UPDATE status = 'completed', executed_at = NOW()
                ");
                $stmt->execute([$migrationFile]);

                $this->addOutput("✓ Migration completed successfully!", 'success');
                $migrationsRun++;

            } catch (\Exception $e) {
                $this->addError("✗ Migration failed: " . $e->getMessage());

                // Record failure
                $stmt = $db->prepare("
                    INSERT INTO migrations (filename, status, error_message, executed_at)
                    VALUES (?, 'failed', ?, NOW())
                    ON DUPLICATE KEY UPDATE status = 'failed', error_message = ?, executed_at = NOW()
                ");
                $stmt->execute([$migrationFile, $e->getMessage(), $e->getMessage()]);
            }
        }

        $this->addOutput("Migrations run: {$migrationsRun}, Skipped: {$migrationsSkipped}", 'warning');
    }

    private function runSeeders($db)
    {
        $this->addOutput("=== Running Seeders ===", 'info');

        // Seed certification agencies
        $this->addOutput("Checking certification agencies...", 'info');
        $stmt = $db->query("SELECT COUNT(*) as count FROM certification_agencies");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] == 0) {
            $this->addOutput("Seeding certification agencies...", 'info');
            try {
                $seederFile = BASE_PATH . '/database/seeders/certification_agencies.sql';
                if (file_exists($seederFile)) {
                    $sql = file_get_contents($seederFile);
                    $db->exec($sql);
                    $this->addOutput("✓ Certification agencies seeded successfully", 'success');
                } else {
                    $this->addError("✗ Seeder file not found: certification_agencies.sql");
                }
            } catch (\Exception $e) {
                $this->addError("✗ Failed to seed certification agencies: " . $e->getMessage());
            }
        } else {
            $this->addOutput("✓ Certification agencies already seeded ({$result['count']} agencies)", 'success');
        }

        // Seed cash drawers and tags
        $this->addOutput("Checking cash drawers...", 'info');
        $stmt = $db->query("SELECT COUNT(*) as count FROM cash_drawers");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] == 0) {
            $this->addOutput("Seeding cash drawers and customer tags...", 'info');
            try {
                $seederFile = BASE_PATH . '/database/seeders/cash_drawers.sql';
                if (file_exists($seederFile)) {
                    $sql = file_get_contents($seederFile);
                    $db->exec($sql);
                    $this->addOutput("✓ Cash drawers and customer tags seeded successfully", 'success');
                } else {
                    $this->addError("✗ Seeder file not found: cash_drawers.sql");
                }
            } catch (\Exception $e) {
                $this->addError("✗ Failed to seed cash drawers: " . $e->getMessage());
            }
        } else {
            $this->addOutput("✓ Cash drawers already seeded ({$result['count']} drawers)", 'success');
        }
    }

    private function getStatistics($db)
    {
        return [
            'migrations_completed' => $db->query("SELECT COUNT(*) FROM migrations WHERE status = 'completed'")->fetchColumn(),
            'agencies' => $db->query("SELECT COUNT(*) FROM certification_agencies")->fetchColumn(),
            'certifications' => $db->query("SELECT COUNT(*) FROM certifications")->fetchColumn(),
            'customer_tags' => $db->query("SELECT COUNT(*) FROM customer_tags")->fetchColumn(),
            'cash_drawers' => $db->query("SELECT COUNT(*) FROM cash_drawers")->fetchColumn(),
            'customers' => $db->query("SELECT COUNT(*) FROM customers")->fetchColumn(),
            'products' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        ];
    }

    private function addOutput($message, $type = 'info')
    {
        $this->output[] = ['message' => $message, 'type' => $type];
    }

    private function addError($message)
    {
        $this->errors[] = $message;
        $this->addOutput($message, 'error');
    }

    private function render($stats)
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Nautilus Deployment</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    padding: 40px 20px;
                }
                .deployment-container {
                    max-width: 900px;
                    margin: 0 auto;
                }
                .card {
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                }
                .output-line {
                    padding: 8px 12px;
                    margin: 4px 0;
                    border-radius: 4px;
                    font-family: 'Courier New', monospace;
                    font-size: 0.9rem;
                }
                .output-line.info {
                    background: #e7f3ff;
                    border-left: 4px solid #0d6efd;
                }
                .output-line.success {
                    background: #d4edda;
                    border-left: 4px solid #198754;
                }
                .output-line.warning {
                    background: #fff3cd;
                    border-left: 4px solid #ffc107;
                }
                .output-line.error {
                    background: #f8d7da;
                    border-left: 4px solid #dc3545;
                }
                .stat-card {
                    text-align: center;
                    padding: 20px;
                    background: white;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    margin: 10px;
                }
                .stat-number {
                    font-size: 2rem;
                    font-weight: bold;
                    color: #667eea;
                }
                .stat-label {
                    color: #6c757d;
                    font-size: 0.9rem;
                    text-transform: uppercase;
                    margin-top: 5px;
                }
            </style>
        </head>
        <body>
            <div class="deployment-container">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-rocket-takeoff"></i>
                            Nautilus Deployment
                        </h4>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-3">Deployment Log:</h5>
                        <div class="output-log">
                            <?php foreach ($this->output as $line): ?>
                                <div class="output-line <?= $line['type'] ?>">
                                    <?= htmlspecialchars($line['message']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($this->errors)): ?>
                            <div class="alert alert-danger mt-4">
                                <h5><i class="bi bi-x-circle-fill"></i> Errors Encountered:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($this->errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($stats)): ?>
                            <div class="mt-4">
                                <h5 class="mb-3">Database Summary:</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="stat-number"><?= $stats['migrations_completed'] ?></div>
                                            <div class="stat-label">Migrations</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="stat-number"><?= $stats['agencies'] ?></div>
                                            <div class="stat-label">Agencies</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="stat-number"><?= $stats['certifications'] ?></div>
                                            <div class="stat-label">Certifications</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="stat-number"><?= $stats['customer_tags'] ?></div>
                                            <div class="stat-label">Customer Tags</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="stat-number"><?= $stats['cash_drawers'] ?></div>
                                            <div class="stat-label">Cash Drawers</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="stat-number"><?= $stats['customers'] ?></div>
                                            <div class="stat-label">Customers</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($this->errors)): ?>
                            <div class="alert alert-success mt-4">
                                <h5><i class="bi bi-check-circle-fill"></i> Deployment Complete!</h5>
                                <p class="mb-0">Your database has been successfully updated with all migrations and seeders.</p>
                            </div>

                            <div class="mt-3">
                                <h6>Next Steps:</h6>
                                <ul>
                                    <li><a href="/store/dashboard">Visit Dashboard</a></li>
                                    <li><a href="/store/cash-drawer">Open Cash Drawer Management</a></li>
                                    <li><a href="/store/customers/tags">Manage Customer Tags</a></li>
                                    <li><a href="/store/admin/settings">Configure Settings</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="mt-3">
                                <a href="?retry=1" class="btn btn-warning">
                                    <i class="bi bi-arrow-clockwise"></i> Retry Deployment
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center text-white">
                    <p class="mb-0">
                        <i class="bi bi-shield-check"></i>
                        Nautilus Dive Shop Management System
                    </p>
                    <small>Enterprise Edition</small>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
