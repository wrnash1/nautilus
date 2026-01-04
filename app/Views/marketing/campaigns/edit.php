<?php
$pageTitle = 'Edit Campaign: ' . htmlspecialchars($campaign['name']);
$activeMenu = 'marketing';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-pencil-square me-2"></i>Edit Email Campaign
        </h1>
        <a href="/store/marketing/campaigns/<?= $campaign['id'] ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Details
        </a>
    </div>

    <form action="/store/marketing/campaigns/<?= $campaign['id'] ?>/update" method="POST">
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
                            <input type="text" name="name" class="form-control" required 
                                   value="<?= htmlspecialchars($campaign['name']) ?>"
                                   placeholder="e.g., Summer Sale Announcement">
                            <small class="text-muted">Internal name for this campaign</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required 
                                   value="<?= htmlspecialchars($campaign['subject']) ?>"
                                   placeholder="e.g., Don't Miss Our Summer Sale!">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Content <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="15" required 
                                      placeholder="Write your email content here..."><?= htmlspecialchars($campaign['content']) ?></textarea>
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
                            <select name="segment" class="form-select">
                                <option value="all" <?= $campaign['segment'] === 'all' ? 'selected' : '' ?>>All Customers</option>
                                <option value="newsletter" <?= $campaign['segment'] === 'newsletter' ? 'selected' : '' ?>>Newsletter Subscribers</option>
                                <option value="active" <?= $campaign['segment'] === 'active' ? 'selected' : '' ?>>Active Customers (Last 90 days)</option>
                                <option value="certified" <?= $campaign['segment'] === 'certified' ? 'selected' : '' ?>>Certified Divers</option>
                                <option value="students" <?= $campaign['segment'] === 'students' ? 'selected' : '' ?>>Current Students</option>
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
                            <input type="radio" name="send_type" value="now" id="sendNow" class="form-check-input" 
                                   <?= empty($campaign['scheduled_at']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sendNow">Send immediately</label>
                        </div>
                        <div class="form-check mb-3">
                            <input type="radio" name="send_type" value="scheduled" id="sendScheduled" class="form-check-input"
                                   <?= !empty($campaign['scheduled_at']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sendScheduled">Schedule for later</label>
                        </div>
                        <div class="mb-3" id="scheduleFields" style="display: <?= !empty($campaign['scheduled_at']) ? 'block' : 'none' ?>;">
                            <label class="form-label">Send Date/Time</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control"
                                   value="<?= !empty($campaign['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($campaign['scheduled_at'])) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-1"></i>Save Changes
                    </button>
                    <button type="submit" name="action" value="send" class="btn btn-success">
                         <i class="bi bi-send me-1"></i>Save & Send
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
