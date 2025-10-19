<?php
$pageTitle = 'Point of Sale';
$activeMenu = 'pos';
$user = currentUser();

// Add mobile-responsive CSS
$additionalCss = '<link rel="stylesheet" href="/assets/css/mobile-pos.css">';

ob_start();
?>

<div class="row mb-3">
    <div class="col-12">
        <h1 class="h3 mb-0">
            <i class="bi bi-cart-check"></i> Point of Sale
        </h1>
    </div>
</div>

<div class="row pos-container">
    <div class="col-md-8 pos-products-section">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-search"></i> Product Search
                </h5>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="productSearch" class="form-control" placeholder="Search products by name or SKU...">
                </div>
                <div id="searchResults" class="mt-2"></div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-grid"></i> Products
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3" id="productGrid">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card h-100 product-card" data-product-id="<?= $product['id'] ?>" data-product-name="<?= htmlspecialchars($product['name']) ?>" data-product-price="<?= $product['retail_price'] ?>" data-product-sku="<?= htmlspecialchars($product['sku']) ?>">
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                                <p class="card-text small text-muted mb-1">SKU: <?= htmlspecialchars($product['sku']) ?></p>
                                <p class="card-text">
                                    <strong class="text-primary"><?= formatCurrency($product['retail_price']) ?></strong>
                                </p>
                                <?php if ($product['track_inventory']): ?>
                                <p class="card-text small">
                                    <span class="badge <?= $product['stock_quantity'] <= $product['low_stock_threshold'] ? 'bg-warning' : 'bg-success' ?>">
                                        Stock: <?= $product['stock_quantity'] ?>
                                    </span>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <button class="btn btn-sm btn-primary w-100 add-to-cart">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 pos-cart-section">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-cart3"></i> Cart
                    <span id="cartCount" class="badge bg-light text-dark ms-2">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div id="cartItems" class="mb-3">
                    <p class="text-muted text-center">Cart is empty</p>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span id="cartSubtotal">$0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax (8%):</span>
                    <span id="cartTax">$0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong id="cartTotal">$0.00</strong>
                </div>
                
                <div class="mb-3">
                    <label for="customerSelect" class="form-label">Customer *</label>
                    <select id="customerSelect" class="form-select" required>
                        <option value="">Select Customer...</option>
                        <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>">
                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                            <?php if ($customer['company_name']): ?>
                            (<?= htmlspecialchars($customer['company_name']) ?>)
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Payment Method *</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="paymentMethod" id="paymentCash" value="cash" checked>
                        <label class="btn btn-outline-success" for="paymentCash">
                            <i class="bi bi-cash"></i> Cash
                        </label>
                        
                        <input type="radio" class="btn-check" name="paymentMethod" id="paymentCard" value="card">
                        <label class="btn btn-outline-primary" for="paymentCard">
                            <i class="bi bi-credit-card"></i> Card
                        </label>
                        
                        <input type="radio" class="btn-check" name="paymentMethod" id="paymentCheck" value="check">
                        <label class="btn btn-outline-info" for="paymentCheck">
                            <i class="bi bi-receipt"></i> Check
                        </label>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button id="clearCartBtn" class="btn btn-outline-danger" disabled>
                        <i class="bi bi-trash"></i> Clear Cart
                    </button>
                    <button id="checkoutBtn" class="btn btn-success btn-lg btn-checkout" disabled>
                        <i class="bi bi-check-circle"></i> Complete Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Floating Action Button -->
<div class="fab-cart d-md-none" id="fabCart">
    <i class="bi bi-cart3"></i>
    <span class="cart-badge" id="fabCartBadge" style="display: none;">0</span>
</div>

<!-- Loading Spinner Overlay -->
<div class="spinner-overlay" id="spinnerOverlay">
    <div class="spinner"></div>
</div>

<?php
$content = ob_get_clean();

$additionalJs = '<script>
let cart = [];
const TAX_RATE = 0.08;
let isMobile = window.innerWidth <= 767;

// Update isMobile on resize
$(window).on("resize", function() {
    isMobile = window.innerWidth <= 767;
});

