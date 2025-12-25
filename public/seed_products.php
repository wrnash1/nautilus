<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$getEnv = function($key) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
};

try {
    $host = $getEnv('DB_HOST');
    $port = $getEnv('DB_PORT');
    $db   = $getEnv('DB_DATABASE');
    $user = $getEnv('DB_USERNAME');
    $pass = $getEnv('DB_PASSWORD');

    $logFile = __DIR__ . '/seed_log.txt';
    file_put_contents($logFile, "Starting Seeder...\n");

    $dsn = "mysql:host={$host};port={$port};dbname={$db}";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    file_put_contents($logFile, "Connected to DB.\n", FILE_APPEND);

    // Seed Products
    $products = [
        ['Mask', 'MASK-001', 50.00, 10],
        ['Fin', 'FIN-001', 100.00, 15],
        ['Regulator', 'REG-001', 350.00, 5],
        ['BCD', 'BCD-001', 400.00, 8],
        ['Wetsuit', 'WET-001', 150.00, 20]
    ];
    
    foreach ($products as $p) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->execute([$p[1]]);
        if (!$stmt->fetch()) {
            $slug = strtolower(str_replace(' ', '-', $p[0]));
            // Assuming retail_price is same as price for now
            // Check for correct column names (stock_quantity vs quantity_in_stock)
            // Attempt generic robust insert
            
            try {
                 $pdo->prepare("INSERT INTO products (name, slug, sku, price, retail_price, quantity_in_stock, description, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Scuba Gear', 1, NOW())")
                    ->execute([$p[0], $slug, $p[1], $p[2], $p[2], $p[3]]);
                 file_put_contents($logFile, "Created product {$p[0]}\n", FILE_APPEND);
            } catch (PDOException $ex) {
                 // Try fallback column name if first fails
                 file_put_contents($logFile, "Insert failed (quantity_in_stock): " . $ex->getMessage() . ". Retrying with stock_quantity.\n", FILE_APPEND);
                 
                 $pdo->prepare("INSERT INTO products (name, slug, sku, price, retail_price, stock_quantity, description, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Scuba Gear', 1, NOW())")
                    ->execute([$p[0], $slug, $p[1], $p[2], $p[2], $p[3]]);
                 file_put_contents($logFile, "Created product {$p[0]} (fallback)\n", FILE_APPEND);
            }

        } else {
            file_put_contents($logFile, "Product {$p[0]} already exists.\n", FILE_APPEND);
        }
    }
    
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/seed_log.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
}
