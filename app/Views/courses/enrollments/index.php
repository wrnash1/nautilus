<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-check"></i> Enrollments</h2>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Start Date</th>
                        <th>Status</th>
                        <th>Enrolled</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($enrollments)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No enrollments found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($enrollments as $enr): ?>
                        <tr>
                            <td><?= htmlspecialchars($enr['student_name']) ?></td>
                            <td><?= htmlspecialchars($enr['course_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($enr['start_date'])) ?></td>
                            <td><span class="badge bg-primary"><?= ucfirst($enr['status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($enr['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
