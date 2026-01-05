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
                        <label class="form-label text-muted small text-uppercase fw-bold"><i
                                class="bi bi-people-fill me-1"></i> Customer</label>
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-grow-1 position-relative">
                                <div class="search-wrapper mb-2">
                                    <i class="bi bi-search search-icon"></i>
                                    <input type="text" id="customerSearchInput"
                                        class="search-input form-control form-control-sm"
                                        placeholder="Search customer (Name, Phone, Email)..." autocomplete="off">
                                    <button type="button" id="clearCustomerBtn" class="btn-clear-search"
                                        style="display: none;">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                    <div id="customerSearchResults" class="search-results shadow-sm"
                                        style="display:none; position:absolute; top:100%; left:0; right:0; background:white; z-index:1000; border-radius:0 0 12px 12px;">
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <script>
                                        window.posConfig = {
                                            taxRate: <?= json_encode($taxRate) ?>,
                                            bitcoinEnabled: <?= json_encode($bitcoinEnabled ?? false) ?>
                                        };
                                    </script>
                                    <style>
                                        /* Professional POS Modernization */
                                        :root {
                                            --pos-primary: #4f46e5;
                                            --pos-secondary: #64748b;
                                            --pos-bg: #f3f4f6;
                                            --pos-card-bg: #ffffff;
                                            --pos-border-radius: 12px;
                                        }

                                        body {
                                            background-color: var(--pos-bg);
                                            font-family: 'Inter', sans-serif;
                                        }

                                        .pos-container {
                                            padding: 1.5rem;
                                            height: calc(100vh - 60px);
                                            overflow: hidden;
                                        }

                                        .product-card-modern {
                                            border: none;
                                            border-radius: var(--pos-border-radius);
                                            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                                            transition: all 0.2s ease-in-out;
                                            background: var(--pos-card-bg);
                                            overflow: hidden;
                                            height: 100%;
                                        }

                                        .product-card-modern:hover {
                                            transform: translateY(-2px);
                                            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                                            border-color: var(--pos-primary);
                                        }

                                        .btn-category {
                                            border-radius: 50px;
                                            padding: 0.5rem 1.2rem;
                                            font-weight: 500;
                                            transition: all 0.2s;
                                            border: 1px solid #e2e8f0;
                                            background: white;
                                            color: var(--pos-secondary);
                                            margin-right: 0.5rem;
                                            margin-bottom: 0.5rem;
                                        }

                                        .btn-category.active {
                                            background-color: var(--pos-primary);
                                            color: white;
                                            border-color: var(--pos-primary);
                                            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
                                        }

                                        .cart-panel {
                                            background: white;
                                            border-radius: var(--pos-border-radius);
                                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                                            height: 100%;
                                            display: flex;
                                            flex-direction: column;
                                            border-left: 1px solid #e5e7eb;
                                        }

                                        .cart-header {
                                            background: var(--pos-primary);
                                            color: white;
                                            padding: 1.25rem;
                                            border-top-left-radius: var(--pos-border-radius);
                                            border-top-right-radius: var(--pos-border-radius);
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: center;
                                        }

                                        .cart-items-wrapper {
                                            flex: 1;
                                            overflow-y: auto;
                                            padding: 1rem;
                                        }

                                        .cart-footer {
                                            background: #f8fafc;
                                            padding: 1.25rem;
                                            border-top: 1px solid #e2e8f0;
                                            border-bottom-left-radius: var(--pos-border-radius);
                                            border-bottom-right-radius: var(--pos-border-radius);
                                        }

                                        .payment-method-btn {
                                            padding: 1rem;
                                            border: 2px solid #e2e8f0;
                                            border-radius: 10px;
                                            text-align: center;
                                            cursor: pointer;
                                            transition: all 0.2s;
                                            font-weight: 600;
                                        }

                                        .btn-check:checked+.payment-method-btn {
                                            background-color: #eef2ff;
                                            border-color: var(--pos-primary);
                                            color: var(--pos-primary);
                                        }

                                        .search-wrapper {
                                            position: relative;
                                        }

                                        .customer-display-card {
                                            background: white;
                                            border-radius: 10px;
                                            padding: 0.75rem;
                                            border: 1px solid #e2e8f0;
                                            display: flex;
                                            align-items: center;
                                            justify-content: space-between;
                                            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                                        }
                                    </style>
                                    <a href="/store/customers/create?return_to=pos"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-person-plus-fill"></i> New
                                    </a>
                                </div>
                            </div>

                            <!-- Enhanced Customer Display -->
                            <div id="customerDisplayCard" class="d-none w-100 mt-2">
                                <div class="d-flex align-items-center gap-3 p-3 rounded border bg-white shadow-sm position-relative"
                                    style="min-height: 80px;">
                                    <img id="activeCustomerImg" src="/assets/img/default-avatar.png"
                                        class="rounded-circle shadow-sm"
                                        style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="fw-bold mb-0 text-primary" id="customerNameDisplay">Customer
                                                    Name</h5>
                                                <div class="d-flex align-items-center gap-2 text-muted small mt-1">
                                                    <span id="customerEmailDisplay"><i class="bi bi-envelope"></i>
                                                        email@example.com</span>
                                                    <span class="mx-1">•</span>
                                                    <span id="customerPhoneDisplay"><i class="bi bi-telephone"></i>
                                                        (555) 123-4567</span>
                                                </div>
                                            </div>
                                            <div id="customerCertDisplay" class="d-flex flex-column align-items-end">
                                                <!-- Cert Info Injected JS -->
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="clearSelectedCustomerBtn"
                                        class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle"
                                        style="width: 24px; height: 24px; padding: 0; line-height: 0;"
                                        title="Clear Customer">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    <input type="hidden" id="selectedCustomerId" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Weather, Time, Clock In/Out, AI Assistant -->
                    <div class="col-md-7">
                        <div class="d-flex justify-content-end align-items-center gap-3 flex-wrap">

                            <!-- Weather Widget -->
                            <div class="weather-widget d-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                                style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white;">
                                <i class="bi bi-cloud-sun fs-4" id="weatherIcon"></i>
                                <div class="text-start">
                                    <div class="fw-bold" id="weatherTemp">--°F</div>
                                    <div class="small opacity-75" id="weatherDesc">Loading...</div>
                                </div>
                            </div>

                            <!-- Date & Time Display -->
                            <div class="datetime-widget text-center px-3 py-2 rounded-3"
                                style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; min-width: 140px;">
                                <div class="fw-bold fs-4 font-monospace" id="posCurrentTime">--:--</div>
                                <div class="small opacity-75" id="posCurrentDate">Loading...</div>
                            </div>

                            <!-- Clock In/Out Button -->
                            <button id="timeClockBtn"
                                class="btn px-4 py-2 d-flex align-items-center gap-2 time-clock-btn"
                                style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 50px; font-weight: 600; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                                <i class="bi bi-clock-history fs-5"></i>
                                <div class="text-start">
                                    <div id="timeClockLabel" class="small">Clock In</div>
                                    <div id="timeClockDuration" class="small opacity-75">--:--:--</div>
                                </div>
                            </button>

                            <div class="vr mx-1" style="height: 40px;"></div>

                            <!-- AI Assistant Button -->
                            <button class="btn px-3 py-2 d-flex align-items-center gap-2 ai-assistant-btn"
                                id="aiAssistantBtn"
                                style="background: linear-gradient(135deg, #ec4899, #be185d); color: white; border: none; border-radius: 50px; font-weight: 600; box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);"
                                data-bs-toggle="modal" data-bs-target="#aiAssistantModal">
                                <i class="bi bi-robot fs-5"></i>
                                <span class="d-none d-lg-inline">AI Assistant</span>
                            </button>

                            <!-- Voice Toggle -->
                            <div class="form-check form-switch mb-0 d-flex align-items-center"
                                title="Toggle Voice Feedback">
                                <input class="form-check-input" type="checkbox" id="voiceFeedbackToggle"
                                    style="cursor: pointer; width: 3rem; height: 1.5rem;">
                                <label class="form-check-label ms-2" for="voiceFeedbackToggle">
                                    <i class="bi bi-mic-fill text-primary fs-5"></i>
                                </label>
                            </div>

                            <!-- Auto-logout Timer (hidden by default) -->
                            <div class="text-danger text-center px-2" id="autoLogoutTimer" style="display: none;">
                                <div class="small">Auto-logout</div>
                                <div id="logoutCountdown" class="fw-bold font-monospace">00:00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 justify-content-start">
                <!-- AIR FILL - Prominent Green Button -->
                <button type="button" class="btn btn-lg px-4 py-3 d-flex align-items-center gap-2 quick-action-btn"
                    id="quickAirFillBtn"
                    style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                    <i class="bi bi-wind fs-4"></i>
                    <span>Air Fill</span>
                    <span class="badge bg-white text-success ms-1">$8</span>
                </button>

                <!-- NITROX FILL Button -->
                <button type="button" class="btn btn-lg px-4 py-3 d-flex align-items-center gap-2 quick-action-btn"
                    id="quickNitroxBtn"
                    style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);">
                    <i class="bi bi-droplet-fill fs-4"></i>
                    <span>Nitrox</span>
                    <span class="badge bg-white text-primary ms-1">$12</span>
                </button>

                <!-- TRIMIX FILL Button -->
                <button type="button" class="btn btn-lg px-4 py-3 d-flex align-items-center gap-2 quick-action-btn"
                    id="quickTrimixBtn"
                    style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border: none; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);">
                    <i class="bi bi-layers-fill fs-4"></i>
                    <span>Trimix</span>
                    <span class="badge bg-white text-purple ms-1">$25</span>
                </button>

                <div class="vr mx-2"></div>

                <!-- RETURNS/REFUNDS Button -->
                <button type="button" class="btn btn-lg px-4 py-3 d-flex align-items-center gap-2 quick-action-btn"
                    id="quickReturnBtn" data-bs-toggle="modal" data-bs-target="#returnModal"
                    style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);">
                    <i class="bi bi-arrow-return-left fs-4"></i>
                    <span>Returns/Refunds</span>
                </button>

                <!-- GIFT CARD Button -->
                <button type="button" class="btn btn-lg px-4 py-3 d-flex align-items-center gap-2 quick-action-btn"
                    id="quickGiftCardBtn" data-bs-toggle="modal" data-bs-target="#giftCardModal"
                    style="background: linear-gradient(135deg, #ec4899, #db2777); color: white; border: none; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(236, 72, 153, 0.4);">
                    <i class="bi bi-gift-fill fs-4"></i>
                    <span>Gift Card</span>
                </button>

                <!-- WORK ORDER Button -->
                <a href="/store/work-orders/create"
                    class="btn btn-lg px-4 py-3 d-flex align-items-center gap-2 quick-action-btn"
                    style="background: linear-gradient(135deg, #64748b, #475569); color: white; border: none; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(100, 116, 139, 0.4); text-decoration: none;">
                    <i class="bi bi-tools fs-4"></i>
                    <span>Work Order</span>
                </a>
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
                                    <button class="btn-category active" data-category="all"><i
                                            class="bi bi-grid-fill"></i> All</button>
                                    <button class="btn-category" data-category="gear"><i
                                            class="bi bi-backpack-fill"></i> Gear</button>
                                    <button class="btn-category" data-category="courses"><i
                                            class="bi bi-mortarboard-fill"></i> Courses</button>
                                    <button class="btn-category" data-category="trips"><i
                                            class="bi bi-airplane-fill"></i> Trips</button>
                                    <button class="btn-category" data-category="fills"><i class="bi bi-wind"></i>
                                        Fills</button>
                                    <button class="btn-category" data-category="rentals"><i
                                            class="bi bi-briefcase-fill"></i> Rentals</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 search-wrapper">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="productSearch" class="search-input w-100"
                            placeholder="Search products, SKUs, or scan barcode..." autocomplete="off">
                        <button class="btn btn-light position-absolute end-0 me-2" id="aiSearchBtn" title="AI Search"
                            style="z-index: 3;">
                            <i class="bi bi-camera-fill text-primary"></i>
                        </button>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="card-body bg-light mt-3 flex-grow-1"
                    style="overflow-y: auto; max-height: calc(100vh - 280px);">
                    <div class="products-grid" id="productGrid">
                        <!-- Products Loop -->
                        <?php foreach ($products as $product): ?>
                            <div class="product-card-modern" data-product-id="<?= $product['id'] ?>"
                                data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                data-product-price="<?= $product['price'] ?>"
                                data-product-sku="<?= htmlspecialchars($product['sku']) ?>" data-category="gear">
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
                                            <?php if ($product['track_inventory']): ?>
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
                            <div class="product-card-modern" data-category="courses"
                                data-product-id="course_<?= $course['id'] ?>" data-course-id="<?= $course['id'] ?>"
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
                            <div class="product-card-modern" data-category="trips" data-product-id="trip_<?= $trip['id'] ?>"
                                data-trip-id="<?= $trip['id'] ?>" data-product-name="<?= htmlspecialchars($trip['name']) ?>"
                                data-product-price="<?= $trip['price'] ?>" data-product-sku="TRIP-<?= $trip['id'] ?>">
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
                            <div class="product-card-modern" data-category="rentals"
                                data-product-id="rental_<?= $rental['id'] ?>" data-rental-id="<?= $rental['id'] ?>"
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
                        <div class="product-card-modern" data-category="fills" data-product-id="fill_air"
                            data-product-name="Air Fill" data-product-price="8.00" data-product-sku="FILL-AIR">
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
                        <div class="product-card-modern" data-category="fills" data-product-id="fill_nitrox"
                            data-product-name="Nitrox Fill" data-product-price="12.00" data-product-sku="FILL-NITROX">
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
                <div class="modern-card h-100 d-flex flex-column border-primary"
                    style="box-shadow: 0 0 20px rgba(0,0,0,0.05);">
                    <!-- Cart Header -->
                    <div class="cart-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cart3 fs-4"></i>
                                <h5 class="mb-0">Current Sale</h5>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="cart-count-badge" id="cartItemCount">0</span>
                                <!-- Voice Toggle -->
                                <div class="form-check form-switch mb-0" title="Toggle Voice Feedback">
                                    <input class="form-check-input" type="checkbox" id="voiceFeedbackToggle"
                                        style="cursor: pointer;">
                                    <label class="form-check-label text-white ms-1" for="voiceFeedbackToggle"><i
                                            class="bi bi-mic-fill"></i></label>
                                </div>

                            </div>
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
                            <input type="text" class="form-control border-start-0 ps-0" id="couponCode"
                                placeholder="Promo Code">
                            <button class="btn btn-outline-secondary" type="button" id="applyCouponBtn">Apply</button>
                        </div>
                        <div id="appliedCoupon"
                            class="d-none alert alert-success d-flex justify-content-between align-items-center py-2 px-3 mb-2 font-monospace">
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
                                <span>Tax (<?= number_format($taxRate * 100, 0) ?>%)</span>
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
                                <input type="radio" class="btn-check" name="paymentMethod" id="paymentCash" value="cash"
                                    checked>
                                <label class="payment-btn" for="paymentCash">
                                    <i class="bi bi-cash-stack"></i>
                                    <span>Cash</span>
                                </label>

                                <input type="radio" class="btn-check" name="paymentMethod" id="paymentCard"
                                    value="card">
                                <label class="payment-btn" for="paymentCard">
                                    <i class="bi bi-credit-card-fill"></i>
                                    <span>Card</span>
                                </label>

                                <?php if ($bitcoinEnabled ?? false): ?>
                                    <input type="radio" class="btn-check" name="paymentMethod" id="paymentBitcoin"
                                        value="bitcoin">
                                    <label class="payment-btn" for="paymentBitcoin" style="border-color: #f7931a;">
                                        <i class="bi bi-currency-bitcoin" style="color: #f7931a;"></i>
                                        <span>Bitcoin</span>
                                    </label>
                                <?php endif; ?>

                                <input type="radio" class="btn-check" name="paymentMethod" id="paymentOther"
                                    value="other">
                                <label class="payment-btn" for="paymentOther">
                                    <i class="bi bi-wallet2"></i>
                                    <span>Other</span>
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="cart-actions mt-3">
                            <div class="d-flex gap-2 mb-2">
                                <button id="layawayBtn" class="btn btn-outline-primary flex-grow-1" disabled
                                    title="Layaway">
                                    <i class="bi bi-calendar-event"></i> Layaway
                                </button>
                                <button id="saveQuoteBtn" class="btn btn-outline-info flex-grow-1" disabled
                                    title="Save Quote">
                                    <i class="bi bi-file-earmark-text"></i> Quote
                                </button>
                                <button id="holdBtn" class="btn btn-outline-warning" disabled title="Hold/Park Sale">
                                    <i class="bi bi-pause-circle"></i>
                                </button>
                                <button id="clearCartBtn" class="btn btn-outline-danger" disabled title="Clear Cart">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
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

