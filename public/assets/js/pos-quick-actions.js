/**
 * POS Quick Actions JavaScript
 * Handles Air Fills, Returns, and Gift Cards
 */

document.addEventListener('DOMContentLoaded', function () {

    // ========================
    // Quick Fill Buttons
    // ========================

    document.getElementById('quickAirFillBtn')?.addEventListener('click', function () {
        addFillToCart('Air Fill', 8.00, 'FILL-AIR');
    });

    document.getElementById('quickNitroxBtn')?.addEventListener('click', function () {
        addFillToCart('Nitrox Fill', 12.00, 'FILL-NITROX');
    });

    document.getElementById('quickTrimixBtn')?.addEventListener('click', function () {
        // Trimix requires gas analysis - prompt for mix
        const mix = prompt('Enter Trimix mix (e.g., 18/45):');
        if (mix) {
            addFillToCart('Trimix Fill (' + mix + ')', 25.00, 'FILL-TRIMIX');
        }
    });

    function addFillToCart(name, price, sku) {
        // Find if already in cart
        const existingItem = window.cart?.find(item => item.sku === sku);

        if (existingItem) {
            existingItem.quantity++;
            updateCartDisplay();
        } else {
            // Add new fill to cart
            if (typeof addToCart === 'function') {
                addToCart({
                    id: 'fill_' + sku.toLowerCase().replace('fill-', ''),
                    name: name,
                    price: price,
                    sku: sku,
                    quantity: 1
                });
            } else if (window.cart) {
                window.cart.push({
                    id: 'fill_' + sku.toLowerCase().replace('fill-', ''),
                    name: name,
                    price: price,
                    sku: sku,
                    quantity: 1
                });
                updateCartDisplay();
            }
        }

        showToast('Added ' + name + ' to cart', 'success');
    }

    // ========================
    // Returns Modal
    // ========================

    document.getElementById('searchReturnBtn')?.addEventListener('click', function () {
        const query = document.getElementById('returnTransactionSearch').value;
        searchTransactions(query);
    });

    document.getElementById('returnTransactionSearch')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            searchTransactions(this.value);
        }
    });

    function searchTransactions(query) {
        fetch('/store/pos/transactions/search?q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(data => {
                const results = document.getElementById('returnSearchResults');
                const list = document.getElementById('returnTransactionsList');

                if (data.transactions && data.transactions.length > 0) {
                    list.innerHTML = data.transactions.map(t => `
                        <tr>
                            <td>${t.receipt_number || '#' + t.id}</td>
                            <td>${new Date(t.created_at).toLocaleDateString()}</td>
                            <td>${t.customer_name || 'Walk-in'}</td>
                            <td>$${parseFloat(t.total).toFixed(2)}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="selectTransactionForReturn(${t.id})">
                                    Select
                                </button>
                            </td>
                        </tr>
                    `).join('');
                    results.style.display = 'block';
                } else {
                    list.innerHTML = '<tr><td colspan="5" class="text-center">No transactions found</td></tr>';
                    results.style.display = 'block';
                }
            })
            .catch(err => {
                console.error('Search error:', err);
            });
    }

    window.selectTransactionForReturn = function (transactionId) {
        fetch('/store/pos/transactions/' + transactionId + '/items')
            .then(r => r.json())
            .then(data => {
                const itemsSection = document.getElementById('returnItemsSection');
                const itemsList = document.getElementById('returnItemsList');

                if (data.items && data.items.length > 0) {
                    itemsList.innerHTML = data.items.map(item => `
                        <tr>
                            <td><input type="checkbox" class="return-item-check" data-item-id="${item.id}" data-price="${item.price}"></td>
                            <td>${item.name}</td>
                            <td>${item.quantity}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm return-qty" 
                                       value="1" min="1" max="${item.quantity}" style="width: 70px;">
                            </td>
                            <td>$${parseFloat(item.price).toFixed(2)}</td>
                            <td>
                                <select class="form-select form-select-sm">
                                    <option>Customer Request</option>
                                    <option>Defective</option>
                                    <option>Wrong Item</option>
                                    <option>Other</option>
                                </select>
                            </td>
                        </tr>
                    `).join('');

                    document.getElementById('returnSearchResults').style.display = 'none';
                    itemsSection.style.display = 'block';
                    document.getElementById('processReturnBtn').disabled = false;

                    // Update totals when checkboxes change
                    itemsList.querySelectorAll('.return-item-check').forEach(cb => {
                        cb.addEventListener('change', updateRefundTotal);
                    });
                    itemsList.querySelectorAll('.return-qty').forEach(input => {
                        input.addEventListener('change', updateRefundTotal);
                    });
                }
            });
    };

    function updateRefundTotal() {
        let total = 0;
        document.querySelectorAll('.return-item-check:checked').forEach(cb => {
            const row = cb.closest('tr');
            const price = parseFloat(cb.dataset.price);
            const qty = parseInt(row.querySelector('.return-qty').value) || 1;
            total += price * qty;
        });
        document.getElementById('refundTotal').textContent = '$' + total.toFixed(2);
    }

    document.getElementById('selectAllReturns')?.addEventListener('change', function () {
        document.querySelectorAll('.return-item-check').forEach(cb => {
            cb.checked = this.checked;
        });
        updateRefundTotal();
    });

    document.getElementById('processReturnBtn')?.addEventListener('click', function () {
        const items = [];
        document.querySelectorAll('.return-item-check:checked').forEach(cb => {
            const row = cb.closest('tr');
            items.push({
                item_id: cb.dataset.itemId,
                quantity: parseInt(row.querySelector('.return-qty').value) || 1,
                reason: row.querySelector('select').value
            });
        });

        const refundMethod = document.getElementById('refundMethod').value;

        fetch('/store/pos/returns', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items, refund_method: refundMethod })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Return processed successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('returnModal')).hide();
                } else {
                    showToast('Return failed: ' + data.error, 'error');
                }
            });
    });

    // ========================
    // Gift Card Modal
    // ========================

    // Check Balance
    document.getElementById('checkGcBalanceBtn')?.addEventListener('click', function () {
        const code = document.getElementById('gcBalanceCode').value;
        if (!code) return;

        fetch('/store/gift-cards/balance?code=' + encodeURIComponent(code))
            .then(r => r.json())
            .then(data => {
                const result = document.getElementById('gcBalanceResult');
                if (data.success) {
                    document.getElementById('gcBalanceAmount').textContent = '$' + parseFloat(data.balance).toFixed(2);
                    result.classList.remove('d-none', 'alert-danger');
                    result.classList.add('alert-info');
                } else {
                    document.getElementById('gcBalanceAmount').textContent = 'Not Found';
                    result.classList.remove('d-none', 'alert-info');
                    result.classList.add('alert-danger');
                }
            });
    });

    // Amount preset buttons
    document.querySelectorAll('.gc-amount-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.gc-amount-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('gcCustomAmount').value = this.dataset.amount;
        });
    });

    // Add Gift Card to Cart
    document.getElementById('addGcToCartBtn')?.addEventListener('click', function () {
        const amount = parseFloat(document.getElementById('gcCustomAmount').value);
        if (!amount || amount <= 0) {
            showToast('Please enter a valid amount', 'error');
            return;
        }

        if (typeof addToCart === 'function') {
            addToCart({
                id: 'gift_card_' + Date.now(),
                name: 'Gift Card ($' + amount.toFixed(2) + ')',
                price: amount,
                sku: 'GIFT-CARD',
                quantity: 1
            });
        }

        showToast('Gift Card added to cart', 'success');
        bootstrap.Modal.getInstance(document.getElementById('giftCardModal')).hide();
    });

    // Redeem Gift Card
    document.getElementById('gcRedeemCode')?.addEventListener('blur', function () {
        const code = this.value;
        if (!code) return;

        fetch('/store/gift-cards/balance?code=' + encodeURIComponent(code))
            .then(r => r.json())
            .then(data => {
                const info = document.getElementById('gcRedeemInfo');
                if (data.success && data.balance > 0) {
                    document.getElementById('gcRedeemBalance').textContent = '$' + parseFloat(data.balance).toFixed(2);
                    info.classList.remove('d-none');
                    info.dataset.code = code;
                    info.dataset.balance = data.balance;
                } else {
                    info.classList.add('d-none');
                    showToast('Invalid or empty gift card', 'error');
                }
            });
    });

    document.getElementById('applyGcBtn')?.addEventListener('click', function () {
        const info = document.getElementById('gcRedeemInfo');
        const code = info.dataset.code;
        const balance = parseFloat(info.dataset.balance);

        // Apply as payment method
        window.giftCardPayment = { code, balance };
        showToast('Gift card applied: $' + balance.toFixed(2), 'success');
        bootstrap.Modal.getInstance(document.getElementById('giftCardModal')).hide();
    });

    // ========================
    // Utility Functions
    // ========================

    function showToast(message, type = 'info') {
        const toast = document.getElementById('posToast');
        if (toast) {
            toast.querySelector('.toast-body').textContent = message;
            toast.classList.remove('bg-success', 'bg-danger', 'bg-info', 'bg-warning');
            toast.classList.add('bg-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'));
            new bootstrap.Toast(toast).show();
        }
    }

    function updateCartDisplay() {
        if (typeof window.renderCart === 'function') {
            window.renderCart();
        }
    }
});
