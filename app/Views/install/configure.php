<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Installation - Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .install-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        .install-body {
            padding: 40px;
        }
        .section-header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .progress-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .progress-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        .spinner-border {
            width: 4rem;
            height: 4rem;
        }
        #testDbBtn {
            min-width: 150px;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            transition: all 0.3s;
        }
        .password-strength.weak {
            width: 33%;
            background: #dc3545;
        }
        .password-strength.medium {
            width: 66%;
            background: #ffc107;
        }
        .password-strength.strong {
            width: 100%;
            background: #198754;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <h2><i class="bi bi-gear-fill"></i> Configuration</h2>
                <p class="mb-0">Configure your Nautilus installation</p>
            </div>

            <div class="install-body">
                <form id="installForm">
                    <!-- Application Settings -->
                    <div class="mb-5">
                        <h4 class="section-header">
                            <i class="bi bi-app-indicator"></i> Application Settings
                        </h4>

                        <div class="mb-3">
                            <label for="app_name" class="form-label">Application Name</label>
                            <input type="text" class="form-control" id="app_name" name="app_name" value="Nautilus" required>
                        </div>

                        <div class="mb-3">
                            <label for="app_url" class="form-label">Application URL</label>
                            <input type="url" class="form-control" id="app_url" name="app_url"
                                   placeholder="http://localhost/nautilus/public" required>
                            <small class="text-muted">The full URL where your application will be accessible</small>
                        </div>

                        <div class="mb-3">
                            <label for="app_timezone" class="form-label">Timezone</label>
                            <select class="form-select" id="app_timezone" name="app_timezone" required>
                                <option value="America/New_York">Eastern Time (US & Canada)</option>
                                <option value="America/Chicago" selected>Central Time (US & Canada)</option>
                                <option value="America/Denver">Mountain Time (US & Canada)</option>
                                <option value="America/Los_Angeles">Pacific Time (US & Canada)</option>
                                <option value="America/Phoenix">Arizona</option>
                                <option value="America/Anchorage">Alaska</option>
                                <option value="Pacific/Honolulu">Hawaii</option>
                            </select>
                        </div>
                    </div>

                    <!-- Database Configuration -->
                    <div class="mb-5">
                        <h4 class="section-header">
                            <i class="bi bi-database"></i> Database Configuration
                        </h4>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="db_host" class="form-label">Database Host</label>
                                <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="db_port" class="form-label">Port</label>
                                <input type="text" class="form-control" id="db_port" name="db_port" value="3306" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="db_database" class="form-label">Database Name</label>
                            <input type="text" class="form-control" id="db_database" name="db_database" value="nautilus" required>
                            <small class="text-muted">Will be created if it doesn't exist</small>
                        </div>

                        <div class="mb-3">
                            <label for="db_username" class="form-label">Database Username</label>
                            <input type="text" class="form-control" id="db_username" name="db_username" value="root" required>
                        </div>

                        <div class="mb-3">
                            <label for="db_password" class="form-label">Database Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="db_password" name="db_password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleDbPassword">
                                    <i class="bi bi-eye" id="dbPasswordIcon"></i>
                                </button>
                            </div>
                            <small class="text-muted">Leave empty if no password is set</small>
                        </div>

                        <button type="button" class="btn btn-outline-primary" id="testDbBtn">
                            <i class="bi bi-check-circle"></i> Test Connection
                        </button>
                        <div id="dbTestResult" class="mt-2"></div>
                    </div>

                    <!-- Admin Account -->
                    <div class="mb-5">
                        <h4 class="section-header">
                            <i class="bi bi-person-badge"></i> Administrator Account
                        </h4>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="admin_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="admin_first_name" name="admin_first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="admin_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="admin_last_name" name="admin_last_name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                        </div>

                        <div class="mb-3">
                            <label for="admin_password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="admin_password" name="admin_password"
                                       minlength="8" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleAdminPassword">
                                    <i class="bi bi-eye" id="adminPasswordIcon"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="admin_password_confirm" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="admin_password_confirm"
                                       name="admin_password_confirm" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleAdminPasswordConfirm">
                                    <i class="bi bi-eye" id="adminPasswordConfirmIcon"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="mt-1"></div>
                        </div>
                    </div>

                    <!-- Demo Data -->
                    <div class="mb-4">
                        <h4 class="section-header">
                            <i class="bi bi-box-seam"></i> Demo Data
                        </h4>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Demo data includes sample products, customers, transactions, courses, and trips.
                            This is useful for testing and learning the system.
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="install_demo_data"
                                   name="install_demo_data" value="true">
                            <label class="form-check-label" for="install_demo_data">
                                <strong>Install demo data</strong>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary btn-lg" id="installBtn">
                            <i class="bi bi-rocket-takeoff"></i> Install Nautilus
                        </button>
                        <a href="/install" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Progress Modal -->
    <div class="progress-container" id="progressContainer">
        <div class="progress-content">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Installing...</span>
            </div>
            <h4 id="progressMessage">Starting installation...</h4>
            <div class="progress mt-3" style="height: 30px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                     id="progressBar" role="progressbar" style="width: 0%">0%</div>
            </div>
            <p class="text-muted mt-3 mb-0">
                <small>Please wait, this may take a few minutes...</small>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle database password visibility
        document.getElementById('toggleDbPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('db_password');
            const icon = document.getElementById('dbPasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Toggle admin password visibility
        document.getElementById('toggleAdminPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('admin_password');
            const icon = document.getElementById('adminPasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Toggle admin password confirm visibility
        document.getElementById('toggleAdminPasswordConfirm').addEventListener('click', function() {
            const passwordInput = document.getElementById('admin_password_confirm');
            const icon = document.getElementById('adminPasswordConfirmIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Test database connection
        document.getElementById('testDbBtn').addEventListener('click', async function() {
            const btn = this;
            const resultDiv = document.getElementById('dbTestResult');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';

            const formData = new FormData();
            formData.append('db_host', document.getElementById('db_host').value);
            formData.append('db_port', document.getElementById('db_port').value);
            formData.append('db_database', document.getElementById('db_database').value);
            formData.append('db_username', document.getElementById('db_username').value);
            formData.append('db_password', document.getElementById('db_password').value);

            try {
                const response = await fetch('/install/test-database', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i> ${result.message}
                            <br><small>MySQL Version: ${result.mysql_version}</small>
                            ${!result.database_exists ? '<br><small class="text-warning">Note: Database will be created during installation</small>' : ''}
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle-fill"></i> ${result.message}
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle-fill"></i> Error: ${error.message}
                    </div>
                `;
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Test Connection';
        });

        // Password strength indicator
        document.getElementById('admin_password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');

            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            strengthDiv.className = 'password-strength';
            if (strength <= 1) {
                strengthDiv.classList.add('weak');
            } else if (strength <= 3) {
                strengthDiv.classList.add('medium');
            } else {
                strengthDiv.classList.add('strong');
            }
        });

        // Password match validation
        document.getElementById('admin_password_confirm').addEventListener('input', function() {
            const password = document.getElementById('admin_password').value;
            const confirm = this.value;
            const matchDiv = document.getElementById('passwordMatch');

            if (confirm.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }

            if (password === confirm) {
                matchDiv.innerHTML = '<small class="text-success"><i class="bi bi-check"></i> Passwords match</small>';
            } else {
                matchDiv.innerHTML = '<small class="text-danger"><i class="bi bi-x"></i> Passwords do not match</small>';
            }
        });

        // Form submission
        document.getElementById('installBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            console.log('Install button clicked');
            
            // Validate form validity
            const form = document.getElementById('installForm');
            if (!form.checkValidity()) {
                console.log('Form invalid');
                form.reportValidity();
                return;
            }

            // Validate password match
            const password = document.getElementById('admin_password').value;
            const confirm = document.getElementById('admin_password_confirm').value;

            if (password !== confirm) {
                console.log('Passwords do not match');
                alert('Passwords do not match!');
                return;
            }

            // Show progress modal
            console.log('Showing progress modal');
            const progressContainer = document.getElementById('progressContainer');
            progressContainer.style.display = 'flex';

            // Submit installation
            const formData = new FormData(form);
            console.log('Sending POST request to /install/process');

            try {
                const response = await fetch('/install/process', {
                    method: 'POST',
                    body: formData
                });

                console.log('Response received', response.status, response.statusText);
                const result = await response.json();
                console.log('Result parsed', result);

                if (result.success) {
                    // Poll for progress updates
                    const progressInterval = setInterval(async () => {
                        const progressResponse = await fetch('/install/progress');
                        const progress = await progressResponse.json();
                        console.log('Progress update', progress);

                        document.getElementById('progressMessage').textContent = progress.message;
                        document.getElementById('progressBar').style.width = progress.percent + '%';
                        document.getElementById('progressBar').textContent = progress.percent + '%';

                        if (progress.percent === 100) {
                            clearInterval(progressInterval);
                            setTimeout(() => {
                                window.location.href = '/install/complete';
                            }, 1000);
                        } else if (progress.percent === -1) {
                            clearInterval(progressInterval);
                            alert('Installation failed: ' + progress.message);
                            progressContainer.style.display = 'none';
                        }
                    }, 500);
                } else {
                    console.error('Installation failed result', result);
                    progressContainer.style.display = 'none';
                    alert('Installation failed: ' + result.message);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                progressContainer.style.display = 'none';
                alert('Installation error: ' + error.message);
            }
        });
    </script>
</body>
</html>
