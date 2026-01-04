<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 bg-primary text-white"
    style="background: linear-gradient(135deg, var(--primary-700) 0%, var(--primary-900) 100%);">
    <div class="container position-relative z-1 text-center">
        <h1 class="display-4 fw-bold font-heading mb-3">Our Services</h1>
        <p class="lead text-light opacity-75 mb-4">Professional dive shop services for all your underwater needs</p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a href="/" class="text-white">Home</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page">Services</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Services Grid -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Equipment Repair -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                    style="width: 56px; height: 56px;">
                                    <i class="bi bi-tools fs-3"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h5 fw-bold mb-0">Equipment Repair</h3>
                            </div>
                        </div>
                        <p class="text-muted mb-3">Professional servicing and repair for all your scuba equipment
                            including regulators, BCDs, and more.</p>
                        <a href="/services/repair" class="btn btn-outline-primary btn-sm">Learn More</a>
                        <a href="#" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal"
                            data-bs-target="#serviceTicketModal">Request Service</a>
                    </div>
                </div>
            </div>

            <!-- Air Fills -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                    style="width: 56px; height: 56px;">
                                    <i class="bi bi-wind fs-3"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h5 fw-bold mb-0">Air Fills</h3>
                            </div>
                        </div>
                        <p class="text-muted mb-3">Air and Nitrox fills available. Fast, reliable service with certified
                            compressors.</p>
                        <a href="/services/fills" class="btn btn-outline-primary btn-sm">Learn More</a>
                    </div>
                </div>
            </div>

            <!-- Equipment Rental -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                    style="width: 56px; height: 56px;">
                                    <i class="bi bi-box-seam fs-3"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h5 fw-bold mb-0">Equipment Rental</h3>
                            </div>
                        </div>
                        <p class="text-muted mb-3">Full range of rental equipment including BCDs, regulators, wetsuits,
                            fins, and masks.</p>
                        <a href="/rentals" class="btn btn-outline-primary btn-sm">View Rentals</a>
                        <a href="#" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal"
                            data-bs-target="#rentalRequestModal">Request Rental</a>
                    </div>
                </div>
            </div>

            <!-- Training & Certification -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                    style="width: 56px; height: 56px;">
                                    <i class="bi bi-award fs-3"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h5 fw-bold mb-0">Training & Certification</h3>
                            </div>
                        </div>
                        <p class="text-muted mb-3">PADI certified courses from beginner to professional level.</p>
                        <a href="/courses" class="btn btn-outline-primary btn-sm">View Courses</a>
                    </div>
                </div>
            </div>

            <!-- Dive Trips -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                    style="width: 56px; height: 56px;">
                                    <i class="bi bi-geo-alt fs-3"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h5 fw-bold mb-0">Dive Trips</h3>
                            </div>
                        </div>
                        <p class="text-muted mb-3">Guided dive trips to amazing locations around the world.</p>
                        <a href="/trips" class="btn btn-outline-primary btn-sm">View Trips</a>
                    </div>
                </div>
            </div>

            <!-- Retail Shop -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                    style="width: 56px; height: 56px;">
                                    <i class="bi bi-shop fs-3"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h5 fw-bold mb-0">Retail Shop</h3>
                            </div>
                        </div>
                        <p class="text-muted mb-3">Full line of diving equipment, accessories, and apparel.</p>
                        <a href="/shop" class="btn btn-outline-primary btn-sm">Browse Shop</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Request Modal -->
<div class="modal fade" id="serviceTicketModal" tabindex="-1" aria-labelledby="serviceTicketModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceTicketModalLabel">Request Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/api/service-tickets" method="POST">
                    <div class="mb-3">
                        <label for="service_name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="service_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="service_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="service_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="service_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="service_phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="service_type" class="form-label">Service Type</label>
                        <select class="form-select" id="service_type" name="service_type" required>
                            <option value="">Select service...</option>
                            <option value="regulator">Regulator Service</option>
                            <option value="bcd">BCD Service</option>
                            <option value="wetsuit">Wetsuit Repair</option>
                            <option value="other">Other Equipment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="service_description" class="form-label">Description</label>
                        <textarea class="form-control" id="service_description" name="description" rows="3"
                            required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rental Request Modal -->
<div class="modal fade" id="rentalRequestModal" tabindex="-1" aria-labelledby="rentalRequestModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rentalRequestModalLabel">Request Equipment Rental</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/api/rental-requests" method="POST">
                    <div class="mb-3">
                        <label for="rental_name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="rental_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="rental_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="rental_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="rental_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="rental_phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="rental_dates" class="form-label">Rental Dates</label>
                        <input type="text" class="form-control" id="rental_dates" name="dates"
                            placeholder="e.g., Jan 15-17, 2026" required>
                    </div>
                    <div class="mb-3">
                        <label for="rental_equipment" class="form-label">Equipment Needed</label>
                        <textarea class="form-control" id="rental_equipment" name="equipment" rows="3"
                            placeholder="List equipment you need (BCD, regulator, wetsuit, etc.)" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>