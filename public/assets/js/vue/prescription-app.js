import { createApp } from 'vue';
import HospitalizationPrescriptionSection from './components/HospitalizationPrescriptionSection.vue';

console.log('Prescription app: Script loaded');

// Function to initialize the Vue app
function initializePrescriptionApp() {
    console.log('Prescription app: Attempting to initialize');
    console.log('Prescription app: window.hospitalizationData', window.hospitalizationData);

    // Check if the prescription-section element exists and data is available
    const prescriptionSection = document.getElementById('prescription-section');
    console.log('Prescription app: prescription-section element', prescriptionSection);

    if (prescriptionSection && window.hospitalizationData) {
        console.log('Prescription app: Initializing Vue app');
        const app = createApp({
            data() {
                return {
                    hospitalization: window.hospitalizationData
                }
            },
            template: '<hospitalization-prescription-section :hospitalization="hospitalization"></hospitalization-prescription-section>'
        });

        app.component('hospitalization-prescription-section', HospitalizationPrescriptionSection);
        app.mount('#prescription-section');
        console.log('Prescription app: Vue app mounted');
    } else {
        console.log('Prescription app: Missing requirements - prescriptionSection:', !!prescriptionSection, 'hospitalizationData:', !!window.hospitalizationData);
        // Retry after a short delay
        setTimeout(initializePrescriptionApp, 100);
    }
}

// Try to initialize immediately
initializePrescriptionApp();

// Also try on DOM ready
document.addEventListener('DOMContentLoaded', initializePrescriptionApp);