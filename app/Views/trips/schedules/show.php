<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar"></i> Trip Schedule Details</h2>
    <div>
        <a href="/trips/schedules" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Schedules
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Schedule Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Trip:</strong><br>
                        <a href="/trips/<?= $schedule['trip_id'] ?>"><?= htmlspecialchars($schedule['trip_name']) ?></a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Trip Code:</strong><br>
                        <?= htmlspecialchars($schedule['trip_code']) ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Departure Date:</strong><br>
                        <?= date('F j, Y', strtotime($schedule['departure_date'])) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Return Date:</strong><br>
                        <?= date('F j, Y', strtotime($schedule['return_date'])) ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong><br>
                        <?php
                        $statusColors = [
                            'scheduled' => 'primary',
                            'full' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $color = $statusColors[$schedule['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($schedule['status']) ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Max Participants:</strong><br>
                        <?= $schedule['max_participants'] ?>
                    </div>
                </div>
                
                <?php if ($schedule['price_override']): ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Price Override:</strong><br>
                        $<?= number_format($schedule['price_override'], 2) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Bookings (<?= count($bookings) ?>)</h5>
                <?php if (hasPermission('trips.create')): ?>
                <a href="/trips/bookings/create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> New Booking
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($bookings)): ?>
                    <p class="text-muted">No bookings yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Booking #</th>
                                    <th>Customer</th>
                                    <th>Participants</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['booking_number']) ?></td>
                                    <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                                    <td><?= $booking['number_of_participants'] ?></td>
                                    <td>$<?= number_format($booking['total_amount'], 2) ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'success',
                                            'cancelled' => 'danger',
                                            'completed' => 'info'
                                        ];
                                        $color = $statusColors[$booking['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?>"><?= ucfirst($booking['status']) ?></span>
                                    </td>
                                    <td>
                                        <a href="/trips/bookings/<?= $booking['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Total Bookings:</strong><br>
                    <?= count($bookings) ?>
                </div>
                <div class="mb-3">
                    <strong>Available Spots:</strong><br>
                    <?php
                    $totalParticipants = array_sum(array_column($bookings, 'number_of_participants'));
                    $availableSpots = $schedule['max_participants'] - $totalParticipants;
                    ?>
                    <?= max(0, $availableSpots) ?> / <?= $schedule['max_participants'] ?>
                </div>
            </div>
        </div>
    </div>
</div>
