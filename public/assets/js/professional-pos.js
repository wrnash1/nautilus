/**
 * Professional POS JavaScript
 * Handles cart management, customer creation, text-to-speech, and payment processing
 */

(function() {
    'use strict';

    // ============================================================================
    // State Management
    // ============================================================================

    let cart = [];
    let selectedCustomerId = null;
    let selectedPaymentMethod = 'cash';
    let taxRate = 0.08; // 8% default, will be loaded from settings
    let appliedCoupon = null;
    let couponDiscount = 0;

    // ============================================================================
    // Initialization
    // ============================================================================

    document.addEventListener('DOMContentLoaded', function() {
        initializeEventListeners();
        loadTaxRate();
        updateCartDisplay();
        updateDateTime();
        // Update date/time every second
        setInterval(updateDateTime, 1000);
    });

    // ============================================================================
    // Event Listeners
    // ============================================================================

    function initializeEventListeners() {
        // Customer search
        const customerSearchInput = document.getElementById('customerSearchInput');
        if (customerSearchInput) {
            customerSearchInput.addEventListener('input', handleCustomerSearch);
            customerSearchInput.addEventListener('focus', function() {
                if (this.value.length >= 2) {
                    handleCustomerSearch();
                }
            });
        }

        const clearCustomerBtn = document.getElementById('clearCustomerBtn');
        if (clearCustomerBtn) {
            clearCustomerBtn.addEventListener('click', clearSelectedCustomer);
        }

        // Hide customer search results when clicking outside
        document.addEventListener('click', function(e) {
            const searchResults = document.getElementById('customerSearchResults');
            const searchInput = document.getElementById('customerSearchInput');
            if (searchResults && searchInput &&
                !searchResults.contains(e.target) &&
                !searchInput.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Customer creation form
        const addCustomerForm = document.getElementById('addCustomerForm');
        if (addCustomerForm) {
            addCustomerForm.addEventListener('submit', handleCustomerCreation);
        }

        // Product tile clicks
        document.querySelectorAll('.product-tile').forEach(tile => {
            tile.addEventListener('click', function() {
                // Check if this is a course with schedule selection
                if (this.classList.contains('course-tile') && this.dataset.courseId) {
                    // Show course schedule selection modal
                    if (typeof window.showCourseScheduleModal === 'function') {
                        window.showCourseScheduleModal(
                            this.dataset.courseId,
                            this.dataset.productName,
                            this.dataset.productPrice
                        );
                    } else {
                        console.error('Course schedule modal function not loaded');
                    }
                    return;
                }

                const productData = {
                    id: this.dataset.productId,
                    name: this.dataset.productName,
                    price: parseFloat(this.dataset.productPrice),
                    type: this.dataset.productType,
                    sku: this.dataset.productSku
                };

                // If it's a course, show add-ons modal
                if (productData.type === 'course') {
                    showCourseAddonsModal(productData);
                } else {
                    addToCart(productData);
                }
            });
        });

        // Payment method selection
        document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
            radio.addEventListener('change', function() {
                selectedPaymentMethod = this.value;
            });
        });

        // Clear cart button
        const clearCartBtn = document.getElementById('clearCartBtn');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', clearCart);
        }

        // Checkout button
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', handleCheckout);
        }

        // Category tabs
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const category = this.dataset.category;
                filterProductsByCategory(category);
            });
        });

        // Search functionality
        const searchInput = document.getElementById('productSearch');
        if (searchInput) {
            searchInput.addEventListener('input', handleSearch);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleBarcodeSearch(this.value);
                }
            });
        }

        // Clear search button
        const clearSearchBtn = document.querySelector('.btn-clear-search');
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                document.getElementById('productSearch').value = '';
                document.querySelector('.search-dropdown')?.remove();
                filterProductsByCategory('all');
            });
        }

        // Course add-ons form
        const courseAddonsForm = document.getElementById('courseAddonsForm');
        if (courseAddonsForm) {
            courseAddonsForm.addEventListener('submit', handleCourseAddons);

            // Update total when checkboxes change
            courseAddonsForm.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateCourseAddonsTotal);
            });
        }

        // Coupon/Discount functionality
        const applyCouponBtn = document.getElementById('applyCouponBtn');
        if (applyCouponBtn) {
            applyCouponBtn.addEventListener('click', handleApplyCoupon);
        }

        const removeCouponBtn = document.getElementById('removeCouponBtn');
        if (removeCouponBtn) {
            removeCouponBtn.addEventListener('click', handleRemoveCoupon);
        }

        const couponCodeInput = document.getElementById('couponCode');
        if (couponCodeInput) {
            couponCodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleApplyCoupon();
                }
            });
        }

        // AI Image Search functionality
        const aiSearchBtn = document.getElementById('aiSearchBtn');
        if (aiSearchBtn) {
            aiSearchBtn.addEventListener('click', showAISearchModal);
        }

        const aiSearchImageInput = document.getElementById('aiSearchImageInput');
        if (aiSearchImageInput) {
            aiSearchImageInput.addEventListener('change', handleImageUpload);
        }

        const aiSearchExecuteBtn = document.getElementById('aiSearchExecuteBtn');
        if (aiSearchExecuteBtn) {
            aiSearchExecuteBtn.addEventListener('click', executeAISearch);
        }
    }

    // ============================================================================
    // Cart Management
    // ============================================================================

    function addToCart(product, addons = []) {
        const existingItem = cart.find(item =>
            item.id === product.id && JSON.stringify(item.addons) === JSON.stringify(addons)
        );

        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                type: product.type,
                sku: product.sku,
                quantity: 1,
                addons: addons,
                addonTotal: addons.reduce((sum, addon) => sum + addon.price, 0)
            });
        }

        updateCartDisplay();
        showToast(`${product.name} added to cart`, 'success');
    }

    function removeFromCart(index) {
        const item = cart[index];
        cart.splice(index, 1);
        updateCartDisplay();
        showToast(`${item.name} removed from cart`, 'info');
    }

    function updateQuantity(index, newQuantity) {
        if (newQuantity <= 0) {
            removeFromCart(index);
            return;
        }

        cart[index].quantity = newQuantity;
        updateCartDisplay();
    }

    function clearCart() {
        if (cart.length === 0) return;

        if (confirm('Are you sure you want to clear the cart?')) {
            cart = [];
            // Also clear any applied coupon
            if (appliedCoupon) {
                handleRemoveCoupon();
            }
            updateCartDisplay();
            showToast('Cart cleared', 'info');
        }
    }

    function updateCartDisplay() {
        const cartItemsList = document.getElementById('cartItemsList');
        const emptyCartMessage = document.getElementById('emptyCartMessage');
        const cartItemCount = document.getElementById('cartItemCount');

        // Update item count
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        if (cartItemCount) {
            cartItemCount.textContent = totalItems;
        }

        // Show/hide empty message
        if (cart.length === 0) {
            if (emptyCartMessage) emptyCartMessage.style.display = 'block';
            if (cartItemsList) cartItemsList.innerHTML = '';
            updateTotals(0, 0, 0);
            return;
        }

        if (emptyCartMessage) emptyCartMessage.style.display = 'none';

        // Render cart items
        if (cartItemsList) {
            cartItemsList.innerHTML = cart.map((item, index) => {
                const itemTotal = (item.price + item.addonTotal) * item.quantity;
                const addonsHtml = item.addons.length > 0 ?
                    `<div class="text-muted small mt-1">
                        <i class="bi bi-plus-circle"></i> ${item.addons.map(a => a.name).join(', ')}
                    </div>` : '';

                return `
                    <div class="cart-item">
                        <div class="cart-item-header">
                            <div>
                                <div class="cart-item-name">${escapeHtml(item.name)}</div>
                                ${addonsHtml}
                            </div>
                            <div class="cart-item-price">$${item.price.toFixed(2)}</div>
                        </div>
                        <div class="cart-item-controls">
                            <div class="qty-controls">
                                <button type="button" class="qty-btn" onclick="posApp.updateQuantity(${index}, ${item.quantity - 1})">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <span class="qty-display">${item.quantity}</span>
                                <button type="button" class="qty-btn" onclick="posApp.updateQuantity(${index}, ${item.quantity + 1})">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <div class="item-total">$${itemTotal.toFixed(2)}</div>
                            <button type="button" class="btn-remove" onclick="posApp.removeFromCart(${index})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Update totals
        const subtotal = cart.reduce((sum, item) => sum + (item.price + item.addonTotal) * item.quantity, 0);
        const discount = couponDiscount || 0;
        const subtotalAfterDiscount = Math.max(0, subtotal - discount);
        const tax = subtotalAfterDiscount * taxRate;
        const total = subtotalAfterDiscount + tax;

        updateTotals(subtotal, discount, tax, total);
    }

    function updateTotals(subtotal, discount, tax, total) {
        const subtotalEl = document.getElementById('cartSubtotal');
        const discountEl = document.getElementById('cartDiscount');
        const discountRow = document.getElementById('discountRow');
        const taxEl = document.getElementById('cartTax');
        const totalEl = document.getElementById('cartTotal');

        if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;

        // Show/hide discount row
        if (discount > 0) {
            if (discountEl) discountEl.textContent = `-$${discount.toFixed(2)}`;
            if (discountRow) discountRow.style.display = 'flex';
        } else {
            if (discountRow) discountRow.style.display = 'none';
        }

        if (taxEl) taxEl.textContent = `$${tax.toFixed(2)}`;
        if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;

        // Enable/disable checkout button
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.disabled = cart.length === 0;
        }

        const clearCartBtn = document.getElementById('clearCartBtn');
        if (clearCartBtn) {
            clearCartBtn.disabled = cart.length === 0;
        }
    }

    // ============================================================================
    // Date/Time Display
    // ============================================================================

    function updateDateTime() {
        const now = new Date();

        // Update cart header datetime (old)
        const dateTimeEl = document.getElementById('posDateTime');
        if (dateTimeEl) {
            const options = {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            dateTimeEl.textContent = now.toLocaleDateString('en-US', options);
        }

        // Update header date
        const dateEl = document.getElementById('posCurrentDate');
        if (dateEl) {
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            dateEl.textContent = now.toLocaleDateString('en-US', dateOptions);
        }

        // Update header time
        const timeEl = document.getElementById('posCurrentTime');
        if (timeEl) {
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            timeEl.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }

    // ============================================================================
    // Customer Management
    // ============================================================================

    let customerSearchTimeout;

    function handleCustomerSearch() {
        clearTimeout(customerSearchTimeout);

        const searchInput = document.getElementById('customerSearchInput');
        const searchResults = document.getElementById('customerSearchResults');
        const query = searchInput.value.trim();

        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        customerSearchTimeout = setTimeout(() => {
            fetch(`/store/customers/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(customers => {
                    if (customers.length === 0) {
                        searchResults.innerHTML = '<div class="search-result-item text-muted">No customers found</div>';
                        searchResults.style.display = 'block';
                        return;
                    }

                    searchResults.innerHTML = customers.map(customer => {
                        const badge = customer.customer_type === 'B2B' ?
                            '<span class="badge bg-primary ms-2">B2B</span>' :
                            '<span class="badge bg-secondary ms-2">B2C</span>';
                        const company = customer.company_name ?
                            `<br><small class="text-muted">${escapeHtml(customer.company_name)}</small>` : '';

                        return `
                            <div class="search-result-item" onclick="selectCustomer(${customer.id}, '${escapeHtml(customer.first_name)} ${escapeHtml(customer.last_name)}', '${escapeHtml(customer.email || '')}', '${escapeHtml(customer.phone || '')}')">
                                <div>
                                    <strong>${escapeHtml(customer.first_name)} ${escapeHtml(customer.last_name)}</strong>
                                    ${badge}
                                    ${company}
                                </div>
                                <div class="text-muted small">
                                    ${customer.email ? '<i class="bi bi-envelope"></i> ' + escapeHtml(customer.email) : ''}
                                    ${customer.phone ? '<i class="bi bi-telephone ms-2"></i> ' + escapeHtml(customer.phone) : ''}
                                </div>
                            </div>
                        `;
                    }).join('');

                    searchResults.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error searching customers:', error);
                    searchResults.innerHTML = '<div class="search-result-item text-danger">Error searching customers</div>';
                    searchResults.style.display = 'block';
                });
        }, 300);
    }

    function clearSelectedCustomer() {
        selectedCustomerId = null;
        document.getElementById('customerSearchInput').value = '';
        document.getElementById('selectedCustomerId').value = '';
        document.getElementById('clearCustomerBtn').style.display = 'none';
        document.getElementById('customerInfo').style.display = 'none';
        document.getElementById('customerSearchResults').style.display = 'none';
    }

    function handleCustomerCreation(e) {
        e.preventDefault();

        const customerType = document.querySelector('input[name="customer_type"]:checked').value;
        const formData = {
            customer_type: customerType,
            first_name: document.getElementById('firstName').value.trim(),
            last_name: document.getElementById('lastName').value.trim(),
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            mobile: document.getElementById('mobile').value.trim(),
            birth_date: document.getElementById('birthDate').value,
            company_name: customerType === 'B2B' ? document.getElementById('companyName').value.trim() : null,
            emergency_contact_name: document.getElementById('emergencyContactName')?.value.trim(),
            emergency_contact_phone: document.getElementById('emergencyContactPhone')?.value.trim()
        };

        // Validate required fields
        if (!formData.first_name || !formData.last_name) {
            showToast('First and last name are required', 'error');
            return;
        }

        if (customerType === 'B2B' && !formData.company_name) {
            showToast('Company name is required for B2B customers', 'error');
            return;
        }

        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';

        // Send AJAX request
        fetch('/api/customers/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new customer to dropdown
                const customerSelect = document.getElementById('customerSelect');
                const option = new Option(
                    `${formData.first_name} ${formData.last_name}`,
                    data.customer_id,
                    true,
                    true
                );
                customerSelect.add(option);

                // Set as selected customer
                selectedCustomerId = data.customer_id;

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
                modal.hide();

                // Reset form
                e.target.reset();

                showToast(`Customer ${formData.first_name} ${formData.last_name} created successfully`, 'success');

                // Show newsletter confirmation if opted in
                if (formData.newsletter_opt_in) {
                    showToast('Customer subscribed to newsletter', 'info');
                }
            } else {
                showToast(data.message || 'Failed to create customer', 'error');
            }
        })
        .catch(error => {
            console.error('Error creating customer:', error);
            showToast('Failed to create customer. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // ============================================================================
    // Coupon/Discount Management
    // ============================================================================

    function handleApplyCoupon() {
        const couponCode = document.getElementById('couponCode').value.trim().toUpperCase();
        const couponMessage = document.getElementById('couponMessage');

        if (!couponCode) {
            showCouponMessage('Please enter a coupon code', 'error');
            return;
        }

        const subtotal = cart.reduce((sum, item) => {
            const itemTotal = (item.price + item.addonTotal) * item.quantity;
            return sum + itemTotal;
        }, 0);

        // Show loading state
        const applyCouponBtn = document.getElementById('applyCouponBtn');
        const originalText = applyCouponBtn.innerHTML;
        applyCouponBtn.disabled = true;
        applyCouponBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Validating...';

        // Validate coupon
        fetch('/store/marketing/coupons/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                code: couponCode,
                customer_id: selectedCustomerId,
                cart_total: subtotal
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                appliedCoupon = {
                    code: couponCode,
                    coupon_id: data.coupon_id,
                    discount: data.discount
                };
                couponDiscount = data.discount;

                // Update UI
                document.getElementById('appliedCouponCode').textContent = couponCode;
                document.getElementById('appliedCoupon').style.display = 'block';
                document.getElementById('couponCode').value = '';
                document.getElementById('couponCode').disabled = true;
                applyCouponBtn.disabled = true;

                updateCartDisplay();
                showCouponMessage(data.message, 'success');
            } else {
                showCouponMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error validating coupon:', error);
            showCouponMessage('Failed to validate coupon. Please try again.', 'error');
        })
        .finally(() => {
            applyCouponBtn.disabled = false;
            applyCouponBtn.innerHTML = originalText;
        });
    }

    function handleRemoveCoupon() {
        appliedCoupon = null;
        couponDiscount = 0;

        // Update UI
        document.getElementById('appliedCoupon').style.display = 'none';
        document.getElementById('couponCode').value = '';
        document.getElementById('couponCode').disabled = false;
        document.getElementById('applyCouponBtn').disabled = false;
        document.getElementById('couponMessage').innerHTML = '';

        updateCartDisplay();
    }

    function showCouponMessage(message, type) {
        const couponMessage = document.getElementById('couponMessage');
        const iconClass = type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill';
        const textClass = type === 'success' ? 'text-success' : 'text-danger';

        couponMessage.innerHTML = `
            <div class="${textClass}">
                <i class="bi bi-${iconClass}"></i> ${message}
            </div>
        `;

        if (type === 'error') {
            setTimeout(() => {
                couponMessage.innerHTML = '';
            }, 5000);
        }
    }

    // ============================================================================
    // Course Add-ons Management
    // ============================================================================

    function showCourseAddonsModal(product) {
        const modal = new bootstrap.Modal(document.getElementById('courseAddonsModal'));
        const form = document.getElementById('courseAddonsForm');

        // Store product data in form
        form.dataset.productId = product.id;
        form.dataset.productName = product.name;
        form.dataset.productPrice = product.price;
        form.dataset.productType = product.type;
        form.dataset.productSku = product.sku;

        // Update modal title
        document.getElementById('courseAddonsModalLabel').textContent = `Add-ons for ${product.name}`;

        // Reset checkboxes
        form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

        // Update initial total
        updateCourseAddonsTotal();

        modal.show();
    }

    function updateCourseAddonsTotal() {
        const form = document.getElementById('courseAddonsForm');
        const basePrice = parseFloat(form.dataset.productPrice);

        let addonsTotal = 0;
        form.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            addonsTotal += parseFloat(checkbox.dataset.price);
        });

        const total = basePrice + addonsTotal;

        document.getElementById('courseBasePrice').textContent = `$${basePrice.toFixed(2)}`;
        document.getElementById('courseAddonsTotal').textContent = `$${addonsTotal.toFixed(2)}`;
        document.getElementById('courseGrandTotal').textContent = `$${total.toFixed(2)}`;
    }

    function handleCourseAddons(e) {
        e.preventDefault();

        const form = e.target;
        const product = {
            id: form.dataset.productId,
            name: form.dataset.productName,
            price: parseFloat(form.dataset.productPrice),
            type: form.dataset.productType,
            sku: form.dataset.productSku
        };

        // Collect selected add-ons
        const addons = [];
        form.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            addons.push({
                id: checkbox.value,
                name: checkbox.dataset.name,
                price: parseFloat(checkbox.dataset.price)
            });
        });

        // Add to cart with add-ons
        addToCart(product, addons);

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('courseAddonsModal'));
        modal.hide();
    }

    // ============================================================================
    // Checkout and Payment Processing
    // ============================================================================

    function handleCheckout() {
        if (cart.length === 0) {
            showToast('Cart is empty', 'error');
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.price + item.addonTotal) * item.quantity, 0);
        const tax = subtotal * taxRate;
        const total = subtotal + tax;

        // Speak the total with tax
        speakTotal(total);

        // Prepare transaction data
        const transactionData = {
            customer_id: selectedCustomerId,
            payment_method: selectedPaymentMethod,
            subtotal: subtotal,
            tax: tax,
            total: total,
            items: cart.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price,
                addons: item.addons
            }))
        };

        // Show loading overlay
        showLoadingOverlay('Processing payment...');

        // Process payment based on method
        if (selectedPaymentMethod === 'bitcoin') {
            processBitcoinPayment(transactionData);
        } else {
            processStandardPayment(transactionData);
        }
    }

    function processStandardPayment(transactionData) {
        // Prepare form data for POST request
        const formData = new FormData();
        formData.append('customer_id', transactionData.customer_id || 0);
        formData.append('payment_method', transactionData.payment_method);
        formData.append('amount_paid', transactionData.total);
        formData.append('items', JSON.stringify(transactionData.items));

        fetch('/pos/checkout', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingOverlay();

            if (data.success) {
                showToast('Transaction completed successfully!', 'success');

                // Clear cart and reset BEFORE showing receipt prompt
                cart = [];
                selectedCustomerId = null;

                const customerSelect = document.getElementById('customerSelect');
                if (customerSelect) {
                    customerSelect.value = '';
                }

                // Hide customer info display
                const customerInfo = document.getElementById('customerInfo');
                if (customerInfo) {
                    customerInfo.style.display = 'none';
                }

                document.querySelectorAll('input[name="paymentMethod"]').forEach(r => r.checked = false);
                document.getElementById('paymentCash').checked = true;
                selectedPaymentMethod = 'cash';

                updateCartDisplay();

                // Redirect to receipt or ask to print
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (confirm('Print receipt?')) {
                    printReceipt(data.transaction_id);
                }
            } else {
                showToast(data.error || 'Transaction failed', 'error');
            }
        })
        .catch(error => {
            hideLoadingOverlay();
            console.error('Checkout error:', error);
            showToast('Failed to process transaction. Please try again.', 'error');
        });
    }

    function processBitcoinPayment(transactionData) {
        // Create Bitcoin invoice
        fetch('/api/payments/bitcoin/create-invoice', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                amount: transactionData.total,
                customer_id: transactionData.customer_id
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingOverlay();

            if (data.success) {
                // Show Bitcoin payment modal with QR code
                showBitcoinPaymentModal(data.invoice_url, data.invoice_id, transactionData);
            } else {
                showToast(data.message || 'Failed to create Bitcoin invoice', 'error');
            }
        })
        .catch(error => {
            hideLoadingOverlay();
            console.error('Bitcoin payment error:', error);
            showToast('Failed to create Bitcoin invoice. Please try again.', 'error');
        });
    }

    function showBitcoinPaymentModal(invoiceUrl, invoiceId, transactionData) {
        // Create modal dynamically
        const modalHtml = `
            <div class="modal fade" id="bitcoinPaymentModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title">
                                <i class="bi bi-currency-bitcoin"></i> Bitcoin Payment
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <p class="lead">Scan QR code or click to open payment page:</p>
                            <div class="mb-3">
                                <a href="${invoiceUrl}" target="_blank">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(invoiceUrl)}"
                                         alt="Bitcoin Payment QR Code" class="img-fluid">
                                </a>
                            </div>
                            <p class="text-muted">Waiting for payment confirmation...</p>
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('bitcoinPaymentModal'));
        modal.show();

        // Poll for payment confirmation
        const pollInterval = setInterval(() => {
            fetch(`/api/payments/bitcoin/check-status/${invoiceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'paid') {
                        clearInterval(pollInterval);
                        modal.hide();

                        // Complete the transaction
                        transactionData.bitcoin_invoice_id = invoiceId;
                        processStandardPayment(transactionData);
                    } else if (data.status === 'expired' || data.status === 'cancelled') {
                        clearInterval(pollInterval);
                        modal.hide();
                        showToast('Bitcoin payment cancelled or expired', 'error');
                    }
                });
        }, 3000); // Check every 3 seconds

        // Clean up on modal close
        document.getElementById('bitcoinPaymentModal').addEventListener('hidden.bs.modal', () => {
            clearInterval(pollInterval);
            document.getElementById('bitcoinPaymentModal').remove();
        });
    }

    // ============================================================================
    // Text-to-Speech
    // ============================================================================

    function speakTotal(total) {
        if ('speechSynthesis' in window) {
            // Cancel any ongoing speech
            window.speechSynthesis.cancel();

            // Create utterance
            const utterance = new SpeechSynthesisUtterance(
                `Your total is ${total.toFixed(2)} dollars`
            );

            // Configure voice settings
            utterance.rate = 0.9; // Slightly slower for clarity
            utterance.pitch = 1.0;
            utterance.volume = 1.0;

            // Speak
            window.speechSynthesis.speak(utterance);
        } else {
            console.warn('Text-to-speech not supported in this browser');
        }
    }

    // ============================================================================
    // Product Search and Filtering
    // ============================================================================

    function filterProductsByCategory(category) {
        // Update active tab
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.category === category);
        });

        // Filter products
        document.querySelectorAll('.product-tile').forEach(tile => {
            if (category === 'all' || tile.dataset.category === category) {
                tile.style.display = '';
            } else {
                tile.style.display = 'none';
            }
        });
    }

    function handleSearch(e) {
        const query = e.target.value.toLowerCase().trim();

        if (query.length < 2) {
            document.querySelector('.search-dropdown')?.remove();
            return;
        }

        // Search products
        const products = Array.from(document.querySelectorAll('.product-tile'));
        const matches = products.filter(tile => {
            const name = tile.dataset.productName.toLowerCase();
            const sku = tile.dataset.productSku.toLowerCase();
            return name.includes(query) || sku.includes(query);
        });

        // Show search results dropdown
        showSearchResults(matches, query);
    }

    function handleBarcodeSearch(barcode) {
        if (!barcode) return;

        // Find product by SKU (barcode)
        const product = Array.from(document.querySelectorAll('.product-tile')).find(
            tile => tile.dataset.productSku === barcode
        );

        if (product) {
            product.click();
            document.getElementById('productSearch').value = '';
            document.querySelector('.search-dropdown')?.remove();
        } else {
            showToast('Product not found', 'error');
        }
    }

    function showSearchResults(matches, query) {
        let dropdown = document.querySelector('.search-dropdown');

        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.className = 'search-dropdown';
            document.querySelector('.product-search-bar').appendChild(dropdown);
        }

        if (matches.length === 0) {
            dropdown.innerHTML = '<div class="search-result-item text-muted">No products found</div>';
            return;
        }

        dropdown.innerHTML = matches.slice(0, 10).map(tile => `
            <div class="search-result-item" data-product-id="${tile.dataset.productId}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${escapeHtml(tile.dataset.productName)}</strong>
                        <br><small class="text-muted">${tile.dataset.productSku}</small>
                    </div>
                    <div class="text-primary fw-bold">$${parseFloat(tile.dataset.productPrice).toFixed(2)}</div>
                </div>
            </div>
        `).join('');

        // Add click handlers
        dropdown.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const tile = document.querySelector(`.product-tile[data-product-id="${productId}"]`);
                if (tile) tile.click();

                document.getElementById('productSearch').value = '';
                dropdown.remove();
            });
        });
    }

    // ============================================================================
    // Utility Functions
    // ============================================================================

    function loadTaxRate() {
        fetch('/api/settings/tax-rate')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    taxRate = parseFloat(data.tax_rate) || 0.08;
                }
            })
            .catch(() => {
                console.warn('Failed to load tax rate, using default 8%');
            });
    }

    function printReceipt(transactionId) {
        window.open(`/transactions/${transactionId}/receipt`, '_blank');
    }

    function showLoadingOverlay(message = 'Loading...') {
        let overlay = document.querySelector('.loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-content">
                    <div class="spinner-border text-light mb-3" style="width: 4rem; height: 4rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="loading-message">${message}</p>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        overlay.classList.add('active');
    }

    function hideLoadingOverlay() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }

    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 10000;';
            document.body.appendChild(toastContainer);
        }

        // Icon mapping
        const icons = {
            success: 'bi-check-circle-fill',
            error: 'bi-x-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };

        // Create toast
        const toastId = `toast-${Date.now()}`;
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icons[type] || icons.info} me-2"></i>
                    ${escapeHtml(message)}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();

        // Remove from DOM after hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ============================================================================
    // AI Image Search
    // ============================================================================

    let currentSearchImage = null;

    function showAISearchModal() {
        const modal = new bootstrap.Modal(document.getElementById('aiSearchModal'));
        modal.show();

        // Reset modal state
        document.getElementById('aiSearchImagePreview').style.display = 'none';
        document.getElementById('aiSearchResults').innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-image" style="font-size: 3rem;"></i>
                <p class="mt-2">Upload an image to find matching products</p>
                <small>Works best with clear photos of diving equipment</small>
            </div>
        `;
        currentSearchImage = null;
    }

    function handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('aiSearchPreviewImg');
            preview.src = e.target.result;
            document.getElementById('aiSearchImagePreview').style.display = 'block';
            currentSearchImage = file;
        };
        reader.readAsDataURL(file);
    }

    async function executeAISearch() {
        if (!currentSearchImage) {
            showToast('Please select an image first', 'error');
            return;
        }

        const loadingEl = document.getElementById('aiSearchLoading');
        const previewEl = document.getElementById('aiSearchImagePreview');
        const resultsEl = document.getElementById('aiSearchResults');

        try {
            // Show loading state
            previewEl.style.display = 'none';
            loadingEl.style.display = 'block';
            resultsEl.innerHTML = '';

            // Perform AI search
            const results = await window.aiImageSearch.searchByImage(currentSearchImage, {
                maxResults: 10,
                minSimilarity: 0.4
            });

            // Hide loading
            loadingEl.style.display = 'none';
            previewEl.style.display = 'block';

            // Display results
            displayAISearchResults(results);

        } catch (error) {
            console.error('AI search error:', error);
            loadingEl.style.display = 'none';
            previewEl.style.display = 'block';

            resultsEl.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Search Error:</strong> ${error.message}
                    <br><small class="mt-2">Make sure AI models are loaded. Check console for details.</small>
                </div>
            `;
        }
    }

    function displayAISearchResults(results) {
        const resultsEl = document.getElementById('aiSearchResults');

        if (results.length === 0) {
            resultsEl.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    No similar products found. Try a different image or angle.
                </div>
            `;
            return;
        }

        resultsEl.innerHTML = results.map(result => {
            const confidenceBadge = getConfidenceBadge(result.similarity);
            const percentage = (result.similarity * 100).toFixed(1);

            return `
                <div class="ai-search-result-item" onclick="addProductFromAISearch(${result.product_id}, '${escapeHtml(result.name)}', ${result.price})">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            ${result.image_path ?
                                `<img src="${result.image_path}" alt="${escapeHtml(result.name)}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">` :
                                `<div style="width: 60px; height: 60px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>`
                            }
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">${escapeHtml(result.name)}</div>
                            <div class="small text-muted">SKU: ${escapeHtml(result.sku)} | ${result.category}</div>
                            <div class="small">
                                ${confidenceBadge}
                                <span class="text-muted">${percentage}% match</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-primary">$${result.price.toFixed(2)}</div>
                            <button class="btn btn-sm btn-outline-primary mt-1">
                                <i class="bi bi-cart-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function getConfidenceBadge(similarity) {
        if (similarity >= 0.9) return '<span class="badge bg-success">Very High</span>';
        if (similarity >= 0.8) return '<span class="badge bg-success">High</span>';
        if (similarity >= 0.7) return '<span class="badge bg-info">Good</span>';
        if (similarity >= 0.6) return '<span class="badge bg-warning">Moderate</span>';
        return '<span class="badge bg-secondary">Low</span>';
    }

    // ============================================================================
    // Public API
    // ============================================================================

    window.posApp = {
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        showCourseAddonsModal,
        setSelectedCustomer: function(id) {
            selectedCustomerId = id;
        }
    };

})();

