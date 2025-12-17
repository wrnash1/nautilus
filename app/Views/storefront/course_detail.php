<?php ob_start(); ?>

<section class="py-5 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="/courses">Courses</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($course['name']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- Left Column: Image & Details -->
            <div class="col-lg-8">
                <?php 
                $img = !empty($course['image_url']) ? $course['image_url'] : 'https://images.unsplash.com/photo-1544551763-46a8723ba1f9?auto=format&fit=crop&q=80&w=1200'; 
                ?>
                <img src="<?= htmlspecialchars($img) ?>" class="img-fluid rounded-3 shadow-sm mb-4" alt="<?= htmlspecialchars($course['name']) ?>">
                
                <h1 class="display-5 fw-bold font-heading mb-3"><?= htmlspecialchars($course['name']) ?></h1>
                
                <!-- Course Details Badges -->
                <div class="d-flex gap-3 mb-4">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                        <i class="fas fa-clock me-2"></i><?= htmlspecialchars($course['duration_days']) ?> Days
                    </span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                        <i class="fas fa-users me-2"></i>Max <?= htmlspecialchars($course['max_students']) ?> Students
                    </span>
                     <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2">
                        <i class="fas fa-tag me-2"></i><?= htmlspecialchars($course['course_code']) ?>
                    </span>
                </div>

                <div class="prose">
                    <h3 class="h5 fw-bold font-heading">About This Course</h3>
                    <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                    
                    <h3 class="h5 fw-bold font-heading mt-4">Prerequisites</h3>
                    <ul class="list-unstyled">
                        <li class="d-flex align-items-center mb-2">
                             <i class="fas fa-check-circle text-success me-2"></i> 10+ Years Old
                        </li>
                        <li class="d-flex align-items-center mb-2">
                             <i class="fas fa-check-circle text-success me-2"></i> Adequate Swimming Skills
                        </li>
                        <li class="d-flex align-items-center mb-2">
                             <i class="fas fa-check-circle text-success me-2"></i> Good Physical Health
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right Column: Booking Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                    <div class="card-body p-4">
                        <h3 class="h2 fw-bold text-primary mb-1">$<?= number_format($course['price'], 2) ?></h3>
                        <p class="text-muted small mb-4">per person</p>

                        <h4 class="h6 fw-bold mb-3">Upcoming Schedules</h4>
                        <?php if (empty($schedules)): ?>
                            <div class="alert alert-warning small">
                                <i class="fas fa-calendar-times me-2"></i> No dates scheduled currently.
                            </div>
                        <?php else: ?>
                            <div class="list-group mb-4">
                                <?php foreach ($schedules as $schedule): ?>
                                <label class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                    <input class="form-check-input flex-shrink-0" type="radio" name="schedule_id" value="<?= $schedule['id'] ?>">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?= date('M d', strtotime($schedule['start_date'])) ?> - <?= date('M d, Y', strtotime($schedule['end_date'])) ?></div>
                                        <div class="small text-muted">Instr: <?= htmlspecialchars($schedule['instructor_name'] ?? 'TBD') ?></div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button onclick="alert('Enrollment Successful! Welcome aboard.')" class="btn btn-primary btn-lg rounded-pill btn-modern">
                                Enroll Now
                            </button>
                            <a href="/courses" class="btn btn-outline-secondary rounded-pill">Back to Courses</a>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top text-center">
                            <small class="text-muted d-block mb-2">Need help?</small>
                            <a href="/contact" class="text-decoration-none fw-bold">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
