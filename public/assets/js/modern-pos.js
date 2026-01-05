/**
 * Modern POS JavaScript
 * Enhanced functionality and user experience
 */

// Global cart state
let cart = [];
const TAX_RATE = window.posConfig?.taxRate || 0.08;
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

// Date/Time Clock handled below with Scuba Clock logic

// Pretty Cash Modal Logic
const promptCashAmount = (total) => {
    return new Promise((resolve) => {
        // Create modal HTML
        const modalId = 'cashPaymentModal';
        let modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-cash-coin"></i> Cash Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <h6 class="text-muted text-uppercase small">Total Amount Due</h6>
                                <h2 class="text-primary fw-bold" id="cashModalTotal">${formatCurrency(total)}</h2>
                            </div>
                            <div class="mb-3">
                                <label for="cashAmountInput" class="form-label">Amount Tendered</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="cashAmountInput" step="0.01" min="0" placeholder="0.00" autofocus>
                                </div>
                                <div class="form-text" id="changePreview">Change Due: $0.00</div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('cashAmountInput').value = ${total}; document.getElementById('cashAmountInput').dispatchEvent(new Event('input'))">Exact Amount</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('cashAmountInput').value = ${Math.ceil(total)}; document.getElementById('cashAmountInput').dispatchEvent(new Event('input'))">Next Dollar</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('cashAmountInput').value = ${Math.ceil(total / 5) * 5}; document.getElementById('cashAmountInput').dispatchEvent(new Event('input'))">$${5}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('cashAmountInput').value = ${Math.ceil(total / 10) * 10}; document.getElementById('cashAmountInput').dispatchEvent(new Event('input'))">$${10}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('cashAmountInput').value = ${Math.ceil(total / 20) * 20}; document.getElementById('cashAmountInput').dispatchEvent(new Event('input'))">$${20}</button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success btn-lg px-4" id="confirmCashPayment">
                                Complete Payment <i class="bi bi-check-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

        // Append to body (remove old one if exists)
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove();

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modalEl = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalEl);
        const input = document.getElementById('cashAmountInput');
        const confirmBtn = document.getElementById('confirmCashPayment');
        const changePreview = document.getElementById('changePreview');

        // Input validation and change preview
        input.addEventListener('input', () => {
            const tendered = parseFloat(input.value) || 0;
            const change = tendered - total;
            if (change >= 0) {
                changePreview.textContent = `Change Due: ${formatCurrency(change)}`;
                changePreview.className = 'form-text text-success fw-bold';
                confirmBtn.disabled = false;
            } else {
                changePreview.textContent = `Amount insufficient (Short: ${formatCurrency(Math.abs(change))})`;
                changePreview.className = 'form-text text-danger';
                confirmBtn.disabled = true;
            }
        });

        // Handle confirm
        confirmBtn.addEventListener('click', () => {
            confirmBtn.clicked = true;
            const tendered = parseFloat(input.value);
            modal.hide();
            // Resolution happens in hidden.bs.modal
        });

        // Handle cancel/close
        modalEl.addEventListener('hidden.bs.modal', () => {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove(); // Cleanup artifact

            if (confirmBtn.clicked) {
                // If confirmed, resolve with value
                const tendered = parseFloat(input.value);
                resolve(tendered);
            } else {
                // If cancelled (and not confirmed), resolve null
                resolve(null);
            }

            setTimeout(() => modalEl.remove(), 100); // Cleanup DOM
        });

        modal.show();
        // Focus input after modal shown
        modalEl.addEventListener('shown.bs.modal', () => input.focus());
    });
};

// Voice Assist Logic
const initVoiceFeedback = () => {
    const toggle = document.getElementById('voiceFeedbackToggle');
    if (!toggle) return;

    // Load preference
    const enabled = localStorage.getItem('pos_voice_enabled') === 'true';
    toggle.checked = enabled;

    toggle.addEventListener('change', (e) => {
        localStorage.setItem('pos_voice_enabled', e.target.checked);
        if (e.target.checked) {
            speak('Voice feedback enabled');
        }
    });
};