<!-- Returns/Refunds Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-return-left"></i> Returns & Refunds
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label">Find Original Transaction</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="returnTransactionSearch"
                            placeholder="Receipt #, customer name, or phone...">
                        <button class="btn btn-primary" type="button" id="searchReturnBtn">Search</button>
                    </div>
                </div>

                <div id="returnSearchResults" class="mb-4" style="display: none;">
                    <h6>Recent Transactions</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="returnTransactionsList">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="returnItemsSection" style="display: none;">
                    <h6>Select Items to Return</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllReturns"></th>
                                    <th>Item</th>
                                    <th>Qty Sold</th>
                                    <th>Return Qty</th>
                                    <th>Price</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody id="returnItemsList">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Refund Method</label>
                            <select class="form-select" id="refundMethod">
                                <option value="original">Original Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="store_credit">Store Credit</option>
                                <option value="gift_card">Gift Card</option>
                            </select>
                        </div>
                        <div class="col-md-6 text-end">
                            <label class="form-label">Refund Total</label>
                            <h3 class="text-danger" id="refundTotal">$0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="processReturnBtn" disabled>
                    <i class="bi bi-arrow-return-left"></i> Process Return
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Gift Card Modal -->
<div class="modal fade" id="giftCardModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ec4899, #db2777); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-gift-fill"></i> Gift Card
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills mb-3" id="giftCardTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="gc-check-tab" data-bs-toggle="pill"
                            data-bs-target="#gc-check" type="button">
                            <i class="bi bi-search"></i> Check Balance
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="gc-sell-tab" data-bs-toggle="pill" data-bs-target="#gc-sell"
                            type="button">
                            <i class="bi bi-cart-plus"></i> Sell New
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="gc-redeem-tab" data-bs-toggle="pill" data-bs-target="#gc-redeem"
                            type="button">
                            <i class="bi bi-check-circle"></i> Redeem
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="giftCardTabContent">
                    <!-- Check Balance -->
                    <div class="tab-pane fade show active" id="gc-check">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" class="form-control" id="gcBalanceCode"
                                placeholder="Enter or scan gift card code...">
                            <button class="btn btn-primary" type="button" id="checkGcBalanceBtn">Check</button>
                        </div>
                        <div id="gcBalanceResult" class="alert alert-info d-none">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Current Balance:</span>
                                <h3 class="mb-0" id="gcBalanceAmount">$0.00</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Sell New -->
                    <div class="tab-pane fade" id="gc-sell">
                        <div class="mb-3">
                            <label class="form-label">Gift Card Amount</label>
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <button type="button" class="btn btn-outline-primary gc-amount-btn"
                                    data-amount="25">$25</button>
                                <button type="button" class="btn btn-outline-primary gc-amount-btn"
                                    data-amount="50">$50</button>
                                <button type="button" class="btn btn-outline-primary gc-amount-btn"
                                    data-amount="75">$75</button>
                                <button type="button" class="btn btn-outline-primary gc-amount-btn"
                                    data-amount="100">$100</button>
                                <button type="button" class="btn btn-outline-primary gc-amount-btn"
                                    data-amount="150">$150</button>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="gcCustomAmount"
                                    placeholder="Custom amount...">
                            </div>
                        </div>
                        <button class="btn btn-success w-100" id="addGcToCartBtn">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                    </div>

                    <!-- Redeem -->
                    <div class="tab-pane fade" id="gc-redeem">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" class="form-control" id="gcRedeemCode"
                                placeholder="Enter gift card code...">
                        </div>
                        <div id="gcRedeemInfo" class="alert alert-success d-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Available Balance:</span>
                                <strong id="gcRedeemBalance">$0.00</strong>
                            </div>
                            <button class="btn btn-success w-100" id="applyGcBtn">
                                <i class="bi bi-check-circle"></i> Apply to Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Assistant Modal -->
