import { createApp } from 'vue';
import HospitalizationNursingNoteSection from './components/HospitalizationNursingNoteSection.vue';

console.log('Nursing Note app: Script loaded');

let retryCount = 0;
const maxRetries = 20; // Maximum 10 seconds of retries

function initializeNursingNoteApp() {
    console.log('Nursing Note app: Attempting to initialize');
    console.log('Nursing Note app: window.hospitalizationData', window.hospitalizationData);

    const nursingNoteSection = document.getElementById('nursing-note-section');
    console.log('Nursing Note app: nursing-note-section element', nursingNoteSection);

    if (nursingNoteSection && window.hospitalizationData) {
        console.log('Nursing Note app: Initializing Vue app');
        const app = createApp({
            data() {
                return {
                    hospitalization: window.hospitalizationData
                }
            },
            template: '<hospitalization-nursing-note-section :hospitalization="hospitalization" :canAddNote="true" :hospitalizationDischarged="hospitalization.is_discharged"></hospitalization-nursing-note-section>'
        });

        app.component('hospitalization-nursing-note-section', HospitalizationNursingNoteSection);
        app.mount('#nursing-note-section');
        console.log('Nursing Note app: Vue app mounted');
    } else {
        console.log('Nursing Note app: Missing requirements - nursingNoteSection:', !!nursingNoteSection, 'hospitalizationData:', !!window.hospitalizationData);
        
        if (retryCount < maxRetries) {
            retryCount++;
            console.log(`Nursing Note app: Retry ${retryCount}/${maxRetries}`);
            setTimeout(initializeNursingNoteApp, 500);
        } else {
            console.error('Nursing Note app: Max retries reached. Failed to initialize.');
        }
    }
}

initializeNursingNoteApp();
document.addEventListener('DOMContentLoaded', initializeNursingNoteApp);