const speak = (text) => {
    if (!('speechSynthesis' in window)) return;
    const utterance = new SpeechSynthesisUtterance(text);
    window.speechSynthesis.speak(utterance);
};

const speakTotal = (amount) => {
    const enabled = localStorage.getItem('pos_voice_enabled') === 'true';
    if (!enabled) return;
    // Split for natural reading: "125 dollars and 50 cents"
    const dollars = Math.floor(amount);
    const cents = Math.round((amount - dollars) * 100);

    let text = `The total is ${dollars} dollars`;
    if (cents > 0) {
        text += ` and ${cents} cents`;
    }
    speak(text);
};

// Modified Checkout Process
const processCheckout = async (action = 'pay') => {
    // ... (existing customer check logic) ...
    const customerIdInput = document.getElementById('selectedCustomerId');
    const customerId = customerIdInput ? customerIdInput.value : null;
    const paymentElement = document.querySelector('input[name="paymentMethod"]:checked');
    const paymentMethod = paymentElement ? paymentElement.value : 'cash';

    // Enforce Mandatory Customer Selection for ALL transactions (User Request)
    if (!customerId) {
        showToast('Customer selection is required for all transactions', 'error');
        // Visual cue
        const searchInput = document.getElementById('customerSearchInput');
        if (searchInput) {
            searchInput.focus();
            searchInput.classList.add('is-invalid');
            setTimeout(() => searchInput.classList.remove('is-invalid'), 2000);
        }
        return;
    }

    if (cart.length === 0) {
        showToast('Cart is empty', 'error');
        return;
    }

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const total = Math.round((subtotal * (1 + TAX_RATE)) * 100) / 100;

    let note = '';
    let amountTendered = total;

    // Voice Feedback (if enabled)
    if (action === 'pay') {
        speakTotal(total);
    }



    // Handle Payment Specifics logic only for 'pay' action
    if (action === 'pay') {
        if (paymentMethod === 'cash') {
            const result = await promptCashAmount(total);
            if (result === null) return; // Cancelled
            amountTendered = result;
        } else if (paymentMethod === 'other') {
            note = prompt('Please enter a reference note (e.g. Check #, Venmo, etc.):');
            if (note === null) return; // User cancelled
        }
    } else {
        // Quote or Layaway - no immediate payment needed for Quote
        // Layaway might need deposit, but for now we'll skip forcing deposit or just ask for optional note
        amountTendered = 0; // Default for quote

        // Optional note for Quote/Layaway
        const actionName = action.charAt(0).toUpperCase() + action.slice(1);
        // note = prompt(`(Optional) Add a note for this ${actionName}:`);
        // if (note === null) note = ''; 
        // Or just let it be empty.
    }

    // Show loading overlay
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.add('active');

    const checkoutBtn = document.getElementById('checkoutBtn');
    const quoteBtn = document.getElementById('saveQuoteBtn');
    const layawayBtn = document.getElementById('layawayBtn');

    if (checkoutBtn) checkoutBtn.disabled = true;
    if (quoteBtn) quoteBtn.disabled = true;
    if (layawayBtn) layawayBtn.disabled = true;

    try {
        const response = await fetch('/store/pos/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                customer_id: customerId,
                items: JSON.stringify(cart),
                payment_method: paymentMethod,
                amount_paid: amountTendered,
                note: note,
                action: action, // Pass action
                csrf_token: typeof csrfToken !== 'undefined' ? csrfToken : ''
            })
        });

        const data = await response.json();

        // Hide overlay immediately so it doesn't block Modals/Toasts
        if (overlay) overlay.classList.remove('active');

        if (data.success) {
            if (action === 'pay') triggerConfetti();

            if (action === 'pay' && paymentMethod === 'cash' && amountTendered > total) {
                const change = amountTendered - total;
                // Nice Change Modal
                const modalHtml = `
                    <div class="modal fade" id="changeModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-center p-4">
                                <div class="mb-3">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                </div>
                                <h4>Payment Successful!</h4>
                                <h2 class="display-4 fw-bold text-primary my-3">Change: ${formatCurrency(change)}</h2>
                                <p class="text-muted">Please give change to customer.</p>
                                <button type="button" class="btn btn-primary btn-lg w-100" data-bs-dismiss="modal">Done</button>
                            </div>
                        </div>
                    </div>`;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                const changeModal = new bootstrap.Modal(document.getElementById('changeModal'));
                changeModal.show();

                // Wait for modal close to redirect
                document.getElementById('changeModal').addEventListener('hidden.bs.modal', () => {
                    cart = []; // Clear cart explicitly
                    updateCart(); // Update UI
                    clearCustomer(); // Clear customer context
                    window.location.href = data.redirect || '/store/pos';
                });
            } else {
                let msg = 'Sale completed successfully!';
                if (action === 'quote') msg = 'Quote saved successfully!';
                if (action === 'layaway') msg = 'Layaway created successfully!';

                showToast(msg, 'success');

                // Clear state immediately to be safe
                cart = [];
                updateCart();
                const target = data.redirect || '/store/pos';

                setTimeout(() => {
                    clearCustomer(); // Async, but we don't wait
                    window.location.href = target;

                    // Fallback reload if href doesn't trigger for some reason
                    setTimeout(() => window.location.reload(), 500);
                }, 1500);
            }
        } else {
            throw new Error(data.error || 'Transaction failed');
        }
    } catch (error) {
        if (overlay) overlay.classList.remove('active');
        if (checkoutBtn) checkoutBtn.disabled = false;
        if (quoteBtn) quoteBtn.disabled = false;
        if (layawayBtn) layawayBtn.disabled = false;
        showToast(error.message || 'An error occurred during transaction', 'error');
    }
};

