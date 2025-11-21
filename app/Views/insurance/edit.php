<?php $this->layout('layouts/admin', ['title' => $title ?? 'Edit Insurance Policy']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/insurance">Insurance</a></li>
                    <li class="breadcrumb-item"><a href="/store/insurance/<?= $policy['id'] ?>">Policy</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Insurance Policy</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <strong>Customer:</strong> <?= htmlspecialchars($policy['first_name'] . ' ' . $policy['last_name']) ?>
                    </div>

                    <form action="/store/insurance/<?= $policy['id'] ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <h6 class="border-bottom pb-2 mb-3">Policy Information</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="insurance_provider" class="form-label">Insurance Provider *</label>
                                <input type="text" class="form-control" id="insurance_provider" name="insurance_provider"
                                       value="<?= htmlspecialchars($policy['insurance_provider'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="policy_number" class="form-label">Policy Number *</label>
                                <input type="text" class="form-control" id="policy_number" name="policy_number"
                                       value="<?= htmlspecialchars($policy['policy_number'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="policy_type" class="form-label">Policy Type</label>
                                <select class="form-select" id="policy_type" name="policy_type">
                                    <option value="individual" <?= ($policy['policy_type'] ?? '') === 'individual' ? 'selected' : '' ?>>Individual</option>
                                    <option value="family" <?= ($policy['policy_type'] ?? '') === 'family' ? 'selected' : '' ?>>Family</option>
                                    <option value="group" <?= ($policy['policy_type'] ?? '') === 'group' ? 'selected' : '' ?>>Group</option>
                                    <option value="professional" <?= ($policy['policy_type'] ?? '') === 'professional' ? 'selected' : '' ?>>Professional</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="coverage_level" class="form-label">Coverage Level</label>
                                <input type="text" class="form-control" id="coverage_level" name="coverage_level"
                                       value="<?= htmlspecialchars($policy['coverage_level'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="coverage_amount" class="form-label">Coverage Amount ($)</label>
                                <input type="number" class="form-control" id="coverage_amount" name="coverage_amount"
                                       min="0" step="0.01" value="<?= $policy['coverage_amount'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="deductible" class="form-label">Deductible ($)</label>
                                <input type="number" class="form-control" id="deductible" name="deductible"
                                       min="0" step="0.01" value="<?= $policy['deductible'] ?? '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="effective_date" class="form-label">Effective Date *</label>
                                <input type="date" class="form-control" id="effective_date" name="effective_date"
                                       value="<?= $policy['effective_date'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="expiration_date" class="form-label">Expiration Date *</label>
                                <input type="date" class="form-control" id="expiration_date" name="expiration_date"
                                       value="<?= $policy['expiration_date'] ?? '' ?>" required>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-4">Coverage Details</h6>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_hyperbaric" name="covers_hyperbaric"
                                           <?= ($policy['covers_hyperbaric'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="covers_hyperbaric">Hyperbaric Treatment</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_evacuation" name="covers_evacuation"
                                           <?= ($policy['covers_evacuation'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="covers_evacuation">Emergency Evacuation</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_recompression" name="covers_recompression"
                                           <?= ($policy['covers_recompression'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="covers_recompression">Recompression</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_medical" name="covers_medical"
                                           <?= ($policy['covers_medical'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="covers_medical">Medical Coverage</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="covers_equipment" name="covers_equipment"
                                           <?= ($policy['covers_equipment'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="covers_equipment">Equipment Coverage</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-4">Emergency Contacts</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="emergency_phone" class="form-label">Emergency Phone</label>
                                <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone"
                                       value="<?= htmlspecialchars($policy['emergency_phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="claims_phone" class="form-label">Claims Phone</label>
                                <input type="tel" class="form-control" id="claims_phone" name="claims_phone"
                                       value="<?= htmlspecialchars($policy['claims_phone'] ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/store/insurance/<?= $policy['id'] ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
