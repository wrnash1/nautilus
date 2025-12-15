/**
 * Modern POS JavaScript
 * Enhanced functionality and user experience
 */

// Global cart state
let cart = [];
const TAX_RATE = 0.08;
let isMobile = window.innerWidth <= 991;

// Utility Functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

const showToast = (message, type = 'info') => {
    const toast = document.getElementById('posToast');
    // If toast element doesn't exist, create it or fallback to alert/console
    if (!toast) {
        // Fallback to console if toast is missing, but keep it clean
        // console.log(`[${type}] ${message}`);
        return;
    }

    const toastBody = toast.querySelector('.toast-body');

    toast.classList.remove('toast-success', 'toast-error', 'toast-info');
    toast.classList.add(`toast-${type}`);
    toastBody.textContent = message;

    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
};

// Clock Update
const updateClock = () => {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    const dateString = now.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric'
    });

    const timeEl = document.getElementById('posCurrentTime');
    if (timeEl) timeEl.textContent = timeString;

    const dateEl = document.getElementById('posCurrentDate');
    if (dateEl) dateEl.textContent = dateString;
};

// Confetti Animation
const triggerConfetti = () => {
    const canvas = document.getElementById('confettiCanvas');
    if (!canvas) return; // Guard clause

    const ctx = canvas.getContext('2d');

    canvas.style.display = 'block';
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const confetti = [];
    const confettiCount = 150;
    const colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'];

    for (let i = 0; i < confettiCount; i++) {
        confetti.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height - canvas.height,
            r: Math.random() * 6 + 4,
            d: Math.random() * confettiCount,
            color: colors[Math.floor(Math.random() * colors.length)],
            tilt: Math.floor(Math.random() * 10) - 10,
            tiltAngleIncremental: (Math.random() * 0.07) + 0.05,
            tiltAngle: 0
        });
    }

    let animationFrame;
    const draw = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        confetti.forEach((particle, i) => {
            ctx.beginPath();
            ctx.lineWidth = particle.r / 2;
            ctx.strokeStyle = particle.color;
            ctx.moveTo(particle.x + particle.tilt + (particle.r / 4), particle.y);
            ctx.lineTo(particle.x + particle.tilt, particle.y + particle.tilt + (particle.r / 4));
            ctx.stroke();

            particle.tiltAngle += particle.tiltAngleIncremental;
            particle.y += (Math.cos(particle.d) + 3 + particle.r / 2) / 2;
            particle.tilt = Math.sin(particle.tiltAngle - (i / 3)) * 15;

            if (particle.y > canvas.height) {
                confetti[i] = {
                    x: Math.random() * canvas.width,
                    y: -10,
                    r: particle.r,
                    d: particle.d,
                    color: particle.color,
                    tilt: particle.tilt,
                    tiltAngleIncremental: particle.tiltAngleIncremental,
                    tiltAngle: particle.tiltAngle
                };
            }
        });

        animationFrame = requestAnimationFrame(draw);
    };

    draw();

    // Stop after 3 seconds
    setTimeout(() => {
        cancelAnimationFrame(animationFrame);
        canvas.style.display = 'none';
    }, 3000);
};

// Ripple Effect
const createRipple = (event, element) => {
    const ripple = element.querySelector('.product-ripple');
    if (!ripple) return;

    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = `${size}px`;
    ripple.style.left = `${x}px`;
    ripple.style.top = `${y}px`;
    ripple.style.transform = 'scale(0)';
    ripple.style.animation = 'none';

    setTimeout(() => {
        ripple.style.animation = 'ripple-animation 0.6s ease-out';
    }, 10);
};