<div class="modal fade" id="aiAssistantModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #ec4899, #be185d); color: white; border: none;">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bi bi-robot fs-4"></i> Nautilus AI Assistant
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Chat Messages -->
                <div id="aiChatMessages" class="p-3" style="height: 350px; overflow-y: auto; background: #f8fafc;">
                    <div class="d-flex gap-3 mb-3">
                        <div class="ai-avatar">
                            <i class="bi bi-robot fs-4 text-primary"></i>
                        </div>
                        <div class="ai-message p-3 rounded-3" style="background: white; max-width: 80%;">
                            <p class="mb-2">Hi! I'm your AI assistant. I can help you with:</p>
                            <ul class="mb-0 small">
                                <li>Finding products by description</li>
                                <li>Looking up customer information</li>
                                <li>Checking inventory levels</li>
                                <li>Answering diving equipment questions</li>
                                <li>Processing complex transactions</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="p-3 bg-light border-top">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button class="btn btn-sm btn-outline-primary ai-quick-btn"
                            data-prompt="Show me low stock items">
                            <i class="bi bi-box"></i> Low Stock
                        </button>
                        <button class="btn btn-sm btn-outline-primary ai-quick-btn"
                            data-prompt="What's our bestseller today?">
                            <i class="bi bi-star"></i> Top Seller
                        </button>
                        <button class="btn btn-sm btn-outline-primary ai-quick-btn"
                            data-prompt="Find dive gear under $100">
                            <i class="bi bi-search"></i> Budget Gear
                        </button>
                        <button class="btn btn-sm btn-outline-primary ai-quick-btn" data-prompt="Show today's schedule">
                            <i class="bi bi-calendar"></i> Schedule
                        </button>
                        <button class="btn btn-sm btn-outline-primary ai-quick-btn" data-prompt="Camera scan product">
                            <i class="bi bi-camera"></i> Scan Item
                        </button>
                    </div>

                    <!-- Input -->
                    <div class="input-group">
                        <input type="text" class="form-control" id="aiChatInput" placeholder="Ask me anything..."
                            style="border-radius: 50px 0 0 50px;">
                        <button class="btn btn-primary px-4" id="aiChatSend" style="border-radius: 0 50px 50px 0;">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confetti Canvas -->
<canvas id="confettiCanvas"
    style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; pointer-events: none; z-index: 9999; display: none;"></canvas>

<!-- Toast Notification -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 10000">
    <div id="posToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Toast Message
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
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
<script>
    window.posConfig = {
        taxRate: ' . $taxRate . '
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.11.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.1"></script>
<script src="/assets/js/ai-image-search.js"></script>
<script src="/assets/js/pos-course-enrollment.js"></script>
<script src="/assets/js/pos-quick-actions.js"></script>
<script src="/assets/js/pos-enhanced.js"></script>
<script src="/assets/js/pos-customer-risk.js"></script>
<script src="/assets/js/pos-shortcuts.js"></script>
<script src="/assets/js/modern-pos.js?v=' . time() . '"></script>
';

require __DIR__ . '/../layouts/admin.php';
?>