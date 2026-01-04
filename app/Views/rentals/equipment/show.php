<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-gear"></i> <?= htmlspecialchars($equipment['name']) ?></h2>
    <div>
        <?php if (hasPermission('rentals.edit')): ?>
            <a href="/store/rentals/equipment/<?= $equipment['id'] ?>/edit" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
        <?php endif; ?>
        <a href="/store/rentals" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Equipment Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Equipment Code:</strong><br>
                        <?= htmlspecialchars($equipment['equipment_code']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Category:</strong><br>
                        <?= htmlspecialchars($equipment['category_name']) ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Size:</strong><br>
                        <?= htmlspecialchars($equipment['size'] ?? 'N/A') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <?php
                        $statusColors = [
                            'available' => 'success',
                            'rented' => 'primary',
                            'maintenance' => 'warning',
                            'damaged' => 'danger',
                            'retired' => 'secondary'
                        ];
                        $color = $statusColors[$equipment['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($equipment['status']) ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Condition:</strong><br>
                        <?php
                        $conditionColors = [
                            'excellent' => 'success',
                            'good' => 'info',
                            'fair' => 'warning',
                            'poor' => 'danger'
                        ];
                        $color = $conditionColors[$equipment['condition']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($equipment['condition']) ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Manufacturer:</strong><br>
                        <?= htmlspecialchars($equipment['manufacturer'] ?? 'N/A') ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Model:</strong><br>
                        <?= htmlspecialchars($equipment['model'] ?? 'N/A') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Serial Number:</strong><br>
                        <?= htmlspecialchars($equipment['serial_number'] ?? 'N/A') ?>
                    </div>
                </div>

                <?php if ($equipment['notes']): ?>
                    <div class="row">
                        <div class="col-12">
                            <strong>Notes:</strong><br>
                            <?= nl2br(htmlspecialchars($equipment['notes'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pricing</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Daily Rate:</strong><br>
                    <span class="h4 text-primary"><?= formatCurrency($equipment['daily_rate']) ?></span>
                </div>

                <?php if ($equipment['weekly_rate']): ?>
                <div class="mb-3">
                    <strong>Weekly Rate:</strong><br>
                    <span class="h4 text-success"><?= formatCurrency($equipment['weekly_rate']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($equipment['weekend_rate'])): ?>
                <div class="mb-3">
                    <strong>Weekend Rate:</strong><br>
                    <span class="h5 text-info"><?= formatCurrency($equipment['weekend_rate']) ?></span>
                    <small class="text-muted d-block">Fri-Sun</small>
                </div>
                <?php endif; ?>

                <?php if ($equipment['purchase_cost']): ?>
                    <div class="mb-3">
                        <strong>Purchase Cost:</strong><br>
                        <?= formatCurrency($equipment['purchase_cost']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($equipment['purchase_date']): ?>
                    <div>
                        <strong>Purchase Date:</strong><br>
                        <?= date('M j, Y', strtotime($equipment['purchase_date'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>