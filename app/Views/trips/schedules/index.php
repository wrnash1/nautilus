<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar"></i> Trip Schedules</h2>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Trip</th>
                        <th>Destination</th>
                        <th>Departure</th>
                        <th>Return</th>
                        <th>Bookings</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No schedules found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($schedules as $sched): ?>
                        <tr>
                            <td><?= htmlspecialchars($sched['trip_name']) ?></td>
                            <td><?= htmlspecialchars($sched['destination']) ?></td>
                            <td><?= date('M j, Y', strtotime($sched['departure_date'])) ?></td>
                            <td><?= date('M j, Y', strtotime($sched['return_date'])) ?></td>
                            <td><?= $sched['booking_count'] ?></td>
                            <td><span class="badge bg-info"><?= ucfirst($sched['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
