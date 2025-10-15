import { createApp } from 'vue';
import VisitSection from './components/VisitSection.vue';

console.log('Visit app: Script loaded');

// Function to initialize the Vue app
function initializeVisitApp() {
    console.log('Visit app: Attempting to initialize');
    console.log('Visit app: window.hospitalizationData', window.hospitalizationData);
    
    // Check if the visit-section element exists and data is available
    const visitSection = document.getElementById('visit-section');
    console.log('Visit app: visit-section element', visitSection);
    
    if (visitSection && window.hospitalizationData) {
        console.log('Visit app: Initializing Vue app');
        const app = createApp({
            data() {
                return {
                    hospitalization: window.hospitalizationData
                }
            },
            template: '<visit-section :hospitalization="hospitalization"></visit-section>'
        });
        
        app.component('visit-section', VisitSection);
        app.mount('#visit-section');
        console.log('Visit app: Vue app mounted');
    } else {
        console.log('Visit app: Missing requirements - visitSection:', !!visitSection, 'hospitalizationData:', !!window.hospitalizationData);
        // Retry after a short delay
        setTimeout(initializeVisitApp, 100);
    }
}

// Try to initialize immediately
initializeVisitApp();

// Also try on DOM ready
document.addEventListener('DOMContentLoaded', initializeVisitApp);