// ============================================================================
// Global Helper Functions (outside IIFE for inline event handlers)
// ============================================================================

function togglePosCustomerType() {
    const isB2B = document.getElementById('posTypeB2B').checked;
    const b2bFields = document.getElementById('posB2bFields');
    const b2cFields = document.getElementById('posB2cFields');
    const birthDateField = document.getElementById('posBirthDateField');
    const companyName = document.getElementById('companyName');

    if (b2bFields) b2bFields.style.display = isB2B ? 'block' : 'none';
    if (b2cFields) b2cFields.style.display = isB2B ? 'none' : 'block';
    if (birthDateField) birthDateField.style.display = isB2B ? 'none' : 'block';

    if (companyName) {
        companyName.required = isB2B;
    }
}

function selectCustomer(id, name, email, phone) {
    // This function is called from onclick in search results
    const searchInput = document.getElementById('customerSearchInput');
    const searchResults = document.getElementById('customerSearchResults');
    const selectedIdInput = document.getElementById('selectedCustomerId');
    const clearBtn = document.getElementById('clearCustomerBtn');
    const customerInfo = document.getElementById('customerInfo');

    // Set selected customer
    window.posApp.setSelectedCustomer(id);
    selectedIdInput.value = id;

    // Update UI
    searchInput.value = name;
    searchResults.style.display = 'none';
    clearBtn.style.display = 'block';

    // Load full customer details including photo and certifications
    loadCustomerDetails(id, name, email, phone);

    // Show customer info panel
    customerInfo.style.display = 'block';
}

