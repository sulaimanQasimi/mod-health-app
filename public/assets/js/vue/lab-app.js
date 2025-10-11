import { createApp } from 'vue';
import LabSection from './components/LabSection.vue';

// Make Vue and createApp available globally for debugging
window.Vue = { createApp };
window.createApp = createApp;

// Create Vue app for lab section
const createLabApp = (appointment, permissions) => {
    const app = createApp({
        components: {
            LabSection
        },
        template: '<LabSection :appointment="appointment" :canAddLab="canAddLab" :canEditLab="canEditLab" :canDeleteLab="canDeleteLab" :appointmentCompleted="appointmentCompleted" />',
        data() {
            return {
                appointment: appointment,
                canAddLab: permissions.canAddLab || false,
                canEditLab: permissions.canEditLab || false,
                canDeleteLab: permissions.canDeleteLab || false,
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

// Auto-initialize if lab section container exists
document.addEventListener('DOMContentLoaded', function() {
    const labContainer = document.getElementById('lab-section-container');
    if (labContainer) {
        try {
            const appointmentData = JSON.parse(labContainer.dataset.appointment || '{}');
            const permissions = JSON.parse(labContainer.dataset.permissions || '{}');
            
            console.log('Initializing Vue Lab App with:', { appointmentData, permissions });
            
            const app = createLabApp(appointmentData, permissions);
            app.mount('#lab-section-container');
            
            console.log('Vue Lab App mounted successfully');
        } catch (error) {
            console.error('Error initializing Vue Lab App:', error);
            // Show fallback content if Vue fails
            showFallbackContent();
        }
    } else {
        console.warn('Lab section container not found');
    }
});

// Make createApp available globally for debugging
window.createApp = createApp;

// Fallback function to show basic lab section if Vue fails
function showFallbackContent() {
    const container = document.getElementById('lab-section-container');
    if (container) {
        container.innerHTML = `
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-body-secondary text-body d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bx bx-hard-hat me-2 text-warning"></i>
                                معاینات
                            </h5>
                            <button type="button" class="btn btn-primary btn-sm" onclick="alert('Vue component failed to load')">
                                <i class="bx bx-plus me-1"></i>
                                اضافه کردن
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="bx bx-info-circle me-2"></i>
                                Vue component failed to load. Please refresh the page or contact support.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// Export for manual initialization
window.createLabApp = createLabApp;
