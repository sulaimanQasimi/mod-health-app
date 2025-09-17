/**
 * Nutrition Care Manager
 * Handles all nutrition care related functionality
 */
class NutritionCareManager {
    constructor(config) {
        this.morphModel = config.morphModel;
        this.morphableType = config.morphableType;
        this.morphableId = config.morphableId;
        this.routes = config.routes;
        this.csrfToken = config.csrfToken;
        this.init();
    }

    init() {
        this.loadData();
        this.bindEvents();
    }

    /**
     * Load nutrition care data via AJAX
     */
    loadData() {
        console.log('Loading nutrition care data...');
        console.log('Morphable Type:', this.morphableType);
        console.log('Morphable ID:', this.morphableId);
        
        $.ajax({
            url: this.routes.index,
            method: 'GET',
            data: {
                morphable_type: this.morphableType,
                morphable_id: this.morphableId
            },
            success: (response) => {
                console.log('Nutrition care data loaded successfully:', response);
                this.renderTable(response.data);
            },
            error: (xhr) => {
                this.handleError(xhr, 'Error loading nutrition care data');
            }
        });
    }

    /**
     * Render the nutrition care table
     */
    renderTable(nutritionCares) {
        const container = $('#nutrition-care-data-container');
        
        if (nutritionCares.length > 0) {
            const tableHtml = this.generateTableHtml(nutritionCares);
            container.html(tableHtml);
        } else {
            container.html(this.generateEmptyStateHtml());
        }
        
        this.bindEventHandlers();
    }