// Modified Customer Search to show Photos
const searchCustomerInput = document.getElementById('customerSearchInput');
const customerSearchResults = document.getElementById('customerSearchResults');

if (searchCustomerInput) {
    searchCustomerInput.addEventListener('input', debounce(async function (e) {
        const query = e.target.value;
        if (query.length < 2) {
            if (customerSearchResults) customerSearchResults.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/store/customers/search?q=${encodeURIComponent(query)}`);
            const customers = await response.json();

            if (customers.length === 0) {
                if (customerSearchResults) {
                    customerSearchResults.innerHTML = '<div class="p-3 text-center text-muted">No customers found</div>';
                    customerSearchResults.style.display = 'block';
                }
                return;
            }

            let html = '<div class="list-group list-group-flush shadow-sm">';
            customers.forEach(customer => {
                const name = `${customer.first_name} ${customer.last_name}`;
                const photo = customer.photo_url || '/assets/img/default-avatar.png'; // Fallback handled in backend too usually

                // Certification Badge Logic
                let certBadge = '';
                if (customer.certification) {
                    const cert = customer.certification;
                    certBadge = `
                            <div class="mt-1 d-flex align-items-center">
                                ${cert.logo_path ? `<img src="${cert.logo_path}" alt="${cert.agency_name}" style="height: 16px; margin-right: 4px;">` : ''}
                                <span class="badge bg-info text-dark" style="font-size: 0.7rem;">${cert.certification_name || 'Certified'}</span>
                            </div>
                        `;
                }

                html += `
                        <button type="button" class="list-group-item list-group-item-action p-2" 
                                onclick="setPosCustomer(${customer.id}, '${name.replace(/'/g, "\\'")}', '${photo}')">
                            <div class="d-flex align-items-center">
                                <img src="${photo}" alt="${name}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                <div class="w-100">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <h6 class="mb-0 text-truncate" style="max-width: 150px;">${name}</h6>
                                        <small class="text-muted">${customer.phone || ''}</small>
                                    </div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 200px;">${customer.email || 'No email'}</small>
                                    ${certBadge}
                                </div>
                            </div>
                        </button>
                    `;
            });
            html += '</div>';

            if (customerSearchResults) {
                customerSearchResults.innerHTML = html;
                customerSearchResults.style.display = 'block';
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }, 300));
}

// Scuba Dive Watch Clock
const initScubaClock = () => {
    // Inject CSS
    const style = document.createElement('style');
    style.innerHTML = `
        .scuba-clock {
            background: #222;
            color: #00ff00; /* Digital green */
            font-family: 'Courier New', monospace;
            border: 4px solid #444;
            border-radius: 50%;
            width: 150px;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.5), inset 0 0 20px rgba(0,0,0,0.8);
            position: relative;
            overflow: hidden;
        }
        .scuba-clock::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            pointer-events: none;
        }
        .scuba-clock .bezel {
            position: absolute;
            top: 2px; left: 2px; right: 2px; bottom: 2px;
            border: 2px dashed #666;
            border-radius: 50%;
            opacity: 0.5;
        }
        .clock-time { font-size: 1.8rem; font-weight: bold; text-shadow: 0 0 5px #00ff00; z-index: 2; }
        .clock-date { font-size: 0.9rem; color: #aaa; z-index: 2; }
        .dive-flag-icon { width: 20px; height: 16px; background: red; position: relative; margin-bottom: 5px; z-index: 2; } /* approximate dive flag */
        .dive-flag-icon::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom right, transparent 45%, white 45%, white 55%, transparent 55%); }
        
        /* Bubbles Animation */
        .bubble {
            position: absolute;
            bottom: -10px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: rise 4s infinite ease-in;
            z-index: 1;
        }
        @keyframes rise {
            0% { bottom: -10px; transform: translateX(0); width: 4px; height: 4px; opacity: 0; }
            50% { opacity: 0.6; }
            100% { bottom: 120px; transform: translateX(-20px); width: 8px; height: 8px; opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // Create Clock Element if checks pass (replacing standard text)
    const timeEl = document.getElementById('posCurrentTime');
    const dateEl = document.getElementById('posCurrentDate');

    // Find container
    const clockContainer = timeEl?.closest('.text-end');

    if (clockContainer) {
        // Clear existing
        clockContainer.innerHTML = '';
        clockContainer.className = 'd-flex justify-content-end align-items-center';

        const clockHtml = `
            <div class="scuba-clock">
                <div class="bezel"></div>
                <div class="dive-flag-icon"></div>
                <div class="clock-date" id="scubaDate">--</div>
                <div class="clock-time" id="scubaTime">--:--</div>
                <div id="bubbleContainer"></div>
            </div>
        `;
        clockContainer.innerHTML = clockHtml;

        // Start Bubbles
        const bubbleContainer = document.getElementById('bubbleContainer');
        setInterval(() => {
            const b = document.createElement('div');
            b.className = 'bubble';
            b.style.left = Math.random() * 100 + '%';
            b.style.animationDuration = (Math.random() * 2 + 3) + 's'; // 3-5s
            bubbleContainer.appendChild(b);
            setTimeout(() => b.remove(), 5000);
        }, 800);
    }
}

// Updated updateClock function
const updateClock = () => {
    const now = new Date();
    const dateString = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    const timeString = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

    const scubaTime = document.getElementById('scubaTime');
    const scubaDate = document.getElementById('scubaDate');

    if (scubaTime) scubaTime.textContent = timeString;
    if (scubaDate) scubaDate.textContent = dateString;
};

// Updated setCustomer to handle Alerts and Profile
const setCustomer = async (id, name, photoUrl = '/assets/img/default-avatar.png') => {
    try {
        const response = await fetch('/store/pos/set-customer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                customer_id: id,
                csrf_token: typeof csrfToken !== 'undefined' ? csrfToken : ''
            })
        });

        const data = await response.json();

        // Update UI regardless of alerts
        const searchInput = document.getElementById('customerSearchInput');
        if (searchInput) searchInput.value = name;

        const hiddenId = document.getElementById('selectedCustomerId');
        if (hiddenId) hiddenId.value = id;

        // Toggle Displays
        const searchWrapper = searchInput?.closest('.search-wrapper');
        const displayCard = document.getElementById('customerDisplayCard');

        if (searchWrapper && displayCard) {
            searchWrapper.classList.add('d-none');
            displayCard.classList.remove('d-none');
        }

        // Update Card Details
        const nameDisplay = document.getElementById('customerNameDisplay');
        if (nameDisplay) nameDisplay.textContent = name;

        const imgDisplay = document.getElementById('activeCustomerImg');
        if (imgDisplay) imgDisplay.src = photoUrl;

        // Enhanced Profile Data
        if (data.customer) {
            const emailEl = document.getElementById('customerEmailDisplay');
            const phoneEl = document.getElementById('customerPhoneDisplay');
            const certEl = document.getElementById('customerCertDisplay');

            if (emailEl) emailEl.innerHTML = `<i class="bi bi-envelope"></i> ${data.customer.email || 'No email'}`;
            if (phoneEl) phoneEl.innerHTML = `<i class="bi bi-telephone"></i> ${data.customer.phone || 'No phone'}`;

            if (certEl && data.customer.certification) {
                const cert = data.customer.certification;
                certEl.innerHTML = `
                    <div class="d-flex align-items-center bg-light px-2 py-1 rounded border">
                        ${cert.logo_path ? `<img src="${cert.logo_path}" alt="${cert.agency_name}" style="height: 20px; margin-right: 6px;">` : ''}
                        <div class="text-end line-height-1">
                            <div class="fw-bold small text-dark">${cert.certification_name}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">${cert.agency_name}</div>
                        </div>
                    </div>
                `;
            } else if (certEl) {
                certEl.innerHTML = `<span class="badge bg-secondary">No Cert</span>`;
            }
        }

        // Fetch and display customer risk scores
        try {
            const riskResponse = await fetch(`/store/api/customers/${id}/risk-score`);
            const riskData = await riskResponse.json();

            if (riskData.success && riskData.data) {
                displayRiskScores(riskData.data);
            }
        } catch (e) {
            console.log('Risk score fetch failed:', e);
        }

        // Hide search results
        const results = document.getElementById('customerSearchResults');
        if (results) results.style.display = 'none';

        showToast(`Customer set to ${name}`, 'success');

        // Check for Status/Alerts
        if (data.status) {
            checkCustomerAlerts(name, data.status);
        }

        // Voice Greeting
        const voiceEnabled = localStorage.getItem('pos_voice_enabled') === 'true';
        if (voiceEnabled) {
            setTimeout(() => speak(`Hello ${name}`), 500);
        }

    } catch (error) {
        console.error('Failed to set customer context', error);
        showToast('Failed to set customer', 'error');
    }
};

