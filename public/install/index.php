<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Dive Shop - Installation Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --nautilus-blue: #0066CC;
            --nautilus-dark: #004D99;
            --nautilus-light: #E6F2FF;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .installer-container {
            max-width: 900px;
            margin: 40px auto;
        }
        .installer-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .installer-header {
            background: linear-gradient(135deg, var(--nautilus-blue) 0%, var(--nautilus-dark) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .installer-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .installer-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .installer-body {
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transition: all 0.3s;
        }
        .step.active .step-circle {
            background: var(--nautilus-blue);
            color: white;
            transform: scale(1.1);
        }
        .step.completed .step-circle {
            background: #28a745;
            color: white;
        }
        .check-item {
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s;
        }
        .check-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .check-status {
            font-size: 1.5rem;
        }
        .check-status.checking {
            color: #ffc107;
        }
        .check-status.success {
            color: #28a745;
        }
        .check-status.error {
            color: #dc3545;
        }
        .check-status.warning {
            color: #ff9800;
        }
        .btn-primary {
            background: var(--nautilus-blue);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--nautilus-dark);
        }
        .progress-container {
            margin: 30px 0;
        }
        .alert {
            border-radius: 10px;
        }
        #loadingSpinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-card">
            <div class="installer-header">
                <h1><i class="bi bi-water"></i> Nautilus Dive Shop</h1>
                <p>Professional Dive Shop Management System</p>
            </div>

            <div class="installer-body">
                <div class="step-indicator">
                    <div class="step active" id="step1">
                        <div class="step-circle">1</div>
                        <small>System Check</small>
                    </div>
                    <div class="step" id="step2">
                        <div class="step-circle">2</div>
                        <small>Configuration</small>
                    </div>
                    <div class="step" id="step3">
                        <div class="step-circle">3</div>
                        <small>Database</small>
                    </div>
                    <div class="step" id="step4">
                        <div class="step-circle">4</div>
                        <small>Complete</small>
                    </div>
                </div>

                <div id="stepContent">
                    <!-- Step 1: System Requirements Check -->
                    <div class="step-content" id="step1Content">
                        <h3 class="mb-4">System Requirements Check</h3>
                        <p class="text-muted mb-4">Checking your server configuration...</p>

                        <div id="checksContainer">
                            <!-- Checks will be dynamically added here -->
                        </div>

                        <div class="mt-4 text-center">
                            <button class="btn btn-primary btn-lg" onclick="performChecks()" id="checkButton">
                                <i class="bi bi-play-circle"></i> Run System Check
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Configuration will be added dynamically -->
                    <div class="step-content" id="step2Content" style="display:none;">
                        <!-- Configuration form -->
                    </div>

                    <!-- Step 3: Database will be added dynamically -->
                    <div class="step-content" id="step3Content" style="display:none;">
                        <!-- Database setup -->
                    </div>

                    <!-- Step 4: Complete will be added dynamically -->
                    <div class="step-content" id="step4Content" style="display:none;">
                        <!-- Success message -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const checks = [
            { name: 'PHP Version (≥ 8.1)', check: 'php_version' },
            { name: 'Apache/Nginx Web Server', check: 'web_server' },
            { name: 'MySQL/MariaDB Server', check: 'mysql_server' },
            { name: 'PHP Extension: PDO', check: 'pdo' },
            { name: 'PHP Extension: PDO_MySQL', check: 'pdo_mysql' },
            { name: 'PHP Extension: OpenSSL', check: 'openssl' },
            { name: 'PHP Extension: MBString', check: 'mbstring' },
            { name: 'PHP Extension: JSON', check: 'json' },
            { name: 'PHP Extension: Curl', check: 'curl' },
            { name: 'PHP Extension: GD/Imagick', check: 'gd' },
            { name: 'PHP Extension: Zip', check: 'zip' },
            { name: 'File Permissions: storage/', check: 'storage_writable' },
            { name: 'File Permissions: public/uploads/', check: 'uploads_writable' },
            { name: '.htaccess Present', check: 'htaccess' },
            { name: 'Apache mod_rewrite', check: 'mod_rewrite' },
            { name: 'SELinux Status', check: 'selinux' },
            { name: 'Firewall Status', check: 'firewall' },
            { name: 'PHP Memory Limit', check: 'memory_limit' }
        ];

        function createCheckItem(check) {
            return `
                <div class="check-item" id="check_${check.check}">
                    <div>
                        <strong>${check.name}</strong>
                        <div class="check-details" style="font-size: 0.9rem; color: #666; margin-top: 5px;"></div>
                    </div>
                    <div class="check-status checking">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            `;
        }

        function performChecks() {
            const container = document.getElementById('checksContainer');
            container.innerHTML = checks.map(createCheckItem).join('');

            document.getElementById('checkButton').disabled = true;
            document.getElementById('checkButton').innerHTML = '<div id="loadingSpinner"></div> Checking...';

            // Perform checks via AJAX
            fetch('check.php')
                .then(response => response.json())
                .then(results => {
                    let criticalError = false;

                    Object.keys(results).forEach(key => {
                        const result = results[key];
                        const checkElem = document.getElementById(`check_${key}`);
                        if (!checkElem) return;

                        const statusElem = checkElem.querySelector('.check-status');
                        const detailsElem = checkElem.querySelector('.check-details');

                        statusElem.className = 'check-status ' + result.status;

                        if (result.status === 'success') {
                            statusElem.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                        } else if (result.status === 'error') {
                            statusElem.innerHTML = '<i class="bi bi-x-circle-fill"></i>';
                            criticalError = true; // Only errors block continuation
                        } else if (result.status === 'warning') {
                            statusElem.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
                            // Warnings don't block continuation
                        }

                        // Display message
                        let detailsHtml = '';
                        if (result.message) {
                            detailsHtml = result.message;
                        }

                        // Add help text if available
                        if (result.help_text) {
                            detailsHtml += '<br><small class="text-muted"><i class="bi bi-info-circle"></i> ' + result.help_text + '</small>';
                        }

                        // Add fix command if available and check failed
                        if (result.fix_command && result.status !== 'success') {
                            detailsHtml += '<br><div class="fix-command mt-2"><strong>How to fix:</strong><br><code style="display:block; background:#f8f9fa; padding:8px; border-radius:4px; margin-top:4px; white-space:pre-wrap; font-size:0.85em;">' + result.fix_command + '</code></div>';
                        }

                        detailsElem.innerHTML = detailsHtml;
                    });

                    document.getElementById('checkButton').disabled = false;

                    if (!criticalError) {
                        document.getElementById('checkButton').innerHTML = '<i class="bi bi-arrow-right-circle"></i> Continue to Configuration';
                        document.getElementById('checkButton').onclick = () => goToStep(2);
                    } else {
                        document.getElementById('checkButton').innerHTML = '<i class="bi bi-arrow-clockwise"></i> Retry Checks';
                        document.getElementById('checkButton').onclick = performChecks;
                    }
                })
                .catch(error => {
                    console.error('Check failed:', error);
                    alert('Failed to perform system checks. Please try again.');
                    document.getElementById('checkButton').disabled = false;
                    document.getElementById('checkButton').innerHTML = '<i class="bi bi-arrow-clockwise"></i> Retry Checks';
                });
        }

        function goToStep(stepNum) {
            // Hide all steps
            for (let i = 1; i <= 4; i++) {
                document.getElementById(`step${i}Content`).style.display = 'none';
                document.getElementById(`step${i}`).classList.remove('active');
                if (i < stepNum) {
                    document.getElementById(`step${i}`).classList.add('completed');
                }
            }

            // Show current step
            document.getElementById(`step${stepNum}Content`).style.display = 'block';
            document.getElementById(`step${stepNum}`).classList.add('active');

            // Load step content if needed
            if (stepNum === 2) {
                loadConfigurationStep();
            } else if (stepNum === 3) {
                loadDatabaseStep();
            } else if (stepNum === 4) {
                loadCompleteStep();
            }
        }

        function loadConfigurationStep() {
            document.getElementById('step2Content').innerHTML = `
                <h3 class="mb-4">Application Configuration</h3>
                <p class="text-muted mb-4">Basic configuration will be set now. Everything else can be configured in the admin panel after installation.</p>

                <form id="configForm">
                    <div class="mb-3">
                        <label class="form-label"><strong>Application URL</strong></label>
                        <input type="url" class="form-control" name="app_url" value="https://nautilus.local" required>
                        <small class="text-muted">Your application's URL</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Business Name</strong></label>
                        <input type="text" class="form-control" name="business_name" placeholder="My Dive Shop" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Admin Email</strong></label>
                        <input type="email" class="form-control" name="admin_email" placeholder="admin@yourdomain.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Timezone</strong></label>
                        <select class="form-control" name="timezone" required>
                            <option value="America/New_York">Eastern Time</option>
                            <option value="America/Chicago">Central Time</option>
                            <option value="America/Denver">Mountain Time</option>
                            <option value="America/Los_Angeles">Pacific Time</option>
                            <option value="America/Anchorage">Alaska Time</option>
                            <option value="Pacific/Honolulu">Hawaii Time</option>
                        </select>
                    </div>

                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-secondary me-2" onclick="goToStep(1)">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveConfigAndContinue()">
                            <i class="bi bi-arrow-right"></i> Continue
                        </button>
                    </div>
                </form>
            `;
        }

        function saveConfigAndContinue() {
            // Save configuration via AJAX
            const form = document.getElementById('configForm');
            const formData = new FormData(form);

            fetch('save-config.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    goToStep(3);
                } else {
                    alert('Failed to save configuration: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Save failed:', error);
                alert('Failed to save configuration. Please try again.');
            });
        }

        function loadDatabaseStep() {
            document.getElementById('step3Content').innerHTML = `
                <h3 class="mb-4">Database Setup</h3>
                <p class="text-muted mb-4">Installing database schema...</p>

                <div class="progress-container">
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" id="dbProgress">0%</div>
                    </div>
                </div>

                <div id="dbLog" class="mt-4" style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 0.9rem;">
                    Starting database installation...<br>
                </div>

                <div class="text-center mt-4" id="dbButtons" style="display:none;">
                    <button class="btn btn-success btn-lg" onclick="goToStep(4)">
                        <i class="bi bi-check-circle"></i> Complete Installation
                    </button>
                </div>
            `;

            installDatabase();
        }

        function installDatabase() {
            const log = document.getElementById('dbLog');
            const progress = document.getElementById('dbProgress');

            fetch('install-db.php')
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        progress.style.width = '100%';
                        progress.textContent = '100%';
                        progress.classList.remove('progress-bar-animated');
                        progress.classList.add('bg-success');

                        log.innerHTML += '<br><strong style="color: green;">✓ Database installed successfully!</strong><br>';
                        log.innerHTML += `<br>Tables created: ${result.tables}<br>`;
                        log.innerHTML += `Admin credentials:<br>`;
                        log.innerHTML += `Email: admin@nautilus.local<br>`;
                        log.innerHTML += `Password: admin123<br>`;
                        log.innerHTML += `<br><em>Please change the default password after logging in.</em>`;

                        document.getElementById('dbButtons').style.display = 'block';
                    } else {
                        progress.classList.add('bg-danger');
                        log.innerHTML += `<br><strong style="color: red;">✗ Installation failed:</strong><br>${result.error}`;
                    }
                })
                .catch(error => {
                    console.error('Installation failed:', error);
                    progress.classList.add('bg-danger');
                    log.innerHTML += `<br><strong style="color: red;">✗ Installation failed:</strong><br>${error.message}`;
                });
        }

        function loadCompleteStep() {
            document.getElementById('step4Content').innerHTML = `
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle" style="font-size: 5rem; color: #28a745;"></i>
                    </div>
                    <h2 class="mb-3">Installation Complete!</h2>
                    <p class="lead mb-4">Your Nautilus Dive Shop system is ready to use.</p>

                    <div class="alert alert-info text-start mb-4">
                        <h5><i class="bi bi-info-circle"></i> Default Admin Credentials</h5>
                        <p class="mb-0"><strong>Email:</strong> admin@nautilus.local<br>
                        <strong>Password:</strong> admin123</p>
                        <small class="text-muted">Please change these credentials after logging in.</small>
                    </div>

                    <div class="d-grid gap-3">
                        <a href="/store" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Go to Admin Dashboard
                        </a>
                        <a href="/" class="btn btn-outline-primary">
                            <i class="bi bi-house-door"></i> View Storefront
                        </a>
                    </div>
                </div>
            `;
        }
    </script>
</body>
</html>