    /**
     * Generate table HTML
     */
    generateTableHtml(nutritionCares) {
        let tableHtml = `
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient Name</th>
                            <th>Nurse</th>
                            <th>Observations</th>
                            <th>Interventions</th>
                            <th>Notes</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>`;
        
        nutritionCares.forEach(nutritionCare => {
            const observations = this.buildObservationsList(nutritionCare);
            const interventions = this.buildInterventionsList(nutritionCare);
            const noteText = this.truncateText(nutritionCare.nutrition_care_full_note, 50);
            
            tableHtml += `
                <tr>
                    <td>${nutritionCare.id}</td>
                    <td>${nutritionCare.patient_name || '-'}</td>
                    <td>${nutritionCare.nurse ? nutritionCare.nurse.full_name : 'N/A'}</td>
                    <td>${observations}</td>
                    <td>${interventions}</td>
                    <td>
                        ${nutritionCare.nutrition_care_full_note ? 
                            `<span class="text-truncate d-inline-block" style="max-width: 200px;" title="${nutritionCare.nutrition_care_full_note}">${noteText}</span>` : 
                            '<span class="text-muted">-</span>'
                        }
                    </td>
                    <td>${this.formatDateTime(nutritionCare.created_at)}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info view-nutrition-care-btn"
                                data-nutrition-care-id="${nutritionCare.id}"
                                title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="${this.routes.print.replace(':id', nutritionCare.id)}" 
                                class="btn btn-sm btn-primary" title="Print" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-warning edit-nutrition-care-btn"
                                data-nutrition-care-id="${nutritionCare.id}"
                                data-cough="${nutritionCare.cough ? '1' : '0'}"
                                data-sound="${nutritionCare.sound ? '1' : '0'}"
                                data-fluid-swallowing-ability="${nutritionCare.fluid_swallowing_ability ? '1' : '0'}"
                                data-weight="${nutritionCare.weight ? '1' : '0'}"
                                data-amount-and-type-of-nutrition="${nutritionCare.amount_and_type_of_nutrition ? '1' : '0'}"
                                data-diarrhea="${nutritionCare.diarrhea ? '1' : '0'}"
                                data-heart-failure-and-kidney-disease="${nutritionCare.heart_failure_and_kidney_disease ? '1' : '0'}"
                                data-remaining-materials="${nutritionCare.remaining_materials ? '1' : '0'}"
                                data-type-of-tube="${nutritionCare.type_of_tube ? '1' : '0'}"
                                data-constipation="${nutritionCare.constipation ? '1' : '0'}"
                                data-nutrition-is-provided="${nutritionCare.nutrition_is_provided ? '1' : '0'}"
                                data-mouth-hygiene="${nutritionCare.mouth_hygiene ? '1' : '0'}"
                                data-oral-nutrition-advices="${nutritionCare.oral_nutrition_advices ? '1' : '0'}"
                                data-voice-exercise="${nutritionCare.voice_exercise ? '1' : '0'}"
                                data-swallowing-exercise="${nutritionCare.swallowing_exercise ? '1' : '0'}"
                                data-aspiration-prevention-proceeded="${nutritionCare.aspiration_prevention_proceeded ? '1' : '0'}"
                                data-nutrition-care-full-note="${nutritionCare.nutrition_care_full_note || ''}"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-nutrition-care-btn" 
                                data-nutrition-care-id="${nutritionCare.id}" 
                                title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
        });
        
        tableHtml += `
                    </tbody>
                </table>
            </div>`;
        
        return tableHtml;
    }

    /**
     * Generate empty state HTML
     */
    generateEmptyStateHtml() {
        return `
            <div class="text-center py-4">
                <div class="mb-3">
                    <i class="bx bx-food-menu bx-lg text-muted"></i>
                </div>
                <h5 class="text-muted">No nutrition care records found</h5>
                <p class="text-muted">Add your first nutrition care record</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNutritionCareModal">
                    <i class="bx bx-plus"></i> Create Nutrition Care
                </button>
            </div>`;
    }

    /**
     * Build observations list
     */
    buildObservationsList(nutritionCare) {
        const observations = [];
        const observationFields = [
            'cough', 'sound', 'fluid_swallowing_ability', 'weight', 
            'amount_and_type_of_nutrition', 'diarrhea', 'heart_failure_and_kidney_disease', 
            'remaining_materials', 'type_of_tube'
        ];
        
        observationFields.forEach(field => {
            if (nutritionCare[field]) {
                observations.push(this.formatFieldName(field));
            }
        });
        
        return observations.length > 0 ? observations.join(', ') : '-';
    }

    /**
     * Build interventions list
     */
    buildInterventionsList(nutritionCare) {
        const interventions = [];
        const interventionFields = [
            'constipation', 'nutrition_is_provided', 'mouth_hygiene', 
            'oral_nutrition_advices', 'voice_exercise', 'swallowing_exercise', 
            'aspiration_prevention_proceeded'
        ];
        
        interventionFields.forEach(field => {
            if (nutritionCare[field]) {
                interventions.push(this.formatFieldName(field));
            }
        });
        
        return interventions.length > 0 ? interventions.join(', ') : '-';
    }

    /**
     * Format field name for display
     */
    formatFieldName(field) {
        return field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    /**
     * Truncate text
     */
    truncateText(text, maxLength) {
        if (!text) return '-';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    /**
     * Format date and time
     */
    formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-CA') + ' ' + date.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'});
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
        this.bindCreateForm();
        this.bindEditForm();
    }

    /**
     * Bind event handlers for dynamically loaded content
     */
    bindEventHandlers() {
        this.bindViewButton();
        this.bindEditButton();
        this.bindDeleteButton();
    }

    /**
     * Bind view button
     */
    bindViewButton() {
        $('.view-nutrition-care-btn').off('click').on('click', (e) => {
            const nutritionCareId = $(e.currentTarget).data('nutrition-care-id');
            this.showViewModal(nutritionCareId);
        });
    }

    /**
     * Bind edit button
     */
    bindEditButton() {
        $('.edit-nutrition-care-btn').off('click').on('click', (e) => {
            const $btn = $(e.currentTarget);
            const nutritionCareId = $btn.data('nutrition-care-id');
            this.showEditModal(nutritionCareId, $btn);
        });
    }

    /**
     * Bind delete button
     */
    bindDeleteButton() {
        $('.delete-nutrition-care-btn').off('click').on('click', (e) => {
            const nutritionCareId = $(e.currentTarget).data('nutrition-care-id');
            this.deleteNutritionCare(nutritionCareId);
        });
    }

    /**
     * Show view modal
     */
    showViewModal(nutritionCareId) {
        $('#viewNutritionCareModal').modal('show');
        this.showLoadingState('#view-nutrition-care-loading', '#view-nutrition-care-content', '#view-nutrition-care-error');
        
        $.ajax({
            url: this.routes.show.replace(':id', nutritionCareId),
            method: 'GET',
            success: (response) => {
                this.populateViewModal(response.data);
                this.hideLoadingState('#view-nutrition-care-loading', '#view-nutrition-care-content');
            },
            error: (xhr) => {
                this.hideLoadingState('#view-nutrition-care-loading');
                this.showErrorState('#view-nutrition-care-error');
            }
        });
    }

    /**
     * Show edit modal
     */
    showEditModal(nutritionCareId, $btn) {
        const observations = this.extractObservations($btn);
        const interventions = this.extractInterventions($btn);
        const nutritionCareFullNote = $btn.data('nutrition-care-full-note');

        // Set the form action URL
        $('#editNutritionCareForm').attr('action', this.routes.update.replace(':id', nutritionCareId));

        // Populate form fields (patient name and nurse are auto-filled)
        $('#edit_nutrition_care_full_note').val(nutritionCareFullNote);

        // Set checkboxes
        this.setCheckboxes(observations, 'edit_');
        this.setCheckboxes(interventions, 'edit_');

        $('#editNutritionCareModal').modal('show');
    }

    /**
     * Extract observations from button data
     */
    extractObservations($btn) {
        return {
            cough: $btn.data('cough'),
            sound: $btn.data('sound'),
            fluid_swallowing_ability: $btn.data('fluid-swallowing-ability'),
            weight: $btn.data('weight'),
            amount_and_type_of_nutrition: $btn.data('amount-and-type-of-nutrition'),
            diarrhea: $btn.data('diarrhea'),
            heart_failure_and_kidney_disease: $btn.data('heart-failure-and-kidney-disease'),
            remaining_materials: $btn.data('remaining-materials'),
            type_of_tube: $btn.data('type-of-tube')
        };
    }

    /**
     * Extract interventions from button data
     */
    extractInterventions($btn) {
        return {
            constipation: $btn.data('constipation'),
            nutrition_is_provided: $btn.data('nutrition-is-provided'),
            mouth_hygiene: $btn.data('mouth-hygiene'),
            oral_nutrition_advices: $btn.data('oral-nutrition-advices'),
            voice_exercise: $btn.data('voice-exercise'),
            swallowing_exercise: $btn.data('swallowing-exercise'),
            aspiration_prevention_proceeded: $btn.data('aspiration-prevention-proceeded')
        };
    }

    /**
     * Set checkboxes based on data
     */
    setCheckboxes(data, prefix = '') {
        Object.keys(data).forEach(key => {
            $(`#${prefix}${key}`).prop('checked', data[key] === '1');
        });
    }

