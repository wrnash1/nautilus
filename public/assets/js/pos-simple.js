/**
 * Nautilus POS - Simple Layout JavaScript
 * Clean, focused functionality
 */

(function () {
    'use strict';

    // === STATE ===
    let cart = [];
    let selectedCustomer = null;
    let selectedPayment = 'cash';

    // === INITIALIZATION ===
    document.addEventListener('DOMContentLoaded', function () {
        initClock();
        initWeather();
        initClockInOut();
        initVoiceToggle();
        initCategories();
        initProductSearch();
        initCustomerSearch();
        initPaymentMethods();
        initQuickActions();
        initCheckout();
        loadProducts();
    });

    // === CLOCK ===
    function initClock() {
        function updateClock() {
            const now = new Date();
            const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const date = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });

            const timeEl = document.getElementById('posTime');
            const dateEl = document.getElementById('posDate');

            if (timeEl) timeEl.textContent = time;
            if (dateEl) dateEl.textContent = date;
        }

        updateClock();
        setInterval(updateClock, 1000);
    }

    // === WEATHER ===
    function initWeather() {
        async function fetchWeather() {
            try {
                // Get location (default to Tampa, FL)
                const lat = 27.9506;
                const lon = -82.4572;

                const response = await fetch(
                    `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code&temperature_unit=fahrenheit`
                );
                const data = await response.json();

                if (data.current) {
                    const temp = Math.round(data.current.temperature_2m);
                    const code = data.current.weather_code;

                    document.getElementById('weatherTemp').textContent = temp + '°F';
                    document.getElementById('weatherDesc').textContent = getWeatherDesc(code);
                    document.getElementById('weatherIcon').className = 'bi ' + getWeatherIcon(code);
                }
            } catch (e) {
                console.error('Weather fetch failed:', e);
                document.getElementById('weatherDesc').textContent = 'Unavailable';
            }
        }

        function getWeatherIcon(code) {
            if (code === 0) return 'bi-sun';
            if (code <= 3) return 'bi-cloud-sun';
            if (code <= 48) return 'bi-cloud';
            if (code <= 67) return 'bi-cloud-rain';
            if (code <= 77) return 'bi-cloud-snow';
            if (code <= 82) return 'bi-cloud-rain-heavy';
            return 'bi-cloud-lightning';
        }

        function getWeatherDesc(code) {
            if (code === 0) return 'Clear';
            if (code <= 3) return 'Partly Cloudy';
            if (code <= 48) return 'Cloudy';
            if (code <= 67) return 'Rain';
            if (code <= 77) return 'Snow';
            if (code <= 82) return 'Heavy Rain';
            return 'Thunderstorm';
        }

        fetchWeather();
        setInterval(fetchWeather, 600000); // Refresh every 10 min
    }

    // === CLOCK IN/OUT ===
    let clockedInTime = null;
    let clockDurationInterval = null;

    function initClockInOut() {
        const btn = document.getElementById('timeClockBtn');
        if (!btn) return;

        // Check if already clocked in (from session storage)
        const savedClockIn = sessionStorage.getItem('clockedInTime');
        if (savedClockIn) {
            clockedInTime = new Date(savedClockIn);
            updateClockButton(true);
            startDurationTimer();
        }

        btn.addEventListener('click', async function () {
            if (clockedInTime) {
                // Clock Out
                if (confirm('Clock out now?')) {
                    try {
                        await fetch('/store/api/time-clock/out', { method: 'POST' });
                    } catch (e) { console.error(e); }

                    clockedInTime = null;
                    sessionStorage.removeItem('clockedInTime');
                    clearInterval(clockDurationInterval);
                    updateClockButton(false);
                }
            } else {
                // Clock In
                try {
                    await fetch('/store/api/time-clock/in', { method: 'POST' });
                } catch (e) { console.error(e); }

                clockedInTime = new Date();
                sessionStorage.setItem('clockedInTime', clockedInTime.toISOString());
                updateClockButton(true);
                startDurationTimer();
            }
        });
    }

    function updateClockButton(isClockedIn) {
        const btn = document.getElementById('timeClockBtn');
        const statusEl = document.getElementById('clockStatus');
        const iconEl = document.getElementById('clockIcon');

        if (isClockedIn) {
            btn.classList.add('clocked-in');
            statusEl.textContent = 'Clock Out';
            iconEl.className = 'bi bi-box-arrow-right';
        } else {
            btn.classList.remove('clocked-in');
            statusEl.textContent = 'Clock In';
            iconEl.className = 'bi bi-person-badge';
            document.getElementById('clockDuration').textContent = '--:--:--';
        }
    }

    function startDurationTimer() {
        function update() {
            if (!clockedInTime) return;
            const elapsed = Date.now() - clockedInTime.getTime();
            const hours = Math.floor(elapsed / 3600000);
            const mins = Math.floor((elapsed % 3600000) / 60000);
            const secs = Math.floor((elapsed % 60000) / 1000);
            document.getElementById('clockDuration').textContent =
                `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        update();
        clockDurationInterval = setInterval(update, 1000);
    }

    // === VOICE TOGGLE ===
    function initVoiceToggle() {
        const toggle = document.getElementById('voiceToggle');
        if (!toggle) return;

        // Load saved preference
        if (localStorage.getItem('voiceFeedback') === 'true') {
            toggle.checked = true;
        }

        toggle.addEventListener('change', function () {
            localStorage.setItem('voiceFeedback', this.checked);
            if (this.checked) {
                speak('Voice feedback enabled');
            }
        });
    }

    function speak(text) {
        if (!localStorage.getItem('voiceFeedback') === 'true') return;
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 1.1;
            speechSynthesis.speak(utterance);
        }
    }

    // === CATEGORIES ===
    function initCategories() {
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filterProducts(this.dataset.category);
            });
        });
    }

    function filterProducts(category) {
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // === PRODUCT SEARCH ===
    function initProductSearch() {
        const input = document.getElementById('productSearch');
        if (!input) return;

        input.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name?.toLowerCase() || '';
                card.style.display = name.includes(query) ? '' : 'none';
            });
        });
    }

    // === PRODUCTS ===
    function loadProducts() {
        // Products are rendered server-side
        // Add click handlers
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function (e) {
                if (e.target.closest('.product-add-btn')) return; // Button handles itself
                addToCart(parseInt(this.dataset.id));
            });
        });
    }

    // === CART ===
    window.addToCart = function (productId) {
        const card = document.querySelector(`.product-card[data-id="${productId}"]`);
        if (!card) return;

        const existing = cart.find(item => item.id === productId);

        if (existing) {
            existing.qty++;
        } else {
            cart.push({
                id: productId,
                name: card.dataset.name,
                price: parseFloat(card.dataset.price),
                qty: 1
            });
        }

        renderCart();

        // Visual feedback
        card.style.transform = 'scale(0.95)';
        setTimeout(() => card.style.transform = '', 150);
    };

    function renderCart() {
        const container = document.getElementById('cartItems');
        if (!container) return;

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="cart-empty">
                    <i class="bi bi-cart-x"></i>
                    <p>Cart is empty</p>
                    <small>Tap products to add them</small>
                </div>
            `;
        } else {
            container.innerHTML = cart.map(item => `
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">$${item.price.toFixed(2)} each</div>
                    </div>
                    <div class="cart-item-qty">
                        <button class="qty-btn" onclick="updateQty(${item.id}, -1)">−</button>
                        <span class="qty-value">${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                    <div class="cart-item-total">$${(item.price * item.qty).toFixed(2)}</div>
                </div>
            `).join('');
        }

        updateTotals();
    }

    window.updateQty = function (productId, delta) {
        const item = cart.find(i => i.id === productId);
        if (!item) return;

        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== productId);
        }

        renderCart();
    };

    function updateTotals() {
        const taxRate = window.posConfig?.taxRate || 0.08;
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const tax = subtotal * taxRate;
        const total = subtotal + tax;

        document.getElementById('cartSubtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('cartTax').textContent = '$' + tax.toFixed(2);
        document.getElementById('cartTotal').textContent = '$' + total.toFixed(2);
    }

    window.clearCart = function () {
        cart = [];
        renderCart();
    };

    document.getElementById('clearCartBtn')?.addEventListener('click', function () {
        if (cart.length > 0 && confirm('Clear entire cart?')) {
            clearCart();
        }
    });

    // === CUSTOMER SEARCH ===
    function initCustomerSearch() {
        const input = document.getElementById('customerSearchInput');
        const results = document.getElementById('customerSearchResults');
        if (!input || !results) return;

        let debounceTimer;

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                results.innerHTML = '';
                results.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const response = await fetch(`/store/api/customers/search?q=${encodeURIComponent(query)}`);
                    const customers = await response.json();

                    if (customers.length > 0) {
                        results.innerHTML = customers.map(c => `
                            <div class="customer-result" data-id="${c.id}" data-name="${c.first_name} ${c.last_name}" data-phone="${c.phone || ''}">
                                <strong>${c.first_name} ${c.last_name}</strong>
                                <span>${c.phone || c.email || ''}</span>
                            </div>
                        `).join('');
                        results.style.display = 'block';

                        results.querySelectorAll('.customer-result').forEach(el => {
                            el.addEventListener('click', function () {
                                selectCustomer({
                                    id: this.dataset.id,
                                    name: this.dataset.name,
                                    phone: this.dataset.phone
                                });
                                results.style.display = 'none';
                                input.value = '';
                            });
                        });
                    } else {
                        results.innerHTML = '<div class="no-results">No customers found</div>';
                        results.style.display = 'block';
                    }
                } catch (e) {
                    console.error('Customer search failed:', e);
                }
            }, 300);
        });

        // Hide on click outside
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.cart-customer')) {
                results.style.display = 'none';
            }
        });

        // Clear customer
        document.getElementById('clearCustomerBtn')?.addEventListener('click', function () {
            selectedCustomer = null;
            document.getElementById('selectedCustomer').style.display = 'none';
            document.getElementById('selectedCustomerId').value = '';
        });
    }

    function selectCustomer(customer) {
        selectedCustomer = customer;
        document.getElementById('selectedCustomerId').value = customer.id;
        document.getElementById('customerName').textContent = customer.name;
        document.getElementById('customerPhone').textContent = customer.phone || '';
        document.getElementById('selectedCustomer').style.display = 'flex';
    }

    // === PAYMENT METHODS ===
    function initPaymentMethods() {
        document.querySelectorAll('.pay-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedPayment = this.dataset.method;
            });
        });
    }

    // === QUICK ACTIONS ===
    function initQuickActions() {
        document.getElementById('quickAirFillBtn')?.addEventListener('click', () => {
            addQuickItem('Air Fill', 8);
        });

        document.getElementById('quickNitroxBtn')?.addEventListener('click', () => {
            addQuickItem('Nitrox Fill', 12);
        });

        document.getElementById('quickTrimixBtn')?.addEventListener('click', () => {
            const mix = prompt('Enter Trimix mix (e.g., 18/45):');
            if (mix) addQuickItem(`Trimix Fill (${mix})`, 25);
        });
    }

    function addQuickItem(name, price) {
        const id = -Math.floor(Math.random() * 10000); // Negative ID for quick items
        cart.push({ id, name, price, qty: 1 });
        renderCart();
    }

    // === CHECKOUT ===
    function initCheckout() {
        document.getElementById('checkoutBtn')?.addEventListener('click', async function () {
            if (cart.length === 0) {
                alert('Cart is empty!');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

            try {
                const taxRate = window.posConfig?.taxRate || 0.08;
                const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                const tax = subtotal * taxRate;
                const total = subtotal + tax;

                const response = await fetch('/store/pos/checkout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        items: cart,
                        customer_id: selectedCustomer?.id || null,
                        payment_method: selectedPayment,
                        subtotal: subtotal,
                        tax: tax,
                        total: total
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert('Sale completed! Transaction #' + result.transaction_id);
                    clearCart();
                    selectedCustomer = null;
                    document.getElementById('selectedCustomer').style.display = 'none';
                } else {
                    throw new Error(result.error || 'Checkout failed');
                }
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-check-lg"></i> Complete Sale (F12)';
            }
        });
    }

    // === EXPOSE GLOBALS ===
    window.addToCart = addToCart;
    window.updateQty = updateQty;
    window.clearCart = clearCart;

})();