const clearCustomer = async () => {
    try {
        // Reset Backend Session
        await fetch('/store/pos/set-customer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                customer_id: 0, // 0 or empty to clear
                csrf_token: typeof csrfToken !== 'undefined' ? csrfToken : ''
            })
        });

        // Update UI
        const searchInput = document.getElementById('customerSearchInput');
        if (searchInput) {
            searchInput.value = '';
            searchInput.closest('.search-wrapper').classList.remove('d-none');
        }

        const hiddenId = document.getElementById('selectedCustomerId');
        if (hiddenId) hiddenId.value = '';

        const displayCard = document.getElementById('customerDisplayCard');
        if (displayCard) displayCard.classList.add('d-none');

        showToast('Customer cleared', 'info');

    } catch (error) {
        console.error('Failed to clear customer', error);
    }
};

const checkCustomerAlerts = (name, status) => {
    const hasBalance = status.outstanding_balance > 0;
    const hasWorkOrders = status.work_orders && status.work_orders.length > 0;
    const hasCourses = status.upcoming_courses && status.upcoming_courses.length > 0;
    const hasTrips = status.upcoming_trips && status.upcoming_trips.length > 0;

    if (!hasBalance && !hasWorkOrders && !hasCourses && !hasTrips) return;

    // Build Modal Content
    let content = '';

    if (hasBalance) {
        content += `
            <div class="alert alert-danger d-flex align-items-center mb-3">
                <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Outstanding Balance</strong>
                    <div class="fs-5">${formatCurrency(status.outstanding_balance)}</div>
                </div>
            </div>`;
    }

    if (hasWorkOrders) {
        content += `<h6 class="fw-bold border-bottom pb-2 mb-2"><i class="bi bi-tools text-primary"></i> Active Work Orders</h6>
                    <ul class="list-group mb-3">`;
        status.work_orders.forEach(wo => {
            content += `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>${wo.work_order_number} - ${wo.equipment_type}</span>
                            <span class="badge bg-warning text-dark">${wo.status}</span>
                        </li>`;
        });
        content += '</ul>';
    }

    if (hasCourses) {
        content += `<h6 class="fw-bold border-bottom pb-2 mb-2"><i class="bi bi-mortarboard text-info"></i> Upcoming Courses</h6>
                    <ul class="list-group mb-3">`;
        status.upcoming_courses.forEach(c => {
            content += `<li class="list-group-item">
                            ${c.name} <br><small class="text-muted">${new Date(c.start_date).toLocaleDateString()}</small>
                        </li>`;
        });
        content += '</ul>';
    }

    if (hasTrips) {
        content += `<h6 class="fw-bold border-bottom pb-2 mb-2"><i class="bi bi-airplane text-success"></i> Upcoming Trips</h6>
                    <ul class="list-group mb-3">`;
        status.upcoming_trips.forEach(t => {
            content += `<li class="list-group-item">
                            ${t.name} <br><small class="text-muted">${new Date(t.departure_date).toLocaleDateString()}</small>
                        </li>`;
        });
        content += '</ul>';
    }

    const modalId = 'customerAlertModal';
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold"><i class="bi bi-info-circle"></i> ${name} - Status Snapshot</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Acknowledge</button>
                    </div>
                </div>
            </div>
        </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();

    document.getElementById(modalId).addEventListener('hidden.bs.modal', function () {
        this.remove();
    });
};

