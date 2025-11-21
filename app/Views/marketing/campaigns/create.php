<?php
$pageTitle = $pageTitle ?? 'Create Campaign';
$activeMenu = 'marketing';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-envelope-plus me-2"></i>Create Email Campaign
        </h1>
        <a href="/store/marketing/campaigns" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Campaigns
        </a>
    </div>

    <form action="/store/marketing/campaigns" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Campaign Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Campaign Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Summer Sale Announcement">
                            <small class="text-muted">Internal name for this campaign</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required placeholder="e.g., Don't Miss Our Summer Sale!">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preview Text</label>
                            <input type="text" name="preview_text" class="form-control" placeholder="Brief preview shown in inbox">
                            <small class="text-muted">Shows after subject line in email clients</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Content <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="15" required placeholder="Write your email content here..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recipients</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Send To</label>
                            <select name="recipient_type" class="form-select">
                                <option value="all">All Customers</option>
                                <option value="newsletter">Newsletter Subscribers</option>
                                <option value="active">Active Customers (Last 90 days)</option>
                                <option value="certified">Certified Divers</option>
                                <option value="students">Current Students</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Schedule</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="radio" name="send_type" value="now" id="sendNow" class="form-check-input" checked>
                            <label class="form-check-label" for="sendNow">Send immediately</label>
                        </div>
                        <div class="form-check mb-3">
                            <input type="radio" name="send_type" value="scheduled" id="sendScheduled" class="form-check-input">
                            <label class="form-check-label" for="sendScheduled">Schedule for later</label>
                        </div>
                        <div class="mb-3" id="scheduleFields" style="display: none;">
                            <label class="form-label">Send Date/Time</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="action" value="send" class="btn btn-primary btn-lg">
                        <i class="bi bi-send me-1"></i>Send Campaign
                    </button>
                    <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark me-1"></i>Save as Draft
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('input[name="send_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('scheduleFields').style.display =
            this.value === 'scheduled' ? 'block' : 'none';
    });
});
</script>
