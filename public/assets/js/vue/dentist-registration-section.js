import { createApp } from 'vue'
import DentistRegistrationSection from './components/DentistRegistrationSection.vue'

// Import SweetAlert2
import Swal from 'sweetalert2'

// Make Swal globally available
window.Swal = Swal

document.addEventListener('DOMContentLoaded', function() {
    console.log("Dentist Registration Section: DOM loaded, initializing Vue app");
    
    const container = document.getElementById('dentist-registration-section-container');
    
    if (container) {
        console.log("Dentist Registration Section: Container found, mounting Vue app");
        
        // Get data attributes
        const entity = JSON.parse(container.dataset.entity || '{}');
        const entityType = container.dataset.entityType || 'appointment';
        const entityId = container.dataset.entityId || null;
        const permissions = JSON.parse(container.dataset.permissions || '{}');
        const appointmentCompleted = container.dataset.appointmentCompleted === 'true';
        
        console.log("Dentist Registration Section: Data attributes:", {
            entity,
            entityType,
            entityId,
            permissions,
            appointmentCompleted
        });
        
        // Create Vue app
        const app = createApp(DentistRegistrationSection, {
            appointment: entity,
            entityType: entityType,
            entityId: entityId,
            canAddDentistRegistration: permissions.canAddDentistRegistration || false,
            appointmentCompleted: appointmentCompleted
        });
        
        // Mount the app
        app.mount('#dentist-registration-section-container');
        
        console.log("Dentist Registration Section: Vue app mounted successfully");
    } else {
        console.error("Dentist Registration Section: Container not found");
    }
});
