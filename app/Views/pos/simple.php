<?php
$pageTitle = 'Point of Sale';
$activeMenu = 'pos';
$user = currentUser();

// Add modern POS CSS
$additionalCss = '
<link rel="stylesheet" href="/assets/css/pos-simple.css">
';

ob_start();
?>

<div class="pos-wrapper">
    <!-- LEFT SIDE: Products -->
    <div class="pos-products">
        <!-- Header Bar -->
        <div class="pos-header">
            <div class="pos-header-left">
                <h1 class="pos-title"><i class="bi bi-cart3"></i> Point of Sale</h1>
            </div>

            <div class="pos-header-center">
                <!-- Weather Widget -->
                <div class="weather-widget" id="weatherWidget">
                    <i class="bi bi-sun" id="weatherIcon"></i>
                    <div class="weather-info">
                        <span id="weatherTemp">--°F</span>
                        <span id="weatherDesc">Loading...</span>
                    </div>
                </div>

                <!-- Clock Display -->
                <div class="clock-widget" id="clockWidget">
                    <div class="clock-time" id="posTime">--:--</div>
                    <div class="clock-date" id="posDate">Loading...</div>
                </div>
            </div>

            <div class="pos-header-right">
                <!-- Voice Toggle -->
                <div class="voice-toggle">
                    <input type="checkbox" id="voiceToggle" class="voice-switch">
                    <label for="voiceToggle" class="voice-label">
                        <i class="bi bi-mic-fill"></i>
                    </label>
                </div>

                <!-- Clock In/Out Button -->
                <button class="clock-btn" id="timeClockBtn">
                    <div class="clock-btn-content">
                        <i class="bi bi-person-badge" id="clockIcon"></i>
                        <div class="clock-btn-text">
                            <span id="clockStatus">Clock In</span>
                            <span id="clockDuration" class="clock-duration">--:--:--</span>
                        </div>
                    </div>
                </button>

                <!-- Help -->
                <button class="pos-btn pos-btn-icon" id="posHelpBtn" title="Keyboard Shortcuts (F1)">
                    <i class="bi bi-keyboard"></i>
                </button>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="pos-quick-actions">
            <button class="quick-btn quick-btn-green" id="quickAirFillBtn">
                <i class="bi bi-wind"></i> Air Fill <span class="price">$8</span>
            </button>
            <button class="quick-btn quick-btn-blue" id="quickNitroxBtn">
                <i class="bi bi-droplet-fill"></i> Nitrox <span class="price">$12</span>
            </button>
            <button class="quick-btn quick-btn-purple" id="quickTrimixBtn">
                <i class="bi bi-layers"></i> Trimix <span class="price">$25</span>
            </button>
            <button class="quick-btn quick-btn-orange" id="quickReturnBtn" data-bs-toggle="modal"
                data-bs-target="#returnModal">
                <i class="bi bi-arrow-return-left"></i> Returns
            </button>
            <button class="quick-btn quick-btn-pink" id="quickGiftCardBtn" data-bs-toggle="modal"
                data-bs-target="#giftCardModal">
                <i class="bi bi-gift"></i> Gift Card
            </button>
        </div>

        <!-- Product Search -->
        <div class="pos-search">
            <i class="bi bi-search"></i>
            <input type="text" id="productSearch" placeholder="Search products... (F2)" autocomplete="off">
        </div>

        <!-- Category Tabs -->
        <div class="pos-categories" id="categoryTabs">
            <button class="cat-btn active" data-category="all">All</button>
            <button class="cat-btn" data-category="gear">Gear</button>
            <button class="cat-btn" data-category="courses">Courses</button>
            <button class="cat-btn" data-category="rentals">Rentals</button>
            <button class="cat-btn" data-category="fills">Fills</button>
            <button class="cat-btn" data-category="accessories">Accessories</button>
        </div>

        <!-- Product Grid -->
        <div class="pos-product-grid" id="productGrid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-id="<?= $product['id'] ?>"
                        data-name="<?= htmlspecialchars($product['name']) ?>" data-price="<?= $product['price'] ?>"
                        data-category="<?= strtolower($product['category_name'] ?? 'all') ?>">
                        <div class="product-img">
                            <img src="<?= $product['image_url'] ?? '/assets/img/product-placeholder.png' ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        <div class="product-info">
                            <div class="product-name">
                                <?= htmlspecialchars($product['name']) ?>
                            </div>
                            <div class="product-price">$
                                <?= number_format($product['price'], 2) ?>
                            </div>
                        </div>
                        <button class="product-add-btn" onclick="addToCart(<?= $product['id'] ?>)">
                            <i class="bi bi-plus-lg"></i> Add
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <i class="bi bi-box-seam"></i>
                    <p>No products found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT SIDE: Cart -->
    <div class="pos-cart">
        <!-- Customer Selection -->
        <div class="cart-customer">
            <input type="text" id="customerSearchInput" placeholder="Customer (F3)..." autocomplete="off">
            <div id="customerSearchResults" class="customer-dropdown"></div>
            <div id="selectedCustomer" class="selected-customer" style="display: none;">
                <img id="customerAvatar" src="/assets/img/default-avatar.png" alt="">
                <div class="customer-info">
                    <div id="customerName">Customer Name</div>
                    <div id="customerPhone" class="customer-meta"></div>
                </div>
                <button class="clear-customer" id="clearCustomerBtn"><i class="bi bi-x"></i></button>
            </div>
            <input type="hidden" id="selectedCustomerId" value="">
        </div>

        <!-- Cart Header -->
        <div class="cart-header">
            <h2><i class="bi bi-basket"></i> Current Sale</h2>
            <button class="clear-cart-btn" id="clearCartBtn" title="Clear Cart (F9)">
                <i class="bi bi-trash"></i>
            </button>
        </div>

        <!-- Cart Items -->
        <div class="cart-items" id="cartItems">
            <div class="cart-empty">
                <i class="bi bi-cart-x"></i>
                <p>Cart is empty</p>
                <small>Tap products to add them</small>
            </div>
        </div>

        <!-- Cart Totals -->
        <div class="cart-totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span id="cartSubtotal">$0.00</span>
            </div>
            <div class="total-row">
                <span>Tax (
                    <?= number_format($taxRate * 100, 2) ?>%)
                </span>
                <span id="cartTax">$0.00</span>
            </div>
            <div class="total-row total-grand">
                <span>Total</span>
                <span id="cartTotal">$0.00</span>
            </div>
        </div>

        <!-- Payment -->
        <div class="cart-payment">
            <div class="payment-methods">
                <button class="pay-btn" data-method="cash">
                    <i class="bi bi-cash"></i> Cash
                </button>
                <button class="pay-btn" data-method="card">
                    <i class="bi bi-credit-card"></i> Card
                </button>
                <?php if ($bitcoinEnabled ?? false): ?>
                    <button class="pay-btn" data-method="bitcoin">
                        <i class="bi bi-currency-bitcoin"></i> BTC
                    </button>
                <?php endif; ?>
            </div>
            <button class="checkout-btn" id="checkoutBtn">
                <i class="bi bi-check-lg"></i> Complete Sale (F12)
            </button>
        </div>
    </div>
</div>

<!-- Config Script -->
<script>
    window.posConfig = {
        taxRate: <?= json_encode($taxRate) ?>,
        bitcoinEnabled: <?= json_encode($bitcoinEnabled ?? false) ?>
    };
</script>

<!-- Modals (keep existing) -->
<?php include __DIR__ . '/partials/_modals.php'; ?>

<?php
$content = ob_get_clean();

$additionalJs = '
<script src="/assets/js/pos-quick-actions.js"></script>
<script src="/assets/js/pos-shortcuts.js"></script>
<script src="/assets/js/pos-simple.js"></script>
';

require __DIR__ . '/../layouts/admin.php';
?>