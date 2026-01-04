<?php $this->layout('layouts/admin', ['title' => $title ?? 'Add Insurance Policy']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/insurance">Insurance</a></li>
                    <li class="breadcrumb-item active">Add Policy</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-shield-plus me-2"></i>Add Insurance Policy</h5>
                </div>
                <div class="card-body">
                    <form action="/store/insurance" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <!-- Customer Selection -->
                        <div class="mb-4">
                            <label for="customer_id" class="form-label">Customer *</label>
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <option value="">Select Customer...</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>">
                                        <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                        (<?= htmlspecialchars($customer['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3">Policy Information</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="insurance_provider" class="form-label">Insurance Provider *</label>
                                <input type="text" class="form-control" id="insurance_provider" name="insurance_provider"
                                       list="providers" required placeholder="e.g., DAN, Divers Alert Network">
                                <datalist id="providers">
                                    <option value="DAN (Divers Alert Network)">
                                    <option value="DiveAssure">
                                    <option value="PADI Travel">
                                    <option value="Dive Master Insurance">
                                    <option value="Global Diving Insurance">
                                    <option value="Aqua Med">
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label for="policy_number" class="form-label">Policy Number *</label>
                                <input type="text" class="form-control" id="policy_number" name="policy_number" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="policy_type" class="form-label">Policy Type</label>
                                <select class="form-select" id="policy_type" name="policy_type">
                                    <option value="individual">Individual</option>
                                    <option value="family">Family</option>
                                    <option value="group">Group</option>
                                    <option value="professional">Professional</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="coverage_level" class="form-label">Coverage Level</label>
                                <input type="text" class="form-control" id="coverage_level" name="coverage_level"
                                       placeholder="e.g., Basic, Preferred, Master">
                            </div>
                            <div class="col-md-4">
                                <label for="coverage_amount" class="form-label">Coverage Amount ($)</label>
                                <input type="number" class="form-control" id="coverage_amount" name="coverage_amount"
                                       min="0" step="0.01">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="deductible" class="form-label">Deductible ($)</label>
                                <input type="number" class="form-control" id="deductible" name="deductible"
                                       min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label for="effective_date" class="form-label">Effective Date *</label>
                                <input type="date" class="form-control" id="effective_date" name="effective_date"
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="expiration_date" class="form-label">Expiration Date *</label>
                                <input type="date" class="form-control" id="expiration_date" name="expiration_date"
                                       value="<?= date('Y-m-d', strtotime('+1 year')) ?>" required>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-4">Coverage Details</h6>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_hyperbaric" name="covers_hyperbaric" checked>
                                    <label class="form-check-label" for="covers_hyperbaric">Hyperbaric Treatment</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_evacuation" name="covers_evacuation" checked>
                                    <label class="form-check-label" for="covers_evacuation">Emergency Evacuation</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_recompression" name="covers_recompression" checked>
                                    <label class="form-check-label" for="covers_recompression">Recompression</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_medical" name="covers_medical" checked>
                                    <label class="form-check-label" for="covers_medical">Medical Coverage</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_equipment" name="covers_equipment">
                                    <label class="form-check-label" for="covers_equipment">Equipment Coverage</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-4">Emergency Contacts</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="emergency_phone" class="form-label">Emergency Phone</label>
                                <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone"
                                       placeholder="24/7 Emergency Line">
                            </div>
                            <div class="col-md-6">
                                <label for="claims_phone" class="form-label">Claims Phone</label>
                                <input type="tel" class="form-control" id="claims_phone" name="claims_phone"
                                       placeholder="Claims Department">
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/store/insurance" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Add Policy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-shield-check me-2"></i>Why Track Insurance?</h6>
                    <ul class="small mb-0">
                        <li>Verify coverage before dives</li>
                        <li>Send renewal reminders</li>
                        <li>Quick access in emergencies</li>
                        <li>Comply with trip requirements</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3 bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-telephone me-2"></i>DAN Emergency</h6>
                    <p class="small mb-1"><strong>Emergency:</strong> +1-919-684-9111</p>
                    <p class="small mb-0"><strong>Non-Emergency:</strong> +1-919-684-2948</p>
                </div>
            </div>
        </div>
    </div>
</div>
