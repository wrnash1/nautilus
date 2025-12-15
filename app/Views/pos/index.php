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

<div class="container-fluid p-3">
    <!-- Top Control Bar (Customer & Info) -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="modern-card p-3">
                <div class="row align-items-center">
                    <!-- Customer Selection -->
                    <div class="col-md-5">
                        <label class="form-label text-muted small text-uppercase fw-bold"><i class="bi bi-people-fill me-1"></i> Customer</label>
                        <div class="search-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="customerSearchInput" class="search-input form-control" placeholder="Search customer (Name, Phone, Email)..." autocomplete="off">
                            <button type="button" id="clearCustomerBtn" class="btn-clear-search" style="display: none;">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                            <input type="hidden" id="selectedCustomerId" value="">
                            <div id="customerSearchResults" class="search-results shadow-sm" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; z-index:1000; border-radius:0 0 12px 12px;"></div>
                        </div>
                        <div class="mt-2 d-flex gap-2">
                             <a href="/store/customers/create?return_to=pos" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-person-plus-fill"></i> New Profile
                            </a>
                            <span id="activeCustomerBadge" class="badge bg-soft-primary text-primary d-none align-items-center p-2 rounded-pill">
                                <i class="bi bi-person-check-fill me-1"></i> <span id="customerNameDisplay">Walk-In</span>
                            </span>
                        </div>
                    </div>

                    <!-- Store Stats / Time -->
                    <div class="col-md-7 text-end">
                        <div class="d-flex justify-content-end align-items-center gap-4">
                            <div class="text-end">
                                <div class="text-muted small">Date</div>
                                <div class="fw-bold" id="posCurrentDate"></div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Time</div>
                                <div class="fw-bold font-monospace fs-5" id="posCurrentTime"></div>
                            </div>
                             <div class="text-end text-danger" id="autoLogoutTimer" style="display: none;">
                                <div class="small">Auto-logout</div>
                                <div id="logoutCountdown" class="fw-bold">00:00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Left Column: Products -->
        <div class="col-lg-8">
            <div class="modern-card h-100 d-flex flex-column">
                <!-- Product Controls -->
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="row g-2">
                        <div class="col-md-12">
                             <div class="category-filter">
                                <div class="btn-group-horizontal" role="group">
                                    <button class="btn-category active" data-category="all"><i class="bi bi-grid-fill"></i> All</button>
                                    <button class="btn-category" data-category="gear"><i class="bi bi-backpack-fill"></i> Gear</button>
                                    <button class="btn-category" data-category="courses"><i class="bi bi-mortarboard-fill"></i> Courses</button>
                                    <button class="btn-category" data-category="trips"><i class="bi bi-airplane-fill"></i> Trips</button>
                                    <button class="btn-category" data-category="fills"><i class="bi bi-wind"></i> Fills</button>
                                    <button class="btn-category" data-category="rentals"><i class="bi bi-briefcase-fill"></i> Rentals</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 search-wrapper">
                         <i class="bi bi-search search-icon"></i>
                         <input type="text" id="productSearch" class="search-input w-100" placeholder="Search products, SKUs, or scan barcode..." autocomplete="off">
                          <button class="btn btn-light position-absolute end-0 me-2" id="aiSearchBtn" title="AI Search" style="z-index: 3;">
                            <i class="bi bi-camera-fill text-primary"></i>
                        </button>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="card-body bg-light mt-3 flex-grow-1" style="overflow-y: auto; max-height: calc(100vh - 280px);">
                    <div class="products-grid" id="productGrid">
                        <!-- Products Loop -->
                        <?php foreach ($products as $product): ?>
                        <div class="product-card-modern"
                             data-product-id="<?= $product['id'] ?>"
                             data-product-name="<?= htmlspecialchars($product['name']) ?>"
                             data-product-price="<?= $product['price'] ?>"
                             data-product-sku="<?= htmlspecialchars($product['sku']) ?>"
                             data-category="gear">
                            <div class="product-image">
                                <i class="bi bi-box-seam product-icon"></i>
                                <?php if ($product['track_inventory'] && $product['quantity_in_stock'] <= $product['low_stock_threshold']): ?>
                                <div class="product-badge bg-danger">
                                    <i class="bi bi-exclamation-circle"></i> Low Stock
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                <div class="product-sku"><?= htmlspecialchars($product['sku']) ?></div>
                                <div class="product-footer">
                                    <div class="product-price"><?= formatCurrency($product['price']) ?></div>
                                    <div class="product-stock">
                                        <?php if($product['track_inventory']): ?>
                                            <i class="bi bi-box"></i> <?= $product['quantity_in_stock'] ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <button class="btn-add-product"><i class="bi bi-plus"></i></button>
                        </div>
                        <?php endforeach; ?>

                        <!-- Courses -->
                        <?php foreach ($courses as $course): ?>
                        <div class="product-card-modern"
                             data-category="courses"
                             data-product-id="course_<?= $course['id'] ?>"
                             data-course-id="<?= $course['id'] ?>"
                             data-product-name="<?= htmlspecialchars($course['name']) ?>"
                             data-product-price="<?= $course['price'] ?>"
                             data-product-sku="<?= htmlspecialchars($course['course_code']) ?>">
                            <div class="product-image course-item">
                                <i class="bi bi-mortarboard-fill product-icon"></i>
                                <div class="product-badge bg-info text-white">Course</div>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($course['name']) ?></div>
                                <div class="product-footer">
                                    <div class="product-price"><?= formatCurrency($course['price']) ?></div>
                                </div>
                            </div>
                            <button class="btn-add-product"><i class="bi bi-plus"></i></button>
                        </div>
                        <?php endforeach; ?>

                         <!-- Trips -->
                        <?php foreach ($trips as $trip): ?>
                        <div class="product-card-modern"
                             data-category="trips"
                             data-product-id="trip_<?= $trip['id'] ?>"
                             data-trip-id="<?= $trip['id'] ?>"
                             data-product-name="<?= htmlspecialchars($trip['name']) ?>"
                             data-product-price="<?= $trip['price'] ?>"
                             data-product-sku="TRIP-<?= $trip['id'] ?>">
                            <div class="product-image trip-item">
                                <i class="bi bi-airplane-fill product-icon"></i>
                                <div class="product-badge" style="background-color: #8b5cf6;">Trip</div>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($trip['name']) ?></div>
                                <div class="product-footer">
                                    <div class="product-price"><?= formatCurrency($trip['price']) ?></div>
                                </div>
                            </div>
                            <button class="btn-add-product"><i class="bi bi-plus"></i></button>
                        </div>
                        <?php endforeach; ?>

                        <!-- Rentals -->
                        <?php foreach ($rentals as $rental): ?>
                        <div class="product-card-modern"
                             data-category="rentals"
                             data-product-id="rental_<?= $rental['id'] ?>"
                             data-rental-id="<?= $rental['id'] ?>"
                             data-product-name="<?= htmlspecialchars($rental['name']) ?>"
                             data-product-price="<?= $rental['daily_rate'] ?>"
                             data-product-sku="<?= htmlspecialchars($rental['sku']) ?>">
                            <div class="product-image rental-item">
                                <i class="bi bi-briefcase-fill product-icon"></i>
                                <div class="product-badge bg-warning text-dark">Rental</div>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($rental['name']) ?></div>
                                <div class="product-footer">
                                    <div class="product-price"><?= formatCurrency($rental['daily_rate']) ?>/day</div>
                                </div>
                            </div>
                            <button class="btn-add-product"><i class="bi bi-plus"></i></button>
                        </div>
                        <?php endforeach; ?>

                        <!-- Fills -->
                        <div class="product-card-modern" data-category="fills" data-product-id="fill_air" data-product-name="Air Fill" data-product-price="8.00" data-product-sku="FILL-AIR">
                            <div class="product-image fill-item">
                                <i class="bi bi-wind product-icon"></i>
                                <div class="product-badge bg-success">Service</div>
                            </div>
                             <div class="product-info">
                                <div class="product-name">Air Fill</div>
                                <div class="product-footer">
                                    <div class="product-price">$8.00</div>
                                </div>
                            </div>
                            <button class="btn-add-product"><i class="bi bi-plus"></i></button>
                        </div>
                        <div class="product-card-modern" data-category="fills" data-product-id="fill_nitrox" data-product-name="Nitrox Fill" data-product-price="12.00" data-product-sku="FILL-NITROX">
                            <div class="product-image fill-item">
                                <i class="bi bi-wind product-icon"></i>
                                <div class="product-badge bg-success">Service</div>
                            </div>
                             <div class="product-info">
                                <div class="product-name">Nitrox Fill</div>
                                <div class="product-footer">
                                    <div class="product-price">$12.00</div>
                                </div>
                            </div>
                            <button class="btn-add-product"><i class="bi bi-plus"></i></button>
                        </div>

                    </div>
                    <div id="noResultsMsg" class="text-center py-5 d-none">
                        <i class="bi bi-search text-muted fs-1"></i>
                        <p class="text-muted mt-2">No items found matching your search.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Cart -->
        <div class="col-lg-4">
            <div class="sticky-cart">
                <div class="modern-card h-100 d-flex flex-column border-primary" style="box-shadow: 0 0 20px rgba(0,0,0,0.05);">
                    <!-- Cart Header -->
                    <div class="cart-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cart3 fs-4"></i>
                                <h5 class="mb-0">Current Sale</h5>
                            </div>
                            <span class="cart-count-badge" id="cartItemCount">0</span>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div class="cart-items-container p-3" id="cartItemsList">
                        <div class="empty-cart-state">
                            <i class="bi bi-cart-x"></i>
                            <p>Your cart is empty</p>
                            <small>Click items on the left to add them</small>
                        </div>
                    </div>

                    <!-- Cart Footer -->
                    <div class="bg-white p-3 border-top">
                        <!-- Coupon -->
                         <div class="input-group mb-3">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-tag-fill"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="couponCode" placeholder="Promo Code">
                            <button class="btn btn-outline-secondary" type="button" id="applyCouponBtn">Apply</button>
                        </div>
                        <div id="appliedCoupon" class="d-none alert alert-success d-flex justify-content-between align-items-center py-2 px-3 mb-2 font-monospace">
                            <span><strong id="appliedCouponCode">CODE</strong> Applied</span>
                            <button type="button" class="btn-close btn-sm" id="removeCouponBtn"></button>
                        </div>

                        <!-- Totals -->
                        <div class="cart-summary">
                            <div class="summary-row text-muted">
                                <span>Subtotal</span>
                                <span id="cartSubtotal">$0.00</span>
                            </div>
                            <div class="summary-row text-success d-none" id="discountRow">
                                <span>Discount</span>
                                <span id="cartDiscount">-$0.00</span>
                            </div>
                             <div class="summary-row text-muted">
                                <span>Tax (8%)</span>
                                <span id="cartTax">$0.00</span>
                            </div>
                            <div class="summary-total d-flex justify-content-between align-items-center">
                                <span class="fw-bold">TOTAL</span>
                                <span class="total-amount fw-bold" id="cartTotal">$0.00</span>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="payment-section mt-3">
                            <label class="form-label small text-uppercase text-muted">Payment Method</label>
                            <div class="payment-methods">
                                <input type="radio" class="btn-check" name="paymentMethod" id="paymentCash" value="cash" checked>
                                <label class="payment-btn" for="paymentCash">
                                    <i class="bi bi-cash-stack"></i>
                                    <span>Cash</span>
                                </label>

                                <input type="radio" class="btn-check" name="paymentMethod" id="paymentCard" value="card">
                                <label class="payment-btn" for="paymentCard">
                                    <i class="bi bi-credit-card-fill"></i>
                                    <span>Card</span>
                                </label>

                                <input type="radio" class="btn-check" name="paymentMethod" id="paymentOther" value="other">
                                <label class="payment-btn" for="paymentOther">
                                    <i class="bi bi-wallet2"></i>
                                    <span>Other</span>
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="cart-actions mt-3">
                            <button id="clearCartBtn" class="btn-clear" disabled>
                                <i class="bi bi-trash"></i> Cancel
                            </button>
                            <button id="checkoutBtn" class="btn-checkout w-100" disabled>
                                <span>Checkout</span> <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email">
                        </div>
                         <div class="col-12">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-ring"></div>
        <div class="spinner-ring"></div>
        <div class="spinner-ring"></div>
        <h4 class="mt-4">Processing Transaction...</h4>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJs = '
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.11.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.1"></script>
<script src="/assets/js/ai-image-search.js"></script>
<script src="/assets/js/pos-course-enrollment.js"></script>
<script src="/assets/js/modern-pos.js?v=' . time() . '"></script>
';

require __DIR__ . '/../layouts/admin.php';
?>
