<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-airplane"></i> <?= htmlspecialchars($trip['name']) ?></h2>
    <a href="/trips" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <h5><?= htmlspecialchars($trip['destination']) ?></h5>
        <p><?= htmlspecialchars($trip['description'] ?? '') ?></p>
        <p><strong>Duration:</strong> <?= $trip['duration_days'] ?> days</p>
        <p><strong>Max Participants:</strong> <?= $trip['max_participants'] ?></p>
        <p><strong>Price:</strong> <?= formatCurrency($trip['price']) ?></p>
    </div>
</div>
