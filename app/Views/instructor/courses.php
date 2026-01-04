<?php
/**
 * Instructor Courses List
 */
$pageTitle = 'My Courses';
ob_start();
?>

<div class="instructor-courses">
    <div class="page-header">
        <h1><i class="fas fa-book"></i> My Courses</h1>
        <a href="/instructor" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Active Courses -->
    <section class="course-section">
        <h2><i class="fas fa-play-circle"></i> Active Courses (
            <?= count($activeCourses) ?>)
        </h2>

        <?php if (empty($activeCourses)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No active courses</p>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($activeCourses as $course): ?>
                    <div class="course-card">
                        <div class="course-status <?= $course['status'] ?>">
                            <?= ucfirst($course['status']) ?>
                        </div>
                        <h3>
                            <?= htmlspecialchars($course['course_name']) ?>
                        </h3>
                        <span class="course-code">
                            <?= htmlspecialchars($course['course_code']) ?>
                        </span>

                        <div class="course-details">
                            <div class="detail-row">
                                <i class="fas fa-calendar"></i>
                                <span>
                                    <?= date('M j', strtotime($course['start_date'])) ?> -
                                    <?= date('M j, Y', strtotime($course['end_date'])) ?>
                                </span>
                            </div>
                            <?php if ($course['location']): ?>
                                <div class="detail-row">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>
                                        <?= htmlspecialchars($course['location']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="detail-row">
                                <i class="fas fa-users"></i>
                                <span>
                                    <?= $course['enrolled_count'] ?> /
                                    <?= $course['max_students'] ?> enrolled
                                </span>
                            </div>
                        </div>

                        <div class="course-progress">
                            <div class="progress-bar">
                                <div class="progress-fill"
                                    style="width: <?= $course['enrolled_count'] > 0 ? round(($course['completed_count'] / $course['enrolled_count']) * 100) : 0 ?>%">
                                </div>
                            </div>
                            <span class="progress-text">
                                <?= $course['completed_count'] ?> /
                                <?= $course['enrolled_count'] ?> completed
                            </span>
                        </div>

                        <div class="course-actions">
                            <a href="/instructor/course/<?= $course['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Completed Courses -->
    <section class="course-section">
        <h2><i class="fas fa-check-circle"></i> Completed Courses (
            <?= count($completedCourses) ?>)
        </h2>

        <?php if (empty($completedCourses)): ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <p>No completed courses yet</p>
            </div>
        <?php else: ?>
            <div class="course-table-wrapper">
                <table class="course-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Dates</th>
                            <th>Students</th>
                            <th>Completed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completedCourses as $course): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars($course['course_name']) ?>
                                    </strong>
                                    <span class="course-code">
                                        <?= htmlspecialchars($course['course_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('M j', strtotime($course['start_date'])) ?> -
                                    <?= date('M j, Y', strtotime($course['end_date'])) ?>
                                </td>
                                <td>
                                    <?= $course['enrolled_count'] ?>
                                </td>
                                <td>
                                    <?= $course['completed_count'] ?>
                                </td>
                                <td>
                                    <a href="/instructor/course/<?= $course['id'] ?>" class="btn btn-outline btn-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<style>
    .instructor-courses {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .course-section {
        margin-bottom: 3rem;
    }

    .course-section h2 {
        font-size: 1.25rem;
        color: var(--text-secondary, #666);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Course Grid */
    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    .course-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        position: relative;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .course-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .course-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .course-status.scheduled {
        background: #e3f2fd;
        color: #1565c0;
    }

    .course-status.in_progress {
        background: #fff3e0;
        color: #e65100;
    }

    .course-card h3 {
        margin: 0 0 0.5rem;
        font-size: 1.2rem;
        padding-right: 80px;
    }

    .course-code {
        display: inline-block;
        background: #f0f0f0;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        color: #666;
    }

    .course-details {
        margin: 1.25rem 0;
    }

    .detail-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        color: var(--text-secondary, #666);
        font-size: 0.9rem;
    }

    .detail-row i {
        width: 16px;
        color: #999;
    }

    .course-progress {
        margin: 1rem 0;
    }

    .progress-bar {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #38ef7d, #11998e);
        border-radius: 4px;
        transition: width 0.3s;
    }

    .progress-text {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.8rem;
        color: #666;
    }

    .course-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.5rem;
    }

    /* Course Table */
    .course-table-wrapper {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .course-table {
        width: 100%;
        border-collapse: collapse;
    }

    .course-table th,
    .course-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .course-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: var(--text-secondary, #666);
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .course-table tbody tr:hover {
        background: #f8f9fa;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 12px;
        color: var(--text-secondary, #666);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Buttons - reuse from dashboard */
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
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>