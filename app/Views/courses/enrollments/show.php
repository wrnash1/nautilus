<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-check"></i> Enrollment Details</h2>
    <a href="/courses/enrollments" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Student Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Student:</strong> <?= htmlspecialchars($enrollment['student_name']) ?></p>
                <p><strong>Course:</strong> <?= htmlspecialchars($enrollment['course_name']) ?></p>
                <p><strong>Status:</strong> <span class="badge bg-primary"><?= ucfirst($enrollment['status']) ?></span></p>
                <p><strong>Enrollment Date:</strong> <?= date('M j, Y', strtotime($enrollment['enrollment_date'])) ?></p>
                <?php if ($enrollment['completion_date']): ?>
                <p><strong>Completion Date:</strong> <?= date('M j, Y', strtotime($enrollment['completion_date'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Attendance Records</h5>
                <?php if (hasPermission('courses.edit')): ?>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                    <i class="bi bi-plus"></i> Mark Attendance
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($attendance)): ?>
                <p class="text-muted">No attendance records yet.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Session Type</th>
                                <th>Attended</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance as $record): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($record['session_date'])) ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $record['session_type'])) ?></td>
                                <td>
                                    <?php if ($record['attended']): ?>
                                    <span class="badge bg-success">Present</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Absent</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($record['performance_notes'] ?? '') ?></td>
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
        <?php if (hasPermission('courses.edit') && $enrollment['status'] !== 'completed'): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Final Grade</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/courses/enrollments/<?= $enrollment['id'] ?>/grade">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    <div class="mb-3">
                        <label for="grade" class="form-label">Grade</label>
                        <select class="form-select" id="grade" name="grade" required>
                            <option value="A">A - Excellent</option>
                            <option value="B">B - Good</option>
                            <option value="C">C - Satisfactory</option>
                            <option value="D">D - Needs Improvement</option>
                            <option value="F">F - Failed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cert_number" class="form-label">Certification Number</label>
                        <input type="text" class="form-control" id="cert_number" name="cert_number">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Complete Course</button>
                </form>
            </div>
        </div>
        <?php elseif ($enrollment['final_grade']): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Final Grade</h5>
            </div>
            <div class="card-body">
                <h3 class="text-center"><?= htmlspecialchars($enrollment['final_grade']) ?></h3>
                <?php if ($enrollment['certification_number']): ?>
                <p class="text-center"><strong>Cert #:</strong> <?= htmlspecialchars($enrollment['certification_number']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/courses/enrollments/<?= $enrollment['id'] ?>/attendance">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    <div class="mb-3">
                        <label for="session_date" class="form-label">Session Date</label>
                        <input type="date" class="form-control" id="session_date" name="session_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="session_type" class="form-label">Session Type</label>
                        <select class="form-select" id="session_type" name="session_type" required>
                            <option value="classroom">Classroom</option>
                            <option value="pool">Pool</option>
                            <option value="open_water">Open Water</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="attended" name="attended" value="1" checked>
                            <label class="form-check-label" for="attended">Student Attended</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Performance Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
