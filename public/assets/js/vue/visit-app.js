import { createApp } from 'vue';
import VisitSection from './components/VisitSection.vue';
import 'vue-multiselect/dist/vue-multiselect.css';

// Function to initialize the Vue app
function initializeVisitApp(attempt = 0, maxAttempts = 100) {
    const visitSection = document.getElementById('visit-section');
    const hasData = window.hospitalizationData !== undefined;
    
    // Check if already mounted
    if (visitSection && visitSection.querySelector('[data-v-app]')) {
        return; // Already initialized
    }
    
    if (visitSection && hasData) {
        try {
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
        } catch (error) {
            console.error('Visit app: Error mounting Vue app:', error);
        }
    } else if (attempt < maxAttempts) {
        // Retry after a short delay if requirements not met
        setTimeout(() => initializeVisitApp(attempt + 1, maxAttempts), 100);
    }
}

// Wait for DOM and data to be ready
function startInitialization() {
    // Check if jQuery is available and wait for it
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function() {
            // Wait a bit more for hospitalizationData to be set
            setTimeout(() => initializeVisitApp(), 100);
        });
    } else {
        // If jQuery is not available, use DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => initializeVisitApp(), 200);
        });
    }
}

startInitialization();
