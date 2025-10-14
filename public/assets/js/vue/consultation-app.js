import { createApp } from 'vue'
import ConsultationSection from './components/ConsultationSection.vue'
import Multiselect from 'vue-multiselect'
import moment from 'moment-jalaali'

// Make moment-jalaali available globally
window.moment = moment

// Register global components
const app = createApp({})

app.component('ConsultationSection', ConsultationSection)
app.component('Multiselect', Multiselect)

// Mount the consultation section
document.addEventListener('DOMContentLoaded', function() {
    const consultationContainer = document.getElementById('consultation-section-container')
    if (consultationContainer) {
        const appointment = JSON.parse(consultationContainer.dataset.appointment || '{}')
        const permissions = JSON.parse(consultationContainer.dataset.permissions || '{}')
        
        const consultationApp = createApp(ConsultationSection, {
            appointment: appointment,
            canAddConsultation: permissions.canAddConsultation || false,
            canEditConsultation: permissions.canEditConsultation || false,
            canDeleteConsultation: permissions.canDeleteConsultation || false,
            appointmentCompleted: appointment.is_completed == 1
        })
        
        consultationApp.component('Multiselect', Multiselect)
        consultationApp.mount('#consultation-section-container')
    }
})
