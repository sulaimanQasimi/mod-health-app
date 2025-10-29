import { createApp } from 'vue'
import PrescriptionIndexPage from './components/PrescriptionIndexPage.vue'
import Multiselect from 'vue-multiselect'
import VuePersianDatetimePicker from 'vue-persian-datetime-picker'

// Global localization function
window.localize = function(key) {
  const container = document.getElementById('prescription-index-app')
  if (container) {
    const data = container.dataset.localize
    const translations = JSON.parse(data || '{}')
    return translations[key] || key
  }
  return key
}

// Mount the app
document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('prescription-index-app')
  if (container) {
    // Get data from container attributes
    const permissions = JSON.parse(container.dataset.permissions || '{}')
    const localize = JSON.parse(container.dataset.localize || '{}')
    const branchId = parseInt(container.dataset.branchId) || null

    // Create Vue app with props
    const app = createApp(PrescriptionIndexPage, {
      permissions: permissions,
      localize: localize,
      branchId: branchId
    })

    // Register global components
    app.component('multiselect', Multiselect)
    app.component('date-picker', VuePersianDatetimePicker)

    // Mount the app
    app.mount('#prescription-index-app')
  }
})
