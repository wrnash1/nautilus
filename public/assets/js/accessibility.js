/**
 * Accessibility Enhancements
 * 
 * Improves keyboard navigation, ARIA labels, and screen reader support
 */

class AccessibilityManager {
    constructor() {
        this.init();
    }

    init() {
        this.enhanceFocusIndicators();
        this.addSkipLinks();
        this.improveFormAccessibility();
        this.addARIALabels();
        this.handleKeyboardNavigation();
        this.announcePageChanges();
    }

    enhanceFocusIndicators() {
        // Add visible focus indicators
        const style = document.createElement('style');
        style.textContent = `
            /* Focus indicators */
            *:focus {
                outline: 2px solid #0066cc;
                outline-offset: 2px;
            }

            *:focus:not(:focus-visible) {
                outline: none;
            }

            *:focus-visible {
                outline: 2px solid #0066cc;
                outline-offset: 2px;
            }

            /* High contrast mode support */
            @media (prefers-contrast: high) {
                *:focus-visible {
                    outline: 3px solid currentColor;
                    outline-offset: 3px;
                }
            }

            /* Reduced motion support */
            @media (prefers-reduced-motion: reduce) {
                *,
                *::before,
                *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }
        `;
        document.head.appendChild(style);
    }

    addSkipLinks() {
        // Add skip to main content link
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'skip-link';
        skipLink.textContent = 'Skip to main content';

        const style = document.createElement('style');
        style.textContent = `
            .skip-link {
                position: absolute;
                top: -40px;
                left: 0;
                background: #0066cc;
                color: white;
                padding: 8px 16px;
                text-decoration: none;
                border-radius: 0 0 4px 0;
                z-index: 10000;
                font-weight: 600;
            }

            .skip-link:focus {
                top: 0;
            }
        `;
        document.head.appendChild(style);

        document.body.insertBefore(skipLink, document.body.firstChild);

        // Ensure main content has ID
        const main = document.querySelector('main, [role="main"], .main-content');
        if (main && !main.id) {
            main.id = 'main-content';
        }
    }

    improveFormAccessibility() {
        // Add ARIA labels to form inputs without labels
        document.querySelectorAll('input, select, textarea').forEach(input => {
            if (!input.getAttribute('aria-label') && !input.getAttribute('aria-labelledby')) {
                const label = document.querySelector(`label[for="${input.id}"]`);
                if (label) {
                    input.setAttribute('aria-labelledby', input.id + '-label');
                    label.id = input.id + '-label';
                } else if (input.placeholder) {
                    input.setAttribute('aria-label', input.placeholder);
                }
            }

            // Add aria-required for required fields
            if (input.required && !input.getAttribute('aria-required')) {
                input.setAttribute('aria-required', 'true');
            }

            // Add aria-invalid for fields with errors
            if (input.classList.contains('is-invalid') || input.classList.contains('error')) {
                input.setAttribute('aria-invalid', 'true');

                // Link to error message if exists
                const errorMsg = input.parentElement.querySelector('.invalid-feedback, .error-message');
                if (errorMsg && !errorMsg.id) {
                    errorMsg.id = input.id + '-error';
                    input.setAttribute('aria-describedby', errorMsg.id);
                }
            }
        });
    }

    addARIALabels() {
        // Add ARIA labels to buttons without text
        document.querySelectorAll('button:not([aria-label])').forEach(button => {
            if (!button.textContent.trim()) {
                const icon = button.querySelector('i, svg');
                if (icon) {
                    const iconClass = icon.className;
                    let label = 'Button';

                    if (iconClass.includes('edit')) label = 'Edit';
                    else if (iconClass.includes('delete') || iconClass.includes('trash')) label = 'Delete';
                    else if (iconClass.includes('plus') || iconClass.includes('add')) label = 'Add';
                    else if (iconClass.includes('search')) label = 'Search';
                    else if (iconClass.includes('close') || iconClass.includes('x')) label = 'Close';
                    else if (iconClass.includes('save')) label = 'Save';
                    else if (iconClass.includes('print')) label = 'Print';

                    button.setAttribute('aria-label', label);
                }
            }
        });

        // Add ARIA labels to links without text
        document.querySelectorAll('a:not([aria-label])').forEach(link => {
            if (!link.textContent.trim()) {
                const icon = link.querySelector('i, svg');
                if (icon) {
                    link.setAttribute('aria-label', 'Link');
                }
            }
        });

        // Mark decorative icons as aria-hidden
        document.querySelectorAll('i.bi, svg').forEach(icon => {
            if (!icon.getAttribute('aria-label') && !icon.getAttribute('role')) {
                icon.setAttribute('aria-hidden', 'true');
            }
        });
    }

    handleKeyboardNavigation() {
        // Trap focus in modals
        document.addEventListener('keydown', (e) => {
            const modal = document.querySelector('.modal.show, .modal-modern.show');
            if (modal && e.key === 'Tab') {
                this.trapFocus(modal, e);
            }
        });

        // Close modals with Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.querySelector('.modal.show, .modal-modern.show');
                if (modal) {
                    const closeBtn = modal.querySelector('[data-dismiss="modal"], .modal-close, .close');
                    if (closeBtn) closeBtn.click();
                }
            }
        });

        // Arrow key navigation for dropdown menus
        document.querySelectorAll('[role="menu"], .dropdown-menu').forEach(menu => {
            menu.addEventListener('keydown', (e) => {
                if (['ArrowDown', 'ArrowUp'].includes(e.key)) {
                    e.preventDefault();
                    this.navigateMenu(menu, e.key);
                }
            });
        });
    }

    trapFocus(element, event) {
        const focusableElements = element.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
        }
    }

    navigateMenu(menu, direction) {
        const items = Array.from(menu.querySelectorAll('a, button'));
        const currentIndex = items.indexOf(document.activeElement);

        let nextIndex;
        if (direction === 'ArrowDown') {
            nextIndex = currentIndex + 1;
            if (nextIndex >= items.length) nextIndex = 0;
        } else {
            nextIndex = currentIndex - 1;
            if (nextIndex < 0) nextIndex = items.length - 1;
        }

        items[nextIndex].focus();
    }

    announcePageChanges() {
        // Create live region for announcements
        const liveRegion = document.createElement('div');
        liveRegion.setAttribute('role', 'status');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.className = 'sr-only';
        liveRegion.id = 'live-region';
        document.body.appendChild(liveRegion);

        // Add screen reader only styles
        const style = document.createElement('style');
        style.textContent = `
            .sr-only {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border-width: 0;
            }
        `;
        document.head.appendChild(style);
    }

    announce(message) {
        const liveRegion = document.getElementById('live-region');
        if (liveRegion) {
            liveRegion.textContent = message;
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        }
    }
}

// Initialize accessibility manager
window.a11y = new AccessibilityManager();

// Helper function to announce messages
window.announce = (message) => {
    if (window.a11y) {
        window.a11y.announce(message);
    }
};
