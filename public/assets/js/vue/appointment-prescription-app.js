import { createApp } from 'vue'
import PrescriptionSection from './components/PrescriptionSection.vue'
import 'vue-multiselect/dist/vue-multiselect.css'

// Initialize Vue app for prescription section
document.addEventListener('DOMContentLoaded', function() {
    // Check for appointment prescription container
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
    
    // Check for ICU prescription container
    const icuPrescriptionContainer = document.getElementById('icu-prescription-section-container');
    if (icuPrescriptionContainer) {
        const icu = JSON.parse(icuPrescriptionContainer.dataset.icu || '{}');
        const permissions = JSON.parse(icuPrescriptionContainer.dataset.permissions || '{}');
        
        const app = createApp(PrescriptionSection, {
            icu: icu,
            canAddPrescription: permissions.canAddPrescription || false,
            canEditPrescription: permissions.canEditPrescription || false,
            canDeletePrescription: permissions.canDeletePrescription || false,
            appointmentCompleted: icu.is_discharged == 1
        });
        
        app.mount('#icu-prescription-section-container');
    }
});
