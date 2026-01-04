<?php $this->layout('layouts/admin', ['title' => $title ?? 'Skill Sign-offs']) ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-check me-2"></i>Skill Sign-offs</h2>
        <a href="/store/instructor/skills/session" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Session
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($sessions ?? [])): ?>
            <div class="text-center py-5">
                <i class="bi bi-clipboard-check display-1 text-muted"></i>
                <p class="mt-3 text-muted">No skill sign-off sessions yet</p>
                <a href="/store/instructor/skills/session" class="btn btn-primary">Start First Session</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Skills Completed</th>
                            <th>Instructor</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($session['date'])) ?></td>
                            <td><?= htmlspecialchars($session['student_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($session['course_name'] ?? '-') ?></td>
                            <td><?= $session['skills_completed'] ?? 0 ?> / <?= $session['total_skills'] ?? 0 ?></td>
                            <td><?= htmlspecialchars($session['instructor_name'] ?? '-') ?></td>
                            <td>
                                <a href="/store/instructor/skills/<?= $session['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
