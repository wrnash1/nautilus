<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storefront Setup - Nautilus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="bi bi-shop"></i> Storefront Theme System Setup</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_POST['run_migration']) && $_POST['run_migration'] === 'yes'): ?>
                            <?php
                            // Run the migration using the migration script
                            echo '<div class="alert alert-info">Running storefront migration...</div>';
                            echo '<pre class="bg-dark text-white p-3 rounded" style="max-height: 500px; overflow-y: auto;">';

                            try {
                                // Execute the migration file
                                $migrationFile = __DIR__ . '/../../../../database/migrations/025_create_storefront_theme_system.sql';

                                if (!file_exists($migrationFile)) {
                                    throw new Exception('Migration file not found: ' . $migrationFile);
                                }

                                $sql = file_get_contents($migrationFile);
                                $db = App\Core\Database::getInstance();

                                // Use mysqli for multi-query support
                                $mysqli = new mysqli(
                                    $_ENV['DB_HOST'] ?? 'localhost',
                                    $_ENV['DB_USERNAME'] ?? 'root',
                                    $_ENV['DB_PASSWORD'] ?? '',
                                    $_ENV['DB_DATABASE'] ?? 'nautilus'
                                );

                                if ($mysqli->connect_error) {
                                    throw new Exception('Database connection failed: ' . $mysqli->connect_error);
                                }

                                // Execute multi-query
                                if ($mysqli->multi_query($sql)) {
                                    do {
                                        if ($result = $mysqli->store_result()) {
                                            $result->free();
                                        }
                                        if ($mysqli->errno) {
                                            echo "Warning: " . $mysqli->error . "\n";
                                        }
                                    } while ($mysqli->more_results() && $mysqli->next_result());
                                }

                                if ($mysqli->errno) {
                                    throw new Exception('SQL Error: ' . $mysqli->error);
                                }

                                $mysqli->close();

                                echo "\n✓ All tables created successfully!\n";
                                echo "✓ Default data inserted\n";
                                echo "</pre>";

                                echo '<div class="alert alert-success mt-3">';
                                echo '<h4 class="alert-heading">✓ Setup Complete!</h4>';
                                echo '<p>The storefront theme system has been installed successfully.</p>';
                                echo '<ul>';
                                echo '<li>6 database tables created</li>';
                                echo '<li>Default theme configuration loaded</li>';
                                echo '<li>Homepage sections initialized</li>';
                                echo '<li>Navigation menus created</li>';
                                echo '</ul>';
                                echo '</div>';

                                echo '<div class="mt-4">';
                                echo '<a href="/" class="btn btn-primary me-2" target="_blank"><i class="bi bi-eye"></i> View Storefront</a>';
                                echo '<a href="/admin/storefront" class="btn btn-success"><i class="bi bi-palette"></i> Configure Theme</a>';
                                echo '</div>';

                            } catch (Exception $e) {
                                echo "</pre>";
                                echo '<div class="alert alert-danger mt-3">';
                                echo '<h4 class="alert-heading">✗ Setup Failed</h4>';
                                echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                                echo '<p class="mb-0">Please check your database connection and try again.</p>';
                                echo '</div>';
                                echo '<a href="/admin/storefront/setup" class="btn btn-warning mt-3">Try Again</a>';
                            }
                            ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <h4 class="alert-heading"><i class="bi bi-info-circle"></i> Welcome to Nautilus Storefront!</h4>
                                <p>This setup wizard will configure your online store's theme system.</p>
                                <p class="mb-0">Click the button below to create the necessary database tables and initialize your storefront with default settings.</p>
                            </div>

                            <h5 class="mb-3 mt-4">What will be installed:</h5>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-palette text-primary"></i> Theme Configuration</h6>
                                            <p class="card-text small mb-0">Customize colors, fonts, and layout options</p>
                                        </div>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-gear text-success"></i> Store Settings</h6>
                                            <p class="card-text small mb-0">Configure store name, contact info, and SEO</p>
                                        </div>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-layout-text-window text-info"></i> Homepage Builder</h6>
                                            <p class="card-text small mb-0">Drag-and-drop sections for your homepage</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-menu-button-wide text-warning"></i> Navigation Menus</h6>
                                            <p class="card-text small mb-0">Custom header and footer navigation</p>
                                        </div>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-megaphone text-danger"></i> Promotional Banners</h6>
                                            <p class="card-text small mb-0">Site-wide promotional messages</p>
                                        </div>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-images text-secondary"></i> Theme Assets</h6>
                                            <p class="card-text small mb-0">Upload logos, favicons, and images</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <strong><i class="bi bi-exclamation-triangle"></i> Note:</strong>
                                This setup is safe to run multiple times. If tables already exist, they will not be recreated.
                            </div>

                            <form method="POST" class="mt-4">
                                <input type="hidden" name="run_migration" value="yes">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="/admin" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-play-circle"></i> Run Setup Now
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!isset($_POST['run_migration'])): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Technical Details</h6>
                        <p class="small text-muted mb-2">This will create the following database tables:</p>
                        <ul class="small mb-0">
                            <li><code>theme_config</code> - Visual theme settings (colors, fonts, layout)</li>
                            <li><code>storefront_settings</code> - Store configuration and feature toggles</li>
                            <li><code>homepage_sections</code> - Homepage content sections</li>
                            <li><code>navigation_menus</code> - Custom navigation menus</li>
                            <li><code>promotional_banners</code> - Marketing banners</li>
                            <li><code>theme_assets</code> - Uploaded theme files</li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