    /**
     * Populate view modal
     */
    populateViewModal(nutritionCare) {
        $('#view-patient-name').text(nutritionCare.patient_name || 'N/A');
        $('#view-nurse-name').text(nutritionCare.nurse ? nutritionCare.nurse.full_name : 'N/A');
        $('#view-nutrition-care-full-note').text(nutritionCare.nutrition_care_full_note || 'N/A');
        $('#view-created-at').text(new Date(nutritionCare.created_at).toLocaleString());
        $('#view-created-by').text(nutritionCare.created_by ? nutritionCare.created_by.name : 'System');

        this.populateObservations(nutritionCare);
        this.populateInterventions(nutritionCare);
    }

    /**
     * Populate observations in view modal
     */
    populateObservations(nutritionCare) {
        const observationsContainer = $('#view-observations');
        const observationsList = [];
        const observationFields = [
            'cough', 'sound', 'fluid_swallowing_ability', 'weight', 
            'amount_and_type_of_nutrition', 'diarrhea', 'heart_failure_and_kidney_disease', 
            'remaining_materials', 'type_of_tube'
        ];
        
        observationFields.forEach(field => {
            if (nutritionCare[field]) {
                observationsList.push(this.formatFieldName(field));
            }
        });
        
        if (observationsList.length > 0) {
            observationsContainer.html('<p>' + observationsList.join(', ') + '</p>');
        } else {
            observationsContainer.html('<p class="text-muted">No observations</p>');
        }
    }

