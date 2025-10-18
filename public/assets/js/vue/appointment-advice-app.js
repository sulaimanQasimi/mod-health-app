import { createApp } from 'vue'
import AdviceSection from './components/AdviceSection.vue'
import 'vue-multiselect/dist/vue-multiselect.css'

// Initialize Vue app for advice section
document.addEventListener('DOMContentLoaded', function() {
    const adviceContainer = document.getElementById('advice-section-container');
    
    if (adviceContainer) {
        const appointment = JSON.parse(adviceContainer.dataset.appointment || '{}');
        const permissions = JSON.parse(adviceContainer.dataset.permissions || '{}');
        
        const app = createApp(AdviceSection, {
            appointment: appointment,
            canAddAdvice: permissions.canAddAdvice || false,
            canEditAdvice: permissions.canEditAdvice || false,
            canDeleteAdvice: permissions.canDeleteAdvice || false,
            appointmentCompleted: appointment.is_completed == 1
        });
        
        app.mount('#advice-section-container');
    }
});
