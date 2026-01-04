<?php
/**
 * Instructor Students List
 * Shows all students across instructor's classes with progress and actions
 */
$pageTitle = 'My Students';
ob_start();

// Session messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="instructor-students">
    <div class="page-header">
        <h1><i class="fas fa-user-graduate"></i> My Students</h1>
        <div class="header-actions">
            <a href="/instructor" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filters-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="studentSearch" placeholder="Search students...">
        </div>
        <div class="filter-group">
            <label>Status:</label>
            <select id="statusFilter">
                <option value="">All</option>
                <option value="enrolled">Enrolled</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    <!-- Students Table -->
    <div class="students-table-wrapper">
        <table class="students-table" id="studentsTable">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Contact</th>
                    <th>Course</th>
                    <th>Progress</th>
                    <th>SMS</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No students found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr data-status="<?= $student['enrollment_status'] ?>">
                            <td class="student-cell">
                                <div class="student-avatar">
                                    <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                                </div>
                                <div class="student-info">
                                    <strong>
                                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </strong>
                                    <span class="status-badge <?= $student['enrollment_status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $student['enrollment_status'])) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="contact-cell">
                                <div><i class="fas fa-envelope"></i>
                                    <?= htmlspecialchars($student['email']) ?>
                                </div>
                                <?php if ($student['phone']): ?>
                                    <div><i class="fas fa-phone"></i>
                                        <?= htmlspecialchars($student['phone']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="course-info">
                                    <strong>
                                        <?= htmlspecialchars($student['course_name']) ?>
                                    </strong>
                                    <small>
                                        <?= date('M j', strtotime($student['start_date'])) ?> -
                                        <?= date('M j', strtotime($student['end_date'])) ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="progress-indicators">
                                    <span
                                        class="progress-step <?= $student['knowledge_status'] === 'completed' ? 'done' : ($student['knowledge_status'] === 'in_progress' ? 'active' : '') ?>"
                                        title="Knowledge Development">K</span>
                                    <span
                                        class="progress-step <?= $student['confined_water_status'] === 'completed' ? 'done' : ($student['confined_water_status'] === 'in_progress' ? 'active' : '') ?>"
                                        title="Confined Water">CW</span>
                                    <span
                                        class="progress-step <?= $student['open_water_status'] === 'completed' ? 'done' : ($student['open_water_status'] === 'in_progress' ? 'active' : '') ?>"
                                        title="Open Water">OW</span>
                                </div>
                            </td>
                            <td>
                                <?php if ($student['sms_opt_in']): ?>
                                    <span class="sms-badge opted-in" title="SMS notifications enabled">
                                        <i class="fas fa-sms"></i> Yes
                                    </span>
                                <?php else: ?>
                                    <span class="sms-badge" title="SMS notifications disabled">
                                        <i class="fas fa-sms"></i> No
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <!-- Email Dropdown -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-primary dropdown-toggle">
                                            <i class="fas fa-envelope"></i> Send Email
                                        </button>
                                        <div class="dropdown-menu">
                                            <form action="/instructor/send-email" method="POST" class="email-form">
                                                <input type="hidden" name="enrollment_id"
                                                    value="<?= $student['enrollment_id'] ?>">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" name="email_type" value="welcome" class="dropdown-item">
                                                    <i class="fas fa-hand-wave"></i> Welcome Email
                                                </button>
                                                <button type="submit" name="email_type" value="reminder" class="dropdown-item">
                                                    <i class="fas fa-bell"></i> Class Reminder
                                                </button>
                                                <button type="submit" name="email_type" value="progress" class="dropdown-item">
                                                    <i class="fas fa-chart-line"></i> Progress Update
                                                </button>
                                                <button type="submit" name="email_type" value="thankyou" class="dropdown-item">
                                                    <i class="fas fa-heart"></i> Thank You
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Progress Link -->
                                    <a href="/instructor/skills/student/<?= $student['enrollment_id'] ?>"
                                        class="btn btn-sm btn-outline" title="Update Progress">
                                        <i class="fas fa-clipboard-check"></i>
                                    </a>

                                    <!-- Transfer Button -->
                                    <button class="btn btn-sm btn-secondary transfer-btn"
                                        data-enrollment="<?= $student['enrollment_id'] ?>"
                                        data-student="<?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>"
                                        title="Transfer to Another Class">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Transfer Modal -->
<div id="transferModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exchange-alt"></i> Transfer Student</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="/store/courses/transfer-student" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="enrollment_id" id="transferEnrollmentId">

            <div class="modal-body">
                <p>Transfer <strong id="transferStudentName"></strong> to:</p>

                <div class="form-group">
                    <label for="new_schedule_id">Select New Class</label>
                    <select name="new_schedule_id" id="new_schedule_id" required>
                        <option value="">-- Select a class --</option>
                        <?php foreach ($availableCourses as $course): ?>
                            <?php if ($course['enrolled_count'] < $course['max_students']): ?>
                                <option value="<?= $course['id'] ?>">
                                    <?= htmlspecialchars($course['course_name']) ?>
                                    (
                                    <?= date('M j', strtotime($course['start_date'])) ?>)
                                    -
                                    <?= $course['enrolled_count'] ?>/
                                    <?= $course['max_students'] ?> spots
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="transfer_reason">Reason (optional)</label>
                    <textarea name="transfer_reason" id="transfer_reason" rows="3"
                        placeholder="Why is this student being transferred?"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary">Transfer Student</button>
            </div>
        </form>
    </div>
</div>

<style>
    .instructor-students {
        padding: 2rem;
        max-width: 1600px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .page-header h1 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    /* Alerts */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Filters */
    .filters-bar {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        min-width: 200px;
        max-width: 400px;
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .search-box input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-group select {
        padding: 0.75rem 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
    }

    /* Table */
    .students-table-wrapper {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .students-table {
        width: 100%;
        border-collapse: collapse;
    }

    .students-table th,
    .students-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .students-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: var(--text-secondary, #666);
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .students-table tbody tr:hover {
        background: #f8f9fa;
    }

    /* Student Cell */
    .student-cell {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .student-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .student-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .status-badge {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.enrolled {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.in_progress {
        background: #cce5ff;
        color: #004085;
    }

    .status-badge.completed {
        background: #d4edda;
        color: #155724;
    }

    /* Contact Cell */
    .contact-cell div {
        font-size: 0.85rem;
        color: var(--text-secondary, #666);
        margin-bottom: 0.25rem;
    }

    .contact-cell i {
        width: 16px;
        margin-right: 0.5rem;
        color: #999;
    }

    /* Course Info */
    .course-info {
        display: flex;
        flex-direction: column;
    }

    .course-info small {
        color: #999;
        font-size: 0.8rem;
    }

    /* Progress Indicators */
    .progress-indicators {
        display: flex;
        gap: 0.5rem;
    }

    .progress-step {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e9ecef;
        color: #999;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .progress-step.active {
        background: #cce5ff;
        color: #004085;
    }

    .progress-step.done {
        background: linear-gradient(135deg, #38ef7d, #11998e);
        color: white;
    }

    /* SMS Badge */
    .sms-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        background: #e9ecef;
        color: #666;
    }

    .sms-badge.opted-in {
        background: #d4edda;
        color: #155724;
    }

    /* Actions */
    .actions-cell {
        white-space: nowrap;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    /* Dropdown */
    .dropdown {
        position: relative;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        min-width: 200px;
        z-index: 100;
        overflow: hidden;
    }

    .dropdown:hover .dropdown-menu {
        display: block;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        font-size: 0.9rem;
        color: #333;
    }

    .dropdown-item:hover {
        background: #f8f9fa;
    }

    .dropdown-item i {
        width: 16px;
        color: #667eea;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
    }

    .modal-header h3 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #999;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .btn-secondary {
        background: #e9ecef;
        color: #495057;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid #667eea;
        color: #667eea;
    }

    .btn-sm {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--text-secondary, #666);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Search functionality
        const searchInput = document.getElementById('studentSearch');
        const statusFilter = document.getElementById('statusFilter');
        const table = document.getElementById('studentsTable');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusTerm = statusFilter.value;
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const status = row.dataset.status;
                const matchesSearch = text.includes(searchTerm);
                const matchesStatus = !statusTerm || status === statusTerm;
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterTable);
        statusFilter.addEventListener('change', filterTable);

        // Transfer modal
        const modal = document.getElementById('transferModal');
        const transferBtns = document.querySelectorAll('.transfer-btn');
        const closeBtns = modal.querySelectorAll('.modal-close');

        transferBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('transferEnrollmentId').value = this.dataset.enrollment;
                document.getElementById('transferStudentName').textContent = this.dataset.student;
                modal.classList.add('active');
            });
        });

        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => modal.classList.remove('active'));
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.classList.remove('active');
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>