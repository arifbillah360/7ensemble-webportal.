/**
 * 7 Ensemble - Modal Handler
 * Handles modal opening/closing and form validation
 */

/**
 * Modal Manager Class
 */
class ModalManager {
    constructor() {
        this.activeModal = null;
        this.modals = new Map();
        this.init();
    }

    /**
     * Initialize modal system
     */
    init() {
        this.setupEventListeners();
        this.registerAllModals();
        console.log('Modal Manager initialized');
    }

    /**
     * Register all modals on the page
     */
    registerAllModals() {
        const modalElements = document.querySelectorAll('.modal-overlay');
        modalElements.forEach(modalElement => {
            const modalId = modalElement.id || this.generateModalId();
            modalElement.id = modalId;
            this.modals.set(modalId, {
                element: modalElement,
                isOpen: false
            });
        });
    }

    /**
     * Generate unique modal ID
     */
    generateModalId() {
        return `modal-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Setup global event listeners
     */
    setupEventListeners() {
        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.close(this.activeModal);
            }
        });

        // Close modal on overlay click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                const modalId = e.target.id;
                if (modalId) {
                    this.close(modalId);
                }
            }
        });

        // Close modal on close button click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-close') || e.target.closest('.modal-close')) {
                const modal = e.target.closest('.modal-overlay');
                if (modal) {
                    this.close(modal.id);
                }
            }
        });
    }

    /**
     * Open a modal
     */
    open(modalId) {
        const modal = this.modals.get(modalId);
        if (!modal) {
            console.error(`Modal with id "${modalId}" not found`);
            return;
        }

        // Close any currently open modal
        if (this.activeModal && this.activeModal !== modalId) {
            this.close(this.activeModal);
        }

        // Show modal
        modal.element.classList.remove('hidden');
        modal.element.style.display = 'flex';
        modal.isOpen = true;
        this.activeModal = modalId;

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Add animation
        setTimeout(() => {
            modal.element.classList.add('modal-open');
        }, 10);

        // Trigger custom event
        const event = new CustomEvent('modalOpen', { detail: { modalId } });
        document.dispatchEvent(event);

        console.log(`Modal "${modalId}" opened`);
    }

    /**
     * Close a modal
     */
    close(modalId) {
        const modal = this.modals.get(modalId);
        if (!modal || !modal.isOpen) {
            return;
        }

        // Remove animation class
        modal.element.classList.remove('modal-open');

        // Hide modal after animation
        setTimeout(() => {
            modal.element.classList.add('hidden');
            modal.element.style.display = 'none';
            modal.isOpen = false;

            // Restore body scroll if no other modals are open
            if (this.activeModal === modalId) {
                this.activeModal = null;
                document.body.style.overflow = '';
            }
        }, 300);

        // Trigger custom event
        const event = new CustomEvent('modalClose', { detail: { modalId } });
        document.dispatchEvent(event);

        console.log(`Modal "${modalId}" closed`);
    }

    /**
     * Close all modals
     */
    closeAll() {
        this.modals.forEach((modal, modalId) => {
            if (modal.isOpen) {
                this.close(modalId);
            }
        });
    }

    /**
     * Check if a modal is open
     */
    isOpen(modalId) {
        const modal = this.modals.get(modalId);
        return modal ? modal.isOpen : false;
    }
}

/**
 * Form Validator Class
 */
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) {
            console.error(`Form with id "${formId}" not found`);
            return;
        }

        this.errors = {};
        this.rules = {};
        this.customMessages = {};
    }

    /**
     * Add validation rules
     */
    addRule(fieldName, rules) {
        this.rules[fieldName] = rules;
        return this;
    }

    /**
     * Add custom error message
     */
    addMessage(fieldName, message) {
        this.customMessages[fieldName] = message;
        return this;
    }

    /**
     * Validate the form
     */
    validate() {
        this.errors = {};
        let isValid = true;

        // Clear previous errors
        this.clearErrors();

        // Validate each field
        for (const [fieldName, rules] of Object.entries(this.rules)) {
            const field = this.form.elements[fieldName];
            if (!field) continue;

            const value = field.value.trim();
            const fieldErrors = [];

            // Required validation
            if (rules.required && !value) {
                fieldErrors.push(this.customMessages[fieldName] || 'Ce champ est requis');
            }

            // Email validation
            if (rules.email && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    fieldErrors.push('Adresse e-mail invalide');
                }
            }

            // Min length validation
            if (rules.minLength && value.length < rules.minLength) {
                fieldErrors.push(`Minimum ${rules.minLength} caractères requis`);
            }

            // Max length validation
            if (rules.maxLength && value.length > rules.maxLength) {
                fieldErrors.push(`Maximum ${rules.maxLength} caractères autorisés`);
            }

            // Pattern validation
            if (rules.pattern && value) {
                const regex = new RegExp(rules.pattern);
                if (!regex.test(value)) {
                    fieldErrors.push(rules.patternMessage || 'Format invalide');
                }
            }

            // Custom validation function
            if (rules.custom && typeof rules.custom === 'function') {
                const customError = rules.custom(value, this.form);
                if (customError) {
                    fieldErrors.push(customError);
                }
            }

            // Checkbox validation
            if (rules.checked && field.type === 'checkbox' && !field.checked) {
                fieldErrors.push('Vous devez accepter ce champ');
            }

            // Store errors
            if (fieldErrors.length > 0) {
                this.errors[fieldName] = fieldErrors;
                this.showFieldError(field, fieldErrors[0]);
                isValid = false;
            }
        }

        return isValid;
    }

    /**
     * Show error for a specific field
     */
    showFieldError(field, message) {
        field.classList.add('error');

        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.classList.add('form-error');
        errorElement.textContent = message;
        errorElement.setAttribute('data-error-for', field.name);

        // Insert after field
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }

    /**
     * Clear all errors
     */
    clearErrors() {
        // Remove error classes
        this.form.querySelectorAll('.error').forEach(el => {
            el.classList.remove('error');
        });

        // Remove error messages
        this.form.querySelectorAll('.form-error').forEach(el => {
            el.remove();
        });

        this.errors = {};
    }

    /**
     * Clear error for specific field
     */
    clearFieldError(fieldName) {
        const field = this.form.elements[fieldName];
        if (!field) return;

        field.classList.remove('error');

        const errorElement = this.form.querySelector(`[data-error-for="${fieldName}"]`);
        if (errorElement) {
            errorElement.remove();
        }

        delete this.errors[fieldName];
    }

    /**
     * Get all errors
     */
    getErrors() {
        return this.errors;
    }

    /**
     * Get form data as object
     */
    getFormData() {
        const formData = new FormData(this.form);
        const data = {};

        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }

        return data;
    }

    /**
     * Setup real-time validation
     */
    setupRealTimeValidation() {
        for (const fieldName of Object.keys(this.rules)) {
            const field = this.form.elements[fieldName];
            if (!field) continue;

            field.addEventListener('blur', () => {
                this.validateField(fieldName);
            });

            field.addEventListener('input', () => {
                if (this.errors[fieldName]) {
                    this.clearFieldError(fieldName);
                }
            });
        }
    }

    /**
     * Validate a single field
     */
    validateField(fieldName) {
        const rules = this.rules[fieldName];
        if (!rules) return true;

        const field = this.form.elements[fieldName];
        if (!field) return true;

        const value = field.value.trim();
        this.clearFieldError(fieldName);

        // Simplified single-field validation
        if (rules.required && !value) {
            this.showFieldError(field, this.customMessages[fieldName] || 'Ce champ est requis');
            return false;
        }

        if (rules.email && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showFieldError(field, 'Adresse e-mail invalide');
                return false;
            }
        }

        return true;
    }
}

/**
 * Global modal instance
 */
const modalManager = new ModalManager();

/**
 * Global helper functions
 */
window.openModal = function(modalId) {
    modalManager.open(modalId);
};

window.closeModal = function(modalId) {
    modalManager.close(modalId);
};

window.closeAllModals = function() {
    modalManager.closeAll();
};

/**
 * Handle registration form submission
 */
function setupRegistrationForms() {
    const forms = document.querySelectorAll('form[id$="Form"]');

    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const validator = new FormValidator(form.id);

            // Add validation rules
            validator.addRule('fullName', {
                required: true,
                minLength: 3
            });

            validator.addRule('email', {
                required: true,
                email: true
            });

            validator.addRule('country', {
                required: true
            });

            validator.addRule('paymentMethod', {
                required: true
            });

            validator.addRule('terms', {
                checked: true
            });

            // Validate form
            if (!validator.validate()) {
                console.log('Form validation failed', validator.getErrors());
                return;
            }

            // Get form data
            const formData = validator.getFormData();
            console.log('Form submitted:', formData);

            // Show loading state
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span> Envoi en cours...';

            try {
                // Submit form (replace with actual API call)
                const response = await submitRegistration(formData);

                if (response.success) {
                    // Show success message
                    alert('Inscription réussie! Vous recevrez un e-mail de confirmation.');
                    form.reset();
                    closeModal(form.closest('.modal-overlay').id);
                } else {
                    alert('Erreur: ' + (response.message || 'Une erreur est survenue'));
                }
            } catch (error) {
                console.error('Submission error:', error);
                alert('Une erreur est survenue lors de l\'envoi du formulaire');
            } finally {
                // Restore button
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    });
}

/**
 * Mock API call for registration
 */
async function submitRegistration(formData) {
    // Simulate API call
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                success: true,
                message: 'Registration successful'
            });
        }, 1500);
    });

    // Actual implementation would be:
    // const response = await fetch('/api/register', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    //     },
    //     body: JSON.stringify(formData)
    // });
    // return await response.json();
}

/**
 * Initialize on DOM ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupRegistrationForms);
} else {
    setupRegistrationForms();
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ModalManager, FormValidator, modalManager };
}
