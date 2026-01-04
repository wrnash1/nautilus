<?php $this->layout('layouts/admin', ['title' => $title ?? 'Contact Support']) ?>

<div class="container-fluid py-4">
    <h2><i class="bi bi-headset me-2"></i>Contact Support</h2>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/help/contact">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="general">General Question</option>
                                <option value="technical">Technical Issue</option>
                                <option value="billing">Billing</option>
                                <option value="feature">Feature Request</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="5" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5>Other Ways to Reach Us</h5>
                    <p><i class="bi bi-envelope me-2"></i>support@example.com</p>
                    <p><i class="bi bi-telephone me-2"></i>(555) 123-4567</p>
                    <p class="text-muted small">Support hours: Mon-Fri 9am-5pm</p>
                </div>
            </div>
        </div>
    </div>
</div>
