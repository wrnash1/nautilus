<?php
/**
 * Course Detail View for Instructors
 * Shows enrolled students and actions
 */
?>

<div class="course-detail">
    <div class="page-header">
        <div class="header-info">
            <a href="/instructor/courses" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to My Courses
            </a>
            <h1>
                <?= htmlspecialchars($schedule['course_name']) ?>
            </h1>
            <span class="course-code">
                <?= htmlspecialchars($schedule['course_code']) ?>
            </span>
        </div>
    </div>

    <!-- Course Info Card -->
    <div class="info-card">
        <div class="info-grid">
            <div class="info-item">
                <i class="fas fa-calendar"></i>
                <div>
                    <label>Dates</label>
                    <span>
                        <?= date('M j', strtotime($schedule['start_date'])) ?> -
                        <?= date('M j, Y', strtotime($schedule['end_date'])) ?>
                    </span>
                </div>
            </div>
            <?php if ($schedule['location']): ?>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <label>Location</label>
                        <span>
                            <?= htmlspecialchars($schedule['location']) ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
            <div class="info-item">
                <i class="fas fa-users"></i>
                <div>
                    <label>Enrollment</label>
                    <span>
                        <?= count($students) ?> /
                        <?= $schedule['max_students'] ?> students
                    </span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-info-circle"></i>
                <div>
                    <label>Status</label>
                    <span class="status-badge <?= $schedule['status'] ?>">
                        <?= ucfirst($schedule['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Section -->
    <div class="section">
        <h2><i class="fas fa-user-graduate"></i> Enrolled Students (
            <?= count($students) ?>)
        </h2>

        <?php if (empty($students)): ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <p>No students enrolled yet</p>
            </div>
        <?php else: ?>
            <div class="students-grid">
                <?php foreach ($students as $student): ?>
                    <div class="student-card">
                        <div class="student-header">
                            <div class="avatar">
                                <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                            </div>
                            <div class="student-name">
                                <h3>
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                </h3>
                                <span class="status <?= $student['enrollment_status'] ?>">
                                    <?= ucfirst($student['enrollment_status']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="student-contact">
                            <div><i class="fas fa-envelope"></i>
                                <?= htmlspecialchars($student['email']) ?>
                            </div>
                            <?php if ($student['phone']): ?>
                                <div><i class="fas fa-phone"></i>
                                    <?= htmlspecialchars($student['phone']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="progress-section">
                            <label>Progress</label>
                            <div class="progress-steps">
                                <div
                                    class="step <?= ($student['knowledge_status'] === 'completed') ? 'done' : (($student['knowledge_status'] === 'in_progress') ? 'active' : '') ?>">
                                    <span>K</span>
                                    <small>Knowledge</small>
                                </div>
                                <div
                                    class="step <?= ($student['confined_water_status'] === 'completed') ? 'done' : (($student['confined_water_status'] === 'in_progress') ? 'active' : '') ?>">
                                    <span>CW</span>
                                    <small>Confined</small>
                                </div>
                                <div
                                    class="step <?= ($student['open_water_status'] === 'completed') ? 'done' : (($student['open_water_status'] === 'in_progress') ? 'active' : '') ?>">
                                    <span>OW</span>
                                    <small>Open Water</small>
                                </div>
                            </div>
                        </div>

                        <div class="student-actions">
                            <a href="/instructor/skills/student/<?= $student['enrollment_id'] ?>"
                                class="btn btn-sm btn-primary">
                                <i class="fas fa-clipboard-check"></i> Update Progress
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .course-detail {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .back-link {
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .page-header h1 {
        margin: 0.5rem 0 0.25rem;
        font-size: 1.75rem;
    }

    .course-code {
        background: #e9ecef;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #666;
    }

    .info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .info-item i {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-item label {
        display: block;
        font-size: 0.8rem;
        color: #999;
        text-transform: uppercase;
    }

    .info-item span {
        font-weight: 600;
        color: #333;
    }

    .section {
        margin-top: 2rem;
    }

    .section h2 {
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #333;
    }

    .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .student-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .student-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .student-name h3 {
        margin: 0;
        font-size: 1rem;
    }

    .student-name .status {
        font-size: 0.75rem;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
    }

    .status.enrolled {
        background: #fff3cd;
        color: #856404;
    }

    .status.in_progress {
        background: #cce5ff;
        color: #004085;
    }

    .status.completed {
        background: #d4edda;
        color: #155724;
    }

    .student-contact {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 1rem;
    }

    .student-contact div {
        margin-bottom: 0.25rem;
    }

    .student-contact i {
        width: 16px;
        margin-right: 0.5rem;
        color: #999;
    }

    .progress-section {
        margin: 1rem 0;
    }

    .progress-section label {
        font-size: 0.8rem;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        display: block;
    }

    .progress-steps {
        display: flex;
        gap: 0.75rem;
    }

    .step {
        flex: 1;
        text-align: center;
        padding: 0.5rem;
        background: #f0f0f0;
        border-radius: 8px;
    }

    .step span {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        color: #999;
    }

    .step small {
        display: block;
        font-size: 0.7rem;
        color: #999;
    }

    .step.active {
        background: #cce5ff;
    }

    .step.active span {
        color: #004085;
    }

    .step.done {
        background: linear-gradient(135deg, #38ef7d, #11998e);
    }

    .step.done span,
    .step.done small {
        color: white;
    }

    .student-actions {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-badge.scheduled {
        background: #e3f2fd;
        color: #1565c0;
    }

    .status-badge.in_progress {
        background: #fff3e0;
        color: #e65100;
    }

    .status-badge.completed {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 12px;
        color: #999;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .btn-sm {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }
</style>