    /**
     * Populate interventions in view modal
     */
    populateInterventions(nutritionCare) {
        const interventionsContainer = $('#view-interventions');
        const interventionsList = [];
        const interventionFields = [
            'constipation', 'nutrition_is_provided', 'mouth_hygiene', 
            'oral_nutrition_advices', 'voice_exercise', 'swallowing_exercise', 
            'aspiration_prevention_proceeded'
        ];
        
        interventionFields.forEach(field => {
            if (nutritionCare[field]) {
                interventionsList.push(this.formatFieldName(field));
            }
        });
        
        if (interventionsList.length > 0) {
            interventionsContainer.html('<p>' + interventionsList.join(', ') + '</p>');
        } else {
            interventionsContainer.html('<p class="text-muted">No interventions</p>');
        }
    }

    /**
     * Delete nutrition care
     */
    deleteNutritionCare(nutritionCareId) {
        if (confirm('Are you sure you want to delete this nutrition care record?')) {
            $.ajax({
                url: this.routes.destroy.replace(':id', nutritionCareId),
                method: 'DELETE',
                data: {
                    _token: this.csrfToken
                },
                success: () => {
                    this.loadData();
                    this.showSuccessMessage('Nutrition care deleted successfully');
                },
                error: (xhr) => {
                    this.handleError(xhr, 'Error deleting nutrition care');
                }
            });
        }
    }

    /**
     * Bind create form
     */
    bindCreateForm() {
        $('#createNutritionCareForm').on('submit', (e) => {
            e.preventDefault();
            this.submitForm('#createNutritionCareForm', 'POST', 'Nutrition care created successfully');
        });
    }

    /**
     * Bind edit form
     */
    bindEditForm() {
        $('#editNutritionCareForm').on('submit', (e) => {
            e.preventDefault();
            this.submitForm('#editNutritionCareForm', 'PUT', 'Nutrition care updated successfully');
        });
    }

    /**
     * Submit form
     */
    submitForm(formSelector, method, successMessage) {
        const form = $(formSelector);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        
        this.setButtonLoading(submitBtn, true, 'Saving...');
        
        $.ajax({
            url: form.attr('action'),
            method: method,
            data: form.serialize(),
            success: () => {
                $(formSelector.replace('#', '#') + 'Modal').modal('hide');
                this.loadData();
                this.showSuccessMessage(successMessage);
            },
            error: (xhr) => {
                this.handleFormError(xhr);
            },
            complete: () => {
                this.setButtonLoading(submitBtn, false, originalText);
            }
        });
    }

    /**
     * Set button loading state
     */
    setButtonLoading(button, loading, text) {
        button.prop('disabled', loading).text(text);
    }

    /**
     * Show loading state
     */
    showLoadingState(loadingSelector, contentSelector, errorSelector) {
        $(loadingSelector).show();
        if (contentSelector) $(contentSelector).hide();
        if (errorSelector) $(errorSelector).hide();
    }

    /**
     * Hide loading state
     */
    hideLoadingState(loadingSelector, contentSelector) {
        $(loadingSelector).hide();
        if (contentSelector) $(contentSelector).show();
    }

    /**
     * Show error state
     */
    showErrorState(errorSelector) {
        $(errorSelector).show();
    }

    /**
     * Handle error
     */
    handleError(xhr, defaultMessage) {
        console.error('Error:', xhr);
        const errorMessage = xhr.responseJSON?.message || defaultMessage;
        this.showErrorMessage(errorMessage);
    }

    /**
     * Handle form error
     */
    handleFormError(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        let errorMessage = 'An error occurred';
        
        if (Object.keys(errors).length > 0) {
            errorMessage = Object.values(errors).flat().join('\n');
        }
        
        this.showErrorMessage(errorMessage);
    }

    /**
     * Show success message
     */
    showSuccessMessage(message) {
        if (typeof showToast === 'function') {
            showToast('success', message);
        } else {
            alert(message);
        }
    }

    /**
     * Show error message
     */
    showErrorMessage(message) {
        if (typeof showToast === 'function') {
            showToast('error', message);
        } else {
            alert(message);
        }
    }
}

// Initialize when document is ready
$(document).ready(function() {
    // Configuration will be passed from the Blade template
    if (typeof window.nutritionCareConfig !== 'undefined') {
        window.nutritionCareManager = new NutritionCareManager(window.nutritionCareConfig);
    }
});
