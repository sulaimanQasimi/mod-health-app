import { createApp } from 'vue';
import PrescriptionSection from './components/PrescriptionSection.vue';

// Make Vue and createApp available globally
window.Vue = { createApp };
window.createApp = createApp;

// Create Vue app for prescription section
const createPrescriptionApp = (appointment, permissions) => {
    const app = createApp({
        components: {
            PrescriptionSection
        },
        template: '<PrescriptionSection :appointment="appointment" :canAddPrescription="canAddPrescription" :canEditPrescription="canEditPrescription" :canDeletePrescription="canDeletePrescription" :appointmentCompleted="appointmentCompleted" />',
        data() {
            return {
                appointment: appointment,
                canAddPrescription: permissions.canAddPrescription || false,
                canEditPrescription: permissions.canEditPrescription || false,
                canDeletePrescription: permissions.canDeletePrescription || false,
                appointmentCompleted: appointment.is_completed == 1
            }
        },
        methods: {
            // Global methods for localization and notifications
            localize(key) {
                // This should be connected to your localization system
                return window.localize ? window.localize(key) : key;
            }
        }
    });

    return app;
};

// Auto-initialize if prescription section container exists
document.addEventListener('DOMContentLoaded', function() {
    const prescriptionContainer = document.getElementById('prescription-section-container');
    if (prescriptionContainer) {
        try {
            const appointmentData = JSON.parse(prescriptionContainer.dataset.appointment || '{}');
            const permissions = JSON.parse(prescriptionContainer.dataset.permissions || '{}');
            
            const app = createPrescriptionApp(appointmentData, permissions);
            app.mount('#prescription-section-container');
        } catch (error) {
            console.error('Error initializing prescription Vue app:', error);
            // Show fallback content if Vue fails
            showFallbackContent();
        }
    }
});

// Fallback content function
function showFallbackContent() {
    const container = document.getElementById('prescription-section-container');
    if (container) {
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="alert alert-warning">
                    <i class="bx bx-error-circle me-2"></i>
                    Failed to load prescription section. Please refresh the page.
                </div>
            </div>
        `;
    }
}

// Make createApp available globally
window.createApp = createApp;
