import { createApp } from 'vue'
import ConsultationSection from './components/ConsultationSection.vue'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import Swal from 'sweetalert2'

// Make Swal available globally
window.Swal = Swal

// Mount the consultation section
document.addEventListener('DOMContentLoaded', function() {
    const consultationContainer = document.getElementById('consultation-section-container')
    if (consultationContainer) {
        const appointment = JSON.parse(consultationContainer.dataset.appointment || '{}')
        const permissions = JSON.parse(consultationContainer.dataset.permissions || '{}')
        
        const consultationApp = createApp(ConsultationSection, {
            entity: appointment,
            entityType: 'appointment',
            canAddConsultation: permissions.canAddConsultation || false,
            canEditConsultation: permissions.canEditConsultation || false,
            canDeleteConsultation: permissions.canDeleteConsultation || false,
            entityCompleted: appointment.is_completed == 1
        })
        
        consultationApp.component('Multiselect', Multiselect)
        consultationApp.mount('#consultation-section-container')
    }
})