// Cart Functions
const updateCart = () => {
    const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);

    // Update badges
    const cartItemCount = document.getElementById('cartItemCount');
    if (cartItemCount) cartItemCount.textContent = `${itemCount}`;

    const fabBadge = document.getElementById('fabBadge');
    if (fabBadge) fabBadge.textContent = itemCount;

    const container = document.getElementById('cartItemsList');
    if (!container) return; // Safety check

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart-state">
                <i class="bi bi-cart-x"></i>
                <p>Your cart is empty</p>
                <small>Click items on the left to add them</small>
            </div>
        `;
        const clearBtn = document.getElementById('clearCartBtn');
        if (clearBtn) clearBtn.disabled = true;

        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) checkoutBtn.disabled = true;

        document.getElementById('cartSubtotal').textContent = '$0.00';
        document.getElementById('cartTax').textContent = '$0.00';
        document.getElementById('cartTotal').textContent = '$0.00';
        return;
    }

    // Render cart items
    let html = '';
    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        html += `
            <div class="cart-item">
                <div class="cart-item-header">
                    <div>
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-sku">SKU: ${item.sku}</div>
                        <div class="cart-item-price">${formatCurrency(item.price)} each</div>
                    </div>
                    <button class="btn-remove-item" data-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="cart-quantity-controls">
                    <button class="btn-qty btn-qty-minus" data-index="${index}">
                        <i class="bi bi-dash"></i>
                    </button>
                    <span class="qty-display">${item.quantity}</span>
                    <button class="btn-qty btn-qty-plus" data-index="${index}">
                        <i class="bi bi-plus"></i>
                    </button>
                    <span class="ms-auto item-total">${formatCurrency(itemTotal)}</span>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Calculate totals
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = Math.round((subtotal * TAX_RATE) * 100) / 100;
    const total = Math.round((subtotal + tax) * 100) / 100;

    document.getElementById('cartSubtotal').textContent = formatCurrency(subtotal);
    document.getElementById('cartTax').textContent = formatCurrency(tax);
    document.getElementById('cartTotal').textContent = formatCurrency(total);

    const clearBtn = document.getElementById('clearCartBtn');
    if (clearBtn) clearBtn.disabled = false;

    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) checkoutBtn.disabled = false;

    // Add event listeners to new cart item buttons
    attachCartEventListeners();
};

const attachCartEventListeners = () => {
    // Remove item
    document.querySelectorAll('.btn-remove-item').forEach(btn => {
        btn.addEventListener('click', function () {
            const index = parseInt(this.dataset.index);
            cart.splice(index, 1);
            updateCart();
            showToast('Item removed from cart', 'info');
        });
    });

    // Decrease quantity
    document.querySelectorAll('.btn-qty-minus').forEach(btn => {
        btn.addEventListener('click', function () {
            const index = parseInt(this.dataset.index);
            if (cart[index].quantity > 1) {
                cart[index].quantity--;
                updateCart();
            }
        });
    });

    // Increase quantity
    document.querySelectorAll('.btn-qty-plus').forEach(btn => {
        btn.addEventListener('click', function () {
            const index = parseInt(this.dataset.index);
            cart[index].quantity++;
            updateCart();
        });
    });
};

const addToCart = (productData, event) => {
    // Ensure price is a number
    if (typeof productData.price === 'string') {
        productData.price = parseFloat(productData.price);
    }

    const existingItem = cart.find(item => item.product_id === productData.product_id);

    if (existingItem) {
        existingItem.quantity++;
        showToast(`${productData.name} quantity updated`, 'success');
    } else {
        cart.push({
            product_id: productData.product_id,
            name: productData.name,
            price: productData.price,
            sku: productData.sku,
            quantity: 1
        });
        showToast(`${productData.name} added to cart`, 'success');
    }

    updateCart();

    // Create ripple effect
    if (event) {
        const productCard = event.target.closest('.product-card-modern');
        if (productCard) {
            createRipple(event, productCard);
        }
    }
};

const clearCart = () => {
    if (cart.length === 0) return;

    if (confirm('Are you sure you want to clear all items from the cart?')) {
        cart = [];
        updateCart();
        showToast('Cart cleared', 'info');
    }
};

const processCheckout = async () => {
    const customerIdInput = document.getElementById('selectedCustomerId');
    const customerId = customerIdInput ? customerIdInput.value : null;
    const paymentElement = document.querySelector('input[name="paymentMethod"]:checked');
    const paymentMethod = paymentElement ? paymentElement.value : 'cash';

    if (!customerId) {
        showToast('Please select a customer', 'error');
        return;
    }

    if (cart.length === 0) {
        showToast('Cart is empty', 'error');
        return;
    }

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const total = Math.round((subtotal * (1 + TAX_RATE)) * 100) / 100;

    // Show loading overlay
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.add('active');

    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
    }

    try {
        const response = await fetch('/pos/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                customer_id: customerId,
                items: JSON.stringify(cart),
                payment_method: paymentMethod,
                amount_paid: total,
                csrf_token: typeof csrfToken !== 'undefined' ? csrfToken : ''
            })
        });

        const data = await response.json();

        if (data.success) {
            // Trigger confetti
            triggerConfetti();

            // Show success message
            showToast('Sale completed successfully!', 'success');

            // Redirect after a brief delay
            setTimeout(() => {
                window.location.href = data.redirect || '/store/pos';
            }, 1500);
        } else {
            throw new Error(data.error || 'Checkout failed');
        }
    } catch (error) {
        if (overlay) overlay.classList.remove('active');
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Complete Sale';
        }
        showToast(error.message || 'An error occurred during checkout', 'error');
    }
};

