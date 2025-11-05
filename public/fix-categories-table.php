<?php
/**
 * Create missing categories table
 * Run at: https://nautilus.local/fix-categories-table.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "<h2>Creating Categories Table</h2><pre>";

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Categories table already exists!\n";
    } else {
        echo "Creating categories table...\n\n";

        $pdo->exec("
            CREATE TABLE categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                parent_id INT UNSIGNED NULL,
                display_order INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_parent_id (parent_id),
                INDEX idx_is_active (is_active),
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        echo "✓ Categories table created!\n\n";

        // Add some default categories
        echo "Adding default categories...\n";
        $pdo->exec("
            INSERT INTO categories (name, description, display_order) VALUES
            ('Scuba Gear', 'Scuba diving equipment and accessories', 1),
            ('Apparel', 'Dive suits, boots, gloves', 2),
            ('Training Materials', 'Books, videos, certification materials', 3),
            ('Accessories', 'Masks, fins, snorkels, and accessories', 4),
            ('Safety Equipment', 'Safety and emergency equipment', 5)
        ");

        echo "✓ Default categories added!\n";
    }

    // Show current categories
    echo "\nCurrent categories:\n";
    $stmt = $pdo->query("SELECT id, name, parent_id FROM categories ORDER BY display_order");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($categories)) {
        echo "  (No categories yet)\n";
    } else {
        foreach ($categories as $cat) {
            $parent = $cat['parent_id'] ? " (parent: {$cat['parent_id']})" : "";
            echo "  [{$cat['id']}] {$cat['name']}$parent\n";
        }
    }

    echo "\n✓ Categories table ready!\n";
    echo "\n<a href='/store/categories'>View Categories</a> | ";
    echo "<a href='/check-schema-issues.php'>Re-check Schema</a> | ";
    echo "<a href='/store'>Dashboard</a>\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>