$(document).ready(function() {
    // Mobile FAB cart toggle
    $("#fabCart").on("click", function() {
        const $cartSection = $(".pos-cart-section");

        if ($cartSection.hasClass("show-cart")) {
            $cartSection.removeClass("show-cart");
        } else {
            $cartSection.addClass("show-cart");
            // Scroll to cart on mobile
            $("html, body").animate({
                scrollTop: $cartSection.offset().top - 20
            }, 300);
        }
    });

    // Close mobile cart when clicking outside
    $(document).on("click", function(e) {
        if (isMobile && !$(e.target).closest(".pos-cart-section, #fabCart").length) {
            $(".pos-cart-section").removeClass("show-cart");
        }
    });

    // Touch-friendly product card interactions
    $(".product-card").on("touchstart", function() {
        $(this).addClass("touching");
    }).on("touchend touchcancel", function() {
        $(this).removeClass("touching");
    });

    // Improved search with mobile keyboard handling
    $("#productSearch").on("input", debounce(function() {
        const query = $(this).val();
        
        if (query.length < 2) {
            $("#searchResults").html("");
            return;
        }
        
        $.get("/pos/search", { q: query }, function(products) {
            if (products.length === 0) {
                $("#searchResults").html("<p class=\"text-muted small\">No products found</p>");
                return;
            }
            
            let html = "<div class=\"list-group\">";
            products.forEach(function(product) {
                html += `<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center add-to-cart-search" data-product-id="${product.id}" data-product-name="${product.name}" data-product-price="${product.retail_price}" data-product-sku="${product.sku}">
                    <div>
                        <strong>${product.name}</strong><br>
                        <small class="text-muted">SKU: ${product.sku}</small>
                    </div>
                    <span class="badge bg-primary">${formatCurrency(product.retail_price)}</span>
                </button>`;
            });
            html += "</div>";
            
            $("#searchResults").html(html);
        });
    }, 300));
    
    $(document).on("click", ".add-to-cart, .add-to-cart-search", function() {
        const $source = $(this).hasClass("add-to-cart") ? $(this).closest(".product-card") : $(this);
        const productId = $source.data("product-id");
        const productName = $source.data("product-name");
        const productPrice = parseFloat($source.data("product-price"));
        const productSku = $source.data("product-sku");

        const existingItem = cart.find(item => item.product_id === productId);

        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({
                product_id: productId,
                name: productName,
                price: productPrice,
                sku: productSku,
                quantity: 1
            });
        }

        updateCart();
        $("#searchResults").html("");
        $("#productSearch").val("");

        // Mobile: Show brief feedback and scroll to cart
        if (isMobile) {
            const $button = $(this);
            const originalHtml = $button.html();
            $button.html("<i class=\"bi bi-check-circle-fill\"></i> Added!");
            setTimeout(() => {
                $button.html(originalHtml);
            }, 1000);
        }
    });
    
    $(document).on("click", ".remove-item", function() {
        const index = $(this).data("index");
        cart.splice(index, 1);
        updateCart();
    });
    
    $(document).on("click", ".qty-minus", function() {
        const index = $(this).data("index");
        if (cart[index].quantity > 1) {
            cart[index].quantity--;
            updateCart();
        }
    });
    
    $(document).on("click", ".qty-plus", function() {
        const index = $(this).data("index");
        cart[index].quantity++;
        updateCart();
    });
    
    $("#clearCartBtn").on("click", function() {
        if (confirm("Clear all items from cart?")) {
            cart = [];
            updateCart();
        }
    });
    
    $("#checkoutBtn").on("click", function() {
        const customerId = $("#customerSelect").val();
        const paymentMethod = $("input[name=\"paymentMethod\"]:checked").val();

        if (!customerId) {
            alert("Please select a customer");
            return;
        }

        if (cart.length === 0) {
            alert("Cart is empty");
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const total = Math.round((subtotal * (1 + TAX_RATE)) * 100) / 100;

        $(this).prop("disabled", true).html("<span class=\"spinner-border spinner-border-sm\"></span> Processing...");

        // Show loading spinner overlay
        $("#spinnerOverlay").addClass("active");

        $.ajax({
            url: "/pos/checkout",
            method: "POST",
            data: {
                customer_id: customerId,
                items: JSON.stringify(cart),
                payment_method: paymentMethod,
                amount_paid: total,
                csrf_token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    $("#spinnerOverlay").removeClass("active");
                    alert("Error: " + (response.error || "Unknown error"));
                    $("#checkoutBtn").prop("disabled", false).html("<i class=\"bi bi-check-circle\"></i> Complete Sale");
                }
            },
            error: function(xhr) {
                $("#spinnerOverlay").removeClass("active");
                const response = xhr.responseJSON || {};
                alert("Error: " + (response.error || xhr.responseText || "Payment processing failed"));
                $("#checkoutBtn").prop("disabled", false).html("<i class=\"bi bi-check-circle\"></i> Complete Sale");
            }
        });
    });
});

function updateCart() {
    const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);

    // Update cart count badge
    $("#cartCount").text(itemCount);

    // Update FAB badge on mobile
    if (itemCount > 0) {
        $("#fabCartBadge").text(itemCount).show();
    } else {
        $("#fabCartBadge").hide();
    }

    if (cart.length === 0) {
        $("#cartItems").html("<p class=\"text-muted text-center\">Cart is empty</p>");
        $("#clearCartBtn, #checkoutBtn").prop("disabled", true);
        $("#cartSubtotal, #cartTax, #cartTotal").text("$0.00");
        return;
    }

    let html = "";
    cart.forEach(function(item, index) {
        html += `<div class="cart-item">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1">
                    <div class="cart-item-name">${item.name}</div>
                    <small class="text-muted">SKU: ${item.sku}</small><br>
                    <small class="cart-item-price">${formatCurrency(item.price)} each</small>
                </div>
                <button class="btn btn-sm btn-danger remove-item" data-index="${index}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="cart-quantity-control">
                <button class="btn btn-sm btn-outline-secondary qty-minus" data-index="${index}">
                    <i class="bi bi-dash"></i>
                </button>
                <input type="number" class="form-control form-control-sm" value="${item.quantity}" readonly>
                <button class="btn btn-sm btn-outline-secondary qty-plus" data-index="${index}">
                    <i class="bi bi-plus"></i>
                </button>
                <span class="ms-2 fw-bold">${formatCurrency(item.price * item.quantity)}</span>
            </div>
        </div>`;
    });

    $("#cartItems").html(html);

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = Math.round((subtotal * TAX_RATE) * 100) / 100;
    const total = Math.round((subtotal + tax) * 100) / 100;

    $("#cartSubtotal").text(formatCurrency(subtotal));
    $("#cartTax").text(formatCurrency(tax));
    $("#cartTotal").text(formatCurrency(total));

    $("#clearCartBtn, #checkoutBtn").prop("disabled", false);
}

function formatCurrency(amount) {
    return "$" + parseFloat(amount).toFixed(2);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>';

require __DIR__ . '/../layouts/app.php';
?>
