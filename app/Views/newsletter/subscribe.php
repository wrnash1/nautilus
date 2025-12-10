<?php $this->layout('layouts/admin', ['title' => $title ?? 'Newsletter Subscription']) ?>

<div class="container-fluid py-4">
    <h2><i class="bi bi-envelope-check me-2"></i>Newsletter Subscription</h2>

    <div class="row mt-4 justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body text-center py-5">
                    <?php if (isset($subscribed) && $subscribed): ?>
                        <i class="bi bi-check-circle display-1 text-success"></i>
                        <h3 class="mt-3">You're Subscribed!</h3>
                        <p class="text-muted">Thank you for subscribing to our newsletter.</p>
                    <?php else: ?>
                        <i class="bi bi-envelope display-1 text-primary"></i>
                        <h3 class="mt-3">Subscribe to Our Newsletter</h3>
                        <p class="text-muted">Stay updated with the latest news and offers.</p>
                        <form method="POST" action="/newsletter/subscribe" class="mt-4">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <div class="input-group">
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                                <button type="submit" class="btn btn-primary">Subscribe</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