// Product Search
const searchProducts = debounce(async (query) => {
    const resultsContainer = document.getElementById('searchResults');
    const clearBtn = document.getElementById('clearSearch');

    if (query.length < 2) {
        if (resultsContainer) resultsContainer.innerHTML = '';
        if (clearBtn) clearBtn.style.display = 'none';
        return;
    }

    if (clearBtn) clearBtn.style.display = 'block';

    try {
        const response = await fetch(`/pos/search?q=${encodeURIComponent(query)}`);
        const products = await response.json();

        if (products.length === 0) {
            if (resultsContainer) resultsContainer.innerHTML = '<p class="text-muted text-center py-3">No products found</p>';
            return;
        }

        let html = '<div class="list-group">';
        products.forEach(product => {
            html += `
                <button type="button" class="list-group-item list-group-item-action search-result-item"
                        data-product-id="${product.id}"
                        data-product-name="${product.name}"
                        data-product-price="${product.retail_price}"
                        data-product-sku="${product.sku}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${product.name}</strong><br>
                            <small class="text-muted">SKU: ${product.sku}</small>
                        </div>
                        <span class="badge bg-primary">${formatCurrency(product.retail_price)}</span>
                    </div>
                </button>
            `;
        });
        html += '</div>';

        if (resultsContainer) resultsContainer.innerHTML = html;

        // Add click listeners to search results
        document.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', function (e) {
                addToCart({
                    product_id: parseInt(this.dataset.productId),
                    name: this.dataset.productName,
                    price: this.dataset.productPrice,
                    sku: this.dataset.productSku
                }, e);

                // Clear search
                document.getElementById('productSearch').value = '';
                if (resultsContainer) resultsContainer.innerHTML = '';
                if (clearBtn) clearBtn.style.display = 'none';
            });
        });
    } catch (error) {
        if (resultsContainer) resultsContainer.innerHTML = '<p class="text-danger text-center py-3">Error loading products</p>';
    }
}, 300);

