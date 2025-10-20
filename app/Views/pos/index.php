<?php
$pageTitle = 'Point of Sale';
$activeMenu = 'pos';
$user = currentUser();

// Add modern POS CSS
$additionalCss = '
<link rel="stylesheet" href="/assets/css/mobile-pos.css">
<link rel="stylesheet" href="/assets/css/professional-pos.css">
';

ob_start();
?>

<!-- Customer Selection Bar (Fixed at Top) -->
<div class="pos-customer-bar">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <label class="customer-label">
                    <i class="bi bi-person-circle"></i> Customer
                </label>
                <div class="customer-select-wrapper">
                    <select id="customerSelect" class="form-select customer-select">
                        <option value="">Walk-In Customer</option>
                        <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>"
                                data-email="<?= htmlspecialchars($customer['email'] ?? '') ?>"
                                data-phone="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                            <?php if ($customer['company_name']): ?>
                            - <?= htmlspecialchars($customer['company_name']) ?>
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-success btn-add-customer" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="bi bi-person-plus-fill"></i> New Customer
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="customer-info" id="customerInfo" style="display: none;">
                    <span class="customer-detail">
                        <i class="bi bi-envelope"></i> <span id="customerEmail">-</span>
                    </span>
                    <span class="customer-detail ms-3">
                        <i class="bi bi-telephone"></i> <span id="customerPhone">-</span>
                    </span>
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
                <input type="text" id="productSearch" class="search-input" placeholder="Search products, courses, or scan barcode..." autocomplete="off">
                <button class="btn-clear-search" id="clearSearch" style="display: none;">
                    <i class="bi bi-x-circle-fill"></i>
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
                 data-product-price="<?= $product['retail_price'] ?>"
                 data-product-sku="<?= htmlspecialchars($product['sku']) ?>"
                 data-category="gear">
                <div class="product-tile-image">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="product-tile-info">
                    <div class="product-tile-name"><?= htmlspecialchars($product['name']) ?></div>
                    <div class="product-tile-price"><?= formatCurrency($product['retail_price']) ?></div>
                </div>
                <?php if ($product['track_inventory'] && $product['stock_quantity'] <= $product['low_stock_threshold']): ?>
                <div class="low-stock-indicator">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= $product['stock_quantity'] ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <!-- Course Tiles -->
            <div class="product-tile" data-category="courses" data-product-id="course_1" data-product-name="Open Water Diver" data-product-price="399.00" data-product-sku="COURSE-OW">
                <div class="product-tile-image course-item">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="product-tile-info">
                    <div class="product-tile-name">Open Water Diver</div>
                    <div class="product-tile-price">$399.00</div>
                </div>
                <div class="course-badge">Course</div>
            </div>

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
            <span class="cart-item-count" id="cartItemCount">0 items</span>
        </div>

        <div class="cart-items-list" id="cartItemsList">
            <div class="empty-cart-message">
                <i class="bi bi-cart-x"></i>
                <p>No items in cart</p>
                <small>Add products to begin checkout</small>
            </div>
        </div>

        <!-- Cart Totals -->
        <div class="cart-totals">
            <div class="total-row subtotal-row">
                <span>Subtotal:</span>
                <span id="cartSubtotal">$0.00</span>
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
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone">
                    </div>
                    <div class="mb-3">
                        <label for="companyName" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="companyName">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="newsletterOptIn">
                        <label class="form-check-label" for="newsletterOptIn">
                            <strong>Subscribe to Newsletter</strong>
                            <br><small class="text-muted">Receive updates, promotions, and dive news</small>
                        </label>
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

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;"></div>
        <p class="mt-3">Processing Payment...</p>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJs = '<script src="/assets/js/professional-pos.js"></script>';

require __DIR__ . '/../layouts/app.php';
?>
