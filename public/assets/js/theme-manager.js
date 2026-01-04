/**
 * Theme Manager
 * Handles light/dark mode switching and persistence
 */

class ThemeManager {
    constructor() {
        this.theme = this.getStoredTheme() || this.getSystemTheme();
        this.init();
    }

    init() {
        // Apply theme on load
        this.applyTheme(this.theme);

        // Create theme toggle button
        this.createToggleButton();

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!this.getStoredTheme()) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    getStoredTheme() {
        return localStorage.getItem('nautilus-theme');
    }

    setStoredTheme(theme) {
        localStorage.setItem('nautilus-theme', theme);
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        this.theme = theme;
        this.updateToggleIcon();
    }

    setTheme(theme) {
        this.applyTheme(theme);
        this.setStoredTheme(theme);
    }

    toggleTheme() {
        const newTheme = this.theme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);

        // Trigger custom event for other components
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: newTheme }
        }));
    }

    createToggleButton() {
        const button = document.createElement('button');
        button.className = 'theme-toggle';
        button.setAttribute('aria-label', 'Toggle theme');
        button.innerHTML = '<i class="bi bi-sun-fill"></i>';
        button.addEventListener('click', () => this.toggleTheme());

        document.body.appendChild(button);
        this.toggleButton = button;

        this.updateToggleIcon();
    }

    updateToggleIcon() {
        if (this.toggleButton) {
            const icon = this.theme === 'light' ? 'moon-stars-fill' : 'sun-fill';
            this.toggleButton.innerHTML = `<i class="bi bi-${icon}"></i>`;
        }
    }
}

// Initialize theme manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.themeManager = new ThemeManager();
    });
} else {
    window.themeManager = new ThemeManager();
}

/**
 * Notification Toast System
 */
class ToastManager {
    constructor() {
        this.container = this.createContainer();
        this.toasts = [];
    }

    createContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: 400px;
        `;
        document.body.appendChild(container);
        return container;
    }

    show(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} slide-up`;
        toast.style.cssText = `
            background: var(--bg-primary);
            padding: 1rem 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-color: var(--${type === 'success' ? 'success' : type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'info'});
            transform: translateX(400px);
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        `;

        const icons = {
            success: 'check-circle-fill',
            error: 'x-circle-fill',
            warning: 'exclamation-triangle-fill',
            info: 'info-circle-fill'
        };

        const colors = {
            success: 'var(--success)',
            error: 'var(--error)',
            warning: 'var(--warning)',
            info: 'var(--info)'
        };

        toast.innerHTML = `
            <i class="bi bi-${icons[type]}" style="font-size: 1.25rem; color: ${colors[type]}; flex-shrink: 0;"></i>
            <span style="color: var(--text-primary); flex: 1;">${message}</span>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: var(--text-tertiary); cursor: pointer; padding: 0; font-size: 1.25rem;">
                <i class="bi bi-x"></i>
            </button>
        `;

        this.container.appendChild(toast);
        this.toasts.push(toast);

        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 10);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.remove(toast);
            }, duration);
        }

        return toast;
    }

    remove(toast) {
        toast.style.transform = 'translateX(400px)';
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.remove();
            this.toasts = this.toasts.filter(t => t !== toast);
        }, 300);
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
}

// Initialize toast manager
window.toast = new ToastManager();

/**
 * Loading Overlay
 */
class LoadingOverlay {
    constructor() {
        this.overlay = null;
    }

    show(message = 'Loading...') {
        if (this.overlay) this.hide();

        this.overlay = document.createElement('div');
        this.overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            opacity: 0;
            transition: opacity 0.2s;
        `;

        this.overlay.innerHTML = `
            <div style="
                background: var(--bg-primary);
                padding: 2rem 3rem;
                border-radius: var(--radius-xl);
                box-shadow: var(--shadow-xl);
                text-align: center;
            ">
                <div class="spinner-modern" style="margin: 0 auto 1rem; width: 3rem; height: 3rem;"></div>
                <div style="color: var(--text-primary); font-weight: 500;">${message}</div>
            </div>
        `;

        document.body.appendChild(this.overlay);
        setTimeout(() => {
            this.overlay.style.opacity = '1';
        }, 10);
    }

    hide() {
        if (this.overlay) {
            this.overlay.style.opacity = '0';
            setTimeout(() => {
                this.overlay?.remove();
                this.overlay = null;
            }, 200);
        }
    }
}

window.loading = new LoadingOverlay();

/**
 * Confirmation Dialog
 */
function confirmModern(message, title = 'Confirm Action') {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'modal-modern';
        modal.innerHTML = `
            <div class="modal-content-modern">
                <div class="modal-header-modern">
                    <h3 style="margin: 0; color: var(--text-primary);">${title}</h3>
                </div>
                <div class="modal-body-modern">
                    <p style="color: var(--text-secondary); margin: 0;">${message}</p>
                </div>
                <div class="modal-footer-modern">
                    <button class="btn-modern btn-secondary" onclick="this.closest('.modal-modern').dispatchEvent(new Event('cancel'))">
                        Cancel
                    </button>
                    <button class="btn-modern btn-primary" onclick="this.closest('.modal-modern').dispatchEvent(new Event('confirm'))">
                        Confirm
                    </button>
                </div>
            </div>
        `;

        modal.addEventListener('cancel', () => {
            modal.classList.remove('show');
            setTimeout(() => modal.remove(), 200);
            resolve(false);
        });

        modal.addEventListener('confirm', () => {
            modal.classList.remove('show');
            setTimeout(() => modal.remove(), 200);
            resolve(true);
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.dispatchEvent(new Event('cancel'));
            }
        });

        document.body.appendChild(modal);
        setTimeout(() => modal.classList.add('show'), 10);
    });
}

window.confirmModern = confirmModern;

/**
 * Auto-save Form Handler
 */
class AutoSaveForm {
    constructor(formId, saveCallback, interval = 30000) {
        this.form = document.getElementById(formId);
        this.saveCallback = saveCallback;
        this.interval = interval;
        this.timeoutId = null;
        this.lastSaved = null;

        if (this.form) {
            this.init();
        }
    }

    init() {
        this.form.addEventListener('input', () => this.scheduleAutoSave());
        this.form.addEventListener('change', () => this.scheduleAutoSave());
    }

    scheduleAutoSave() {
        clearTimeout(this.timeoutId);
        this.timeoutId = setTimeout(() => this.save(), this.interval);
    }

    async save() {
        const formData = new FormData(this.form);
        try {
            await this.saveCallback(formData);
            this.lastSaved = new Date();
            this.showSaveIndicator();
        } catch (error) {
            console.error('Auto-save failed:', error);
        }
    }

    showSaveIndicator() {
        const indicator = document.createElement('div');
        indicator.style.cssText = `
            position: fixed;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 500;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 9999;
        `;
        indicator.textContent = 'Saved';

        document.body.appendChild(indicator);
        setTimeout(() => indicator.style.opacity = '1', 10);
        setTimeout(() => {
            indicator.style.opacity = '0';
            setTimeout(() => indicator.remove(), 300);
        }, 2000);
    }
}

window.AutoSaveForm = AutoSaveForm;
