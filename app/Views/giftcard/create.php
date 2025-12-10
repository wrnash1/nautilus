<?php $this->layout('layouts/admin', ['title' => $title ?? 'Issue Gift Card']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/gift-cards">Gift Cards</a></li>
                    <li class="breadcrumb-item active">Issue New</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gift me-2"></i>Issue Gift Card</h2>
        <a href="/store/gift-cards" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/store/gift-cards">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="1" required>
                                </div>
                                <div class="form-text">Minimum $1.00</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Card Type</label>
                                <select name="card_type" class="form-select">
                                    <option value="physical">Physical Card</option>
                                    <option value="digital">Digital (E-Gift)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Assign to Customer (Optional)</label>
                            <select name="customer_id" class="form-select">
                                <option value="">-- Unassigned --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>">
                                        <?= htmlspecialchars($customer['last_name'] . ', ' . $customer['first_name']) ?>
                                        (<?= htmlspecialchars($customer['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Leave blank to sell as anonymous gift card</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Expiration Date</label>
                            <input type="date" name="expiry_date" class="form-control"
                                   value="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                            <div class="form-text">Default: 1 year from today</div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/store/gift-cards" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-gift me-1"></i>Issue Gift Card
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>About Gift Cards</h5>
                    <ul class="mb-0">
                        <li>Card number and PIN generated automatically</li>
                        <li>Cards can be reloaded at any time</li>
                        <li>Balance is tracked per transaction</li>
                        <li>Cards can be assigned to customers or sold anonymously</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Common Amounts</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.querySelector('input[name=amount]').value='25.00'">$25</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.querySelector('input[name=amount]').value='50.00'">$50</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.querySelector('input[name=amount]').value='75.00'">$75</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.querySelector('input[name=amount]').value='100.00'">$100</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.querySelector('input[name=amount]').value='150.00'">$150</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.querySelector('input[name=amount]').value='200.00'">$200</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
