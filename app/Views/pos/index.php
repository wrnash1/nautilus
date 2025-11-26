<?php
$pageTitle = 'Point of Sale';
$activeMenu = 'pos';
$user = currentUser();

// Add modern POS CSS
$additionalCss = '
<link rel="stylesheet" href="/assets/css/modern-pos.css">
';

ob_start();
?>

<!-- POS Header Bar -->
<div class="pos-header-bar">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center py-2">
            <div class="pos-store-logo">
                <?php
                $logoPath = getSettingValue('store_logo') ?? '/assets/images/nautilus-logo.png';
                $storeName = getSettingValue('store_name') ?? 'Nautilus Dive Shop';
                ?>
                <img src="<?= $logoPath ?>" alt="<?= htmlspecialchars($storeName) ?>" style="height: 50px; max-width: 200px; object-fit: contain;">
            </div>
            <div class="pos-datetime-display">
                <div class="text-end">
                    <div id="posCurrentDate" class="fw-bold" style="font-size: 1.1rem;"></div>
                    <div id="posCurrentTime" class="text-muted" style="font-size: 1.5rem; font-family: 'Courier New', monospace;"></div>
                    <div class="text-danger small mt-1" id="autoLogoutTimer" style="display: none;">
                        <i class="bi bi-hourglass-split"></i> Auto-logout in <span id="logoutCountdown">00:00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Selection Bar (Fixed at Top) -->
<div class="pos-customer-bar">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <label class="customer-label">
                    <i class="bi bi-person-circle"></i> Customer
                </label>
                <div class="customer-select-wrapper">
                    <div class="position-relative" style="flex: 1;">
                        <input type="text" id="customerSearchInput" class="form-control customer-select" placeholder="Search customer or Walk-In..." autocomplete="off" style="padding-right: 2.5rem;" aria-label="Search customer">
                        <button type="button" id="clearCustomerBtn" class="btn btn-sm btn-link position-absolute" style="right: 0.5rem; top: 50%; transform: translateY(-50%); display: none;" aria-label="Clear customer search">
                            <i class="bi bi-x-circle-fill text-muted" aria-hidden="true"></i>
                        </button>
                        <input type="hidden" id="selectedCustomerId" value="">
                        <div id="customerSearchResults" class="search-dropdown"></div>
                    </div>
                    <a href="/store/customers/create?return_to=pos" class="btn btn-success btn-add-customer">
                        <i class="bi bi-person-plus-fill"></i> New
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="customer-info-panel" id="customerInfo" style="display: none;">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div id="customerPhoto" class="customer-photo-container">
                                <i class="bi bi-person-circle" style="font-size: 4rem; color: #6c757d;"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="customer-details">
                                <div class="customer-name fw-bold mb-1" id="customerName"></div>
                                <div class="customer-contact small">
                                    <span class="me-3">
                                        <i class="bi bi-envelope"></i> <span id="customerEmail">-</span>
                                    </span>
                                    <span>
                                        <i class="bi bi-telephone"></i> <span id="customerPhone">-</span>
                                    </span>
                                </div>
                                <div id="customerCertifications" class="customer-certs mt-2">
                                    <!-- Certification badges will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main POS Container -->
