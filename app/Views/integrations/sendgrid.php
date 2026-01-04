<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-envelope"></i> Email Marketing (SendGrid)</h2>
    <a href="/store/admin/settings/integrations" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Integrations
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">SendGrid Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/store/integrations/sendgrid/config">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="mb-3">
                        <label for="sendgrid_api_key" class="form-label">API Key</label>
                        <input type="password" class="form-control" id="sendgrid_api_key" name="sendgrid_api_key"
                            placeholder="<?= !empty($settings['sendgrid_api_key']) ? '••••••••••••••••' : 'SG.xxxxxxxxxxxxxxxxxxxx' ?>">
                        <small class="text-muted">Leave blank to keep existing key</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sendgrid_from_email" class="form-label">From Email</label>
                                <input type="email" class="form-control" id="sendgrid_from_email"
                                    name="sendgrid_from_email"
                                    value="<?= htmlspecialchars($settings['sendgrid_from_email'] ?? '') ?>"
                                    placeholder="noreply@yourdiveshop.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sendgrid_from_name" class="form-label">From Name</label>
                                <input type="text" class="form-control" id="sendgrid_from_name"
                                    name="sendgrid_from_name"
                                    value="<?= htmlspecialchars($settings['sendgrid_from_name'] ?? '') ?>"
                                    placeholder="Your Dive Shop">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="sendgrid_reply_to" class="form-label">Reply-To Email</label>
                        <input type="email" class="form-control" id="sendgrid_reply_to" name="sendgrid_reply_to"
                            value="<?= htmlspecialchars($settings['sendgrid_reply_to'] ?? '') ?>"
                            placeholder="info@yourdiveshop.com">
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="sendgrid_enabled"
                                name="sendgrid_enabled" value="1" <?= ($settings['sendgrid_enabled'] ?? '') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sendgrid_enabled">
                                <strong>Enable SendGrid Integration</strong>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
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

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Email Templates</h5>
                <a href="/store/integrations/sendgrid/templates" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Manage Templates
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Template Name</th>
                            <th>Category</th>
                            <th>Subject</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No templates yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($template['name']) ?>
                                    </td>
                                    <td><span class="badge bg-secondary">
                                            <?= ucfirst($template['category']) ?>
                                        </span></td>
                                    <td>
                                        <?= htmlspecialchars($template['subject']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Sends</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>To</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSends)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No emails sent yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($recentSends, 0, 10) as $email): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($email['to_email']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($email['subject']) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $colors = [
                                            'sent' => 'success',
                                            'delivered' => 'success',
                                            'opened' => 'info',
                                            'clicked' => 'primary',
                                            'bounced' => 'danger',
                                            'failed' => 'danger'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $colors[$email['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($email['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('M j, g:i a', strtotime($email['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Send</h5>
            </div>
            <div class="card-body">
                <form id="quickSendForm">
                    <div class="mb-3">
                        <label class="form-label">Segment</label>
                        <select class="form-select" name="segment">
                            <option value="newsletter">Newsletter Subscribers</option>
                            <option value="customers">All Customers</option>
                            <option value="divers">Certified Divers</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" placeholder="Email subject...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="content" rows="4"
                            placeholder="Email content..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-send"></i> Send Campaign
                    </button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Usage Stats</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Emails Sent (This Month)</span>
                    <strong>
                        <?= count($recentSends) ?>
                    </strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Open Rate</span>
                    <strong>--</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Click Rate</span>
                    <strong>--</strong>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Setup Guide</h5>
            </div>
            <div class="card-body">
                <ol class="small mb-0">
                    <li>Create a SendGrid account at <a href="https://sendgrid.com" target="_blank">sendgrid.com</a>
                    </li>
                    <li>Verify your sender domain</li>
                    <li>Create an API key with "Mail Send" permissions</li>
                    <li>Enter the API key above</li>
                    <li>Test the connection</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('testConnection').addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';

        fetch('/store/integrations/sendgrid/test', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                alert(data.success ? '✓ Connection successful!' : '✗ Connection failed: ' + data.error);
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-wifi"></i> Test Connection';
            });
    });

    document.getElementById('quickSendForm').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!confirm('Send this campaign to all recipients in the selected segment?')) return;

        const formData = new FormData(this);
        fetch('/store/integrations/sendgrid/send-bulk', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(`Campaign sent! ${data.sent} emails delivered, ${data.failed} failed.`);
                    location.reload();
                } else {
                    alert('Send failed: ' + data.error);
                }
            });
    });
</script>