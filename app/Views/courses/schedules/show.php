<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar"></i> Schedule Details</h2>
    <a href="/store/courses/schedules" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <h5><?= htmlspecialchars($schedule['course_name']) ?></h5>
        <p>Start: <?= date('M j, Y', strtotime($schedule['start_date'])) ?></p>
        <p>End: <?= date('M j, Y', strtotime($schedule['end_date'])) ?></p>
    </div>
</div>