<div class="pos-professional-layout">
    <!-- Left Side: Products/Items -->
    <div class="pos-products-panel">
        <!-- Category Tabs -->
        <div class="category-tabs">
            <button class="category-tab active" data-category="all">
                <i class="bi bi-grid-fill"></i> All Items
            </button>
            <button class="category-tab" data-category="gear">
                <i class="bi bi-backpack-fill"></i> Gear
            </button>
            <button class="category-tab" data-category="courses">
                <i class="bi bi-mortarboard-fill"></i> Courses
            </button>
            <button class="category-tab" data-category="trips">
                <i class="bi bi-airplane-fill"></i> Trips
            </button>
            <button class="category-tab" data-category="fills">
                <i class="bi bi-wind"></i> Air Fills
            </button>
            <button class="category-tab" data-category="rentals">
                <i class="bi bi-briefcase-fill"></i> Rentals
            </button>
        </div>

        <!-- Search Bar -->
        <div class="product-search-bar">
            <div class="search-input-group">
                <i class="bi bi-search"></i>
                <input type="text" id="productSearch" class="search-input" placeholder="Search products, courses, or scan barcode..." autocomplete="off" aria-label="Search products">
                <button class="btn btn-primary btn-sm ms-2" id="aiSearchBtn" title="AI Visual Search - Take or upload photo">
                    <i class="bi bi-camera-fill"></i> AI Search
                </button>
                <button class="btn-clear-search" id="clearSearch" style="display: none;" aria-label="Clear product search">
                    <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                </button>
            </div>
            <div id="searchResults" class="search-dropdown"></div>
        </div>

        <!-- Products Grid -->
        <div class="products-grid-pro" id="productGrid">
            <?php foreach ($products as $product): ?>
            <div class="product-tile"
                 data-product-id="<?= $product['id'] ?>"
                 data-product-name="<?= htmlspecialchars($product['name']) ?>"
                 data-product-price="<?= $product['price'] ?>"
                 data-product-sku="<?= htmlspecialchars($product['sku']) ?>"
                 data-category="gear">
                <div class="product-tile-image">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="product-tile-info">
                    <div class="product-tile-name"><?= htmlspecialchars($product['name']) ?></div>
                    <div class="product-tile-price"><?= formatCurrency($product['price']) ?></div>
                </div>
                <?php if ($product['track_inventory'] && $product['quantity_in_stock'] <= $product['low_stock_threshold']): ?>
                <div class="low-stock-indicator">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= $product['quantity_in_stock'] ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <!-- Course Tiles -->
            <?php foreach ($courses as $course): ?>
            <div class="product-tile course-tile"
                 data-category="courses"
                 data-product-id="course_<?= $course['id'] ?>"
                 data-course-id="<?= $course['id'] ?>"
                 data-product-name="<?= htmlspecialchars($course['name']) ?>"
                 data-product-price="<?= $course['price'] ?>"
                 data-product-sku="<?= htmlspecialchars($course['course_code']) ?>">
                <div class="product-tile-image course-item">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="product-tile-info">
                    <div class="product-tile-name"><?= htmlspecialchars($course['name']) ?></div>
                    <div class="product-tile-price"><?= formatCurrency($course['price']) ?></div>
                </div>
                <div class="course-badge">Course</div>
            </div>
            <?php endforeach; ?>

            <!-- Trip Tiles -->
            <?php foreach ($trips as $trip): ?>
            <div class="product-tile trip-tile"
                 data-category="trips"
                 data-product-id="trip_<?= $trip['id'] ?>"
                 data-trip-id="<?= $trip['id'] ?>"
                 data-product-name="<?= htmlspecialchars($trip['name']) ?>"
                 data-product-price="<?= $trip['price'] ?>"
                 data-product-sku="TRIP-<?= $trip['id'] ?>">
                <div class="product-tile-image trip-item">
                    <i class="bi bi-airplane-fill"></i>
                </div>
                <div class="product-tile-info">
                    <div class="product-tile-name"><?= htmlspecialchars($trip['name']) ?></div>
                    <div class="product-tile-price"><?= formatCurrency($trip['price']) ?></div>
                </div>
                <div class="course-badge" style="background: var(--pos-info);">Trip</div>
            </div>
            <?php endforeach; ?>

            <!-- Rental Tiles -->
            <?php foreach ($rentals as $rental): ?>
            <div class="product-tile rental-tile"
                 data-category="rentals"
                 data-product-id="rental_<?= $rental['id'] ?>"
                 data-rental-id="<?= $rental['id'] ?>"
                 data-product-name="<?= htmlspecialchars($rental['name']) ?>"
                 data-product-price="<?= $rental['daily_rate'] ?>"
                 data-product-sku="<?= htmlspecialchars($rental['sku']) ?>">
                <div class="product-tile-image rental-item">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div class="product-tile-info">
                    <div class="product-tile-name"><?= htmlspecialchars($rental['name']) ?></div>
                    <div class="product-tile-price"><?= formatCurrency($rental['daily_rate']) ?>/day</div>
                </div>
                <div class="course-badge" style="background: var(--pos-warning);">Rental</div>
            </div>
            <?php endforeach; ?>

            <!-- Air Fill Tiles -->
            <div class="product-tile" data-category="fills" data-product-id="fill_air" data-product-name="Air Fill" data-product-price="8.00" data-product-sku="FILL-AIR">
                <div class="product-tile-image fill-item">
                    <i class="bi bi-wind"></i>
                </div>
                <div class="product-tile-info">
                    <div class="product-tile-name">Air Fill</div>
                    <div class="product-tile-price">$8.00</div>
                </div>
            </div>

            <div class="product-tile" data-category="fills" data-product-id="fill_nitrox" data-product-name="Nitrox Fill" data-product-price="12.00" data-product-sku="FILL-NITROX">
                <div class="product-tile-image fill-item">
                    <i class="bi bi-wind"></i>
                </div>
                <div class="product-tile-info">
                    <div class="product-tile-name">Nitrox Fill</div>
                    <div class="product-tile-price">$12.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Cart/Checkout -->
    <div class="pos-cart-panel">
        <div class="cart-header">
            <h3><i class="bi bi-cart3"></i> Current Sale</h3>
            <div class="d-flex flex-column align-items-end">
                <span class="cart-item-count" id="cartItemCount">0 items</span>
                <small class="text-muted mt-1" id="posDateTime" style="font-size: 0.75rem;"></small>
            </div>
        </div>

        <div class="cart-items-list" id="cartItemsList">
            <div class="empty-cart-message">
                <i class="bi bi-cart-x"></i>
                <p>No items in cart</p>
                <small>Add products to begin checkout</small>
            </div>
        </div>

        <!-- Discount/Coupon Section -->
        <div class="discount-section" style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="couponCode" placeholder="Enter coupon or promo code" style="text-transform: uppercase;">
                <button class="btn btn-outline-primary" type="button" id="applyCouponBtn">
                    <i class="bi bi-tag-fill"></i> Apply
                </button>
            </div>
            <div id="couponMessage" style="margin-top: 0.5rem; font-size: 0.875rem;"></div>
            <div id="appliedCoupon" style="display: none; margin-top: 0.5rem;">
                <div class="d-flex justify-content-between align-items-center p-2 bg-success bg-opacity-10 rounded">
                    <span class="text-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong id="appliedCouponCode"></strong> applied
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeCouponBtn">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Cart Totals -->
        <div class="cart-totals">
            <div class="total-row subtotal-row">
                <span>Subtotal:</span>
                <span id="cartSubtotal">$0.00</span>
            </div>
            <div class="total-row discount-row" id="discountRow" style="display: none; color: var(--success);">
                <span>Discount:</span>
                <span id="cartDiscount">-$0.00</span>
            </div>
            <div class="total-row tax-row">
                <span>Tax (8%):</span>
                <span id="cartTax">$0.00</span>
            </div>
            <div class="total-row grand-total-row">
                <span>Total:</span>
                <span class="grand-total-amount" id="cartTotal">$0.00</span>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="payment-methods-section">
            <label class="payment-label">Payment Method</label>
            <div class="payment-buttons">
                <input type="radio" class="btn-check" name="paymentMethod" id="paymentCash" value="cash" checked>
                <label class="payment-method-btn" for="paymentCash">
                    <i class="bi bi-cash-stack"></i>
                    <span>Cash</span>
                </label>

                <input type="radio" class="btn-check" name="paymentMethod" id="paymentCard" value="card">
                <label class="payment-method-btn" for="paymentCard">
                    <i class="bi bi-credit-card-fill"></i>
                    <span>Card</span>
                </label>

                <input type="radio" class="btn-check" name="paymentMethod" id="paymentCheck" value="check">
                <label class="payment-method-btn" for="paymentCheck">
                    <i class="bi bi-receipt"></i>
                    <span>Check</span>
                </label>

                <input type="radio" class="btn-check" name="paymentMethod" id="paymentBitcoin" value="bitcoin">
                <label class="payment-method-btn" for="paymentBitcoin">
                    <i class="bi bi-currency-bitcoin"></i>
                    <span>Bitcoin</span>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="checkout-actions">
            <button id="clearCartBtn" class="btn-action btn-clear-cart" disabled>
                <i class="bi bi-trash3-fill"></i> Clear
            </button>
            <button id="checkoutBtn" class="btn-action btn-checkout-primary" disabled>
                <i class="bi bi-check-circle-fill"></i> Complete Sale
            </button>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus-fill"></i> Add New Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCustomerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer Type *</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type"
                                       id="posTypeB2C" value="B2C" checked onchange="togglePosCustomerType()">
                                <label class="form-check-label" for="posTypeB2C">B2C (Individual)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type"
                                       id="posTypeB2B" value="B2B" onchange="togglePosCustomerType()">
                                <label class="form-check-label" for="posTypeB2B">B2B (Business)</label>
                            </div>
                        </div>
                    </div>

                    <div id="posB2bFields" style="display: none;" class="mb-3">
                        <label for="companyName" class="form-label">Company Name *</label>
                        <input type="text" class="form-control" id="companyName">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="lastName" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="tel" class="form-control" id="mobile">
                        </div>
                        <div class="col-md-6 mb-3" id="posBirthDateField">
                            <label for="birthDate" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="birthDate">
                        </div>
                    </div>

                    <div id="posB2cFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emergencyContactName" class="form-label">Emergency Contact Name</label>
                                <input type="text" class="form-control" id="emergencyContactName">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergencyContactPhone" class="form-label">Emergency Contact Phone</label>
                                <input type="tel" class="form-control" id="emergencyContactPhone">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Course Add-ons Modal -->
