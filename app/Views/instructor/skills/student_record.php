<?php $this->layout('layouts/admin', ['title' => $title ?? 'Student Record']) ?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/store/instructor/skills">Skills</a></li>
            <li class="breadcrumb-item active">Student Record</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-badge me-2"></i>Student Skill Record</h2>
        <a href="/store/instructor/skills" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <?php if (isset($student)): ?>
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <i class="bi bi-person-circle display-1 text-muted"></i>
                    <h4 class="mt-3"><?= htmlspecialchars($student['name'] ?? 'Student') ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($student['email'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Skill Progress</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($skills ?? [])): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Skill</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Instructor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($skills as $skill): ?>
                            <tr>
                                <td><?= htmlspecialchars($skill['name']) ?></td>
                                <td>
                                    <?php if ($skill['completed']): ?>
                                    <span class="badge bg-success">Completed</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $skill['completed_date'] ? date('M j, Y', strtotime($skill['completed_date'])) : '-' ?></td>
                                <td><?= htmlspecialchars($skill['instructor'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-muted">No skills recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">Student not found.</div>
    <?php endif; ?>
</div>
