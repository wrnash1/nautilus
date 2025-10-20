<?php
$pageTitle = 'Integrations';
$activeMenu = 'settings';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">Integrations</li>
        </ol>
    </nav>

    <h1 class="h3"><i class="bi bi-plugin"></i> Third-Party Integrations</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Wave Apps -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="bi bi-cloud"></i> Wave Apps (Accounting)</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update">
                    <input type="hidden" name="category" value="wave">

                    <div class="mb-3">
                        <label for="wave_access_token" class="form-label">Wave Access Token</label>
                        <input type="password" name="settings[wave_access_token]" id="wave_access_token"
                               class="form-control" value="<?= htmlspecialchars($settings['wave_access_token'] ?? '') ?>">
                        <small class="text-muted">Get this from Wave Settings → Integrations → API</small>
                    </div>

                    <div class="mb-3">
                        <label for="wave_business_id" class="form-label">Wave Business ID</label>
                        <input type="text" name="settings[wave_business_id]" id="wave_business_id"
                               class="form-control" value="<?= htmlspecialchars($settings['wave_business_id'] ?? '') ?>">
                        <small class="text-muted">Found in your Wave business URL</small>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="settings[wave_auto_sync]" class="form-check-input" id="wave_auto_sync"
                               <?= !empty($settings['wave_auto_sync']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="wave_auto_sync">
                            Auto-sync transactions to Wave (creates invoices automatically)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Wave Settings</button>
                    <a href="/integrations/wave" class="btn btn-success">
                        <i class="bi bi-cloud-upload"></i> Go to Wave Sync Dashboard
                    </a>
                </form>
            </div>
        </div>

        <!-- Google Calendar -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="bi bi-calendar-check"></i> Google Calendar Integration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update">
                    <input type="hidden" name="category" value="integrations">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="mb-3">
                        <label for="google_calendar_enabled" class="form-label">
                            <strong>Enable Google Calendar Sync</strong>
                        </label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="settings[google_calendar_enabled]"
                                   class="form-check-input" id="google_calendar_enabled"
                                   <?= !empty($settings['google_calendar_enabled']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="google_calendar_enabled">
                                Sync schedules to Google Calendar
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="google_client_id" class="form-label">Google Client ID</label>
                        <input type="text" name="settings[google_client_id]" id="google_client_id"
                               class="form-control" value="<?= htmlspecialchars($settings['google_client_id'] ?? '') ?>">
                        <small class="text-muted">From Google Cloud Console OAuth 2.0 credentials</small>
                    </div>

                    <div class="mb-3">
                        <label for="google_client_secret" class="form-label">Google Client Secret</label>
                        <input type="password" name="settings[google_client_secret]" id="google_client_secret"
                               class="form-control" value="<?= htmlspecialchars($settings['google_client_secret'] ?? '') ?>">
                        <small class="text-muted">Keep this secure - never share publicly</small>
                    </div>

                    <div class="mb-3">
                        <label for="google_calendar_id" class="form-label">Default Calendar ID</label>
                        <input type="text" name="settings[google_calendar_id]" id="google_calendar_id"
                               class="form-control" value="<?= htmlspecialchars($settings['google_calendar_id'] ?? '') ?>"
                               placeholder="primary">
                        <small class="text-muted">Use "primary" for main calendar or specific calendar ID</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Auto-Sync Settings</label>
                        <div class="form-check">
                            <input type="checkbox" name="settings[google_sync_staff_schedules]"
                                   class="form-check-input" id="google_sync_staff_schedules"
                                   <?= !empty($settings['google_sync_staff_schedules']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="google_sync_staff_schedules">
                                Sync staff schedules
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="settings[google_sync_course_schedules]"
                                   class="form-check-input" id="google_sync_course_schedules"
                                   <?= !empty($settings['google_sync_course_schedules']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="google_sync_course_schedules">
                                Sync course schedules
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="settings[google_sync_trip_schedules]"
                                   class="form-check-input" id="google_sync_trip_schedules"
                                   <?= !empty($settings['google_sync_trip_schedules']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="google_sync_trip_schedules">
                                Sync trip schedules
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="settings[google_sync_appointments]"
                                   class="form-check-input" id="google_sync_appointments"
                                   <?= !empty($settings['google_sync_appointments']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="google_sync_appointments">
                                Sync customer appointments
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Save Google Calendar Settings
                    </button>
                    <?php if (!empty($settings['google_client_id']) && !empty($settings['google_client_secret'])): ?>
                    <a href="/admin/settings/integrations/google/authorize" class="btn btn-primary">
                        <i class="bi bi-google"></i> Authorize Google Calendar
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- PADI Integration -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="bi bi-award"></i> PADI Integration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update">
                    <input type="hidden" name="category" value="integrations">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="settings[padi_enabled]"
                                   class="form-check-input" id="padi_enabled"
                                   <?= !empty($settings['padi_enabled']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="padi_enabled">
                                Enable PADI Integration
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="padi_api_key" class="form-label">PADI API Key</label>
                        <input type="password" name="settings[padi_api_key]" id="padi_api_key"
                               class="form-control" value="<?= htmlspecialchars($settings['padi_api_key'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="padi_api_secret" class="form-label">PADI API Secret</label>
                        <input type="password" name="settings[padi_api_secret]" id="padi_api_secret"
                               class="form-control" value="<?= htmlspecialchars($settings['padi_api_secret'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="padi_api_endpoint" class="form-label">PADI API Endpoint</label>
                        <input type="url" name="settings[padi_api_endpoint]" id="padi_api_endpoint"
                               class="form-control" value="<?= htmlspecialchars($settings['padi_api_endpoint'] ?? '') ?>"
                               placeholder="https://api.padi.com">
                    </div>

                    <button type="submit" class="btn btn-info text-white">
                        <i class="bi bi-save"></i> Save PADI Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- SSI Integration -->
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="card-title mb-0"><i class="bi bi-award-fill"></i> SSI Integration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update">
                    <input type="hidden" name="category" value="integrations">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="settings[ssi_enabled]"
                                   class="form-check-input" id="ssi_enabled"
                                   <?= !empty($settings['ssi_enabled']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ssi_enabled">
                                Enable SSI Integration
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ssi_api_key" class="form-label">SSI API Key</label>
                        <input type="password" name="settings[ssi_api_key]" id="ssi_api_key"
                               class="form-control" value="<?= htmlspecialchars($settings['ssi_api_key'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="ssi_api_endpoint" class="form-label">SSI API Endpoint</label>
                        <input type="url" name="settings[ssi_api_endpoint]" id="ssi_api_endpoint"
                               class="form-control" value="<?= htmlspecialchars($settings['ssi_api_endpoint'] ?? '') ?>"
                               placeholder="https://api.ssi.com">
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Save SSI Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- Twilio SMS Integration -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0"><i class="bi bi-phone"></i> Twilio SMS Integration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update">
                    <input type="hidden" name="category" value="integrations">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="settings[twilio_enabled]"
                                   class="form-check-input" id="twilio_enabled"
                                   <?= !empty($settings['twilio_enabled']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="twilio_enabled">
                                Enable Twilio SMS Notifications
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="twilio_account_sid" class="form-label">Account SID</label>
                        <input type="text" name="settings[twilio_account_sid]" id="twilio_account_sid"
                               class="form-control" value="<?= htmlspecialchars($settings['twilio_account_sid'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="twilio_auth_token" class="form-label">Auth Token</label>
                        <input type="password" name="settings[twilio_auth_token]" id="twilio_auth_token"
                               class="form-control" value="<?= htmlspecialchars($settings['twilio_auth_token'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="twilio_from_number" class="form-label">From Phone Number</label>
                        <input type="tel" name="settings[twilio_from_number]" id="twilio_from_number"
                               class="form-control" value="<?= htmlspecialchars($settings['twilio_from_number'] ?? '') ?>"
                               placeholder="+1234567890">
                    </div>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-save"></i> Save Twilio Settings
                    </button>
                    <?php if (!empty($settings['twilio_account_sid']) && !empty($settings['twilio_auth_token'])): ?>
                    <button type="button" class="btn btn-outline-danger" onclick="testTwilio()">
                        <i class="bi bi-send"></i> Send Test SMS
                    </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Google Calendar Setup Guide -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-google"></i> Google Calendar Setup</h6>
            </div>
            <div class="card-body small">
                <h6>1. Create Google Cloud Project</h6>
                <ol>
                    <li>Go to <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a></li>
                    <li>Create new project</li>
                    <li>Enable Google Calendar API</li>
                </ol>

                <h6>2. Create OAuth 2.0 Credentials</h6>
                <ol>
                    <li>Go to Credentials → Create Credentials</li>
                    <li>Choose "OAuth 2.0 Client ID"</li>
                    <li>Application type: Web application</li>
                    <li>Add authorized redirect URIs</li>
                    <li>Copy Client ID & Secret</li>
                </ol>

                <h6>3. Configure & Authorize</h6>
                <ol>
                    <li>Paste credentials in form</li>
                    <li>Save settings</li>
                    <li>Click "Authorize Google Calendar"</li>
                    <li>Grant calendar permissions</li>
                </ol>

                <div class="alert alert-warning mt-3">
                    <small><strong>Note:</strong> Redirect URI should be:<br>
                    <code>https://yourdomain.com/admin/settings/integrations/google/callback</code></small>
                </div>
            </div>
        </div>

        <!-- Wave Setup Guide -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-cloud"></i> Wave Setup Guide</h6>
            </div>
            <div class="card-body small">
                <h6>1. Get Wave API Token</h6>
                <ol>
                    <li>Log in to Wave Apps</li>
                    <li>Go to Settings → Integrations</li>
                    <li>Create API Application</li>
                    <li>Copy Access Token</li>
                </ol>

                <h6>2. Find Business ID</h6>
                <ol>
                    <li>In Wave, go to your Business</li>
                    <li>Check URL for Business ID</li>
                </ol>

                <h6>3. Configure & Test</h6>
                <ol>
                    <li>Enter credentials above</li>
                    <li>Save settings</li>
                    <li>Click "Go to Wave Sync Dashboard"</li>
                    <li>Test connection</li>
                </ol>
            </div>
        </div>

        <!-- Integration Tips -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Integration Tips</h6>
            </div>
            <div class="card-body small">
                <p><strong>Security Best Practices:</strong></p>
                <ul>
                    <li>Never share API keys publicly</li>
                    <li>Use environment variables in production</li>
                    <li>Rotate keys regularly</li>
                    <li>Monitor API usage for anomalies</li>
                </ul>

                <p class="mt-3"><strong>Sync Frequency:</strong></p>
                <p>Google Calendar syncs occur:</p>
                <ul>
                    <li>When creating/updating schedules</li>
                    <li>When deleting appointments</li>
                    <li>Via manual sync button</li>
                </ul>

                <p class="mt-3"><strong>Need Help?</strong></p>
                <p>Check the documentation or contact support for integration assistance.</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
