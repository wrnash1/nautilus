<?php

namespace App\Controllers\Integrations;

use App\Services\Integration\GoogleWorkspaceService;

class GoogleWorkspaceController
{
    private GoogleWorkspaceService $googleService;

    public function __construct()
    {
        $this->googleService = new GoogleWorkspaceService();
    }

    /**
     * Google Workspace integration dashboard
     */
    public function index()
    {
        $pageTitle = 'Google Workspace Integration';
        $activeMenu = 'integrations';

        // Get current configuration status
        $isConfigured = $this->googleService->isConfigured();
        $services = [
            'calendar' => $this->googleService->isCalendarEnabled(),
            'gmail' => $this->googleService->isGmailEnabled(),
            'drive' => $this->googleService->isDriveEnabled(),
        ];

        $content = $this->renderView($pageTitle, $isConfigured, $services);

        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Save Google Workspace configuration
     */
    public function saveConfig()
    {
        try {
            $credentialsFile = $_FILES['credentials_file'] ?? null;
            $enableCalendar = isset($_POST['enable_calendar']);
            $enableGmail = isset($_POST['enable_gmail']);
            $enableDrive = isset($_POST['enable_drive']);

            if ($credentialsFile && $credentialsFile['error'] === UPLOAD_ERR_OK) {
                $result = $this->googleService->saveCredentials($credentialsFile['tmp_name']);

                if ($result) {
                    // Update service settings
                    $this->googleService->updateSettings([
                        'calendar_enabled' => $enableCalendar,
                        'gmail_enabled' => $enableGmail,
                        'drive_enabled' => $enableDrive,
                    ]);

                    $_SESSION['flash_success'] = 'Google Workspace configuration saved successfully!';
                } else {
                    $_SESSION['flash_error'] = 'Failed to save credentials. Please check the file format.';
                }
            } else {
                // Just update settings without new credentials
                $this->googleService->updateSettings([
                    'calendar_enabled' => $enableCalendar,
                    'gmail_enabled' => $enableGmail,
                    'drive_enabled' => $enableDrive,
                ]);

                $_SESSION['flash_success'] = 'Settings updated successfully!';
            }

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /integrations/google-workspace');
        exit;
    }

    /**
     * Test Google Workspace connection
     */
    public function testConnection()
    {
        try {
            $result = $this->googleService->testConnection();

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Successfully connected to Google Workspace!' : 'Connection failed. Check your credentials.'
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Render the view content
     */
    private function renderView(string $pageTitle, bool $isConfigured, array $services): string
    {
        ob_start();
        ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-google"></i> Google Workspace Integration</h1>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Configuration</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($isConfigured): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Google Workspace is configured and ready to use.
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> Google Workspace is not configured yet. Please upload your credentials file.
                            </div>
                            <?php endif; ?>

                            <form method="POST" action="/integrations/google-workspace/config" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                                <div class="mb-3">
                                    <label class="form-label">Google Service Account Credentials (JSON)</label>
                                    <input type="file" name="credentials_file" class="form-control" accept=".json">
                                    <small class="form-text text-muted">Upload your Google Cloud service account credentials file</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Enable Services</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="enable_calendar" id="enableCalendar" <?= $services['calendar'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enableCalendar">
                                            Google Calendar (sync course schedules and dive trips)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="enable_gmail" id="enableGmail" <?= $services['gmail'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enableGmail">
                                            Gmail (send emails through Google)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="enable_drive" id="enableDrive" <?= $services['drive'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enableDrive">
                                            Google Drive (store backups and documents)
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Configuration
                                </button>

                                <?php if ($isConfigured): ?>
                                <button type="button" class="btn btn-outline-primary" onclick="testConnection()">
                                    <i class="bi bi-plug"></i> Test Connection
                                </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Status</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>Configuration:</strong>
                                    <?php if ($isConfigured): ?>
                                    <span class="badge bg-success">Configured</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning">Not Configured</span>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Calendar:</strong>
                                    <?= $services['calendar'] ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>' ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Gmail:</strong>
                                    <?= $services['gmail'] ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>' ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Drive:</strong>
                                    <?= $services['drive'] ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>' ?>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Setup Instructions</h5>
                        </div>
                        <div class="card-body">
                            <ol class="small">
                                <li>Create a project in Google Cloud Console</li>
                                <li>Enable Calendar, Gmail, and Drive APIs</li>
                                <li>Create a service account</li>
                                <li>Download the JSON credentials file</li>
                                <li>Upload the file above</li>
                                <li>Enable desired services</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function testConnection() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';

            fetch('/integrations/google-workspace/test', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Success! ' + data.message);
                } else {
                    alert('Failed: ' + (data.error || data.message));
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-plug"></i> Test Connection';
            })
            .catch(error => {
                alert('Error: ' + error);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-plug"></i> Test Connection';
            });
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
