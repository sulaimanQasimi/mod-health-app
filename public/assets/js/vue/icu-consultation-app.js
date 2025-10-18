import { createApp } from 'vue'
import ConsultationSection from './components/ConsultationSection.vue'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import Swal from 'sweetalert2'

// Make Swal available globally
window.Swal = Swal

// Mount the consultation section
document.addEventListener('DOMContentLoaded', function() {
    const consultationContainer = document.getElementById('icu-consultation-section-container')
    if (consultationContainer) {
        const icu = JSON.parse(consultationContainer.dataset.icu || '{}')
        const permissions = JSON.parse(consultationContainer.dataset.permissions || '{}')
        
        const consultationApp = createApp(ConsultationSection, {
            entity: icu,
            entityType: 'icu',
            canAddConsultation: permissions.canAddConsultation || false,
            canEditConsultation: permissions.canEditConsultation || false,
            canDeleteConsultation: permissions.canDeleteConsultation || false,
            entityCompleted: icu.is_discharged == 1
        })
        
        consultationApp.component('Multiselect', Multiselect)
        consultationApp.mount('#icu-consultation-section-container')
    }
})
