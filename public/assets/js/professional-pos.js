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

    // ============================================================================
    // Initialization
    // ============================================================================

    document.addEventListener('DOMContentLoaded', function() {
        initializeEventListeners();
        loadTaxRate();
        updateCartDisplay();
    });

    // ============================================================================
    // Event Listeners
    // ============================================================================

    function initializeEventListeners() {
        // Customer selection
        const customerSelect = document.getElementById('customerSelect');
        if (customerSelect) {
            customerSelect.addEventListener('change', function() {
                selectedCustomerId = this.value || null;
            });
        }

        // Customer creation form
        const addCustomerForm = document.getElementById('addCustomerForm');
        if (addCustomerForm) {
            addCustomerForm.addEventListener('submit', handleCustomerCreation);
        }

        // Product tile clicks
        document.querySelectorAll('.product-tile').forEach(tile => {
            tile.addEventListener('click', function() {
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
        const tax = subtotal * taxRate;
        const total = subtotal + tax;

        updateTotals(subtotal, tax, total);
    }

    function updateTotals(subtotal, tax, total) {
        const subtotalEl = document.getElementById('subtotalAmount');
        const taxEl = document.getElementById('taxAmount');
        const totalEl = document.getElementById('grandTotalAmount');

        if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
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
    // Customer Management
    // ============================================================================

    function handleCustomerCreation(e) {
        e.preventDefault();

        const formData = {
            first_name: document.getElementById('firstName').value.trim(),
            last_name: document.getElementById('lastName').value.trim(),
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            newsletter_opt_in: document.getElementById('newsletterOptIn').checked
        };

        // Validate required fields
        if (!formData.first_name || !formData.last_name) {
            showToast('First and last name are required', 'error');
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
        fetch('/api/transactions/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(transactionData)
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingOverlay();

            if (data.success) {
                showToast('Transaction completed successfully!', 'success');

                // Print receipt if requested
                if (confirm('Print receipt?')) {
                    printReceipt(data.transaction_id);
                }

                // Clear cart and reset
                cart = [];
                selectedCustomerId = null;
                document.getElementById('customerSelect').value = '';
                document.querySelectorAll('input[name="paymentMethod"]').forEach(r => r.checked = false);
                document.getElementById('paymentCash').checked = true;
                selectedPaymentMethod = 'cash';

                updateCartDisplay();
            } else {
                showToast(data.message || 'Transaction failed', 'error');
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
            if (category === 'all' || tile.dataset.productCategory === category) {
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
    // Public API
    // ============================================================================

    window.posApp = {
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        showCourseAddonsModal
    };

})();
