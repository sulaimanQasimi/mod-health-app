import { createApp } from 'vue'
import DentalChart from './components/DentalChart.vue'

// Initialize Vue app for dental chart
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('dental-chart-vue-container');
    
    if (container) {
        const dentistRegistrationId = parseInt(container.dataset.dentistRegistrationId || '0');
        const teethDataRaw = container.dataset.teethData || '{}';
        let teethData = {};
        
        try {
            teethData = JSON.parse(teethDataRaw);
        } catch (e) {
            console.error('Error parsing teeth data:', e);
        }
        
        // Teeth data is already keyed by tooth_number from the backend
        const teethDataKeyed = teethData || {};
        
        const app = createApp(DentalChart, {
            teethData: teethDataKeyed,
            dentistRegistrationId: dentistRegistrationId
        });
        
        // Handle tooth click event
        app.config.globalProperties.$toothClickHandler = function(toothNumber) {
            // Find if chart exists for this tooth
            const toothData = teethDataKeyed[toothNumber];
            const chartId = toothData ? toothData.id : null;
            
            // Open modal or navigate to edit/create
            openToothModal(toothNumber, chartId);
        };
        
        app.mount('#dental-chart-vue-container');
    }
});

// Global function for opening tooth modal (can be called from Vue component)
function openToothModal(toothNumber, chartId) {
    const modalBody = document.getElementById('toothModalBody');
    if (!modalBody) {
        // Create modal if it doesn't exist
        createToothModal();
    }
    
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('toothModal'));
    const modalTitle = document.getElementById('modalToothNumber');
    
    if (modalTitle) {
        modalTitle.textContent = toothNumber;
    }
    
    if (chartId) {
        // Load existing chart data
        modalBody.innerHTML = `
            <div class="text-center mb-3">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        // Load chart details via AJAX or redirect
        window.location.href = `/dental-charts/edit/${chartId}`;
    } else {
        // Show create form
        const dentistRegistrationId = document.getElementById('dental-chart-vue-container')?.dataset.dentistRegistrationId || '';
        modalBody.innerHTML = `
            <form action="/dental-charts/store/${dentistRegistrationId}" method="POST">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                <input type="hidden" name="tooth_number" value="${toothNumber}">
                <input type="hidden" name="chart_date" value="${new Date().toISOString().split('T')[0]}">
                <div class="mb-3">
                    <label class="form-label">${window.localize ? window.localize('global.tooth_condition') : 'Tooth Condition'}</label>
                    <select name="tooth_condition" class="form-select" required>
                        <option value="healthy">${window.localize ? window.localize('global.healthy') : 'Healthy'}</option>
                        <option value="cavity">${window.localize ? window.localize('global.cavity') : 'Cavity'}</option>
                        <option value="filling">${window.localize ? window.localize('global.filling') : 'Filling'}</option>
                        <option value="crown">${window.localize ? window.localize('global.crown') : 'Crown'}</option>
                        <option value="missing">${window.localize ? window.localize('global.missing') : 'Missing'}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">${window.localize ? window.localize('global.notes') : 'Notes'}</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">${window.localize ? window.localize('global.save') : 'Save'}</button>
            </form>
        `;
    }
    
    modal.show();
}

function createToothModal() {
    const modalHTML = `
        <div class="modal fade" id="toothModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${window.localize ? window.localize('global.tooth') : 'Tooth'} <span id="modalToothNumber"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

// Make function globally available
window.openToothModal = openToothModal;
