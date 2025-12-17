<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 bg-primary text-white" style="background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 100%);">
    <div class="container position-relative z-1 text-center">
        <h1 class="display-4 fw-bold font-heading mb-3">Start Your Adventure</h1>
        <p class="lead text-light opacity-75 mb-0">Explore the underwater world with our professional diving courses.</p>
    </div>
    <!-- Decorative wave SVG bottom -->
    <div class="position-absolute bottom-0 start-0 w-100 overflow-hidden" style="height: 60px;">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="width: 100%; height: 100%; transform: rotate(180deg);">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="var(--body-bg)"></path>
        </svg>
    </div>
</section>

<!-- Courses Grid -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            
            <?php if (empty($courses)): ?>
                <div class="col-12 text-center py-5">
                    <p class="lead text-muted">No courses available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm hover-lift transition-base">
                        <?php 
                        $img = !empty($course['image_url']) ? $course['image_url'] : 'https://images.unsplash.com/photo-1544551763-46a8723ba1f9?auto=format&fit=crop&q=80&w=800'; 
                        ?>
                        <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($course['name']) ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column p-4">
                            <h3 class="h5 font-heading fw-bold mb-2"><?= htmlspecialchars($course['name']) ?></h3>
                            <p class="text-muted small flex-grow-1"><?= htmlspecialchars(mb_strimwidth($course['description'] ?? '', 0, 100, '...')) ?></p>
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <span class="h5 text-primary mb-0 fw-bold">$<?= number_format($course['price'] ?? 0, 2) ?></span>
                                <a href="/course/<?= $course['id'] ?>" class="btn btn-primary btn-sm btn-modern rounded-pill px-4">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>
