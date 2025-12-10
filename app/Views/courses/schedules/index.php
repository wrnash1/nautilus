<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar"></i> Course Schedules</h2>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Location</th>
                        <th>Instructor</th>
                        <th>Enrolled</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No schedules found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($schedules as $sched): ?>
                        <tr>
                            <td><?= htmlspecialchars($sched['course_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($sched['start_date'])) ?></td>
                            <td><?= date('M j, Y', strtotime($sched['end_date'])) ?></td>
                            <td><?= htmlspecialchars($sched['location'] ?? 'TBD') ?></td>
                            <td><?= htmlspecialchars($sched['instructor_name'] ?? 'TBD') ?></td>
                            <td><?= $sched['enrolled_count'] ?></td>
                            <td><span class="badge bg-info"><?= ucfirst($sched['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
