<?php
// Course Schedule Roster View
// Shows complete student roster with transfer capabilities
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-calendar-check"></i> Course Roster</h2>
        <p class="text-muted mb-0"><?= htmlspecialchars($schedule['course_name']) ?></p>
    </div>
    <div>
        <a href="<?= url('/store/courses/schedules') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Schedules
        </a>
    </div>
</div>

<!-- Schedule Information Card -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Schedule Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar3"></i> Dates:</strong><br>
                            <?= date('l, F j, Y', strtotime($schedule['start_date'])) ?><br>
                            to <?= date('l, F j, Y', strtotime($schedule['end_date'])) ?>
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-clock"></i> Time:</strong><br>
                            <?= $schedule['start_time'] ? date('g:i A', strtotime($schedule['start_time'])) : 'TBD' ?>
                            -
                            <?= $schedule['end_time'] ? date('g:i A', strtotime($schedule['end_time'])) : 'TBD' ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-person-badge"></i> Instructor:</strong><br>
                            <?= htmlspecialchars($schedule['instructor_name']) ?>
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-geo-alt"></i> Location:</strong><br>
                            <?= htmlspecialchars($schedule['location'] ?: 'Location TBD') ?>
                        </p>
                        <p class="mb-0">
                            <strong><i class="bi bi-tag"></i> Course Code:</strong>
                            <span class="badge bg-secondary"><?= htmlspecialchars($schedule['course_code']) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Statistics -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-people-fill"></i> Enrollment</h5>
            </div>
            <div class="card-body text-center">
                <h1 class="display-4 mb-0"><?= $schedule['current_enrollment'] ?> / <?= $schedule['max_students'] ?></h1>
                <p class="text-muted mb-0">Students Enrolled</p>

                <?php
                $availableSpots = $schedule['max_students'] - $schedule['current_enrollment'];
                $percentFull = ($schedule['current_enrollment'] / $schedule['max_students']) * 100;
                ?>

                <div class="progress mt-3" style="height: 25px;">
                    <div class="progress-bar bg-<?= $percentFull >= 100 ? 'danger' : ($percentFull >= 80 ? 'warning' : 'success') ?>"
                         style="width: <?= $percentFull ?>%">
                        <?= round($percentFull) ?>%
                    </div>
                </div>

                <p class="mt-2 mb-0">
                    <span class="badge bg-<?= $availableSpots > 0 ? 'success' : 'danger' ?>">
                        <?= $availableSpots ?> spot<?= $availableSpots != 1 ? 's' : '' ?> available
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Student Roster Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list-check"></i> Student Roster</h5>
        <div>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Roster
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="exportRoster()">
                <i class="bi bi-file-earmark-excel"></i> Export
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($roster)): ?>
        <div class="text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3">No students enrolled yet.</p>
            <p class="small text-muted">Students will appear here when they purchase this course at the POS.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="30">#</th>
                        <th>Student Name</th>
                        <th>Contact Information</th>
                        <th>Emergency Contact</th>
                        <th>Certifications</th>
                        <th width="120">Payment</th>
                        <th width="120">Status</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roster as $index => $student): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></strong>
                            <br>
                            <small class="text-muted">
                                Enrolled: <?= date('M j, Y', strtotime($student['enrollment_date'])) ?>
                            </small>
                        </td>
                        <td>
                            <div class="small">
                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($student['email'] ?: 'N/A') ?><br>
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($student['phone'] ?: 'N/A') ?>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <?php if ($student['emergency_contact_name']): ?>
                                <strong><?= htmlspecialchars($student['emergency_contact_name']) ?></strong><br>
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($student['emergency_contact_phone']) ?>
                                <?php else: ?>
                                <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($student['certifications']): ?>
                            <div class="small">
                                <?php
                                $certs = explode(', ', $student['certifications']);
                                foreach ($certs as $cert):
                                ?>
                                <span class="badge bg-info mb-1"><?= htmlspecialchars($cert) ?></span><br>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <span class="badge bg-secondary">Beginner</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $paymentColors = [
                                'paid' => 'success',
                                'partial' => 'warning',
                                'pending' => 'danger',
                                'refunded' => 'secondary'
                            ];
                            $paymentColor = $paymentColors[$student['payment_status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $paymentColor ?>">
                                <?= strtoupper($student['payment_status']) ?>
                            </span>
                            <div class="small text-muted mt-1">
                                $<?= number_format($student['amount_paid'], 2) ?>
                            </div>
                        </td>
                        <td>
                            <?php
                            $statusColors = [
                                'enrolled' => 'primary',
                                'in_progress' => 'info',
                                'completed' => 'success',
                                'dropped' => 'danger',
                                'failed' => 'danger'
                            ];
                            $statusColor = $statusColors[$student['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $statusColor ?>">
                                <?= ucfirst($student['status']) ?>
                            </span>
                            <?php if ($student['final_grade']): ?>
                            <div class="small text-muted mt-1">
                                Grade: <?= htmlspecialchars($student['final_grade']) ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('/store/customers/' . $student['customer_id']) ?>"
                                   class="btn btn-outline-info"
                                   title="View Customer">
                                    <i class="bi bi-person"></i>
                                </a>
                                <?php if (hasPermission('courses.edit') && $student['status'] === 'enrolled'): ?>
                                <button type="button"
                                        class="btn btn-outline-warning"
                                        onclick="showTransferModal(<?= $student['enrollment_id'] ?>, '<?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>')"
                                        title="Transfer to Different Schedule">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                                <?php endif; ?>
                                <button type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="viewEnrollmentDetails(<?= $student['enrollment_id'] ?>)"
                                        title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
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

<!-- Transfer Student Modal -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-left-right"></i> Transfer Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferForm">
                <div class="modal-body">
                    <input type="hidden" id="transfer_enrollment_id" name="enrollment_id">

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Transferring: <strong id="transfer_student_name"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select New Schedule *</label>
                        <select class="form-select" id="new_schedule_id" name="new_schedule_id" required>
                            <option value="">Choose a schedule...</option>
                            <?php foreach ($availableSchedules as $availSchedule): ?>
                            <?php if ($availSchedule['id'] != $schedule['id']): ?>
                            <option value="<?= $availSchedule['id'] ?>">
                                <?= date('M j', strtotime($availSchedule['start_date'])) ?>
                                - <?= date('M j, Y', strtotime($availSchedule['end_date'])) ?>
                                | Instructor: <?= htmlspecialchars($availSchedule['instructor_name']) ?>
                                | <?= $availSchedule['available_spots'] ?> spots available
                            </option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($availableSchedules) || count($availableSchedules) <= 1): ?>
                        <div class="text-danger small mt-1">
                            No other schedules available for this course. Please create a new schedule first.
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Transfer *</label>
                        <select class="form-select mb-2" id="reason_preset" onchange="setTransferReason(this.value)">
                            <option value="">Select a reason or type custom...</option>
                            <option value="Schedule conflict">Schedule conflict</option>
                            <option value="Student request">Student request</option>
                            <option value="Instructor change">Instructor change</option>
                            <option value="Location preference">Location preference</option>
                            <option value="Medical/personal reasons">Medical/personal reasons</option>
                            <option value="custom">Custom reason...</option>
                        </select>
                        <textarea class="form-control"
                                  id="reason"
                                  name="reason"
                                  rows="3"
                                  placeholder="Enter reason for transfer..."
                                  required></textarea>
                        <div class="form-text">
                            This will be recorded in the student's enrollment history.
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Note:</strong> The student's payment information and enrollment status will be preserved.
                        Only the schedule will change.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Confirm Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Transfer Student Modal
function showTransferModal(enrollmentId, studentName) {
    document.getElementById('transfer_enrollment_id').value = enrollmentId;
    document.getElementById('transfer_student_name').textContent = studentName;
    document.getElementById('new_schedule_id').value = '';
    document.getElementById('reason').value = '';
    document.getElementById('reason_preset').value = '';

    const modal = new bootstrap.Modal(document.getElementById('transferModal'));
    modal.show();
}

function setTransferReason(value) {
    const reasonTextarea = document.getElementById('reason');
    if (value === 'custom') {
        reasonTextarea.value = '';
        reasonTextarea.focus();
    } else if (value) {
        reasonTextarea.value = value;
    }
}

// Handle transfer form submission
document.getElementById('transferForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('<?= url('/store/courses/transfer-student') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Student transferred successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Transfer failed'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during transfer. Please try again.');
    });
});

