/**
 * Tailwind Forms JavaScript
 * Provides interactive functionality for form components
 */

class TailwindForms {
    constructor() {
        this.init();
    }

    init() {
        this.setupFileInputs();
        this.setupPasswordToggles();
        this.setupCharacterCounters();
        this.setupFormValidation();
    }

    /**
     * Setup file input previews
     */
    setupFileInputs() {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    this.showFilePreview(input, file);
                }
            });
        });
    }

    /**
     * Show file preview
     */
    showFilePreview(input, file) {
        const preview = input.parentNode.querySelector('.file-preview');
        if (!preview) return;

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `<img src="${e.target.result}" class="max-w-xs h-32 object-cover rounded">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `<div class="text-sm text-gray-600">${file.name}</div>`;
        }
    }

    /**
     * Setup password toggle functionality
     */
    setupPasswordToggles() {
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const input = button.parentNode.querySelector('input');
                const icon = button.querySelector('svg');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerHTML = this.getEyeSlashIcon();
                } else {
                    input.type = 'password';
                    icon.innerHTML = this.getEyeIcon();
                }
            });
        });
    }

    /**
     * Setup character counters for textareas
     */
    setupCharacterCounters() {
        document.querySelectorAll('textarea[data-max-length]').forEach(textarea => {
            const maxLength = parseInt(textarea.dataset.maxLength);
            const counter = textarea.parentNode.querySelector('.char-counter');
            
            if (!counter) return;

            const updateCounter = () => {
                const remaining = maxLength - textarea.value.length;
                counter.textContent = `${textarea.value.length}/${maxLength}`;
                
                if (remaining < 0) {
                    counter.classList.add('text-red-600');
                    textarea.classList.add('border-red-300');
                } else {
                    counter.classList.remove('text-red-600');
                    textarea.classList.remove('border-red-300');
                }
            };

            textarea.addEventListener('input', updateCounter);
            updateCounter();
        });
    }

    /**
     * Setup form validation
     */
    setupFormValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * Validate form
     */
    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });

        return isValid;
    }

    /**
     * Show field error
     */
    showFieldError(field, message) {
        field.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        
        let errorElement = field.parentNode.querySelector('.field-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error mt-1 text-sm text-red-600';
            field.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    }

    /**
     * Clear field error
     */
    clearFieldError(field) {
        field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    /**
     * Get eye icon SVG
     */
    getEyeIcon() {
        return `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
    }

    /**
     * Get eye slash icon SVG
     */
    getEyeSlashIcon() {
        return `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />`;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new TailwindForms();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TailwindForms;
}
