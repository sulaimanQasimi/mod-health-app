import { createRouter, createWebHistory } from 'vue-router'
import PrescriptionIndexPage from '../components/PrescriptionIndexPage.vue'
import PrescriptionShowApp from '../components/PrescriptionShowApp.vue'

const routes = [
  {
    path: '/prescriptions',
    name: 'prescriptions.index',
    component: PrescriptionIndexPage,
    meta: { 
      title: 'Prescriptions List',
      requiresAuth: true
    }
  },
  {
    path: '/prescriptions/:id',
    name: 'prescriptions.show',
    component: PrescriptionShowApp,
    props: true,
    meta: { 
      title: 'Prescription Details',
      requiresAuth: true
    }
  },
  {
    path: '/prescriptions/show/:id',
    name: 'prescriptions.show.legacy',
    component: PrescriptionShowApp,
    props: true,
    meta: { 
      title: 'Prescription Details',
      requiresAuth: true
    }
  },
  // Redirect legacy routes
  {
    path: '/prescriptions/show/:id',
    redirect: to => `/prescriptions/${to.params.id}`
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Return to saved position if available (browser back/forward)
    if (savedPosition) {
      return savedPosition
    }
    // Scroll to top for new routes
    return { top: 0 }
  }
})

// Navigation guards
router.beforeEach((to, from, next) => {
  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title
  }
  
  // Check authentication (if needed)
  if (to.meta.requiresAuth) {
    // Add any auth checks here if needed
    // For now, we'll assume user is authenticated
  }
  
  next()
})

// Global error handling
router.onError((error) => {
  console.error('Router error:', error)
  // You could redirect to an error page here
})

export default router