// View enrollment details
function viewEnrollmentDetails(enrollmentId) {
    window.location.href = '<?= url('/store/courses/enrollments/') ?>' + enrollmentId;
}

// Export roster to CSV
function exportRoster() {
    const table = document.querySelector('table');
    let csv = [];

    // Headers
    csv.push(['#', 'Name', 'Email', 'Phone', 'Emergency Contact', 'Emergency Phone', 'Certifications', 'Payment Status', 'Amount Paid', 'Status'].join(','));

    // Data rows
    <?php foreach ($roster as $index => $student): ?>
    csv.push([
        '<?= $index + 1 ?>',
        '<?= addslashes($student['first_name'] . ' ' . $student['last_name']) ?>',
        '<?= addslashes($student['email'] ?: '') ?>',
        '<?= addslashes($student['phone'] ?: '') ?>',
        '<?= addslashes($student['emergency_contact_name'] ?: '') ?>',
        '<?= addslashes($student['emergency_contact_phone'] ?: '') ?>',
        '<?= addslashes($student['certifications'] ?: 'None') ?>',
        '<?= $student['payment_status'] ?>',
        '<?= $student['amount_paid'] ?>',
        '<?= $student['status'] ?>'
    ].join(','));
    <?php endforeach; ?>

    // Create download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'course_roster_<?= date('Y-m-d') ?>.csv';
    a.click();
}

// Print styles
const style = document.createElement('style');
style.textContent = `
    @media print {
        .btn, .card-header button { display: none !important; }
        .card { border: 1px solid #000 !important; }
    }
`;
document.head.appendChild(style);
</script>
