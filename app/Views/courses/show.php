<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-mortarboard"></i> <?= htmlspecialchars($course['name']) ?></h2>
    <div>
        <?php if (hasPermission('courses.edit')): ?>
        <a href="/store/courses/<?= $course['id'] ?>/edit" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
        <a href="/store/courses" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Course Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Course Code:</strong><br>
                        <?= htmlspecialchars($course['course_code']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Duration:</strong><br>
                        <?= $course['duration_days'] ?> days
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Max Students:</strong><br>
                        <?= $course['max_students'] ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Price:</strong><br>
                        <?= formatCurrency($course['price']) ?>
                    </div>
                </div>
                
                <?php if ($course['description']): ?>
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Description:</strong><br>
                        <?= nl2br(htmlspecialchars($course['description'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($course['prerequisites']): ?>
                <div class="row">
                    <div class="col-12">
                        <strong>Prerequisites:</strong><br>
                        <?= nl2br(htmlspecialchars($course['prerequisites'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Upcoming Schedules</h5>
            </div>
            <div class="card-body">
                <?php if (empty($schedules)): ?>
                <p class="text-muted">No schedules available</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $sched): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($sched['start_date'])) ?></td>
                                <td><?= date('M j, Y', strtotime($sched['end_date'])) ?></td>
                                <td><?= htmlspecialchars($sched['location'] ?? 'TBD') ?></td>
                                <td><span class="badge bg-info"><?= ucfirst($sched['status']) ?></span></td>
                                <td><?= $sched['enrolled_count'] ?> / <?= $sched['max_students'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
