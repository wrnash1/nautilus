<?php
// Start output buffering for content
ob_start();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4">Contact Us</h1>

            <div class="row mb-5">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="bi bi-telephone fs-1 text-primary mb-3"></i>
                            <h3 class="h5">Call Us</h3>
                            <?php if ($contactPhone): ?>
                            <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $contactPhone)) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($contactPhone) ?>
                            </a>
                            <?php else: ?>
                            <p class="text-muted">Phone number coming soon</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="bi bi-envelope fs-1 text-primary mb-3"></i>
                            <h3 class="h5">Email Us</h3>
                            <?php if ($contactEmail): ?>
                            <a href="mailto:<?= htmlspecialchars($contactEmail) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($contactEmail) ?>
                            </a>
                            <?php else: ?>
                            <p class="text-muted">Email coming soon</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="bi bi-geo-alt fs-1 text-primary mb-3"></i>
                            <h3 class="h5">Visit Us</h3>
                            <?php if ($storeAddress): ?>
                            <p><?= nl2br(htmlspecialchars($storeAddress)) ?></p>
                            <?php else: ?>
                            <p class="text-muted">Address coming soon</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="h4 mb-4">Send Us a Message</h2>

                    <?php if (isset($_SESSION['contact_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['contact_success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['contact_success']); endif; ?>

                    <?php if (isset($_SESSION['contact_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['contact_error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['contact_error']); endif; ?>

                    <form method="POST" action="/contact/submit">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select a subject...</option>
                                <option value="general">General Inquiry</option>
                                <option value="equipment">Equipment Question</option>
                                <option value="courses">Course Information</option>
                                <option value="trips">Dive Trip Information</option>
                                <option value="rental">Equipment Rental</option>
                                <option value="service">Equipment Service</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-center text-muted">
                    <small>We typically respond within 24 hours during business days.</small>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// Capture content
$content = ob_get_clean();

// Set page variables
$pageTitle = 'Contact Us - ' . ($settings->get('store_name', 'Nautilus Dive Shop'));
$metaDescription = 'Contact ' . ($settings->get('store_name', 'Nautilus Dive Shop')) . ' for equipment, training, and dive trip information.';

// Include layout
include __DIR__ . '/layouts/main.php';
?>
