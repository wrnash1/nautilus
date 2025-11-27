<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Universal Installer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --background: #0f172a;
            --surface: #1e293b;
            --surface-light: #334155;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(245, 158, 11, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(239, 68, 68, 0.15) 0px, transparent 50%);
        }

        .installer-wrapper {
            width: 100%;
            max-width: 900px;
            padding: 2rem;
        }

        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 600px;
        }

        .header {
            padding: 2.5rem;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(to right, #60a5fa, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            padding: 0 4rem;
            margin-top: 2rem;
            position: relative;
        }

        .progress-line {
            position: absolute;
            top: 50%;
            left: 4rem;
            right: 4rem;
            height: 2px;
            background: var(--surface-light);
            z-index: 0;
            transform: translateY(-50%);
        }

        .progress-line-fill {
            position: absolute;
            top: 50%;
            left: 4rem;
            height: 2px;
            background: var(--primary);
            z-index: 0;
            transform: translateY(-50%);
            width: 0%;
            transition: width 0.5s ease;
        }

        .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--surface);
            border: 2px solid var(--surface-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .step-dot.active {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(59, 130, 246, 0.1);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }

        .step-dot.completed {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .content {
            flex: 1;
            padding: 3rem;
            position: relative;
        }

        .step-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .step-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .checks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .check-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .check-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .check-name {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .check-status {
            font-size: 1.2rem;
        }

        .status-loading { color: var(--text-muted); animation: spin 1s linear infinite; }
        .status-success { color: var(--success); }
        .status-error { color: var(--error); }
        .status-warning { color: var(--warning); }

        @keyframes spin { 100% { transform: rotate(360deg); } }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--surface-light);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
        }

        .console-log {
            background: #000;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #0f0;
            height: 200px;
            overflow-y: auto;
            margin-top: 1rem;
            border: 1px solid var(--border);
        }

        .success-card {
            text-align: center;
            padding: 2rem;
        }

        .success-icon {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 1.5rem;
        }

        .credentials-box {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            padding: 1.5rem;
            border-radius: 12px;
            margin: 2rem 0;
            text-align: left;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        input[type="checkbox"] {
            width: 1.2rem;
            height: 1.2rem;
            accent-color: var(--primary);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
        }

        .fix-command {
            margin-top: 0.75rem;
            background: rgba(0, 0, 0, 0.3);
            padding: 0.75rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #e2e8f0;
            white-space: pre-wrap;
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            position: relative;
        }

        .copy-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-muted);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: var(--text);
        }
    </style>
