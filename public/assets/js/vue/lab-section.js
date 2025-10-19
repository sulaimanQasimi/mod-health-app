import { createApp } from 'vue'
import LabSection from './components/LabSection.vue'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import Swal from 'sweetalert2'

window.Swal = Swal

// Function to show fallback content
function showFallbackContent() {
    const loadingFallback = document.getElementById('lab-loading-fallback')
    const fallbackContent = document.getElementById('lab-fallback-content')
    
    if (loadingFallback) {
        loadingFallback.style.display = 'none'
    }
    if (fallbackContent) {
        fallbackContent.style.display = 'block'
    }
}

// Function to initialize the lab section
function initializeLabSection() {
    console.log('Lab Section: Initializing...')
    const labContainer = document.getElementById('lab-section-container')
    console.log('Lab Section: Container found:', labContainer)
    
    if (labContainer) {
        try {
            const entity = JSON.parse(labContainer.dataset.entity || '{}')
            const permissions = JSON.parse(labContainer.dataset.permissions || '{}')
            const entityType = labContainer.dataset.entityType || 'appointment'
            const entityId = labContainer.dataset.entityId || entity.id
            const appointmentCompleted = labContainer.dataset.appointmentCompleted === 'true'
            
            console.log('Lab Section: Entity:', entity)
            console.log('Lab Section: Permissions:', permissions)
            console.log('Lab Section: Entity Type:', entityType)
            console.log('Lab Section: Entity ID:', entityId)
            
            const labApp = createApp(LabSection, {
                appointment: entity,
                canAddLab: permissions.canAddLab || false,
                canEditLab: permissions.canEditLab || false,
                canDeleteLab: permissions.canDeleteLab || false,
                appointmentCompleted: appointmentCompleted,
                entityType: entityType,
                entityId: entityId
            })
            
            labApp.component('Multiselect', Multiselect)
            labApp.mount('#lab-section-container')
            console.log('Lab Section: Vue app mounted successfully')
            
            // Hide loading fallback
            const loadingFallback = document.getElementById('lab-loading-fallback')
            if (loadingFallback) {
                loadingFallback.style.display = 'none'
            }
            
        } catch (error) {
            console.error('Lab Section: Error mounting Vue app:', error)
            showFallbackContent()
        }
    } else {
        console.error('Lab Section: Container not found')
        showFallbackContent()
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Lab Section: DOM Content Loaded')
    
    // Set a timeout to show fallback if Vue doesn't load within 5 seconds
    const timeoutId = setTimeout(() => {
        console.warn('Lab Section: Timeout reached, showing fallback content')
        showFallbackContent()
    }, 5000)
    
    // Try to initialize immediately
    try {
        initializeLabSection()
        clearTimeout(timeoutId) // Clear timeout if successful
    } catch (error) {
        console.error('Lab Section: Initialization failed:', error)
        showFallbackContent()
    }
})

// Also try to initialize after a short delay in case DOM isn't fully ready
setTimeout(() => {
    const labContainer = document.getElementById('lab-section-container')
    if (labContainer && labContainer.innerHTML.includes('در حال بارگذاری')) {
        console.log('Lab Section: Retrying initialization after delay')
        initializeLabSection()
    }
}, 1000)
