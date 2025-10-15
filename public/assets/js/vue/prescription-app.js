import { createApp } from 'vue';
import HospitalizationPrescriptionSection from './components/HospitalizationPrescriptionSection.vue';

console.log('Prescription app: Script loaded');

let retryCount = 0;
const maxRetries = 20; // Maximum 10 seconds of retries

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
        
        if (retryCount < maxRetries) {
            retryCount++;
            console.log(`Prescription app: Retry ${retryCount}/${maxRetries}`);
            // Retry after a longer delay to allow jQuery ready to complete
            setTimeout(initializePrescriptionApp, 500);
        } else {
            console.error('Prescription app: Max retries reached. Failed to initialize.');
        }
    }
}

// Try to initialize immediately
initializePrescriptionApp();

// Also try on DOM ready
document.addEventListener('DOMContentLoaded', initializePrescriptionApp);