</head>
<body>
    <div class="installer-wrapper">
        <div class="card">
            <div class="header">
                <div class="logo">NAUTILUS</div>
                <div class="subtitle">Universal Installer • Multi-Distribution Linux Support</div>
                
                <div class="progress-bar">
                    <div class="progress-line"></div>
                    <div class="progress-line-fill" id="progressFill"></div>
                    <div class="step-dot active" data-step="1">1</div>
                    <div class="step-dot" data-step="2">2</div>
                    <div class="step-dot" data-step="3">3</div>
                    <div class="step-dot" data-step="4">4</div>
                </div>
            </div>

            <div class="content">
                <!-- Step 1: System Check -->
                <div class="step-content active" id="step1">
                    <h2>System Check</h2>
                    <p style="color: var(--text-muted); margin-bottom: 1rem;">Checking your server environment for compatibility.</p>
                    
                    <div id="osInfoBadge" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); padding: 1rem; border-radius: 12px; margin-bottom: 2rem; display: none;">
                        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 200px;">
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Detected Environment</div>
                                <div style="font-weight: 600; font-size: 1.1rem;" id="osName">Loading...</div>
                            </div>
                            <div style="flex: 1; min-width: 150px;">
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Package Manager</div>
                                <div style="font-weight: 500;" id="packageManager">-</div>
                            </div>
                            <div style="flex: 1; min-width: 150px;">
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">PHP Version</div>
                                <div style="font-weight: 500;" id="phpVersion">-</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checks-grid" id="checksContainer">
                        <!-- Checks injected here -->
                    </div>

                    <div class="error-message" id="systemCheckError">
                        Some system requirements are not met. Please fix the issues marked in red.
                    </div>

                    <div class="actions">
                        <button class="btn btn-primary" id="btnCheckAgain" onclick="runSystemChecks()">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Re-check
                        </button>
                        <button class="btn btn-primary" id="btnStep1Next" onclick="nextStep(2)" disabled>
                            Continue
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Configuration -->
                <div class="step-content" id="step2">
                    <h2>Configuration</h2>
                    <p style="color: var(--text-muted); margin-bottom: 2rem;">Set up your dive shop details.</p>

                    <form id="configForm">
                        <div class="form-group">
                            <label class="form-label">Admin Email</label>
                            <input type="email" class="form-control" name="admin_email" required placeholder="admin@your-domain.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Timezone</label>
                            <select class="form-control" name="timezone">
                                <?php
                                $timezones = DateTimeZone::listIdentifiers();
                                foreach ($timezones as $tz) {
                                    echo '<option value="' . $tz . '"' . ($tz === 'UTC' ? ' selected' : '') . '>' . $tz . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <h3 style="font-size: 1.1rem; margin: 2rem 0 1rem; color: var(--text-muted);">Database Connection</h3>

                        <div class="form-group">
                            <label class="form-label">Database Host</label>
                            <input type="text" class="form-control" name="db_host" required value="localhost" placeholder="localhost">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Database Name</label>
                            <input type="text" class="form-control" name="db_name" required placeholder="nautilus">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Database User</label>
                            <input type="text" class="form-control" name="db_user" required placeholder="root">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Database Password</label>
                            <input type="password" class="form-control" name="db_password" placeholder="password">
                        </div>

                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="installDemoData" name="install_demo_data">
                            <div>
                                <div style="font-weight: 500;">Install Demo Data</div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">Populate with sample products, customers, and bookings.</div>
                            </div>
                        </div>
                    </form>

                    <div class="actions">
                        <button class="btn btn-outline" onclick="prevStep(1)">Back</button>
                        <button class="btn btn-primary" onclick="submitConfig()">
                            Continue
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Installation -->
                <div class="step-content" id="step3">
                    <h2>Installing</h2>
                    <p style="color: var(--text-muted); margin-bottom: 2rem;">Please wait while we set up your database and configure the system.</p>

                    <div class="console-log" id="installLog">
                        > Initializing installation...<br>
                    </div>

                    <div class="actions">
                        <button class="btn btn-primary" id="btnStep3Next" onclick="nextStep(4)" disabled>
                            Complete
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Step 4: Complete -->
                <div class="step-content" id="step4">
                    <div class="success-card">
                        <div class="success-icon">
                            <svg width="80" height="80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h2>Installation Complete!</h2>
                        <p style="color: var(--text-muted);">Nautilus has been successfully installed.</p>

                        <div class="credentials-box">
                            <h3 style="margin-bottom: 1rem; color: var(--success);">Admin Credentials</h3>
                            <p><strong>Email:</strong> <span id="finalEmail">admin@example.com</span></p>
                            <p><strong>Password:</strong> admin123</p>
                            <p style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-muted);">* Please change your password immediately after logging in.</p>
                        </div>

                        <div class="actions" style="justify-content: center;">
                            <a href="/" class="btn btn-outline">View Storefront</a>
                            <a href="/store" class="btn btn-primary">Go to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            runSystemChecks();
            
            // Auto-fill URL
            document.querySelector('input[name="app_url"]').value = window.location.origin;
        });

        function updateProgress(step) {
            // Update dots
            document.querySelectorAll('.step-dot').forEach(dot => {
                const dotStep = parseInt(dot.dataset.step);
                if (dotStep < step) {
                    dot.className = 'step-dot completed';
                    dot.innerHTML = '✓';
                } else if (dotStep === step) {
                    dot.className = 'step-dot active';
                    dot.innerHTML = step;
                } else {
                    dot.className = 'step-dot';
                    dot.innerHTML = dotStep;
                }
            });

            // Update line fill
            const fill = document.getElementById('progressFill');
            const percentage = ((step - 1) / 3) * 100;
            fill.style.width = `${percentage}%`;
        }

        function nextStep(step) {
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.getElementById(`step${step}`).classList.add('active');
            currentStep = step;
            updateProgress(step);
        }

        function prevStep(step) {
            nextStep(step);
        }

        async function runSystemChecks() {
            const container = document.getElementById('checksContainer');
            container.innerHTML = ''; // Clear
            document.getElementById('btnCheckAgain').disabled = true;
            document.getElementById('systemCheckError').style.display = 'none';

            try {
                const response = await fetch('check.php');
                const checks = await response.json();
                let allPassed = true;

                Object.values(checks).forEach(check => {
                    // Extract and display OS info if present
                    if (check.os_data) {
                        document.getElementById('osInfoBadge').style.display = 'block';
                        document.getElementById('osName').textContent = check.os_data.pretty_name;
                        document.getElementById('packageManager').textContent = check.os_data.package_manager.toUpperCase();
                        return; // Skip adding this to the checks grid
                    }
                    
                    // Extract PHP version for display
                    if (check.name && check.name.includes('PHP Version')) {
                        document.getElementById('phpVersion').textContent = check.message.replace('PHP ', '');
                    }
                    
                    const div = document.createElement('div');
                    div.className = 'check-item';
                    
                    let icon = '';
                    let statusClass = '';
                    
                    if (check.status === 'success') {
                        icon = '✓';
                        statusClass = 'status-success';
                    } else if (check.status === 'warning') {
                        icon = '!';
                        statusClass = 'status-warning';
                    } else {
                        icon = '✕';
                        statusClass = 'status-error';
                        allPassed = false;
                    }

                    div.innerHTML = `
                        <div style="display:flex; justify-content:space-between; align-items:center; width:100%">
                            <span class="check-name">${check.name}</span>
                            <span class="check-status ${statusClass}">${icon}</span>
                        </div>
                    `;
                    
                    if (check.status !== 'success') {
                        div.style.flexDirection = 'column';
                        div.style.alignItems = 'flex-start';
                        
                        if (check.fix_command) {
                            const fixDiv = document.createElement('div');
                            fixDiv.className = 'fix-command';
                            fixDiv.textContent = check.fix_command;
                            
                            const copyBtn = document.createElement('button');
                            copyBtn.className = 'copy-btn';
                            copyBtn.textContent = 'Copy';
                            copyBtn.onclick = (e) => {
                                e.stopPropagation();
                                navigator.clipboard.writeText(check.fix_command).then(() => {
                                    copyBtn.textContent = 'Copied!';
                                    setTimeout(() => copyBtn.textContent = 'Copy', 2000);
                                });
                            };
                            
                            fixDiv.appendChild(copyBtn);
                            div.appendChild(fixDiv);
                        }
                        
                        if (check.help_text) {
                             const helpDiv = document.createElement('div');
                             helpDiv.style.marginTop = '0.5rem';
                             helpDiv.style.fontSize = '0.85rem';
                             helpDiv.style.color = 'var(--text-muted)';
                             helpDiv.textContent = check.help_text;
                             div.appendChild(helpDiv);
                        }
                    }
                    
                    if (check.status !== 'success' && check.message) {
                         div.title = check.message; // Simple tooltip
                    }
                    
                    container.appendChild(div);
                });

                if (allPassed) {
                    document.getElementById('btnStep1Next').disabled = false;
                } else {
                    document.getElementById('systemCheckError').style.display = 'block';
                }

            } catch (e) {
                console.error(e);
                container.innerHTML = '<div style="color:var(--error)">Failed to load system checks.</div>';
            } finally {
                document.getElementById('btnCheckAgain').disabled = false;
            }
        }

        async function submitConfig() {
            const form = document.getElementById('configForm');
            const formData = new FormData(form);
            
            // Store email for final step
            document.getElementById('finalEmail').textContent = formData.get('admin_email');

            try {
                const response = await fetch('save-config.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    nextStep(3);
                    startInstallation(formData.get('install_demo_data') === 'on');
                } else {
                    alert('Configuration Error: ' + result.message);
                }
            } catch (e) {
                alert('Failed to save configuration.');
            }
        }

        async function startInstallation(installDemo) {
            const log = document.getElementById('installLog');
            const logLine = (msg) => log.innerHTML += `> ${msg}<br>`;
            
            logLine('Creating database tables...');
            
            try {
                const response = await fetch(`install-db.php?demo=${installDemo ? 1 : 0}`);
                const result = await response.json();

                if (result.success) {
                    logLine('<span style="color:var(--success)">Database installed successfully!</span>');
                    logLine(`Created ${result.tables} tables.`);
                    if (installDemo) logLine('Demo data installed.');
                    
                    document.getElementById('btnStep3Next').disabled = false;
                    
                    // Auto advance after 1s
                    setTimeout(() => nextStep(4), 1000);
                } else {
                    logLine(`<span style="color:var(--error)">Error: ${result.error}</span>`);
                }
            } catch (e) {
                logLine(`<span style="color:var(--error)">Fatal Error: ${e.message}</span>`);
            }
        }
    </script>
</body>
</html>
