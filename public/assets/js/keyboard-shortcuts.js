/**
 * Keyboard Shortcuts Manager
 * 
 * Provides global keyboard shortcuts for common actions
 */

class KeyboardShortcuts {
    constructor() {
        this.shortcuts = new Map();
        this.init();
    }

    init() {
        document.addEventListener('keydown', (e) => this.handleKeyPress(e));
        this.registerDefaultShortcuts();
        this.showHelpOnFirstVisit();
    }

    register(key, modifiers, callback, description) {
        const shortcutKey = this.getShortcutKey(key, modifiers);
        this.shortcuts.set(shortcutKey, { callback, description, key, modifiers });
    }

    getShortcutKey(key, modifiers = {}) {
        const parts = [];
        if (modifiers.ctrl) parts.push('ctrl');
        if (modifiers.alt) parts.push('alt');
        if (modifiers.shift) parts.push('shift');
        if (modifiers.meta) parts.push('meta');
        parts.push(key.toLowerCase());
        return parts.join('+');
    }

    handleKeyPress(e) {
        // Don't trigger shortcuts when typing in inputs
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
            // Except for Escape key
            if (e.key !== 'Escape') return;
        }

        const shortcutKey = this.getShortcutKey(e.key, {
            ctrl: e.ctrlKey,
            alt: e.altKey,
            shift: e.shiftKey,
            meta: e.metaKey
        });

        const shortcut = this.shortcuts.get(shortcutKey);
        if (shortcut) {
            e.preventDefault();
            shortcut.callback(e);
        }
    }

    registerDefaultShortcuts() {
        // Quick Search
        this.register('k', { ctrl: true }, () => {
            const searchInput = document.querySelector('#quick-search, [name="search"], input[type="search"]');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }, 'Quick Search');

        // New Customer
        this.register('n', { ctrl: true, shift: true }, () => {
            if (window.location.pathname.includes('/customers')) {
                window.location.href = '/store/customers/create';
            }
        }, 'New Customer');

        // Dashboard
        this.register('h', { ctrl: true }, () => {
            window.location.href = '/store/dashboard';
        }, 'Go to Dashboard');

        // Help
        this.register('/', { shift: true }, () => {
            this.showHelp();
        }, 'Show Keyboard Shortcuts');

        // Escape to close modals
        this.register('Escape', {}, () => {
            const modal = document.querySelector('.modal.show, .modal-modern.show');
            if (modal) {
                const closeBtn = modal.querySelector('[data-dismiss="modal"], .modal-close');
                if (closeBtn) closeBtn.click();
            }
        }, 'Close Modal');

        // Save (Ctrl+S)
        this.register('s', { ctrl: true }, (e) => {
            const saveBtn = document.querySelector('button[type="submit"], .btn-save');
            if (saveBtn && !saveBtn.disabled) {
                saveBtn.click();
            }
        }, 'Save Form');

        // Print (Ctrl+P)
        this.register('p', { ctrl: true }, (e) => {
            // Let browser handle print
        }, 'Print Page');
    }

    showHelp() {
        const shortcuts = Array.from(this.shortcuts.values());

        const modal = document.createElement('div');
        modal.className = 'modal-modern show';
        modal.innerHTML = `
            <div class="modal-content-modern" style="max-width: 600px;">
                <div class="modal-header-modern">
                    <h3 style="margin: 0;">⌨️ Keyboard Shortcuts</h3>
                    <button class="btn-ghost" onclick="this.closest('.modal-modern').remove()" style="padding: 0.5rem;">
                        <i class="bi bi-x" style="font-size: 1.5rem;"></i>
                    </button>
                </div>
                <div class="modal-body-modern">
                    <div style="display: grid; gap: 1rem;">
                        ${shortcuts.map(s => `
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-secondary); border-radius: 0.5rem;">
                                <span style="color: var(--text-primary);">${s.description}</span>
                                <kbd style="background: var(--bg-primary); padding: 0.25rem 0.75rem; border-radius: 0.25rem; border: 1px solid var(--border-color); font-family: monospace; font-size: 0.875rem;">
                                    ${this.formatShortcut(s.key, s.modifiers)}
                                </kbd>
                            </div>
                        `).join('')}
                    </div>
                    <div style="margin-top: 1.5rem; padding: 1rem; background: var(--info-light); border-radius: 0.5rem; border-left: 4px solid var(--info);">
                        <p style="margin: 0; color: var(--info-dark); font-size: 0.875rem;">
                            <strong>Tip:</strong> Press <kbd>?</kbd> anytime to see this help menu.
                        </p>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close on background click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    formatShortcut(key, modifiers) {
        const parts = [];
        if (modifiers.ctrl) parts.push('Ctrl');
        if (modifiers.alt) parts.push('Alt');
        if (modifiers.shift) parts.push('Shift');
        if (modifiers.meta) parts.push('⌘');

        // Format key name
        const keyName = key === 'Escape' ? 'Esc' :
            key === '/' ? '?' :
                key.toUpperCase();
        parts.push(keyName);

        return parts.join(' + ');
    }

    showHelpOnFirstVisit() {
        if (!localStorage.getItem('keyboard_shortcuts_seen')) {
            setTimeout(() => {
                toast.info('Press ? to see keyboard shortcuts', 5000);
                localStorage.setItem('keyboard_shortcuts_seen', 'true');
            }, 2000);
        }
    }
}

// Initialize keyboard shortcuts
window.shortcuts = new KeyboardShortcuts();
