<?php
// POS Modals - Returns, Gift Cards, etc.
?>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-return-left"></i> Process Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Search by Receipt # or Phone</label>
                    <input type="text" class="form-control" id="returnSearchInput"
                        placeholder="Enter receipt number or customer phone...">
                </div>
                <div id="returnSearchResults"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Gift Card Modal -->
<div class="modal fade" id="giftCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gift"></i> Gift Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#gcCheck">Check
                            Balance</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#gcSell">Sell</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#gcRedeem">Redeem</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="gcCheck">
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="Enter gift card number">
                        </div>
                        <button class="btn btn-primary">Check Balance</button>
                    </div>
                    <div class="tab-pane fade" id="gcSell">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" placeholder="$0.00" min="5" step="5">
                        </div>
                        <button class="btn btn-success">Generate Gift Card</button>
                    </div>
                    <div class="tab-pane fade" id="gcRedeem">
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="Enter gift card number">
                        </div>
                        <button class="btn btn-primary">Apply to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cash Payment Modal -->
<div class="modal fade" id="cashModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash"></i> Cash Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Amount Received</label>
                    <input type="number" class="form-control form-control-lg" id="cashReceived" step="0.01" min="0">
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label>Total Due</label>
                        <div class="fs-4 fw-bold" id="cashTotalDue">$0.00</div>
                    </div>
                    <div class="col-6">
                        <label>Change</label>
                        <div class="fs-4 fw-bold text-success" id="cashChange">$0.00</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button class="btn btn-outline-secondary cash-quick" data-amount="1">$1</button>
                    <button class="btn btn-outline-secondary cash-quick" data-amount="5">$5</button>
                    <button class="btn btn-outline-secondary cash-quick" data-amount="10">$10</button>
                    <button class="btn btn-outline-secondary cash-quick" data-amount="20">$20</button>
                    <button class="btn btn-outline-secondary cash-quick" data-amount="50">$50</button>
                    <button class="btn btn-outline-secondary cash-quick" data-amount="100">$100</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-lg" id="completeCashPayment">
                    <i class="bi bi-check-lg"></i> Complete
                </button>
            </div>
        </div>
    </div>
</div>