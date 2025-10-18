import { createApp } from 'vue'
import ConsultationSection from './components/ConsultationSection.vue'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import Swal from 'sweetalert2'

// Make Swal available globally
window.Swal = Swal

document.addEventListener('DOMContentLoaded', function() {
    function initializeVueApp() {
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
            return true;
        } else {
            return false;
        }
    }
    
    // Try to initialize immediately
    if (!initializeVueApp()) {
        // If container not found, wait for accordion to be opened
        const accordionButton = document.querySelector('[data-bs-target="#consultationsCollapse"]');
        if (accordionButton) {
            accordionButton.addEventListener('click', function() {
                setTimeout(() => {
                    if (!document.querySelector('#icu-consultation-section-container .consultation-section')) {
                        initializeVueApp();
                    }
                }, 300); // Wait for accordion animation
            });
        }
    }
})
