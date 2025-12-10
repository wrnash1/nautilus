<?php
$pageTitle = 'Contact Us';
$company = getCompanyInfo();
ob_start();
?>

<!-- Contact Header -->
<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-12 text-center">
            <h1>Contact Us</h1>
            <p class="text-muted">We'd love to hear from you!</p>
        </div>
    </div>
</div>

<!-- Contact Information & Form -->
<div class="container mb-5">
    <div class="row">
        <!-- Contact Info -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title mb-4">Get in Touch</h4>
                    
                    <?php if ($company['address']): ?>
                    <div class="mb-3">
                        <h6><i class="bi bi-geo-alt-fill text-primary"></i> Address</h6>
                        <p class="ms-4">
                            <?= htmlspecialchars($company['address']) ?><br>
                            <?= htmlspecialchars($company['city']) ?>, <?= htmlspecialchars($company['state']) ?> <?= htmlspecialchars($company['zip']) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($company['phone']): ?>
                    <div class="mb-3">
                        <h6><i class="bi bi-telephone-fill text-primary"></i> Phone</h6>
                        <p class="ms-4">
                            <a href="tel:<?= htmlspecialchars($company['phone']) ?>"><?= htmlspecialchars($company['phone']) ?></a>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($company['email']): ?>
                    <div class="mb-3">
                        <h6><i class="bi bi-envelope-fill text-primary"></i> Email</h6>
                        <p class="ms-4">
                            <a href="mailto:<?= htmlspecialchars($company['email']) ?>"><?= htmlspecialchars($company['email']) ?></a>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <h6><i class="bi bi-clock-fill text-primary"></i> Hours</h6>
                        <p class="ms-4">
                            Monday - Friday: 9:00 AM - 6:00 PM<br>
                            Saturday: 10:00 AM - 4:00 PM<br>
                            Sunday: Closed
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Send Us a Message</h4>
                    
                    <form method="POST" action="/contact">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <select class="form-select" id="subject" name="subject">
                                <option value="general">General Inquiry</option>
                                <option value="courses">Course Information</option>
                                <option value="trips">Trip Information</option>
                                <option value="equipment">Equipment Sales</option>
                                <option value="rentals">Equipment Rentals</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="container mb-5">
    <h3 class="text-center mb-4">Frequently Asked Questions</h3>
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Do I need to be certified to dive?</h5>
                    <p class="card-text">For our beginner courses, no certification is required. We offer Open Water certification courses for those new to diving.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">What should I bring?</h5>
                    <p class="card-text">We provide all necessary equipment. Just bring a swimsuit, towel, and enthusiasm to learn!</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">How do I book a trip?</h5>
                    <p class="card-text">Browse our trips page, select your preferred trip, and contact us to reserve your spot.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Do you offer equipment rentals?</h5>
                    <p class="card-text">Yes! We have a full range of quality dive equipment available for rent.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/public.php';
?>