<div class="modal fade" id="courseAddonsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-mortarboard-fill"></i> <span id="courseTitle">Course Options</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Select add-ons and materials for this course:</p>

                <div class="addon-section mb-4">
                    <h6><i class="bi bi-book-fill"></i> Course Materials</h6>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input course-addon" id="addonManual" data-price="45.00" data-name="Student Manual">
                        <label class="form-check-label" for="addonManual">
                            Student Manual - <strong>$45.00</strong>
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input course-addon" id="addonElearning" data-price="195.00" data-name="eLearning Access">
                        <label class="form-check-label" for="addonElearning">
                            eLearning Access - <strong>$195.00</strong>
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input course-addon" id="addonLogbook" data-price="25.00" data-name="Logbook">
                        <label class="form-check-label" for="addonLogbook">
                            Logbook - <strong>$25.00</strong>
                        </label>
                    </div>
                </div>

                <div class="addon-section mb-4">
                    <h6><i class="bi bi-patch-check-fill"></i> Certification</h6>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input course-addon" id="addonCert" data-price="35.00" data-name="Certification Card" checked>
                        <label class="form-check-label" for="addonCert">
                            Certification Card - <strong>$35.00</strong>
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input course-addon" id="addonEcard" data-price="0.00" data-name="eCard (Digital)">
                        <label class="form-check-label" for="addonEcard">
                            eCard (Digital) - <strong>Free</strong>
                        </label>
                    </div>
                </div>

                <div class="addon-section">
                    <h6><i class="bi bi-gear-fill"></i> Equipment</h6>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input course-addon" id="addonMask" data-price="75.00" data-name="Mask & Snorkel Set">
                        <label class="form-check-label" for="addonMask">
                            Mask & Snorkel Set - <strong>$75.00</strong>
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input course-addon" id="addonFins" data-price="95.00" data-name="Fins">
                        <label class="form-check-label" for="addonFins">
                            Fins - <strong>$95.00</strong>
                        </label>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <strong>Course Total with Add-ons:</strong> <span id="courseAddonTotal" class="float-end">$0.00</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addCourseToCart">
                    <i class="bi bi-cart-plus-fill"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Course Schedule Selection Modal -->
