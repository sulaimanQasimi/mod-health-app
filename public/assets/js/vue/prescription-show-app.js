import { createApp } from 'vue'
import PrescriptionShowApp from './components/PrescriptionShowApp.vue'
import 'vue-multiselect/dist/vue-multiselect.css'

// Get prescription ID from data attribute
const prescriptionId = document.getElementById('prescription-show-app').dataset.prescriptionId
console.log('Prescription ID from data attribute:', prescriptionId)

// Create Vue app instance with props
const app = createApp(PrescriptionShowApp, {
    prescriptionId: prescriptionId
})

// Global properties for localization (if needed)
// Note: Using window.localize function from show.blade.php instead

// Mount the app
app.mount('#prescription-show-app')
