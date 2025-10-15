import { createApp } from 'vue';
import AdviceSection from './components/AdviceSection.vue';

console.log('Advice app: Script loaded');

let retryCount = 0;
const maxRetries = 20; // Maximum 10 seconds of retries

function initializeAdviceApp() {
    console.log('Advice app: Attempting to initialize');
    console.log('Advice app: window.hospitalizationData', window.hospitalizationData);

    const adviceSection = document.getElementById('advice-section');
    console.log('Advice app: advice-section element', adviceSection);

    if (adviceSection && window.hospitalizationData) {
        console.log('Advice app: Initializing Vue app');
        const app = createApp({
            data() {
                return {
                    hospitalization: window.hospitalizationData
                }
            },
            template: '<advice-section :appointment="hospitalization" :canAddAdvice="true" :appointmentCompleted="hospitalization.is_discharged"></advice-section>'
        });

        app.component('advice-section', AdviceSection);
        app.mount('#advice-section');
        console.log('Advice app: Vue app mounted');
    } else {
        console.log('Advice app: Missing requirements - adviceSection:', !!adviceSection, 'hospitalizationData:', !!window.hospitalizationData);
        
        if (retryCount < maxRetries) {
            retryCount++;
            console.log(`Advice app: Retry ${retryCount}/${maxRetries}`);
            setTimeout(initializeAdviceApp, 500);
        } else {
            console.error('Advice app: Max retries reached. Failed to initialize.');
        }
    }
}

initializeAdviceApp();
document.addEventListener('DOMContentLoaded', initializeAdviceApp);