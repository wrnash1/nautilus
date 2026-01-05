/**
 * POS Keyboard Shortcuts and Touch Support
 * Hotkeys for fast POS operation
 * Touch-optimized interactions
 */

(function () {
    'use strict';

    // === KEYBOARD SHORTCUTS ===
    const SHORTCUTS = {
        // Function keys
        'F1': { action: 'help', description: 'Show help / shortcuts' },
        'F2': { action: 'search-product', description: 'Focus product search' },
        'F3': { action: 'search-customer', description: 'Focus customer search' },
        'F4': { action: 'air-fill', description: 'Add Air Fill' },
        'F5': { action: 'nitrox', description: 'Add Nitrox Fill' },
        'F6': { action: 'returns', description: 'Open Returns' },
        'F7': { action: 'gift-card', description: 'Open Gift Card' },
        'F8': { action: 'clock', description: 'Clock In/Out' },
        'F9': { action: 'clear-cart', description: 'Clear Cart' },
        'F10': { action: 'pay-cash', description: 'Pay with Cash' },
        'F11': { action: 'pay-card', description: 'Pay with Card' },
        'F12': { action: 'checkout', description: 'Complete Checkout' },

        // Ctrl combinations
        'Ctrl+Enter': { action: 'checkout', description: 'Complete Checkout' },
        'Ctrl+Delete': { action: 'clear-cart', description: 'Clear Cart' },
        'Ctrl+/': { action: 'help', description: 'Show shortcuts' },

        // Numbers for quick quantity
        'Ctrl+1': { action: 'qty-1', description: 'Set quantity to 1' },
        'Ctrl+2': { action: 'qty-2', description: 'Set quantity to 2' },
        'Ctrl+3': { action: 'qty-3', description: 'Set quantity to 3' },

        // Escape
        'Escape': { action: 'close-modal', description: 'Close modal/clear' }
    };

    // Handle keyboard events
    document.addEventListener('keydown', function (e) {
        // Don't trigger if typing in input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            if (e.key === 'Escape') {
                e.target.blur();
            }
            return;
        }

        let key = '';
        if (e.ctrlKey) key += 'Ctrl+';
        if (e.shiftKey) key += 'Shift+';
        if (e.altKey) key += 'Alt+';
        key += e.key;

        const shortcut = SHORTCUTS[key];
        if (shortcut) {
            e.preventDefault();
            executeAction(shortcut.action);
        }
    });

    function executeAction(action) {
        switch (action) {
            case 'help':
                showShortcutsHelp();
                break;
            case 'search-product':
                document.getElementById('productSearch')?.focus();
                break;
            case 'search-customer':
                document.getElementById('customerSearchInput')?.focus();
                break;
            case 'air-fill':
                document.getElementById('quickAirFillBtn')?.click();
                break;
            case 'nitrox':
                document.getElementById('quickNitroxBtn')?.click();
                break;
            case 'returns':
                document.getElementById('quickReturnBtn')?.click();
                break;
            case 'gift-card':
                document.getElementById('quickGiftCardBtn')?.click();
                break;
            case 'clock':
                document.getElementById('timeClockBtn')?.click();
                break;
            case 'clear-cart':
                if (confirm('Clear entire cart?')) {
                    window.clearCart?.();
                }
                break;
            case 'pay-cash':
                document.querySelector('input[value="cash"]')?.click();
                break;
            case 'pay-card':
                document.querySelector('input[value="credit"]')?.click();
                break;
            case 'checkout':
                document.getElementById('checkoutBtn')?.click();
                break;
            case 'close-modal':
                document.querySelector('.modal.show .btn-close')?.click();
                break;
        }
    }

    function showShortcutsHelp() {
        const existingModal = document.getElementById('shortcutsHelpModal');
        if (existingModal) existingModal.remove();

        let rows = '';
        for (const [key, info] of Object.entries(SHORTCUTS)) {
            rows += `<tr><td><kbd>${key}</kbd></td><td>${info.description}</td></tr>`;
        }

        const modalHtml = `
            <div class="modal fade" id="shortcutsHelpModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-keyboard"></i> Keyboard Shortcuts</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-sm">
                                <thead><tr><th>Key</th><th>Action</th></tr></thead>
                                <tbody>${rows}</tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        new bootstrap.Modal(document.getElementById('shortcutsHelpModal')).show();
    }

    // === TOUCH SUPPORT ===

    // Prevent double-tap zoom on buttons
    document.addEventListener('touchend', function (e) {
        if (e.target.closest('button, .btn, .product-card-modern')) {
            e.preventDefault();
            e.target.click();
        }
    }, { passive: false });

    // Add touch feedback class
    document.addEventListener('touchstart', function (e) {
        const touchable = e.target.closest('button, .btn, .product-card-modern, .cart-item');
        if (touchable) {
            touchable.classList.add('touch-active');
            setTimeout(() => touchable.classList.remove('touch-active'), 200);
        }
    }, { passive: true });

    // Long press for context menu on cart items
    let longPressTimer;
    document.addEventListener('touchstart', function (e) {
        const cartItem = e.target.closest('.cart-item');
        if (cartItem) {
            longPressTimer = setTimeout(() => {
                showCartItemOptions(cartItem);
            }, 500);
        }
    }, { passive: true });

    document.addEventListener('touchend', function () {
        clearTimeout(longPressTimer);
    }, { passive: true });

    function showCartItemOptions(cartItem) {
        // Vibrate for feedback if supported
        if (navigator.vibrate) navigator.vibrate(50);

        const itemId = cartItem.dataset.itemId;
        const options = [
            { label: 'Remove Item', action: 'remove' },
            { label: 'Change Quantity', action: 'qty' },
            { label: 'Add Discount', action: 'discount' }
        ];

        // Create context menu (simplified version)
        const menu = document.createElement('div');
        menu.className = 'touch-context-menu';
        menu.innerHTML = options.map(opt =>
            `<button class="context-btn" data-action="${opt.action}">${opt.label}</button>`
        ).join('');

        menu.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 10px;
            display: flex;
            gap: 10px;
            z-index: 10000;
        `;

        document.body.appendChild(menu);

        menu.addEventListener('click', function (e) {
            const action = e.target.dataset.action;
            if (action === 'remove') {
                window.removeFromCart?.(itemId);
            }
            menu.remove();
        });

        // Close on outside click
        setTimeout(() => {
            document.addEventListener('click', function close(e) {
                if (!menu.contains(e.target)) {
                    menu.remove();
                    document.removeEventListener('click', close);
                }
            });
        }, 100);
    }

    // === HELP BUTTON (visible) ===
    document.addEventListener('DOMContentLoaded', function () {
        // Add floating help button
        const helpBtn = document.createElement('button');
        helpBtn.id = 'posHelpBtn';
        helpBtn.innerHTML = '<i class="bi bi-keyboard"></i>';
        helpBtn.title = 'Keyboard Shortcuts (F1)';
        helpBtn.className = 'btn btn-outline-secondary';
        helpBtn.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        helpBtn.addEventListener('click', showShortcutsHelp);
        document.body.appendChild(helpBtn);
    });

    // Expose for external use
    window.POSShortcuts = {
        show: showShortcutsHelp,
        execute: executeAction
    };

})();
