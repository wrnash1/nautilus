<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-ticket"></i> Trip Bookings</h2>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Customer</th>
                        <th>Trip</th>
                        <th>Departure</th>
                        <th>Participants</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No bookings found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['booking_number']) ?></td>
                            <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                            <td><?= htmlspecialchars($booking['trip_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($booking['departure_date'])) ?></td>
                            <td><?= $booking['number_of_participants'] ?></td>
                            <td><?= formatCurrency($booking['total_amount']) ?></td>
                            <td><span class="badge bg-warning"><?= ucfirst($booking['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
