<?php
/**
 * Create missing cash drawer views
 * Run at: https://nautilus.local/fix-cash-drawer-views.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "<h2>Creating Cash Drawer Views</h2><pre>";

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Creating missing database views...\n\n";

    // Check if cash_drawers table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'cash_drawers'")->fetchAll();
    if (empty($tables)) {
        echo "Creating cash_drawers table first...\n";
        $pdo->exec("
            CREATE TABLE cash_drawers (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                location VARCHAR(100),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Add default cash drawers
        $pdo->exec("
            INSERT INTO cash_drawers (name, location, is_active) VALUES
            ('Main Register', 'Front Counter', 1),
            ('Pool Register', 'Pool Area', 1),
            ('Boat Register', 'Dock', 1)
        ");

        echo "✓ cash_drawers table created with default drawers\n\n";
    }

    // Create cash_drawer_sessions_open view
    echo "Creating cash_drawer_sessions_open view...\n";

    $pdo->exec("DROP VIEW IF EXISTS cash_drawer_sessions_open");

    $pdo->exec("
        CREATE VIEW cash_drawer_sessions_open AS
        SELECT
            cds.*,
            cd.name as drawer_name,
            cd.location as drawer_location,
            u.first_name,
            u.last_name,
            CONCAT(u.first_name, ' ', u.last_name) as user_name
        FROM cash_drawer_sessions cds
        LEFT JOIN cash_drawers cd ON cds.register_id = cd.id
        LEFT JOIN users u ON cds.user_id = u.id
        WHERE cds.status = 'open'
        AND cds.closed_at IS NULL
    ");

    echo "✓ cash_drawer_sessions_open view created\n\n";

    // Create cash_drawer_sessions_closed view
    echo "Creating cash_drawer_sessions_closed view...\n";

    $pdo->exec("DROP VIEW IF EXISTS cash_drawer_sessions_closed");

    $pdo->exec("
        CREATE VIEW cash_drawer_sessions_closed AS
        SELECT
            cds.*,
            cd.name as drawer_name,
            cd.location as drawer_location,
            u.first_name,
            u.last_name,
            CONCAT(u.first_name, ' ', u.last_name) as user_name
        FROM cash_drawer_sessions cds
        LEFT JOIN cash_drawers cd ON cds.register_id = cd.id
        LEFT JOIN users u ON cds.user_id = u.id
        WHERE cds.status != 'open'
        AND cds.closed_at IS NOT NULL
    ");

    echo "✓ cash_drawer_sessions_closed view created\n\n";

    // Test the views
    echo "Testing views...\n";

    $openCount = $pdo->query("SELECT COUNT(*) FROM cash_drawer_sessions_open")->fetchColumn();
    echo "  Open sessions: $openCount\n";

    $closedCount = $pdo->query("SELECT COUNT(*) FROM cash_drawer_sessions_closed")->fetchColumn();
    echo "  Closed sessions: $closedCount\n";

    $drawerCount = $pdo->query("SELECT COUNT(*) FROM cash_drawers")->fetchColumn();
    echo "  Cash drawers: $drawerCount\n";

    echo "\n✓ All views created successfully!\n";
    echo "\n<a href='/store/cash-drawer'>Go to Cash Drawer</a> | ";
    echo "<a href='/store'>Dashboard</a>\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>