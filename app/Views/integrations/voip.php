<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-telephone"></i> VOIP Integration</h2>
    <a href="/admin/integrations" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Integrations
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">VOIP Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/integrations/voip/save">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="mb-4">
                        <label for="voip_provider" class="form-label">VOIP Provider</label>
                        <select class="form-select" id="voip_provider" name="voip_provider">
                            <option value="">Select Provider</option>
                            <option value="twilio" <?= ($settings['voip_provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio</option>
                            <option value="google_voice" <?= ($settings['voip_provider'] ?? '') === 'google_voice' ? 'selected' : '' ?>>Google Voice</option>
                            <option value="ringcentral" <?= ($settings['voip_provider'] ?? '') === 'ringcentral' ? 'selected' : '' ?>>RingCentral</option>
                            <option value="3cx" <?= ($settings['voip_provider'] ?? '') === '3cx' ? 'selected' : '' ?>>3CX
                            </option>
                        </select>
                    </div>

                    <div id="twilioSettings" class="provider-settings" style="display: none;">
                        <h6 class="text-muted mb-3">Twilio Settings</h6>

                        <div class="mb-3">
                            <label for="voip_account_sid" class="form-label">Account SID</label>
                            <input type="text" class="form-control" id="voip_account_sid" name="voip_account_sid"
                                value="<?= htmlspecialchars($settings['voip_account_sid'] ?? '') ?>"
                                placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        </div>

                        <div class="mb-3">
                            <label for="voip_auth_token" class="form-label">Auth Token</label>
                            <input type="password" class="form-control" id="voip_auth_token" name="voip_auth_token"
                                placeholder="<?= !empty($settings['voip_auth_token']) ? '••••••••••••••••' : 'Enter Auth Token' ?>">
                            <small class="text-muted">Leave blank to keep existing token</small>
                        </div>

                        <div class="mb-3">
                            <label for="voip_phone_number" class="form-label">Twilio Phone Number</label>
                            <input type="text" class="form-control" id="voip_phone_number" name="voip_phone_number"
                                value="<?= htmlspecialchars($settings['voip_phone_number'] ?? '') ?>"
                                placeholder="+1234567890">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="text-muted mb-3">Features</h6>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="voip_caller_id_enabled"
                                name="voip_caller_id_enabled" value="1" <?= ($settings['voip_caller_id_enabled'] ?? '') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="voip_caller_id_enabled">
                                <strong>Caller ID Popup</strong>
                                <br><small class="text-muted">Show customer info when they call</small>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="voip_click_to_call_enabled"
                                name="voip_click_to_call_enabled" value="1" <?= ($settings['voip_click_to_call_enabled'] ?? '') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="voip_click_to_call_enabled">
                                <strong>Click-to-Call</strong>
                                <br><small class="text-muted">Call customers directly from their profile</small>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="voip_sms_enabled"
                                name="voip_sms_enabled" value="1" <?= ($settings['voip_sms_enabled'] ?? '') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="voip_sms_enabled">
                                <strong>SMS Messaging</strong>
                                <br><small class="text-muted">Send text messages to customers</small>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="voip_call_logging_enabled"
                                name="voip_call_logging_enabled" value="1" <?= ($settings['voip_call_logging_enabled'] ?? '') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="voip_call_logging_enabled">
                                <strong>Call Logging</strong>
                                <br><small class="text-muted">Keep a log of all calls on customer profiles</small>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Configuration
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="testConnection">
                            <i class="bi bi-wifi"></i> Test Connection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">How It Works</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6><i class="bi bi-telephone-inbound text-success"></i> Incoming Calls</h6>
                    <p class="text-muted small">When a customer calls, a popup shows their profile, purchase history,
                        and quick actions.</p>
                </div>
                <div class="mb-3">
                    <h6><i class="bi bi-telephone-outbound text-primary"></i> Click-to-Call</h6>
                    <p class="text-muted small">Click any phone number in the system to start a call directly from your
                        computer.</p>
                </div>
                <div class="mb-3">
                    <h6><i class="bi bi-chat-dots text-info"></i> SMS</h6>
                    <p class="text-muted small">Send appointment reminders, equipment ready notifications, and marketing
                        messages.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Provider Setup</h5>
            </div>
            <div class="card-body">
                <h6>Twilio</h6>
                <ol class="small text-muted">
                    <li>Sign up at <a href="https://www.twilio.com" target="_blank">twilio.com</a></li>
                    <li>Get your Account SID and Auth Token from Console</li>
                    <li>Buy a phone number</li>
                    <li>Enter credentials above</li>
                </ol>

                <h6 class="mt-3">Google Voice</h6>
                <p class="small text-muted">Google Voice integration uses your existing Google Workspace account.
                    Click-to-call opens in a new tab.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('voip_provider').addEventListener('change', function () {
        const provider = this.value;
        document.querySelectorAll('.provider-settings').forEach(el => el.style.display = 'none');

        if (provider === 'twilio') {
            document.getElementById('twilioSettings').style.display = 'block';
        }
    });

    // Trigger on load
    document.getElementById('voip_provider').dispatchEvent(new Event('change'));

    document.getElementById('testConnection').addEventListener('click', function () {
        fetch('/admin/integrations/voip/test', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                alert(data.success ? 'Connection successful!' : 'Connection failed: ' + data.error);
            });
    });
</script>