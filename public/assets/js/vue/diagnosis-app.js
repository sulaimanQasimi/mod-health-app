import { createApp } from 'vue'
import DiagnosisSection from './components/DiagnosisSection.vue'
import 'vue-multiselect/dist/vue-multiselect.css'

// Initialize Vue app for diagnosis section
document.addEventListener('DOMContentLoaded', function() {
    const diagnosisContainer = document.getElementById('diagnosis-section-container');
    
    if (diagnosisContainer) {
        const appointment = JSON.parse(diagnosisContainer.dataset.appointment || '{}');
        const permissions = JSON.parse(diagnosisContainer.dataset.permissions || '{}');
        
        const app = createApp(DiagnosisSection, {
            appointment: appointment,
            canAddDiagnosis: permissions.canAddDiagnosis || false,
            canEditDiagnosis: permissions.canEditDiagnosis || false,
            canDeleteDiagnosis: permissions.canDeleteDiagnosis || false,
            appointmentCompleted: appointment.is_completed == 1
        });
        
        app.mount('#diagnosis-section-container');
    }
});
