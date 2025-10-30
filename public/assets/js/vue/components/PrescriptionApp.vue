<template>
  <div class="prescription-app">
    <!-- Router view for rendering different components -->
    <router-view 
      :permissions="permissions"
      :localize="props.localize"
      :branch-id="branchId"
    />
    
    <!-- Global loading overlay -->
    <div v-if="isLoading" class="global-loading-overlay">
      <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">{{ localize('global.loading') }}...</span>
        </div>
        <p class="mt-2">{{ localize('global.loading') }}...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, provide, onMounted } from 'vue'
import { useRouter } from 'vue-router'

// Props
const props = defineProps({
  permissions: {
    type: Object,
    default: () => ({})
  },
  localize: {
    type: Object,
    default: () => ({})
  },
  branchId: {
    type: Number,
    default: null
  }
})

// Reactive data
const isLoading = ref(false)
const router = useRouter()

// Provide global context to child components
provide('permissions', props.permissions)
provide('localize', props.localize)
provide('branchId', props.branchId)
provide('isLoading', isLoading)

// Global localization function
const localize = (key) => {
  // First try to get from props
  if (props.localize && props.localize[key]) {
    return props.localize[key]
  }
  
  // Fallback to global function
  if (window.localize) {
    return window.localize(key)
  }
  
  // Final fallback
  return key
}

// Router navigation guards
router.beforeEach((to, from, next) => {
  isLoading.value = true
  next()
})

router.afterEach((to, from) => {
  isLoading.value = false
})

// Lifecycle
onMounted(() => {
  // Initialize any global state if needed
  console.log('PrescriptionApp mounted with router')
})
</script>

<style scoped>
.prescription-app {
  position: relative;
  min-height: 100vh;
}

.global-loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-spinner {
  text-align: center;
}

/* Router transition animations */
.router-enter-active,
.router-leave-active {
  transition: opacity 0.3s ease;
}

.router-enter-from,
.router-leave-to {
  opacity: 0;
}
</style>
