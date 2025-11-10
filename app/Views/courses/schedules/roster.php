<?php
$pageTitle = 'Course Roster - ' . ($schedule['course_name'] ?? 'Schedule');
$activeMenu = 'courses';
$user = currentUser();

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/store/courses">Courses</a></li>
            <li class="breadcrumb-item"><a href="/store/courses/schedules">Schedules</a></li>
            <li class="breadcrumb-item"><a href="/store/courses/schedules/<?= $schedule['id'] ?>"><?= htmlspecialchars($schedule['course_name']) ?></a></li>
            <li class="breadcrumb-item active">Roster</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="bi bi-people-fill"></i> Course Roster</h2>
        <a href="/store/courses/schedules/<?= $schedule['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Schedule
        </a>
    </div>
</div>

<!-- Schedule Info Card -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h5><?= htmlspecialchars($schedule['course_name']) ?></h5>
                        <p class="text-muted mb-0"><?= htmlspecialchars($schedule['course_code']) ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Dates</small>
                        <p class="mb-0"><strong><?= date('M j', strtotime($schedule['start_date'])) ?> - <?= date('M j, Y', strtotime($schedule['end_date'])) ?></strong></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Instructor</small>
                        <p class="mb-0"><strong><?= htmlspecialchars($schedule['instructor_name'] ?? 'TBA') ?></strong></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Enrollment</small>
                        <p class="mb-0"><strong><?= $schedule['current_enrollment'] ?> / <?= $schedule['max_students'] ?></strong></p>
                        <?php if ($schedule['current_enrollment'] >= $schedule['max_students']): ?>
                        <span class="badge bg-warning">Full</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Roster Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Student Roster</h5>
            <button class="btn btn-sm btn-primary" onclick="printRoster()">
                <i class="bi bi-printer"></i> Print Roster
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($roster)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No students enrolled yet.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" id="rosterTable">
                <thead>
                    <tr>
                        <th width="50">Photo</th>
                        <th>Student Name</th>
                        <th>Contact</th>
                        <th>Enrolled</th>
                        <th width="150">Requirements</th>
                        <th width="200">Status</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roster as $student): ?>
                    <tr>
                        <td>
                            <?php if (!empty($student['photo_path'])): ?>
                            <img src="<?= htmlspecialchars($student['photo_path']) ?>"
                                 alt="<?= htmlspecialchars($student['student_name']) ?>"
                                 class="rounded-circle"
                                 style="width: 40px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px; font-size: 18px;">
                                <?= strtoupper(substr($student['student_name'], 0, 1)) ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($student['student_name']) ?></strong>
                        </td>
                        <td>
                            <small>
                                <?php if ($student['email']): ?>
                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($student['email']) ?><br>
                                <?php endif; ?>
                                <?php if ($student['phone']): ?>
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($student['phone']) ?>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <small><?= date('M j, Y', strtotime($student['enrollment_date'])) ?></small>
                        </td>
                        <td>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar <?= $student['completion_percentage'] == 100 ? 'bg-success' : 'bg-warning' ?>"
                                     role="progressbar"
                                     style="width: <?= $student['completion_percentage'] ?>%"
                                     aria-valuenow="<?= $student['completion_percentage'] ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <?= $student['completion_percentage'] ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= $student['completed_requirements'] ?> / <?= $student['total_requirements'] ?> complete
                            </small>
                        </td>
                        <td>
                            <?php if ($student['completion_percentage'] == 100): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Ready
                            </span>
                            <?php elseif ($student['completion_percentage'] >= 50): ?>
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> In Progress
                            </span>
                            <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-exclamation-triangle"></i> Pending
                            </span>
                            <?php endif; ?>

                            <?php if ($student['enrollment_status'] == 'enrolled'): ?>
                            <span class="badge bg-primary">Enrolled</span>
                            <?php elseif ($student['enrollment_status'] == 'in_progress'): ?>
                            <span class="badge bg-info">In Progress</span>
                            <?php elseif ($student['enrollment_status'] == 'completed'): ?>
                            <span class="badge bg-success">Completed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary"
                                        onclick="viewRequirements(<?= $student['enrollment_id'] ?>)"
                                        title="View Requirements">
                                    <i class="bi bi-list-check"></i>
                                </button>
                                <a href="/store/courses/enrollments/<?= $student['enrollment_id'] ?>"
                                   class="btn btn-outline-secondary"
                                   title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Requirements Modal -->
<div class="modal fade" id="requirementsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Requirements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requirementsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewRequirements(enrollmentId) {
    const modal = new bootstrap.Modal(document.getElementById('requirementsModal'));
    modal.show();

    fetch(`/api/courses/enrollments/${enrollmentId}/requirements`)
        .then(response => response.json())
        .then(data => {
            let html = `
                <div class="mb-3">
                    <h6>${data.student_name}</h6>
                    <p class="text-muted">${data.course_name}</p>
                </div>
                <div class="list-group">
            `;

            data.requirements.forEach(req => {
                const icon = req.is_completed ?
                    '<i class="bi bi-check-circle-fill text-success"></i>' :
                    '<i class="bi bi-circle text-muted"></i>';

                const badge = req.is_mandatory ?
                    '<span class="badge bg-danger">Required</span>' :
                    '<span class="badge bg-secondary">Optional</span>';

                const status = req.is_completed ?
                    `<small class="text-success">Completed ${req.completed_at ? new Date(req.completed_at).toLocaleDateString() : ''}</small>` :
                    `<small class="text-warning">Pending</small>`;

                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                ${icon}
                                <strong class="ms-2">${req.requirement_name}</strong>
                                ${badge}
                                <br>
                                <small class="text-muted ms-4">${req.description || ''}</small>
                                <br>
                                <span class="ms-4">${status}</span>
                            </div>
                            ${!req.is_completed ? `
                                <button class="btn btn-sm btn-success" onclick="markComplete(${req.id})">
                                    <i class="bi bi-check"></i> Mark Complete
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            document.getElementById('requirementsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('requirementsContent').innerHTML = `
                <div class="alert alert-danger">
                    Failed to load requirements. Please try again.
                </div>
            `;
        });
}

function markComplete(requirementId) {
    if (!confirm('Mark this requirement as completed?')) {
        return;
    }

    fetch(`/api/courses/requirements/${requirementId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update requirement');
        }
    });
}

function printRoster() {
    window.print();
}

// Print styles
const printStyles = `
    @media print {
        .btn, .modal, nav, .breadcrumb, .card-header button {
            display: none !important;
        }
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
    }
`;
const styleSheet = document.createElement("style");
styleSheet.textContent = printStyles;
document.head.appendChild(styleSheet);
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
