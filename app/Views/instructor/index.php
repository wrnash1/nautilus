<?php
/**
 * Instructor Dashboard
 * Shows: Today's classes, student count, pending paperwork, upcoming sessions
 */
$pageTitle = 'Instructor Dashboard';
ob_start();
?>

<div class="instructor-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1>Welcome,
                <?= htmlspecialchars($instructor['first_name'] ?? 'Instructor') ?>!
            </h1>
            <p class="date-display">
                <?= date('l, F j, Y') ?>
            </p>
        </div>
        <div class="quick-actions">
            <a href="/instructor/courses" class="btn btn-primary">
                <i class="fas fa-book"></i> My Courses
            </a>
            <a href="/instructor/students" class="btn btn-secondary">
                <i class="fas fa-users"></i> My Students
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon today">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3>
                    <?= count($todayClasses) ?>
                </h3>
                <p>Today's Classes</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon students">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-content">
                <h3>
                    <?= $studentCount ?>
                </h3>
                <p>Active Students</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content">
                <h3>
                    <?= count($pendingPaperwork) ?>
                </h3>
                <p>Pending Paperwork</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="fas fa-certificate"></i>
            </div>
            <div class="stat-content">
                <h3>
                    <?= count($recentCompletions) ?>
                </h3>
                <p>Recent Certifications</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Today's Classes -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-check"></i> Today's Classes</h2>
            </div>
            <div class="card-body">
                <?php if (empty($todayClasses)): ?>
                    <div class="empty-state">
                        <i class="fas fa-coffee"></i>
                        <p>No classes scheduled for today</p>
                    </div>
                <?php else: ?>
                    <div class="class-list">
                        <?php foreach ($todayClasses as $class): ?>
                            <div class="class-item">
                                <div class="class-time">
                                    <i class="fas fa-clock"></i>
                                    <?= $class['start_time'] ? date('g:i A', strtotime($class['start_time'])) : 'TBD' ?>
                                </div>
                                <div class="class-info">
                                    <h4>
                                        <?= htmlspecialchars($class['course_name']) ?>
                                    </h4>
                                    <span class="badge">
                                        <?= htmlspecialchars($class['course_code']) ?>
                                    </span>
                                    <span class="students-count">
                                        <i class="fas fa-users"></i>
                                        <?= $class['enrolled_count'] ?> students
                                    </span>
                                </div>
                                <div class="class-actions">
                                    <a href="/instructor/course/<?= $class['id'] ?>" class="btn btn-sm btn-outline">
                                        View <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Sessions -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-alt"></i> Upcoming Sessions (7 Days)</h2>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingSessions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No upcoming sessions</p>
                    </div>
                <?php else: ?>
                    <div class="upcoming-list">
                        <?php foreach ($upcomingSessions as $session): ?>
                            <div class="upcoming-item">
                                <div class="date-badge">
                                    <span class="day">
                                        <?= date('d', strtotime($session['start_date'])) ?>
                                    </span>
                                    <span class="month">
                                        <?= date('M', strtotime($session['start_date'])) ?>
                                    </span>
                                </div>
                                <div class="session-info">
                                    <h4>
                                        <?= htmlspecialchars($session['course_name']) ?>
                                    </h4>
                                    <p>
                                        <i class="fas fa-users"></i>
                                        <?= $session['enrolled_count'] ?> enrolled
                                        <?php if ($session['location']): ?>
                                            • <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($session['location']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Paperwork -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-file-alt"></i> Pending Paperwork</h2>
            </div>
            <div class="card-body">
                <?php if (empty($pendingPaperwork)): ?>
                    <div class="empty-state success">
                        <i class="fas fa-check-circle"></i>
                        <p>All paperwork is up to date!</p>
                    </div>
                <?php else: ?>
                    <div class="paperwork-list">
                        <?php foreach ($pendingPaperwork as $item): ?>
                            <div class="paperwork-item">
                                <div class="student-avatar">
                                    <?= strtoupper(substr($item['first_name'], 0, 1) . substr($item['last_name'], 0, 1)) ?>
                                </div>
                                <div class="paperwork-info">
                                    <h4>
                                        <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?>
                                    </h4>
                                    <p>
                                        <?= htmlspecialchars($item['course_name']) ?>
                                    </p>
                                </div>
                                <div class="status-badge <?= $item['overall_status'] ?? 'pending' ?>">
                                    <?= ucfirst($item['overall_status'] ?? 'Not Started') ?>
                                </div>
                                <a href="/instructor/skills/student/<?= $item['enrollment_id'] ?>"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Update
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Completions -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-trophy"></i> Recent Certifications</h2>
            </div>
            <div class="card-body">
                <?php if (empty($recentCompletions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-medal"></i>
                        <p>No recent certifications</p>
                    </div>
                <?php else: ?>
                    <div class="completions-list">
                        <?php foreach ($recentCompletions as $completion): ?>
                            <div class="completion-item">
                                <div class="completion-icon">
                                    <i class="fas fa-award"></i>
                                </div>
                                <div class="completion-info">
                                    <h4>
                                        <?= htmlspecialchars($completion['first_name'] . ' ' . $completion['last_name']) ?>
                                    </h4>
                                    <p>
                                        <?= htmlspecialchars($completion['course_name']) ?>
                                    </p>
                                    <small>
                                        <?= date('M j, Y', strtotime($completion['completion_date'])) ?>
                                        <?php if ($completion['certification_number']): ?>
                                            • #
                                            <?= htmlspecialchars($completion['certification_number']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .instructor-dashboard {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .welcome-section h1 {
        font-size: 2rem;
        color: var(--text-primary, #1a1a2e);
        margin: 0;
    }

    .date-display {
        color: var(--text-secondary, #666);
        margin: 0.5rem 0 0;
    }

    .quick-actions {
        display: flex;
        gap: 1rem;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-icon.today {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .stat-icon.students {
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
    }

    .stat-icon.pending {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
    }

    .stat-icon.completed {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        color: white;
    }

    .stat-content h3 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-primary, #1a1a2e);
    }

    .stat-content p {
        margin: 0.25rem 0 0;
        color: var(--text-secondary, #666);
        font-size: 0.9rem;
    }

    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    .dashboard-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #eee;
    }

    .card-header h2 {
        margin: 0;
        font-size: 1.1rem;
        color: var(--text-primary, #1a1a2e);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-header h2 i {
        color: var(--primary, #667eea);
    }

    .card-body {
        padding: 1rem 1.5rem;
        max-height: 350px;
        overflow-y: auto;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--text-secondary, #666);
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }

    .empty-state.success i {
        color: #38ef7d;
        opacity: 1;
    }

    /* Class List */
    .class-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 8px;
        background: #f8f9fa;
        margin-bottom: 0.75rem;
    }

    .class-time {
        background: var(--primary, #667eea);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .class-info {
        flex: 1;
    }

    .class-info h4 {
        margin: 0 0 0.25rem;
        font-size: 1rem;
    }

    .badge {
        display: inline-block;
        background: #e9ecef;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        margin-right: 0.5rem;
    }

    .students-count {
        color: var(--text-secondary, #666);
        font-size: 0.85rem;
    }

    /* Upcoming List */
    .upcoming-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .upcoming-item:last-child {
        border-bottom: none;
    }

    .date-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 0.5rem;
        border-radius: 8px;
        text-align: center;
        min-width: 50px;
    }

    .date-badge .day {
        display: block;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .date-badge .month {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .session-info h4 {
        margin: 0 0 0.25rem;
        font-size: 0.95rem;
    }

    .session-info p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--text-secondary, #666);
    }

    /* Paperwork List */
    .paperwork-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .paperwork-info {
        flex: 1;
    }

    .paperwork-info h4 {
        margin: 0;
        font-size: 0.95rem;
    }

    .paperwork-info p {
        margin: 0.25rem 0 0;
        font-size: 0.8rem;
        color: var(--text-secondary, #666);
    }

    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.pending,
    .status-badge.enrolled {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.in_progress,
    .status-badge.in_training {
        background: #cce5ff;
        color: #004085;
    }

    /* Completions List */
    .completion-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .completion-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f5af19, #f12711);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .completion-info h4 {
        margin: 0;
        font-size: 0.95rem;
    }

    .completion-info p {
        margin: 0.25rem 0;
        font-size: 0.85rem;
        color: var(--text-secondary, #666);
    }

    .completion-info small {
        color: #999;
        font-size: 0.75rem;
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

    .btn-primary:hover {
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: #e9ecef;
        color: #495057;
    }

    .btn-secondary:hover {
        background: #dee2e6;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid #667eea;
        color: #667eea;
    }

    .btn-outline:hover {
        background: #667eea;
        color: white;
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