<div class="modal fade" id="courseScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-check"></i> Select Course Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Course:</strong> <span id="modalCourseName"></span><br>
                    <strong>Price:</strong> <span id="modalCoursePrice"></span>
                </div>

                <p class="text-muted mb-3">Please select which class schedule to enroll the student in:</p>

                <div id="schedulesList" class="schedules-list">
                    <!-- Schedules will be loaded here dynamically -->
                </div>

                <div id="schedulesLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Loading available schedules...</p>
                </div>

                <div id="schedulesEmpty" class="text-center py-5" style="display: none;">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No schedules available for this course.</p>
                    <small>Please contact administration to schedule a new class.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- AI Visual Search Modal -->
<div class="modal fade" id="aiSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-camera-fill"></i> AI Visual Search
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Capture or Upload Image</h6>

                        <!-- Camera Input -->
                        <div class="mb-3">
                            <input type="file" id="aiSearchImageInput" accept="image/*" capture="environment" class="form-control" style="display: none;">
                            <button type="button" class="btn btn-primary w-100 mb-2" onclick="document.getElementById('aiSearchImageInput').click()">
                                <i class="bi bi-camera"></i> Take Photo / Upload Image
                            </button>
                        </div>

                        <!-- Image Preview -->
                        <div id="aiSearchImagePreview" style="display: none;">
                            <img id="aiSearchPreviewImg" src="" alt="Search Image" class="img-fluid rounded mb-2" style="max-height: 300px; width: 100%; object-fit: contain; border: 2px solid #dee2e6;">
                            <button type="button" class="btn btn-success w-100" id="aiSearchExecuteBtn">
                                <i class="bi bi-search"></i> Search Similar Products
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div id="aiSearchLoading" style="display: none;" class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted">AI is analyzing your image...</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="mb-3">Search Results</h6>
                        <div id="aiSearchResults" class="ai-search-results-container">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-image" style="font-size: 3rem;"></i>
                                <p class="mt-2">Upload an image to find matching products</p>
                                <small>Works best with clear photos of diving equipment</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="text-muted small me-auto">
                    <i class="bi bi-shield-check"></i> All processing happens locally - your images never leave this device
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;"></div>
        <p class="mt-3">Processing Payment...</p>
    </div>
</div>

<!-- Floating Action Button for Mobile -->
<button id="fabCart" class="fab-cart d-lg-none">
    <i class="bi bi-cart3"></i>
    <span id="fabBadge" class="fab-badge">0</span>
</button>

<?php
$content = ob_get_clean();

$additionalJs = '
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.11.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.1"></script>
<script src="/assets/js/ai-image-search.js"></script>
<script src="/assets/js/pos-course-enrollment.js"></script>
<script src="/assets/js/modern-pos.js"></script>
';

require __DIR__ . '/../layouts/app.php';
?>
