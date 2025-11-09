<!-- Step 2: Database Configuration -->
<div class="step-content">
    <h2>Database Configuration</h2>
    <p>Enter your database connection details. The installer will create the necessary database and tables.</p>

    <?php
    $errors = [];
    $testResult = null;

    // Handle connection test
    if (isset($_POST['test_connection'])) {
        $host = $_POST['db_host'] ?? '';
        $port = $_POST['db_port'] ?? '3306';
        $database = $_POST['db_database'] ?? '';
        $username = $_POST['db_username'] ?? '';
        $password = $_POST['db_password'] ?? '';

        try {
            $dsn = "mysql:host={$host};port={$port}";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Check if database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$database}'");
            $dbExists = $stmt->rowCount() > 0;

            if (!$dbExists) {
                // Try to create database
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $testResult = [
                    'success' => true,
                    'message' => 'Database connection successful! Database created.',
                    'database_created' => true
                ];
            } else {
                $testResult = [
                    'success' => true,
                    'message' => 'Database connection successful! Database already exists.',
                    'database_created' => false
                ];
            }

            // Store in session for next step
            $_SESSION['db_config'] = [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password
            ];

        } catch (PDOException $e) {
            $testResult = [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    // Get saved values from session
    $dbConfig = $_SESSION['db_config'] ?? [
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'nautilus',
        'username' => 'root',
        'password' => ''
    ];
    ?>

    <?php if ($testResult): ?>
        <div class="alert <?= $testResult['success'] ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($testResult['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="dbConfigForm">
        <div class="form-group">
            <label for="db_host">Database Host</label>
            <input type="text" id="db_host" name="db_host" class="form-control"
                   value="<?= htmlspecialchars($dbConfig['host']) ?>" required>
            <small class="form-text">Usually "localhost" or "127.0.0.1"</small>
        </div>

        <div class="form-group">
            <label for="db_port">Database Port</label>
            <input type="text" id="db_port" name="db_port" class="form-control"
                   value="<?= htmlspecialchars($dbConfig['port']) ?>" required>
            <small class="form-text">Default MySQL port is 3306</small>
        </div>

        <div class="form-group">
            <label for="db_database">Database Name</label>
            <input type="text" id="db_database" name="db_database" class="form-control"
                   value="<?= htmlspecialchars($dbConfig['database']) ?>" required>
            <small class="form-text">Will be created if it doesn't exist</small>
        </div>

        <div class="form-group">
            <label for="db_username">Database Username</label>
            <input type="text" id="db_username" name="db_username" class="form-control"
                   value="<?= htmlspecialchars($dbConfig['username']) ?>" required autocomplete="off">
            <small class="form-text">MySQL user with CREATE DATABASE privileges</small>
        </div>

        <div class="form-group">
            <label for="db_password">Database Password</label>
            <input type="password" id="db_password" name="db_password" class="form-control"
                   value="<?= htmlspecialchars($dbConfig['password']) ?>" autocomplete="new-password">
            <small class="form-text">Leave blank if no password is set</small>
        </div>

        <div class="form-actions">
            <button type="submit" name="test_connection" class="btn btn-secondary">
                Test Connection
            </button>
        </div>
    </form>
</div>

<div class="step-actions">
    <form method="POST">
        <button type="submit" name="back" class="btn btn-secondary">Back</button>
        <button type="submit" name="next" class="btn btn-primary"
                <?= !isset($testResult) || !$testResult['success'] ? 'disabled' : '' ?>>
            Continue to Application Configuration
        </button>
    </form>
</div>

<style>
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 0.875em;
    color: #6c757d;
}

.form-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.alert {
    padding: 15px 20px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
