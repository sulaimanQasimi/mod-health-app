import { createApp } from 'vue'
import PrescriptionApp from './components/PrescriptionApp.vue'
import router from './router/prescription-router.js'
import Multiselect from 'vue-multiselect'
import VuePersianDatetimePicker from 'vue-persian-datetime-picker'
import 'vue-multiselect/dist/vue-multiselect.css'

// Global translation store and helper
window.__translations = window.__translations || {}
window.localize = function(key) {
  if (window.__translations && key in window.__translations) {
    return window.__translations[key]
  }
  const container = document.getElementById('prescription-index-app')
  if (container) {
    const data = container.dataset.localize
    try {
      const translations = JSON.parse(data || '{}')
      Object.assign(window.__translations, translations)
      return window.__translations[key] || key
    } catch (e) {
      return key
    }
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
    // Merge once at boot so SPA routes have all keys available
    Object.assign(window.__translations, localize)
    const branchId = parseInt(container.dataset.branchId) || null

    // Create Vue app with router
    const app = createApp(PrescriptionApp, {
      permissions: permissions,
      localize: localize,
      branchId: branchId
    })

    // Use router
    app.use(router)

    // Register global components
    app.component('multiselect', Multiselect)
    app.component('date-picker', VuePersianDatetimePicker)

    // Mount the app
    app.mount('#prescription-index-app')
  }
})
