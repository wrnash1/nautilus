<!-- Step 1: Requirements Check -->
<div class="step-content">
    <h2>System Requirements</h2>
    <p>Please ensure your system meets the following requirements before proceeding.</p>

    <?php
    $requirements = [
        'php_version' => [
            'name' => 'PHP Version (>= 8.2)',
            'required' => true,
            'status' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'current' => PHP_VERSION
        ],
        'pdo' => [
            'name' => 'PDO Extension',
            'required' => true,
            'status' => extension_loaded('pdo'),
            'current' => extension_loaded('pdo') ? 'Installed' : 'Not Installed'
        ],
        'pdo_mysql' => [
            'name' => 'PDO MySQL Driver',
            'required' => true,
            'status' => extension_loaded('pdo_mysql'),
            'current' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed'
        ],
        'mbstring' => [
            'name' => 'Mbstring Extension',
            'required' => true,
            'status' => extension_loaded('mbstring'),
            'current' => extension_loaded('mbstring') ? 'Installed' : 'Not Installed'
        ],
        'json' => [
            'name' => 'JSON Extension',
            'required' => true,
            'status' => extension_loaded('json'),
            'current' => extension_loaded('json') ? 'Installed' : 'Not Installed'
        ],
        'curl' => [
            'name' => 'cURL Extension',
            'required' => true,
            'status' => extension_loaded('curl'),
            'current' => extension_loaded('curl') ? 'Installed' : 'Not Installed'
        ],
        'gd' => [
            'name' => 'GD Extension',
            'required' => false,
            'status' => extension_loaded('gd'),
            'current' => extension_loaded('gd') ? 'Installed' : 'Not Installed'
        ],
        'zip' => [
            'name' => 'ZIP Extension',
            'required' => false,
            'status' => extension_loaded('zip'),
            'current' => extension_loaded('zip') ? 'Installed' : 'Not Installed'
        ]
    ];

    // Check directory permissions
    $basePath = dirname(dirname(dirname(__DIR__)));
    $directories = [
        'storage' => [
            'name' => 'Storage Directory',
            'path' => $basePath . '/storage',
            'required' => true
        ],
        'logs' => [
            'name' => 'Logs Directory',
            'path' => $basePath . '/storage/logs',
            'required' => true
        ],
        'cache' => [
            'name' => 'Cache Directory',
            'path' => $basePath . '/storage/cache',
            'required' => true
        ],
        'uploads' => [
            'name' => 'Uploads Directory',
            'path' => $basePath . '/public/uploads',
            'required' => true
        ],
        'backups' => [
            'name' => 'Backups Directory',
            'path' => $basePath . '/storage/backups',
            'required' => true
        ]
    ];

    // Check and create directories if needed
    foreach ($directories as $key => &$dir) {
        if (!is_dir($dir['path'])) {
            @mkdir($dir['path'], 0755, true);
        }
        $dir['exists'] = is_dir($dir['path']);
        $dir['writable'] = is_writable($dir['path']);
        $dir['status'] = $dir['exists'] && $dir['writable'];
    }

    // Determine if we can proceed
    $canProceed = true;
    foreach ($requirements as $req) {
        if ($req['required'] && !$req['status']) {
            $canProceed = false;
            break;
        }
    }
    foreach ($directories as $dir) {
        if ($dir['required'] && !$dir['status']) {
            $canProceed = false;
            break;
        }
    }
    ?>

    <div class="requirements-section">
        <h3>PHP Extensions</h3>
        <table class="requirements-table">
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th>Status</th>
                    <th>Current</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $req): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($req['name']) ?>
                        <?php if ($req['required']): ?>
                            <span class="badge badge-required">Required</span>
                        <?php else: ?>
                            <span class="badge badge-optional">Optional</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($req['status']): ?>
                            <span class="status-icon status-success">✓</span>
                        <?php else: ?>
                            <span class="status-icon status-error">✗</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($req['current']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="requirements-section">
        <h3>Directory Permissions</h3>
        <table class="requirements-table">
            <thead>
                <tr>
                    <th>Directory</th>
                    <th>Path</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($directories as $dir): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($dir['name']) ?>
                        <?php if ($dir['required']): ?>
                            <span class="badge badge-required">Required</span>
                        <?php endif; ?>
                    </td>
                    <td><code><?= htmlspecialchars($dir['path']) ?></code></td>
                    <td>
                        <?php if ($dir['status']): ?>
                            <span class="status-icon status-success">✓ Writable</span>
                        <?php elseif ($dir['exists']): ?>
                            <span class="status-icon status-warning">⚠ Not Writable</span>
                        <?php else: ?>
                            <span class="status-icon status-error">✗ Does Not Exist</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!$canProceed): ?>
        <div class="alert alert-error">
            <strong>Warning:</strong> Some required system requirements are not met. Please fix the issues above before continuing.
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <strong>Success:</strong> All required system requirements are met. You can proceed with the installation.
        </div>
    <?php endif; ?>
</div>

<div class="step-actions">
    <form method="POST">
        <button type="submit" name="next" class="btn btn-primary" <?= !$canProceed ? 'disabled' : '' ?>>
            Continue to Database Configuration
        </button>
    </form>
</div>

<style>
.requirements-section {
    margin: 30px 0;
}

.requirements-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.requirements-table th,
.requirements-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.requirements-table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.requirements-table code {
    background-color: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9em;
}

.badge {
    display: inline-block;
    padding: 2px 8px;
    font-size: 0.75em;
    border-radius: 3px;
    margin-left: 8px;
}

.badge-required {
    background-color: #dc3545;
    color: white;
}

.badge-optional {
    background-color: #6c757d;
    color: white;
}

.status-icon {
    font-weight: bold;
    font-size: 1.1em;
}

.status-success {
    color: #28a745;
}

.status-error {
    color: #dc3545;
}

.status-warning {
    color: #ffc107;
}

.alert {
    padding: 15px 20px;
    border-radius: 5px;
    margin-top: 20px;
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
</style>