async function loadCustomerDetails(id, name, email, phone) {
    // Update basic info immediately
    document.getElementById('customerName').textContent = name;
    document.getElementById('customerEmail').textContent = email || '-';
    document.getElementById('customerPhone').textContent = phone || '-';

    try {
        // Fetch full customer details including photo and certifications
        const response = await fetch(`/store/api/customers/${id}/pos-info`);
        const data = await response.json();

        // Update photo
        const photoContainer = document.getElementById('customerPhoto');
        if (data.photo_path) {
            photoContainer.innerHTML = `
                <img src="${data.photo_path}" alt="${name}"
                     style="width: 70px; height: 70px; border-radius: 50%;
                            border: 3px solid #0066cc; object-fit: cover;">
            `;
        } else {
            photoContainer.innerHTML = `
                <i class="bi bi-person-circle" style="font-size: 4rem; color: #6c757d;"></i>
            `;
        }

        // Display certifications with agency logos
        const certsContainer = document.getElementById('customerCertifications');
        if (data.certifications && data.certifications.length > 0) {
            certsContainer.innerHTML = data.certifications.map(cert => {
                const agencyColor = cert.agency_color || '#0066cc';
                const agencyAbbr = cert.agency_abbreviation || cert.agency_name;
                const agencyLogo = cert.agency_logo_path;

                if (agencyLogo) {
                    // Show agency logo with certification name
                    return `
                        <div class="d-inline-block me-2 mb-1" style="background: white; padding: 4px 8px; border-radius: 6px; border: 2px solid ${agencyColor};">
                            <img src="${agencyLogo}" alt="${agencyAbbr}" style="height: 24px; vertical-align: middle; margin-right: 6px;">
                            <span style="font-size: 0.75rem; font-weight: 600; color: #333;">${cert.cert_name}</span>
                        </div>
                    `;
                } else {
                    // Fallback to colored badge
                    return `
                        <span class="badge me-1" style="background-color: ${agencyColor}; font-size: 0.75rem;">
                            ${agencyAbbr} - ${cert.cert_name}
                        </span>
                    `;
                }
            }).join('');
        } else {
            certsContainer.innerHTML = '<small class="text-muted">No certifications on file</small>';
        }
    } catch (error) {
        console.error('Error loading customer details:', error);
        // Basic info is already shown, so we can continue
    }
}

function addProductFromAISearch(productId, productName, price) {
    // Add product to cart from AI search results
    window.posApp.addToCart({
        id: productId,
        name: productName,
        price: price,
        type: 'product',
        sku: ''
    });

    // Close the AI search modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('aiSearchModal'));
    if (modal) {
        modal.hide();
    }
}
