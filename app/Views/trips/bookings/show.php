<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-ticket"></i> <?= htmlspecialchars($booking['booking_number']) ?></h2>
    <a href="/trips/bookings" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <h5><?= htmlspecialchars($booking['customer_name']) ?></h5>
        <p>Trip: <?= htmlspecialchars($booking['trip_name']) ?></p>
        <p>Departure: <?= date('M j, Y', strtotime($booking['departure_date'])) ?></p>
        <p>Participants: <?= $booking['number_of_participants'] ?></p>
        <p>Total: <?= formatCurrency($booking['total_amount']) ?></p>
        <p>Status: <span class="badge bg-warning"><?= ucfirst($booking['status']) ?></span></p>
    </div>
</div>
