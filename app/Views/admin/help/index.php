<?php $this->layout('layouts/admin', ['title' => $title ?? 'Help Center']) ?>

<div class="container-fluid py-4">
    <h2><i class="bi bi-question-circle me-2"></i>Help Center</h2>

    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-book display-4 text-primary"></i>
                    <h5 class="mt-3">Documentation</h5>
                    <p class="text-muted">Learn how to use the system</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-chat-dots display-4 text-success"></i>
                    <h5 class="mt-3">Support</h5>
                    <p class="text-muted">Contact our support team</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-play-circle display-4 text-info"></i>
                    <h5 class="mt-3">Tutorials</h5>
                    <p class="text-muted">Watch video tutorials</p>
                </div>
            </div>
        </div>
    </div>
</div>
