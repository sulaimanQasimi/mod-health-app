import { createApp } from 'vue'
import LabTestRegistrationSection from './components/LabTestRegistrationSection.vue'
import Multiselect from 'vue-multiselect'

// Import SweetAlert2
import Swal from 'sweetalert2'

// Make Swal globally available
window.Swal = Swal

// Import CSS
import 'vue-multiselect/dist/vue-multiselect.css'

document.addEventListener('DOMContentLoaded', function() {
    console.log("Lab Test Registration Section: DOM loaded, initializing Vue app");
    
    const container = document.getElementById('lab-test-registration-section-container');
    
    if (container) {
        console.log("Lab Test Registration Section: Container found, mounting Vue app");
        
        // Get data attributes
        const entity = JSON.parse(container.dataset.entity || '{}');
        const entityType = container.dataset.entityType || 'appointment';
        const entityId = container.dataset.entityId || null;
        const permissions = JSON.parse(container.dataset.permissions || '{}');
        const appointmentCompleted = container.dataset.appointmentCompleted === 'true';
        
        console.log("Lab Test Registration Section: Data attributes:", {
            entity,
            entityType,
            entityId,
            permissions,
            appointmentCompleted
        });
        
        // Create Vue app
        const app = createApp(LabTestRegistrationSection, {
            appointment: entity,
            entityType: entityType,
            entityId: entityId,
            canAddTestRegistration: permissions.canAddTestRegistration || false,
            appointmentCompleted: appointmentCompleted
        });
        
        // Register Multiselect component globally
        app.component('multiselect', Multiselect);
        
        // Mount the app
        app.mount('#lab-test-registration-section-container');
        
        console.log("Lab Test Registration Section: Vue app mounted successfully");
    } else {
        console.error("Lab Test Registration Section: Container not found");
    }
});
