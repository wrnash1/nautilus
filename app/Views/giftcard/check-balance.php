<?php $this->layout('layouts/admin', ['title' => $title ?? 'Check Gift Card Balance']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/gift-cards">Gift Cards</a></li>
                    <li class="breadcrumb-item active">Check Balance</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-search me-2"></i>Check Gift Card Balance</h2>
        <a href="/store/gift-cards" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="/store/gift-cards/check-balance">
                        <div class="mb-4">
                            <label class="form-label">Gift Card Number</label>
                            <input type="text" name="card_number" class="form-control form-control-lg"
                                   placeholder="GC-XXXXXXXXXXXXXXXX" value="<?= htmlspecialchars($cardNumber ?? '') ?>" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-search me-2"></i>Check Balance
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($cardNumber && $card): ?>
                <?php $isExpired = strtotime($card['expiry_date']) < time(); ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Card Found</h5>
                    </div>
                    <div class="card-body text-center">
                        <h6 class="text-muted">Card Number</h6>
                        <h4><code><?= htmlspecialchars($card['card_number']) ?></code></h4>

                        <hr>

                        <h6 class="text-muted">Current Balance</h6>
                        <h1 class="text-success mb-3">$<?= number_format($card['current_balance'], 2) ?></h1>

                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <small class="text-muted">Status</small><br>
                                <?php if ($isExpired): ?>
                                    <span class="badge bg-danger">Expired</span>
                                <?php elseif ($card['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= ucfirst($card['status']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Expires</small><br>
                                <strong><?= date('M j, Y', strtotime($card['expiry_date'])) ?></strong>
                            </div>
                        </div>

                        <a href="/store/gift-cards/<?= $card['id'] ?>" class="btn btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>View Full Details
                        </a>
                    </div>
                </div>
            <?php elseif ($cardNumber && !$card): ?>
                <div class="card mt-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-x-circle me-2"></i>Card Not Found</h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted mb-0">No gift card found with number: <code><?= htmlspecialchars($cardNumber) ?></code></p>
                        <p class="text-muted">Please check the card number and try again.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
