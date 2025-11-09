<!-- Step 5: Installation -->
<div class="step-content">
    <h2>Installing Nautilus</h2>
    <p>Please wait while the application is being installed. This may take a few minutes.</p>

    <div id="installation-progress">
        <div class="progress-item" id="progress-env" data-status="pending">
            <span class="status-icon">⏳</span>
            <span class="status-text">Creating environment configuration...</span>
        </div>

        <div class="progress-item" id="progress-database" data-status="pending">
            <span class="status-icon">⏳</span>
            <span class="status-text">Creating database tables...</span>
        </div>

        <div class="progress-item" id="progress-migrations" data-status="pending">
            <span class="status-icon">⏳</span>
            <span class="status-text">Running database migrations...</span>
        </div>

        <div class="progress-item" id="progress-admin" data-status="pending">
            <span class="status-icon">⏳</span>
            <span class="status-text">Creating administrator account...</span>
        </div>

        <div class="progress-item" id="progress-directories" data-status="pending">
            <span class="status-icon">⏳</span>
            <span class="status-text">Setting up directories and permissions...</span>
        </div>

        <div class="progress-item" id="progress-sample" data-status="pending">
            <span class="status-icon">⏳</span>
            <span class="status-text">Loading sample data (optional)...</span>
        </div>

        <div class="progress-item" id="progress-complete" data-status="pending">
            <span class="status-icon">⏳</span>
            <span class="status-text">Finalizing installation...</span>
        </div>
    </div>

    <div id="installation-log" style="display: none;">
        <h3>Installation Log</h3>
        <div id="log-content"></div>
    </div>

    <div id="installation-error" class="alert alert-error" style="display: none;">
        <strong>Installation Failed:</strong>
        <p id="error-message"></p>
        <button onclick="location.reload()" class="btn btn-secondary">Retry Installation</button>
    </div>
</div>

<div class="step-actions" id="step-actions" style="display: none;">
    <form method="POST">
        <button type="submit" name="next" class="btn btn-primary">
            Complete Installation
        </button>
    </form>
</div>

<style>
.progress-item {
    padding: 15px 20px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
    border-left: 4px solid #ddd;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.progress-item[data-status="processing"] {
    background-color: #e7f3ff;
    border-left-color: #0066cc;
}

.progress-item[data-status="success"] {
    background-color: #d4edda;
    border-left-color: #28a745;
}

.progress-item[data-status="error"] {
    background-color: #f8d7da;
    border-left-color: #dc3545;
}

.status-icon {
    margin-right: 15px;
    font-size: 1.2em;
    min-width: 30px;
    text-align: center;
}

.status-text {
    flex: 1;
    font-weight: 500;
}

#installation-log {
    margin-top: 30px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

#installation-log h3 {
    margin-top: 0;
    font-size: 1.1em;
}

#log-content {
    font-family: 'Courier New', monospace;
    font-size: 0.85em;
    max-height: 300px;
    overflow-y: auto;
    background-color: #fff;
    padding: 15px;
    border-radius: 4px;
    white-space: pre-wrap;
}

.alert-error {
    padding: 20px;
    border-radius: 5px;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    margin-top: 20px;
}

.alert-error strong {
    display: block;
    margin-bottom: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Start installation automatically
    setTimeout(startInstallation, 1000);
});

async function startInstallation() {
    const steps = [
        { id: 'env', name: 'Creating environment configuration', action: 'create_env' },
        { id: 'database', name: 'Creating database tables', action: 'create_database' },
        { id: 'migrations', name: 'Running database migrations', action: 'run_migrations' },
        { id: 'admin', name: 'Creating administrator account', action: 'create_admin' },
        { id: 'directories', name: 'Setting up directories', action: 'setup_directories' },
        { id: 'sample', name: 'Loading sample data', action: 'load_sample_data' },
        { id: 'complete', name: 'Finalizing installation', action: 'finalize' }
    ];

    for (const step of steps) {
        await executeStep(step);
    }
}

async function executeStep(step) {
    const element = document.getElementById('progress-' + step.id);

    // Mark as processing
    element.setAttribute('data-status', 'processing');
    element.querySelector('.status-icon').textContent = '⏳';

    try {
        // Make AJAX request to install endpoint
        const response = await fetch('install_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=' + step.action
        });

        const result = await response.json();

        if (result.success) {
            // Mark as success
            element.setAttribute('data-status', 'success');
            element.querySelector('.status-icon').textContent = '✓';

            // Add to log
            if (result.log) {
                addToLog(result.log);
            }

            // Small delay for visual effect
            await new Promise(resolve => setTimeout(resolve, 500));

        } else {
            throw new Error(result.error || 'Unknown error occurred');
        }

    } catch (error) {
        // Mark as error
        element.setAttribute('data-status', 'error');
        element.querySelector('.status-icon').textContent = '✗';

        // Show error message
        showError(error.message);
        throw error; // Stop installation
    }
}

function addToLog(message) {
    const log = document.getElementById('installation-log');
    const logContent = document.getElementById('log-content');

    log.style.display = 'block';
    logContent.textContent += message + '\n';
    logContent.scrollTop = logContent.scrollHeight;
}

function showError(message) {
    const errorDiv = document.getElementById('installation-error');
    const errorMessage = document.getElementById('error-message');

    errorMessage.textContent = message;
    errorDiv.style.display = 'block';
}

// Show complete button when all steps are done
window.addEventListener('load', function() {
    const observer = new MutationObserver(function() {
        const allSuccess = Array.from(document.querySelectorAll('.progress-item'))
            .every(item => item.getAttribute('data-status') === 'success');

        if (allSuccess) {
            document.getElementById('step-actions').style.display = 'block';
        }
    });

    document.querySelectorAll('.progress-item').forEach(item => {
        observer.observe(item, { attributes: true, attributeFilter: ['data-status'] });
    });
});
</script>