// Override window.setPosCustomer
window.setPosCustomer = setCustomer;

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

    const quoteBtn = document.getElementById('saveQuoteBtn');
    const layawayBtn = document.getElementById('layawayBtn');
    const clearBtn = document.getElementById('clearCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart-state">
                <i class="bi bi-cart-x"></i>
                <p>Your cart is empty</p>
                <small>Click items on the left to add them</small>
                <div id="restoreHoldContainer" class="mt-3"></div>
            </div>
        `;

        // Check localstorage for hold
        const held = localStorage.getItem('pos_held_cart');
        if (held) {
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm btn-outline-warning mt-2';
            btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Restore Held Cart';
            btn.onclick = () => {
                try {
                    const data = JSON.parse(held);
                    cart = data.cart || [];
                    localStorage.removeItem('pos_held_cart');
                    updateCart();
                    showToast('Cart restored!', 'success');
                } catch (e) { console.error(e); }
            };
            setTimeout(() => {
                const c = document.getElementById('restoreHoldContainer');
                if (c) c.appendChild(btn);
            }, 100);
        }

        if (clearBtn) clearBtn.disabled = true;
        if (checkoutBtn) checkoutBtn.disabled = true;
        if (quoteBtn) quoteBtn.disabled = true;
        if (layawayBtn) layawayBtn.disabled = true;
        if (holdBtn) holdBtn.disabled = true; // Disable hold if empty

        document.getElementById('cartSubtotal').textContent = '$0.00';
        document.getElementById('cartTax').textContent = '$0.00';
        document.getElementById('cartTotal').textContent = '$0.00';
        return;
    }

    if (holdBtn) holdBtn.disabled = false; // Enable hold


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

    if (clearBtn) clearBtn.disabled = false;
    if (checkoutBtn) checkoutBtn.disabled = false;
    if (quoteBtn) quoteBtn.disabled = false;
    if (layawayBtn) layawayBtn.disabled = false;

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

// Expose for external modules
window.addItemToCart = (item) => {
    // Adapt format if necessary, assuming item matches productData structure
    // item from pos-course-enrollment has { product_id, name, price, quantity, type, course_id, schedule_id }
    // addToCart expects (productData, event)
    // We'll push directly to cart or reuse logic
    const existingItem = cart.find(i => i.product_id === item.product_id);
    if (existingItem) {
        existingItem.quantity++;
        showToast(`${item.name} quantity updated`, 'success');
    } else {
        cart.push(item);
        showToast(`${item.name} added to cart`, 'success');
    }
    updateCart();
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

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Hold Button Logic
    const holdBtn = document.getElementById('holdBtn');
    if (holdBtn) {
        holdBtn.addEventListener('click', () => {
            if (cart.length === 0) return;

            // Simple LocalStorage Hold
            const holdData = {
                timestamp: Date.now(),
                cart: cart,
                // Maybe customer too?
            };
            localStorage.setItem('pos_held_cart', JSON.stringify(holdData));

            cart = [];
            updateCart();
            clearCustomer();
            showToast('Transaction placed on hold', 'warning');
        });

        // Restore Hold Check
        const held = localStorage.getItem('pos_held_cart');
        if (held) {
            try {
                const heldData = JSON.parse(held);
                const minutes = Math.floor((Date.now() - heldData.timestamp) / 60000);
                showToast(`Held cart available (${minutes}m ago). Check cart to restore.`, 'info');
            } catch (e) { localStorage.removeItem('pos_held_cart'); }
        }
    }
    // Clock
    if (typeof initScubaClock === 'function') {
        initScubaClock();
    }
    updateClock();
    setInterval(updateClock, 1000); // 1s update for seconds if needed, or 60s
    setInterval(updateClock, 60000);

    // Initial Badge Logic (if customer already set in session/page load)
    const hiddenId = document.getElementById('selectedCustomerId');
    if (hiddenId && hiddenId.value && hiddenId.value != '0') {
        const nameDisplay = document.getElementById('customerNameDisplay');
        if (nameDisplay) setCustomer(hiddenId.value, nameDisplay.textContent);
    }

    // Category Filter
    const categoryBtns = document.querySelectorAll('.btn-category');
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update Active State
            categoryBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const category = btn.dataset.category;
            const items = document.querySelectorAll('.product-card-modern');

            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'flex'; // Restore flex display
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Product Search Filter
    const productSearch = document.getElementById('productSearch');
    if (productSearch) {
        productSearch.addEventListener('input', debounce((e) => {
            const term = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.product-card-modern');
            let found = false;

            items.forEach(item => {
                const name = item.dataset.productName.toLowerCase();
                const sku = item.dataset.productSku.toLowerCase();
                // If category filter is active, respect it? For now, search overrides category or searches within?
                // Let's search all products for simplicity, or search visible ones.
                // Usually search resets categories to 'All' or searches globally.

                if (name.includes(term) || sku.includes(term)) {
                    item.style.display = 'flex';
                    found = true;
                } else {
                    item.style.display = 'none';
                }
            });

            const noMsg = document.getElementById('noResultsMsg');
            if (noMsg) noMsg.style.display = found ? 'none' : 'block';
        }, 300));
    }

    // Product Grid Clicks (Delegation)
    const grid = document.getElementById('productGrid');
    if (grid) {
        grid.addEventListener('click', (e) => {
            const card = e.target.closest('.product-card-modern');
            if (card) {
                // Ignore if clicking on specific non-interactive sub-elements if any? (none so far)
                const productData = {
                    product_id: card.dataset.productId,
                    name: card.dataset.productName,
                    price: card.dataset.productPrice,
                    sku: card.dataset.productSku
                };

                // Special handling for courses/trips/rentals to ensure unique ID if needed?
                // The dataset.productId is mostly unique (e.g. 'course_5', 'trip_2').

                // Special handling for courses
                if (card.dataset.category === 'courses' && typeof window.showCourseScheduleModal === 'function') {
                    const courseId = card.dataset.courseId; // Assuming available in dataset
                    window.showCourseScheduleModal(courseId, productData.name, productData.price);
                    return;
                }

                addToCart(productData, e);
            }
        });
    }

    // Checkout Actions
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) checkoutBtn.addEventListener('click', () => processCheckout('pay'));

    const quoteBtn = document.getElementById('saveQuoteBtn');
    if (quoteBtn) quoteBtn.addEventListener('click', () => processCheckout('quote'));

    const layawayBtn = document.getElementById('layawayBtn');
    if (layawayBtn) layawayBtn.addEventListener('click', () => processCheckout('layaway'));

    const clearCartBtn = document.getElementById('clearCartBtn');
    if (clearCartBtn) clearCartBtn.addEventListener('click', clearCart);

    // Customer Actions
    const clearCustomerBtn = document.getElementById('clearSelectedCustomerBtn');
    if (clearCustomerBtn) clearCustomerBtn.addEventListener('click', clearCustomer);

    // Initial Cart UI Update
    updateCart();
});


// Initialize Button Listeners (Critical Fix)
document.addEventListener('DOMContentLoaded', () => {
    // Layaway Button
    const layawayBtn = document.getElementById('layawayBtn');
    if (layawayBtn) {
        layawayBtn.addEventListener('click', () => processCheckout('layaway'));
    }

    // Quote Button
    const quoteBtn = document.getElementById('saveQuoteBtn');
    if (quoteBtn) {
        quoteBtn.addEventListener('click', () => processCheckout('quote'));
    }

    // Payment Method Listeners (Card, Other) - Trigger checkout if clicked while selected?
    // Or just ensure the radio button update works.
    // The "Pay" button typically triggers the 'pay' action.
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => processCheckout('pay'));
    }

    // Initialize Voice Feedback
    // Initialize Voice Feedback
    initVoiceFeedback();

    // Initialize Time Clock
    initTimeClock();
});

async function initTimeClock() {
    const btn = document.getElementById('timeClockBtn');
    const label = document.getElementById('timeClockLabel');
    if (!btn) return;

    // Helper to update button state
    const updateBtn = (status) => {
        if (status === 'clocked_in') {
            btn.classList.remove('btn-outline-secondary', 'btn-outline-primary');
            btn.classList.add('btn-danger'); // Red for Clock Out
            btn.innerHTML = '<i class="bi bi-clock-fill"></i> <span class="d-none d-md-inline">Clock Out</span>';
            btn.dataset.status = 'clocked_in';
        } else {
            btn.classList.remove('btn-danger', 'btn-outline-secondary');
            btn.classList.add('btn-outline-success'); // Green for Clock In
            btn.innerHTML = '<i class="bi bi-clock"></i> <span class="d-none d-md-inline">Clock In</span>';
            btn.dataset.status = 'clocked_out';
        }
    };

    // Check status
    try {
        const res = await fetch('/store/staff/timeclock/status');
        const data = await res.json();
        updateBtn(data.status);
    } catch (e) {
        console.error('Time clock status check failed', e);
        if (btn && !btn.dataset.status) {
            btn.innerHTML = '<i class="bi bi-clock"></i> <span class="d-none d-md-inline">Clock In</span>';
            btn.dataset.status = 'clocked_out'; // Default to clocked out on error
        }
    }

    // Click handler
    btn.addEventListener('click', async () => {
        const currentStatus = btn.dataset.status || 'clocked_out';
        const action = currentStatus === 'clocked_in' ? 'clockout' : 'clockin';

        // Optimistic UI update
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        try {
            const res = await fetch(`/store/staff/timeclock/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    csrf_token: typeof csrfToken !== 'undefined' ? csrfToken : ''
                })
            });

            const data = await res.json();

            if (data.success) {
                showToast(data.message, 'success');
                updateBtn(data.status);
            } else {
                showToast(data.message || 'Action failed', 'error');
                btn.innerHTML = originalContent; // Revert on failure
            }
        } catch (e) {
            showToast('Connection error', 'error');
            console.error(e);
            btn.innerHTML = originalContent;
        } finally {
            btn.disabled = false;
        }
    });
}
