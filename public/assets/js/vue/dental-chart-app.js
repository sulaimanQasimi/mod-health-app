import { createApp } from 'vue'
import DentalChart from './components/DentalChart.vue'

// Define closeModal function
function closeModal() {
    const modalElement = document.getElementById('toothModal');
    if (!modalElement) return;
    
    try {
        // Try Bootstrap 5 Modal API first
        if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
            const modalInstance = window.bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            } else {
                // Create new instance and hide
                const modal = new window.bootstrap.Modal(modalElement);
                modal.hide();
            }
        }
        // Try jQuery Bootstrap
        else if (typeof window.$ !== 'undefined' && window.$.fn.modal) {
            window.$(modalElement).modal('hide');
        }
        // Fallback: hide manually
        else {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            document.body.classList.remove('modal-open');
            const backdrop = document.getElementById('toothModalBackdrop');
            if (backdrop) backdrop.remove();
        }
    } catch (e) {
        console.error('Error closing modal:', e);
        // Fallback manual close
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        document.body.classList.remove('modal-open');
        const backdrop = document.getElementById('toothModalBackdrop');
        if (backdrop) backdrop.remove();
    }
}

// Define submitToothForm function - AJAX submission
function submitToothForm(form, chartId, isEdit) {
    const formData = new FormData(form);
    
    // Get dentist registration ID from form or container
    const dentistRegistrationId = form.dataset.dentistRegistrationId || 
                                   document.getElementById('dental-chart-vue-container')?.dataset.dentistRegistrationId || 
                                   form.querySelector('input[name="dentist_registration_id"]')?.value || '';
    
    // Determine the correct URL - prioritize explicit parameters over form.action
    let url;
    if (isEdit && chartId) {
        // For edit, always use the update route
        url = `/dental-charts/update/${chartId}`;
    } else if (dentistRegistrationId) {
        // For create, use the store route
        url = `/dental-charts/store/${dentistRegistrationId}`;
    } else if (form.action && form.action !== window.location.href && 
               !form.action.includes('dentist-registrations/show') &&
               form.action.includes('dental-charts')) {
        // Only use form.action if it's a valid dental-charts route
        url = form.action;
    } else {
        console.error('Cannot determine submission URL', {
            isEdit,
            chartId,
            dentistRegistrationId,
            formAction: form.action,
            currentUrl: window.location.href
        });
        alert('Error: Cannot determine submission URL. Please refresh the page.');
        return;
    }
    
    // Normalize URL - ensure it's a relative path starting with /
    if (url.startsWith('http://') || url.startsWith('https://')) {
        // Extract path from full URL
        try {
            const urlObj = new URL(url);
            url = urlObj.pathname;
        } catch (e) {
            console.error('Invalid URL format:', url);
        }
    } else if (!url.startsWith('/')) {
        url = '/' + url;
    }
    
    console.log('Submitting to URL:', url, { isEdit, chartId, dentistRegistrationId });
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + (window.localize ? window.localize('global.saving') : 'Saving...');
    
    // Add CSRF token to form data if not present
    if (!formData.has('_token')) {
        formData.append('_token', csrfToken);
    }
    
    // Add method spoofing for PUT if editing
    if (isEdit && !formData.has('_method')) {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If HTML response (redirect), parse it
            return response.text().then(html => {
                // Check if it's a redirect or error page
                if (response.redirected || response.url !== url) {
                    return { success: true, redirect: true, url: response.url };
                }
                // Try to extract error messages from HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const errorMessages = doc.querySelectorAll('.error, .invalid-feedback, .alert-danger');
                if (errorMessages.length > 0) {
                    const errors = Array.from(errorMessages).map(el => el.textContent.trim()).join(', ');
                    return { success: false, message: errors };
                }
                return { success: true };
            });
        }
    })
    .then(data => {
        if (data && data.success !== undefined) {
            if (data.success) {
                // Show success message
                if (data.message) {
                    // You can use a toast notification here if available
                    console.log('Success:', data.message);
                }
                
                // Close modal
                closeModal();
                
                // Reload the chart data without full page reload
                if (window.location.reload) {
                    // Small delay to show success, then reload
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    window.location.reload();
                }
            } else {
                // Show error message
                const errorMsg = data.message || data.error || (window.localize ? window.localize('global.save_failed') : 'Save failed');
                alert(errorMsg);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } else if (data && data.errors) {
            // Validation errors
            const errorMessages = Object.values(data.errors).flat().join('\n');
            alert(errorMessages || (window.localize ? window.localize('global.validation_failed') : 'Validation failed'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        } else {
            // Unknown response format, assume success
            closeModal();
            setTimeout(() => window.location.reload(), 500);
        }
    })
    .catch(error => {
        console.error('Error submitting form:', error);
        alert(window.localize ? window.localize('global.save_failed') : 'Save failed: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Define openToothModal function, before Vue app initialization
function openToothModal(toothNumber, chartId) {
    console.log('openToothModal called with:', toothNumber, chartId);
    
    const modalBody = document.getElementById('toothModalBody');
    const modalElement = document.getElementById('toothModal');
    
    if (!modalElement) {
        console.error('Tooth modal element not found. Creating modal...');
        createToothModal();
        // Try again after creating
        setTimeout(() => openToothModal(toothNumber, chartId), 100);
        return;
    }
    
    // Check if Bootstrap is available and use appropriate method
    // Use the same pattern as other modals in the codebase
    let modal;
    if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
        try {
            // Check if modal instance already exists
            let modalInstance = window.bootstrap.Modal.getInstance(modalElement);
            if (!modalInstance) {
                modalInstance = new window.bootstrap.Modal(modalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
            }
            modal = modalInstance;
        } catch (error) {
            console.log('Bootstrap Modal error, using jQuery fallback:', error);
            // Fallback to jQuery Bootstrap
            if (typeof window.$ !== 'undefined' && window.$.fn.modal) {
                modal = { 
                    show: () => window.$(modalElement).modal('show'), 
                    hide: () => window.$(modalElement).modal('hide') 
                };
            } else {
                // Final fallback: manual show
                modal = {
                    show: () => {
                        modalElement.style.display = 'block';
                        modalElement.classList.add('show');
                        document.body.classList.add('modal-open');
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.id = 'toothModalBackdrop';
                        backdrop.onclick = () => modal.hide();
                        document.body.appendChild(backdrop);
                    },
                    hide: () => {
                        modalElement.style.display = 'none';
                        modalElement.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.getElementById('toothModalBackdrop');
                        if (backdrop) backdrop.remove();
                    }
                };
            }
        }
    }
    // Try jQuery Bootstrap
    else if (typeof window.$ !== 'undefined' && window.$.fn.modal) {
        modal = { 
            show: () => window.$(modalElement).modal('show'), 
            hide: () => window.$(modalElement).modal('hide') 
        };
    }
    // Fallback: show manually
    else {
        console.warn('Bootstrap not available, using manual modal');
        modal = {
            show: () => {
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                document.body.classList.add('modal-open');
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'toothModalBackdrop';
                backdrop.onclick = () => modal.hide();
                document.body.appendChild(backdrop);
            },
            hide: () => {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');
                const backdrop = document.getElementById('toothModalBackdrop');
                if (backdrop) backdrop.remove();
            }
        };
    }
    const modalTitle = document.getElementById('modalToothNumber');
    
    if (modalTitle) {
        modalTitle.textContent = toothNumber;
    }
    
    const dentistRegistrationId = document.getElementById('dental-chart-vue-container')?.dataset.dentistRegistrationId || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    if (chartId) {
        // Load existing chart data via AJAX
        modalBody.innerHTML = `
            <div class="text-center mb-3">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        fetch(`/dental-charts/edit/${chartId}`)
            .then(response => response.text())
            .then(html => {
                // Extract form from the edit page HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const form = doc.querySelector('form');
                if (form) {
                    // Force update form action to correct AJAX endpoint
                    form.setAttribute('action', `/dental-charts/update/${chartId}`);
                    form.setAttribute('method', 'POST');
                    
                    // Store IDs on form for easy access
                    form.dataset.dentistRegistrationId = dentistRegistrationId;
                    form.dataset.chartId = chartId;
                    
                    // Ensure method spoofing for PUT
                    let methodInput = form.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);
                    } else {
                        methodInput.value = 'PUT';
                    }
                    
                    // Ensure CSRF token is present
                    let csrfInput = form.querySelector('input[name="_token"]');
                    if (!csrfInput) {
                        csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);
                    } else {
                        csrfInput.value = csrfToken;
                    }
                    
                    // chart_date is now automatically set to today in the backend, no need to include it in the form
                    
                    // Initialize Persian datepicker for other date fields if needed
                    setTimeout(() => {
                        if (typeof window.$ !== 'undefined' && window.$.fn.persianDatepicker) {
                            // Check for other date pickers in the form (not chart_date)
                            const datePickerInput = form.querySelector('input.datepicker_dari:not([name="chart_date"])');
                            if (datePickerInput && !datePickerInput.dataset.persianDatepickerInitialized) {
                                // Get the value - it should be in Persian format from backend
                                const currentValue = datePickerInput.value;
                                
                                $(datePickerInput).persianDatepicker({
                                    formatDate: 'YYYY-MM-DD',
                                    calendar: {
                                        persian: {
                                            locale: 'en',
                                            showHint: true,
                                            leapYearMode: 'algorithmic'
                                        }
                                    },
                                    checkDate: function(unix) {
                                        return true;
                                    },
                                    onSelect: function() {
                                        const selectedDate = $(this).val();
                                        if (selectedDate) {
                                            datePickerInput.value = selectedDate;
                                        }
                                    }
                                });
                                
                                // Set the value after initialization if editing
                                if (currentValue) {
                                    $(datePickerInput).val(currentValue);
                                }
                                
                                datePickerInput.dataset.persianDatepickerInitialized = 'true';
                            }
                        }
                    }, 150);
                    
                    // Add onsubmit to handle AJAX
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const submitFn = typeof submitToothForm === 'function' 
                            ? submitToothForm 
                            : window.submitToothForm;
                        if (submitFn) {
                            submitFn(form, chartId, true);
                        }
                    });
                    modalBody.innerHTML = '';
                    modalBody.appendChild(form);
                } else {
                    // Fallback to simple form
                    showToothForm(modalBody, toothNumber, dentistRegistrationId, csrfToken, chartId);
                }
            })
            .catch(error => {
                console.error('Error loading chart:', error);
                showToothForm(modalBody, toothNumber, dentistRegistrationId, csrfToken, chartId);
            });
    } else {
        // Show create form
        showToothForm(modalBody, toothNumber, dentistRegistrationId, csrfToken, null);
    }
    
    try {
        if (modal && typeof modal.show === 'function') {
            modal.show();
            console.log('Modal shown successfully');
        } else {
            console.error('Modal show method not available');
            // Fallback: manual show
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'toothModalBackdrop';
            backdrop.onclick = () => {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');
                backdrop.remove();
            };
            document.body.appendChild(backdrop);
        }
    } catch (error) {
        console.error('Error showing modal:', error);
        // Fallback: try direct show
        if (modalElement) {
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'toothModalBackdrop';
            backdrop.onclick = () => {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');
                backdrop.remove();
            };
            document.body.appendChild(backdrop);
        }
    }
}

// Make functions globally available immediately - before DOMContentLoaded
window.openToothModal = openToothModal;
window.submitToothForm = submitToothForm;
window.closeModal = closeModal;

// Also make them available on window load as backup
window.addEventListener('load', function() {
    if (!window.openToothModal) {
        window.openToothModal = openToothModal;
    }
    if (!window.submitToothForm) {
        window.submitToothForm = submitToothForm;
    }
    if (!window.closeModal) {
        window.closeModal = closeModal;
    }
});

// Initialize Vue app for dental chart
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('dental-chart-vue-container');
    
    if (container) {
        const dentistRegistrationId = parseInt(container.dataset.dentistRegistrationId || '0');
        const teethDataRaw = container.dataset.teethData || '{}';
        let teethData = {};
        
        try {
            // Clean the data string - remove any HTML entities
            const cleanData = teethDataRaw.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, '&');
            teethData = JSON.parse(cleanData);
        } catch (e) {
            console.error('Error parsing teeth data:', e);
            console.error('Raw data:', teethDataRaw);
            console.error('Data length:', teethDataRaw.length);
            // Try to fix common issues
            try {
                // Try with unescaped quotes
                const fixedData = teethDataRaw.replace(/&quot;/g, '"');
                teethData = JSON.parse(fixedData);
            } catch (e2) {
                console.error('Second parse attempt failed:', e2);
                teethData = {};
            }
        }
        
        // Teeth data is already keyed by tooth_number from the backend
        const teethDataKeyed = teethData || {};
        
        const app = createApp(DentalChart, {
            teethData: teethDataKeyed,
            dentistRegistrationId: dentistRegistrationId
        });
        
        app.mount('#dental-chart-vue-container');
    }
});


function showToothForm(modalBody, toothNumber, dentistRegistrationId, csrfToken, chartId) {
    const isEdit = chartId !== null;
    const actionUrl = isEdit ? `/dental-charts/update/${chartId}` : `/dental-charts/store/${dentistRegistrationId}`;
    const method = isEdit ? 'PUT' : 'POST';
    
    // Get today's date in Persian format for default value
    const today = new Date();
    const todayPersian = today.toLocaleDateString('fa-IR');
    
    modalBody.innerHTML = `
        <form id="toothForm" action="${actionUrl}" method="POST">
            <input type="hidden" name="_token" value="${csrfToken}">
            ${isEdit ? '<input type="hidden" name="_method" value="PUT">' : ''}
            <input type="hidden" name="tooth_number" value="${toothNumber}">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">وضعیت دندان <span class="text-danger">*</span></label>
                    <select name="tooth_condition" class="form-select" required>
                        <option value="healthy">Healthy</option>
                        <option value="cavity">Cavity</option>
                        <option value="filling">Filling</option>
                        <option value="crown">Crown</option>
                        <option value="bridge">Bridge</option>
                        <option value="root_canal">Root Canal</option>
                        <option value="implant">Implant</option>
                        <option value="decay">Decay</option>
                        <option value="fractured">Fractured</option>
                        <option value="extraction">Extraction</option>
                        <option value="missing">Missing</option>
                        <option value="impacted">Impacted</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">سلامت لثه</label>
                    <select name="gum_health" class="form-select">
                        <option value="">Select</option>
                        <option value="healthy">Healthy</option>
                        <option value="gingivitis">Gingivitis</option>
                        <option value="periodontitis">Periodontitis</option>
                        <option value="recession">Recession</option>
                        <option value="bleeding">Bleeding</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">امتیاز بهداشت دهان</label>
                    <input type="number" step="0.1" min="0" max="10" name="oral_hygiene_score" class="form-control" placeholder="0-10">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">عمق پاکت (mm)</label>
                    <input type="number" step="0.01" min="0" max="20" name="pocket_depth" class="form-control" placeholder="0-20">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">خونریزی</label>
                    <select name="bleeding" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">حرکت دندان</label>
                    <select name="mobility" class="form-select">
                        <option value="">Select</option>
                        <option value="none">None</option>
                        <option value="grade1">Grade 1</option>
                        <option value="grade2">Grade 2</option>
                        <option value="grade3">Grade 3</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">سابقه درمان</label>
                    <textarea name="treatment_history" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">یادداشت ها</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="submit" class="btn btn-primary">ذخیره</button>
            </div>
        </form>
    `;
    
    // Attach event listener to the form after it's added to DOM
    setTimeout(() => {
        const form = document.getElementById('toothForm');
        if (form) {
            // Ensure form has correct action attribute
            if (!form.action || form.action.includes('dentist-registrations/show')) {
                const correctAction = isEdit ? `/dental-charts/update/${chartId}` : `/dental-charts/store/${dentistRegistrationId}`;
                form.setAttribute('action', correctAction);
                form.action = correctAction;
            }
            
            // Remove any existing listeners by cloning
            const newForm = form.cloneNode(true);
            
            // Ensure cloned form has correct action
            newForm.setAttribute('action', form.action);
            newForm.action = form.action;
            
            form.parentNode.replaceChild(newForm, form);
            
            // Store dentist registration ID and chart ID on form for easy access
            newForm.dataset.dentistRegistrationId = dentistRegistrationId;
            newForm.dataset.chartId = chartId || '';
            
            // Initialize Persian datepicker
            if (typeof window.$ !== 'undefined' && window.$.fn.persianDatepicker) {
                // chart_date is now automatically set to today in the backend
                // Check for other date pickers if needed
                const datePickerInput = newForm.querySelector('input.datepicker_dari:not([name="chart_date"])');
                if (datePickerInput && !datePickerInput.dataset.persianDatepickerInitialized) {
                    $(datePickerInput).persianDatepicker({
                        formatDate: 'YYYY-MM-DD',
                        calendar: {
                            persian: {
                                locale: 'en',
                                showHint: true,
                                leapYearMode: 'algorithmic'
                            }
                        },
                        checkDate: function(unix) {
                            return true;
                        },
                        onSelect: function() {
                            // Ensure the value is set correctly
                            const selectedDate = $(this).val();
                            if (selectedDate) {
                                datePickerInput.value = selectedDate;
                            }
                        }
                    });
                    datePickerInput.dataset.persianDatepickerInitialized = 'true';
                    
                    // Set default value to today if not editing
                    if (!isEdit && !datePickerInput.value) {
                        // Get today's date in Persian format
                        const today = new Date();
                        const year = today.getFullYear();
                        const month = String(today.getMonth() + 1).padStart(2, '0');
                        const day = String(today.getDate()).padStart(2, '0');
                        // Note: This is a temporary Gregorian date, the datepicker will convert it
                        // We'll let the datepicker handle the default
                    }
                }
            } else {
                console.warn('Persian datepicker library not loaded');
            }
            
            // Add event listener to the new form
            newForm.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Get the function reference
                const submitFn = typeof submitToothForm === 'function' 
                    ? submitToothForm 
                    : (typeof window.submitToothForm === 'function' 
                        ? window.submitToothForm 
                        : null);
                
                if (submitFn) {
                    submitFn(newForm, chartId, isEdit);
                } else {
                    console.error('submitToothForm function not available');
                    alert('Form submission error. Please refresh the page.');
                }
            });
        }
    }, 150);
}


function createToothModal() {
    // Check if modal already exists
    if (document.getElementById('toothModal')) {
        return;
    }
    
    const modalHTML = `
        <div class="modal fade" id="toothModal" tabindex="-1" aria-labelledby="toothModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="toothModalLabel">${window.localize ? window.localize('global.tooth') : 'Tooth'} <span id="modalToothNumber"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="toothModalBody">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

