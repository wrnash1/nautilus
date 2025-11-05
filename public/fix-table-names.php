<?php
/**
 * Check and fix table naming issues
 * Run at: https://nautilus.local/fix-table-names.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "<h2>Table Naming Issue Fixer</h2><pre>";

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Checking table naming...\n\n";

    // Check what exists
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    $hasCategories = in_array('categories', $tables);
    $hasProductCategories = in_array('product_categories', $tables);

    echo "Table status:\n";
    echo "  categories: " . ($hasCategories ? '✓ EXISTS' : '✗ MISSING') . "\n";
    echo "  product_categories: " . ($hasProductCategories ? '✓ EXISTS' : '✗ MISSING') . "\n\n";

    // Solution: Create VIEW if needed
    if (!$hasCategories && $hasProductCategories) {
        echo "Creating 'categories' as VIEW of 'product_categories'...\n";

        // Drop view if exists
        $pdo->exec("DROP VIEW IF EXISTS categories");

        // Create view
        $pdo->exec("
            CREATE VIEW categories AS
            SELECT * FROM product_categories
        ");

        echo "✓ View created! 'categories' now points to 'product_categories'\n\n";
    } elseif ($hasCategories && !$hasProductCategories) {
        echo "Creating 'product_categories' as VIEW of 'categories'...\n";

        $pdo->exec("DROP VIEW IF EXISTS product_categories");
        $pdo->exec("
            CREATE VIEW product_categories AS
            SELECT * FROM categories
        ");

        echo "✓ View created! 'product_categories' now points to 'categories'\n\n";
    } elseif (!$hasCategories && !$hasProductCategories) {
        echo "Neither table exists. Creating 'product_categories' table...\n\n";

        $pdo->exec("
            CREATE TABLE product_categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                parent_id INT UNSIGNED NULL,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                image_path VARCHAR(255),
                is_active BOOLEAN DEFAULT TRUE,
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL,
                INDEX idx_parent_id (parent_id),
                INDEX idx_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        echo "✓ Table created!\n\n";

        // Add default categories
        echo "Adding default categories...\n";
        $pdo->exec("
            INSERT INTO product_categories (name, slug, description, sort_order) VALUES
            ('Scuba Gear', 'scuba-gear', 'Scuba diving equipment', 1),
            ('Apparel', 'apparel', 'Dive suits, boots, gloves', 2),
            ('Training', 'training', 'Training materials and certifications', 3),
            ('Accessories', 'accessories', 'Masks, fins, snorkels', 4),
            ('Safety', 'safety', 'Safety and emergency equipment', 5)
        ");
        echo "✓ Default categories added!\n\n";

        // Create view
        $pdo->exec("
            CREATE VIEW categories AS
            SELECT * FROM product_categories
        ");
        echo "✓ View 'categories' created!\n\n";
    } else {
        echo "✓ Both tables exist - no action needed\n\n";
    }

    // Verify
    echo "Verification:\n";
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        echo "  ✓ categories: $count rows\n";
    } catch (Exception $e) {
        echo "  ✗ categories: ERROR - " . $e->getMessage() . "\n";
    }

    try {
        $count = $pdo->query("SELECT COUNT(*) FROM product_categories")->fetchColumn();
        echo "  ✓ product_categories: $count rows\n";
    } catch (Exception $e) {
        echo "  ✗ product_categories: ERROR - " . $e->getMessage() . "\n";
    }

    echo "\n✓ Table naming fixed!\n";
    echo "\n<a href='/check-schema-issues.php'>Re-check Schema</a> | <a href='/store'>Dashboard</a>\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>