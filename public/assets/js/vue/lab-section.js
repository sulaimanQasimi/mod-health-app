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

// Store mounted app instances to prevent double mounting
const mountedLabApps = new WeakMap()

// Function to initialize the lab section
function initializeLabSection() {
    const labContainer = document.getElementById('lab-section-container')
    
    if (labContainer) {
        // Check if already mounted using WeakMap
        if (mountedLabApps.has(labContainer)) {
            return; // Already initialized
        }
        
        try {
            const entity = JSON.parse(labContainer.dataset.entity || '{}')
            const permissions = JSON.parse(labContainer.dataset.permissions || '{}')
            const entityType = labContainer.dataset.entityType || 'appointment'
            const entityId = labContainer.dataset.entityId || entity.id
            const appointmentCompleted = labContainer.dataset.appointmentCompleted === 'true'
            
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
            
            // Mark as mounted
            mountedLabApps.set(labContainer, labApp)
            
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

// Function to wait for element and initialize
function waitForLabContainer(maxAttempts = 50, attempt = 0) {
    const labContainer = document.getElementById('lab-section-container')
    
    if (labContainer) {
        try {
            initializeLabSection()
        } catch (error) {
            console.error('Lab Section: Initialization failed:', error)
            showFallbackContent()
        }
    } else if (attempt < maxAttempts) {
        // Retry after 100ms
        setTimeout(() => waitForLabContainer(maxAttempts, attempt + 1), 100)
    } else {
        console.error('Lab Section: Container not found after max attempts')
        showFallbackContent()
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    waitForLabContainer()
})

// Also try after a short delay in case scripts load in different order
setTimeout(() => {
    const labContainer = document.getElementById('lab-section-container')
    if (labContainer && !mountedLabApps.has(labContainer)) {
        waitForLabContainer()
    }
}, 500)
