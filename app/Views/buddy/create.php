<?php $this->layout('layouts/admin', ['title' => $title ?? 'Create Buddy Pair']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/buddies">Buddy Pairs</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Create Buddy Pair</h5>
                </div>
                <div class="card-body">
                    <form action="/store/buddies" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="diver1_id" class="form-label">Diver 1 *</label>
                                <select class="form-select" id="diver1_id" name="diver1_id" required>
                                    <option value="">Select Diver</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>">
                                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                            (<?= htmlspecialchars($customer['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="diver2_id" class="form-label">Diver 2 *</label>
                                <select class="form-select" id="diver2_id" name="diver2_id" required>
                                    <option value="">Select Diver</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>">
                                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                            (<?= htmlspecialchars($customer['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="relationship_type" class="form-label">Relationship Type</label>
                                <select class="form-select" id="relationship_type" name="relationship_type">
                                    <option value="trip_specific">Trip Specific</option>
                                    <option value="preferred">Preferred Partners</option>
                                    <option value="permanent">Permanent Partners</option>
                                    <option value="single_dive">Single Dive Only</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="paired_for_date" class="form-label">Paired For Date</label>
                                <input type="date" class="form-control" id="paired_for_date" name="paired_for_date">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="trip_id" class="form-label">Paired For Trip (Optional)</label>
                            <select class="form-select" id="trip_id" name="trip_id">
                                <option value="">No specific trip</option>
                                <?php foreach ($trips as $trip): ?>
                                    <option value="<?= $trip['id'] ?>">
                                        <?= htmlspecialchars($trip['trip_name']) ?> - <?= date('M j, Y', strtotime($trip['start_date'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Any special notes about this pairing..."></textarea>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/store/buddies" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Create Pair
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-info-circle me-2"></i>Relationship Types</h6>
                    <ul class="small mb-0">
                        <li><strong>Permanent</strong> - Long-term dive partners</li>
                        <li><strong>Preferred</strong> - Preferred when available</li>
                        <li><strong>Trip Specific</strong> - For a particular trip</li>
                        <li><strong>Single Dive</strong> - One-time pairing</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3 bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-shield-check me-2"></i>Safety Note</h6>
                    <p class="small mb-0">
                        Always consider certification levels and experience when pairing divers.
                        Matching similar skill levels improves safety and dive enjoyment.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