// Category Filter
const filterByCategory = (category) => {
    const products = document.querySelectorAll('.product-card-modern');

    products.forEach(product => {
        if (category === 'all' || product.dataset.category === category) {
            product.style.display = ''; // Revert to CSS default (block)
        } else {
            product.style.display = 'none';
        }
    });
};

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', function () {
    // Update clock every second
    updateClock();
    setInterval(updateClock, 1000);

    // Update mobile status on resize
    window.addEventListener('resize', debounce(() => {
        isMobile = window.innerWidth <= 991;
    }, 250));

    // Product card click handlers
    const cards = document.querySelectorAll('.product-card-modern');
    cards.forEach(card => {
        const addBtn = card.querySelector('.btn-add-product');

        if (addBtn) {
            addBtn.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent default button action
                e.stopPropagation();
                addToCart({
                    product_id: card.dataset.productId,
                    name: card.dataset.productName,
                    price: card.dataset.productPrice,
                    sku: card.dataset.productSku
                }, e);
            });
        }

        // Double click on card to add
        card.addEventListener('dblclick', function (e) {
            e.preventDefault();
            addToCart({
                product_id: card.dataset.productId,
                name: card.dataset.productName,
                price: card.dataset.productPrice,
                sku: card.dataset.productSku
            }, e);
        });
    });

    // Search input
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            searchProducts(e.target.value);
        });
    }

    // Clear search button
    const clearSearchBtn = document.getElementById('clearSearch');
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            document.getElementById('productSearch').value = '';
            document.getElementById('searchResults').innerHTML = '';
            this.style.display = 'none';
        });
    }

    // Category filters
    document.querySelectorAll('.btn-category').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-category').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterByCategory(this.dataset.category);
        });
    });

    // Clear cart button
    const clearCartBtn = document.getElementById('clearCartBtn');
    if (clearCartBtn) clearCartBtn.addEventListener('click', clearCart);

    // Checkout button
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) checkoutBtn.addEventListener('click', processCheckout);

    // Mobile FAB cart button
    const fabCart = document.getElementById('fabCart');
    if (fabCart) {
        fabCart.addEventListener('click', function () {
            const cartSection = document.querySelector('.pos-cart-panel');
            if (cartSection) {
                cartSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        // Ctrl/Cmd + F for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            const search = document.getElementById('productSearch');
            if (search) search.focus();
        }

        // Ctrl/Cmd + Enter for checkout
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const btn = document.getElementById('checkoutBtn');
            if (btn && !btn.disabled) {
                e.preventDefault();
                processCheckout();
            }
        }

        // Escape to clear search
        if (e.key === 'Escape') {
            const search = document.getElementById('productSearch');
            if (search) search.value = '';

            const results = document.getElementById('searchResults');
            if (results) results.innerHTML = '';

            const clearBtn = document.getElementById('clearSearch');
            if (clearBtn) clearBtn.style.display = 'none';
        }
    });

    // Initialize cart
    updateCart();

    // Auto-Logout Logic
    let logoutTimer;
    const LOGOUT_TIME = 15 * 60; // 15 minutes in seconds
    let timeLeft = LOGOUT_TIME;
    const timerDisplay = document.getElementById('autoLogoutTimer');
    const countdownDisplay = document.getElementById('logoutCountdown');

    const resetTimer = () => {
        timeLeft = LOGOUT_TIME;
        if (timerDisplay) timerDisplay.style.display = 'none';
    };

    const updateLogoutTimer = () => {
        timeLeft--;

        if (timeLeft <= 60 && timerDisplay) {
            timerDisplay.style.display = 'block';
            const minutes = Math.floor(timeLeft / 60).toString().padStart(2, '0');
            const seconds = (timeLeft % 60).toString().padStart(2, '0');
            if (countdownDisplay) countdownDisplay.textContent = `${minutes}:${seconds}`;
        } else if (timerDisplay) {
            timerDisplay.style.display = 'none';
        }

        if (timeLeft <= 0) {
            window.location.href = '/store/logout';
        }
    };

    // Track activity
    const activityEvents = ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart'];
    activityEvents.forEach(event => {
        document.addEventListener(event, resetTimer);
    });

    // Customer Selection Logic (New)
    const setCustomer = async (id, name) => {
        try {
            await fetch('/store/pos/set-customer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    customer_id: id,
                    csrf_token: typeof csrfToken !== 'undefined' ? csrfToken : ''
                })
            });

            // Update UI
            document.getElementById('customerSearchInput').value = name;
            document.getElementById('selectedCustomerId').value = id;
            document.getElementById('clearCustomerBtn').style.display = 'block';

            // Also update badge text
            const display = document.getElementById('customerNameDisplay');
            if (display) {
                display.textContent = name;
                document.getElementById('activeCustomerBadge').classList.remove('d-none');
                document.getElementById('activeCustomerBadge').classList.add('d-flex');
            }

            showToast(`Customer set to ${name}`, 'success');
        } catch (error) {
            console.error('Failed to set customer context', error);
        }
    };

    const clearCustomer = async () => {
        try {
            await fetch('/store/pos/clear-customer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    csrf_token: typeof csrfToken !== 'undefined' ? csrfToken : ''
                })
            });

            // Update UI
            document.getElementById('customerSearchInput').value = '';
            document.getElementById('selectedCustomerId').value = '';
            document.getElementById('clearCustomerBtn').style.display = 'none';

            const display = document.getElementById('customerNameDisplay');
            if (display) {
                display.textContent = 'Walk-In';
                document.getElementById('activeCustomerBadge').classList.add('d-none');
                document.getElementById('activeCustomerBadge').classList.remove('d-flex');
            }

            showToast('Customer cleared', 'info');
        } catch (error) {
            console.error('Failed to clear customer context', error);
        }
    };

    const clearCustomerBtn = document.getElementById('clearCustomerBtn');
    if (clearCustomerBtn) {
        clearCustomerBtn.addEventListener('click', clearCustomer);
    }

    // Expose for usage in HTML onclicks if needed
    window.setPosCustomer = setCustomer;

    // Start timer
    setInterval(updateLogoutTimer, 1000);
});
