<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-check"></i> <?= htmlspecialchars($reservation['reservation_number']) ?></h2>
    <a href="/rentals/reservations" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Reservation Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Customer:</strong><br>
                        <?= htmlspecialchars($reservation['customer_name']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        <?= htmlspecialchars($reservation['customer_email']) ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Phone:</strong><br>
                        <?= htmlspecialchars($reservation['customer_phone'] ?? 'N/A') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <?php
                        $statusColors = [
                            'pending' => 'warning',
                            'confirmed' => 'info',
                            'active' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $color = $statusColors[$reservation['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($reservation['status']) ?></span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Start Date:</strong><br>
                        <?= date('M j, Y', strtotime($reservation['start_date'])) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>End Date:</strong><br>
                        <?= date('M j, Y', strtotime($reservation['end_date'])) ?>
                    </div>
                </div>
                
                <?php if ($reservation['notes']): ?>
                <div class="row">
                    <div class="col-12">
                        <strong>Notes:</strong><br>
                        <?= nl2br(htmlspecialchars($reservation['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Equipment Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Equipment</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Daily Rate</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No items</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['equipment_code']) ?></td>
                                    <td><?= htmlspecialchars($item['equipment_name']) ?></td>
                                    <td><?= htmlspecialchars($item['category_name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= formatCurrency($item['daily_rate']) ?></td>
                                    <td><?= formatCurrency($item['total_amount']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Total Amount:</strong><br>
                    <span class="h4 text-primary"><?= formatCurrency($reservation['total_amount']) ?></span>
                </div>
                
                <?php if ($reservation['deposit_amount'] > 0): ?>
                <div class="mb-3">
                    <strong>Deposit:</strong><br>
                    <span class="h5 text-success"><?= formatCurrency($reservation['deposit_amount']) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong>Created:</strong><br>
                    <?= date('M j, Y g:i A', strtotime($reservation['created_at'])) ?>
                </div>
            </div>
        </div>
    </div>
</div>
