import { createApp } from 'vue'
import PrescriptionSection from './components/PrescriptionSection.vue'

// Initialize Vue app for prescription section
document.addEventListener('DOMContentLoaded', function() {
    const prescriptionContainer = document.getElementById('prescription-section-container');
    
    if (prescriptionContainer) {
        const appointment = JSON.parse(prescriptionContainer.dataset.appointment || '{}');
        const permissions = JSON.parse(prescriptionContainer.dataset.permissions || '{}');
        
        const app = createApp(PrescriptionSection, {
            appointment: appointment,
            canAddPrescription: permissions.canAddPrescription || false,
            canEditPrescription: permissions.canEditPrescription || false,
            canDeletePrescription: permissions.canDeletePrescription || false,
            appointmentCompleted: appointment.is_completed == 1
        });
        
        app.mount('#prescription-section-container');
    }
});
