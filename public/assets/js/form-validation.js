/**
 * Form Validation System
 * 
 * Provides real-time form validation with accessibility support
 */

class FormValidator {
    constructor() {
        this.forms = new Map();
        this.init();
    }

    init() {
        // Auto-validate all forms with data-validate attribute
        document.querySelectorAll('form[data-validate]').forEach(form => {
            this.attachToForm(form);
        });

        // Add custom validation rules
        this.addCustomRules();
    }

    attachToForm(form) {
        const formId = form.id || 'form-' + Math.random().toString(36).substr(2, 9);
        form.id = formId;

        const validator = {
            form: form,
            fields: new Map(),
            isValid: true
        };

        this.forms.set(formId, validator);

        // Validate on submit
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(formId)) {
                e.preventDefault();
                e.stopPropagation();

                // Focus first invalid field
                const firstInvalid = form.querySelector('[aria-invalid="true"]');
                if (firstInvalid) {
                    firstInvalid.focus();
                }

                if (window.toast) {
                    toast.error('Please fix the errors in the form');
                }

                if (window.announce) {
                    announce('Form has errors. Please review and correct them.');
                }
            }
        });

        // Validate fields on blur
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });

            // Real-time validation for certain fields
            if (field.type === 'email' || field.type === 'url' || field.type === 'tel') {
                field.addEventListener('input', () => {
                    if (field.value) {
                        this.validateField(field);
                    }
                });
            }
        });
    }

    validateForm(formId) {
        const validator = this.forms.get(formId);
        if (!validator) return true;

        let isValid = true;
        const fields = validator.form.querySelectorAll('input, select, textarea');

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        validator.isValid = isValid;
        return isValid;
    }

    validateField(field) {
        // Skip disabled and readonly fields
        if (field.disabled || field.readOnly) return true;

        const errors = [];

        // Required validation
        if (field.required && !field.value.trim()) {
            errors.push('This field is required');
        }

        // Type-specific validation
        if (field.value) {
            switch (field.type) {
                case 'email':
                    if (!this.isValidEmail(field.value)) {
                        errors.push('Please enter a valid email address');
                    }
                    break;

                case 'url':
                    if (!this.isValidURL(field.value)) {
                        errors.push('Please enter a valid URL');
                    }
                    break;

                case 'tel':
                    if (!this.isValidPhone(field.value)) {
                        errors.push('Please enter a valid phone number');
                    }
                    break;

                case 'number':
                    if (field.min && parseFloat(field.value) < parseFloat(field.min)) {
                        errors.push(`Value must be at least ${field.min}`);
                    }
                    if (field.max && parseFloat(field.value) > parseFloat(field.max)) {
                        errors.push(`Value must be at most ${field.max}`);
                    }
                    break;
            }

            // Pattern validation
            if (field.pattern && !new RegExp(field.pattern).test(field.value)) {
                errors.push(field.title || 'Please match the requested format');
            }

            // Min/max length
            if (field.minLength && field.value.length < field.minLength) {
                errors.push(`Must be at least ${field.minLength} characters`);
            }
            if (field.maxLength && field.value.length > field.maxLength) {
                errors.push(`Must be at most ${field.maxLength} characters`);
            }

            // Custom validation
            if (field.dataset.validate) {
                const customError = this.customValidate(field);
                if (customError) {
                    errors.push(customError);
                }
            }
        }

        // Update field state
        this.updateFieldState(field, errors);

        return errors.length === 0;
    }

    updateFieldState(field, errors) {
        const hasErrors = errors.length > 0;

        // Update ARIA attributes
        field.setAttribute('aria-invalid', hasErrors ? 'true' : 'false');

        // Update classes
        field.classList.toggle('is-invalid', hasErrors);
        field.classList.toggle('is-valid', !hasErrors && field.value);

        // Update or create error message
        let errorContainer = field.parentElement.querySelector('.invalid-feedback');

        if (hasErrors) {
            if (!errorContainer) {
                errorContainer = document.createElement('div');
                errorContainer.className = 'invalid-feedback';
                errorContainer.id = field.id + '-error';
                field.setAttribute('aria-describedby', errorContainer.id);
                field.parentElement.appendChild(errorContainer);
            }
            errorContainer.textContent = errors[0];
            errorContainer.style.display = 'block';
        } else if (errorContainer) {
            errorContainer.style.display = 'none';
        }
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidURL(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    isValidPhone(phone) {
        // Basic phone validation (can be customized)
        return /^[\d\s\-\+\(\)]+$/.test(phone) && phone.replace(/\D/g, '').length >= 10;
    }

    customValidate(field) {
        const validationType = field.dataset.validate;

        switch (validationType) {
            case 'password-strength':
                return this.validatePasswordStrength(field.value);

            case 'confirm-password':
                const passwordField = document.getElementById(field.dataset.confirmFor);
                if (passwordField && field.value !== passwordField.value) {
                    return 'Passwords do not match';
                }
                break;

            case 'credit-card':
                if (!this.isValidCreditCard(field.value)) {
                    return 'Please enter a valid credit card number';
                }
                break;

            case 'date-future':
                const date = new Date(field.value);
                if (date <= new Date()) {
                    return 'Date must be in the future';
                }
                break;

            case 'date-past':
                const pastDate = new Date(field.value);
                if (pastDate >= new Date()) {
                    return 'Date must be in the past';
                }
                break;
        }

        return null;
    }

    validatePasswordStrength(password) {
        if (password.length < 8) {
            return 'Password must be at least 8 characters';
        }
        if (!/[A-Z]/.test(password)) {
            return 'Password must contain at least one uppercase letter';
        }
        if (!/[a-z]/.test(password)) {
            return 'Password must contain at least one lowercase letter';
        }
        if (!/[0-9]/.test(password)) {
            return 'Password must contain at least one number';
        }
        if (!/[^A-Za-z0-9]/.test(password)) {
            return 'Password must contain at least one special character';
        }
        return null;
    }

    isValidCreditCard(number) {
        // Luhn algorithm
        const digits = number.replace(/\D/g, '');
        if (digits.length < 13 || digits.length > 19) return false;

        let sum = 0;
        let isEven = false;

        for (let i = digits.length - 1; i >= 0; i--) {
            let digit = parseInt(digits[i]);

            if (isEven) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }

            sum += digit;
            isEven = !isEven;
        }

        return sum % 10 === 0;
    }

    addCustomRules() {
        // Add CSS for validation states
        const style = document.createElement('style');
        style.textContent = `
            .is-invalid {
                border-color: #ef4444 !important;
            }

            .is-valid {
                border-color: #10b981 !important;
            }

            .invalid-feedback {
                display: none;
                color: #ef4444;
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }

            .invalid-feedback:before {
                content: '⚠ ';
            }

            .valid-feedback {
                display: none;
                color: #10b981;
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }

            .valid-feedback:before {
                content: '✓ ';
            }

            /* Password strength indicator */
            .password-strength {
                height: 4px;
                background: #e5e7eb;
                border-radius: 2px;
                margin-top: 0.5rem;
                overflow: hidden;
            }

            .password-strength-bar {
                height: 100%;
                transition: width 0.3s, background-color 0.3s;
                width: 0;
            }

            .password-strength-weak { width: 33%; background: #ef4444; }
            .password-strength-medium { width: 66%; background: #f59e0b; }
            .password-strength-strong { width: 100%; background: #10b981; }
        `;
        document.head.appendChild(style);
    }
}

// Initialize form validator
window.formValidator = new FormValidator();

// Helper function to manually validate a form
window.validateForm = (formId) => {
    return window.formValidator.validateForm(formId);
};
