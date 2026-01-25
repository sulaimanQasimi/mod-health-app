<template>
  <div class="prescription-index-page">
    <!-- Filters Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">{{ localize('global.filters') }}</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-2">
            <label for="patient_name" class="form-label">{{ localize('global.patient_name') }}</label>
            <input 
              type="text" 
              class="form-control" 
              id="patient_name" 
              v-model="filters.patient_name"
              :placeholder="localize('global.search_by_patient_name')"
              @keyup.enter="applyFilters"
            >
          </div>
          <div class="col-md-2">
            <label for="card_number" class="form-label">{{ localize('global.card_number') }}</label>
            <input 
              type="text" 
              class="form-control" 
              id="card_number" 
              v-model="filters.card_number"
              :placeholder="localize('global.search_by_card_number')"
              @keyup.enter="applyFilters"
            >
          </div>
          <div class="col-md-1">
            <label for="father_name" class="form-label">{{ localize('global.father_name') }}</label>
            <input 
              type="text" 
              class="form-control" 
              id="father_name" 
              v-model="filters.father_name"
              :placeholder="localize('global.search_by_father_name')"
              @keyup.enter="applyFilters"
            >
          </div>
          <div class="col-md-1">
            <label for="patient_id" class="form-label">{{ localize('global.patient_id') }}</label>
            <input 
              type="text" 
              class="form-control" 
              id="patient_id" 
              v-model="filters.patient_id"
              :placeholder="localize('global.search_by_patient_id')"
              @keyup.enter="applyFilters"
              @change="applyFilters"
            >
          </div>
          <div class="col-md-1">
            <label for="token_filter" class="form-label">{{ localize('global.token_id') }}</label>
            <input 
              type="text" 
              class="form-control" 
              id="token_filter" 
              v-model="filters.token_filter"
              :placeholder="localize('global.search_by_token_id')"
            >
          </div>

          <div class="col-md-2">
            <label for="doctor_id" class="form-label">{{ localize('global.doctor_name') }}</label>
            <select class="form-select" id="doctor_id" v-model="filters.doctor_id" @change="applyFilters">
              <option value="">{{ localize('global.all') }}</option>
              <option v-for="d in doctors" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
          <div class="col-md-1">
            <label for="status" class="form-label">{{ localize('global.status') }}</label>
            <select class="form-select" id="status" v-model="filters.status" @change="applyFilters" :disabled="isDeliveredRoute">
              <option value="">{{ localize('global.all') }}</option>
              <option value="0">{{ localize('global.not_delivered') }}</option>
              <option value="1">{{ localize('global.delivered') }}</option>
            </select>
          </div>
          <div class="col-md-2 d-flex align-items-end gap-1 flex-wrap">
            <button 
              type="button" 
              class="btn btn-primary flex-shrink-0" 
              @click="applyFilters"
              :disabled="loading"
              :title="localize('global.search')"
            >
              <i class="bx bx-search" v-if="!loading"></i>
              <i class="bx bx-loader-alt bx-spin" v-else></i>
            </button>
            <button 
              type="button" 
              class="btn btn-secondary flex-shrink-0" 
              @click="clearFilters"
              :title="localize('global.clear_filters')"
            >
              <i class="bx bx-refresh"></i>
              <span v-if="activeFiltersCount > 0" class="badge bg-danger ms-1">{{ activeFiltersCount }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ localize('global.new_prescriptions') }}</h5>
        <div class="d-flex gap-2">
          <!-- Export Dropdown -->
          <div class="dropdown">
            <button 
              class="btn btn-outline-success dropdown-toggle" 
              type="button" 
              data-bs-toggle="dropdown"
              :disabled="prescriptions.length === 0"
            >
              <i class="bx bx-download me-1"></i>
              {{ localize('global.export') }}
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item" href="#" @click.prevent="exportData('excel')">
                  <i class="bx bx-file me-2"></i>
                  {{ localize('global.export_excel') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="#" @click.prevent="exportData('pdf')">
                  <i class="bx bx-file-pdf me-2"></i>
                  {{ localize('global.export_pdf') }}
                </a>
              </li>
            </ul>
          </div>
          
          <!-- Bulk Actions Dropdown -->
          <div class="dropdown" v-if="selectedPrescriptions.length > 0">
            <button 
              class="btn btn-outline-warning dropdown-toggle" 
              type="button" 
              data-bs-toggle="dropdown"
            >
              <i class="bx bx-check-square me-1"></i>
              {{ localize('global.bulk_actions') }} ({{ selectedPrescriptions.length }})
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item" href="#" @click.prevent="bulkChangeStatus('delivered')">
                  <i class="bx bx-check me-2"></i>
                  {{ localize('global.mark_as_delivered') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="#" @click.prevent="bulkChangeStatus('not_delivered')">
                  <i class="bx bx-x me-2"></i>
                  {{ localize('global.mark_as_not_delivered') }}
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item" href="#" @click.prevent="bulkPrint">
                  <i class="bx bx-printer me-2"></i>
                  {{ localize('global.bulk_print') }}
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="#" @click.prevent="bulkDelete">
                  <i class="bx bx-trash me-2"></i>
                  {{ localize('global.bulk_delete') }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <div class="card-body">
        <!-- Loading Overlay -->
        <div v-if="loading" class="loading-overlay">
          <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">{{ localize('global.loading') }}...</span>
            </div>
            <p class="mt-2">{{ localize('global.loading_prescriptions') }}</p>
          </div>
        </div>

        <!-- Prescriptions Table -->
        <div class="table-responsive" v-if="!loading">
          <table class="table table-striped">
            <thead>
              <tr>
                <th width="50">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    :checked="isAllSelected"
                    @change="toggleSelectAll"
                  >
                </th>
                <th @click="sortBy('created_at')" class="sortable">
                  {{ localize('global.number') }}
                  <i class="bx bx-sort" v-if="sorting.sortBy !== 'created_at'"></i>
                  <i class="bx bx-sort-up" v-else-if="sorting.sortOrder === 'asc'"></i>
                  <i class="bx bx-sort-down" v-else></i>
                </th>
                <th>{{ localize('global.patient_id') }}</th>
                <th>{{ localize('global.card_number') }}</th>
                <th @click="sortBy('patient_name')" class="sortable">
                  <span style="font-size: 17px; color: green;">{{ localize('global.patient_name') }}</span>
                  <i class="bx bx-sort" v-if="sorting.sortBy !== 'patient_name'"></i>
                  <i class="bx bx-sort-up" v-else-if="sorting.sortOrder === 'asc'"></i>
                  <i class="bx bx-sort-down" v-else></i>
                </th>
                <th>{{ localize('global.father_name') }}</th>
                <th>{{ localize('global.token_id') }}</th>
                <th @click="sortBy('doctor_name')" class="sortable">
                  {{ localize('global.doctor_name') }}
                  <i class="bx bx-sort" v-if="sorting.sortBy !== 'doctor_name'"></i>
                  <i class="bx bx-sort-up" v-else-if="sorting.sortOrder === 'asc'"></i>
                  <i class="bx bx-sort-down" v-else></i>
                </th>
                <th>{{ localize('global.created_at') }}</th>
                <th @click="sortBy('is_completed')" class="sortable">
                  {{ localize('global.status') }}
                  <i class="bx bx-sort" v-if="sorting.sortBy !== 'is_completed'"></i>
                  <i class="bx bx-sort-up" v-else-if="sorting.sortOrder === 'asc'"></i>
                  <i class="bx bx-sort-down" v-else></i>
                </th>
                <th>{{ localize('global.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(prescription, index) in prescriptions" :key="prescription.id">
                <td>
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    :value="prescription.id"
                    v-model="selectedPrescriptions"
                  >
                </td>
                <td>{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
                <td>
                  <span class="badge bg-light text-dark">{{ prescription.patient_id || prescription.patient?.id || '-' }}</span>
                </td>
                <td>
                  <span class="badge bg-secondary">{{ prescription.patient?.id_card || '-' }}</span>
                </td>
                <td style="font-size: 17px; color: green; font-weight: bold;">
                  {{ prescription.patient?.name || '-' }}
                </td>
                <td>
                  <span class="text-muted">{{ prescription.patient?.father_name || '-' }}</span>
                </td>
                <td>
                  <div v-if="prescription.token">
                    <span class="badge bg-info">{{ prescription.token.number }}</span>
                    <br>
                    <small class="text-muted">{{ formatPersianDate(prescription.token.date) }}</small>
                  </div>
                  <span v-else class="text-muted">-</span>
                </td>
                <td>{{ prescription.doctor_name || prescription.doctor?.name || '-' }}</td>
                <td>{{ formatPersianDate(prescription.created_at) }}</td>
                <td>
                  <span 
                    class="badge" 
                    :class="prescription.is_completed ? 'bg-success' : 'bg-warning'"
                  >
                    {{ prescription.is_completed ? localize('global.delivered') : localize('global.not_delivered') }}
                  </span>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <router-link 
                      :to="{ name: 'prescriptions.show', params: { id: prescription.id }}" 
                      class="btn btn-sm btn-outline-primary" 
                      :title="localize('global.view')"
                    >
                      <i class="bx bx-show-alt"></i>
                    </router-link>
                    <a 
                      :href="`/prescriptions/thermal-receipt/${prescription.id}`" 
                      class="btn btn-sm btn-outline-success" 
                      target="_blank" 
                      :title="localize('global.thermal_print')"
                    >
                      <i class="bx bx-printer"></i>
                    </a>
                  </div>
                </td>
              </tr>
              
              <!-- Empty State -->
              <tr v-if="prescriptions.length === 0">
                <td colspan="11" class="text-center py-4">
                  <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    {{ localize('global.no_prescriptions_found') }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-4">
          <nav aria-label="Prescription pagination">
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: !pagination.links.prev }">
                <a class="page-link" href="#" @click.prevent="goToPage(1)">
                  {{ localize('global.first') }}
                </a>
              </li>
              <li class="page-item" :class="{ disabled: !pagination.links.prev }">
                <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page - 1)">
                  {{ localize('global.previous') }}
                </a>
              </li>
              
              <li 
                v-for="page in visiblePages" 
                :key="page" 
                class="page-item" 
                :class="{ active: page === pagination.current_page }"
              >
                <a class="page-link" href="#" @click.prevent="goToPage(page)">
                  {{ page }}
                </a>
              </li>
              
              <li class="page-item" :class="{ disabled: !pagination.links.next }">
                <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page + 1)">
                  {{ localize('global.next') }}
                </a>
              </li>
              <li class="page-item" :class="{ disabled: !pagination.links.next }">
                <a class="page-link" href="#" @click.prevent="goToPage(pagination.last_page)">
                  {{ localize('global.last') }}
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

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
  },
  // Optional default status when landing from special routes (e.g., delivered)
  defaultStatus: {
    type: [String, Number],
    default: ''
  }
})

// Router
const router = useRouter()
const route = useRoute()

// Reactive data
const prescriptions = ref([])
const loading = ref(false)
const selectedPrescriptions = ref([])
const doctors = ref([])

const filters = reactive({
  patient_name: '',
  card_number: '',
  father_name: '',
  patient_id: '',
  token_filter: '',
  doctor_id: '',
  status: ''
})

const sorting = reactive({
  sortBy: 'created_at',
  sortOrder: 'desc'
})
// Route-aware flags
const isDeliveredRoute = computed(() => route.name === 'prescriptions.delivered')


const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0,
  links: {
    first: null,
    last: null,
    prev: null,
    next: null
  }
})

// Computed properties
const activeFiltersCount = computed(() => {
  let count = 0
  if (filters.patient_name) count++
  if (filters.card_number) count++
  if (filters.father_name) count++
  if (filters.patient_id) count++
  if (filters.token_filter) count++
  if (filters.doctor_id) count++
  if (filters.status) count++
  return count
})

const isAllSelected = computed(() => {
  return prescriptions.value.length > 0 && selectedPrescriptions.value.length === prescriptions.value.length
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, pagination.current_page - 2)
  const end = Math.min(pagination.last_page, pagination.current_page + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

// Methods
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

const formatPersianDate = (date) => {
  if (!date) return '-'
  // This would need to be implemented with the Persian date library
  return new Date(date).toLocaleDateString('fa-IR')
}

const fetchPrescriptions = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.current_page,
      perPage: pagination.per_page,
      sortBy: sorting.sortBy,
      sortOrder: sorting.sortOrder,
      patient_name: filters.patient_name || undefined,
      card_number: filters.card_number || undefined,
      father_name: filters.father_name || undefined,
      patient_id: filters.patient_id || undefined,
      token_filter: filters.token_filter || undefined,
      doctor_id: filters.doctor_id || undefined,
      status: isDeliveredRoute.value ? '1' : filters.status
    }

    const response = await axios.get('/prescription-ajax/prescriptions-index', { params })
    
    if (response.data.success) {
      prescriptions.value = response.data.data
      Object.assign(pagination, response.data.pagination)
      Object.assign(pagination.links, response.data.links)
    } else {
      console.error('Failed to fetch prescriptions:', response.data.message)
    }
  } catch (error) {
    console.error('Error fetching prescriptions:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  pagination.current_page = 1
  selectedPrescriptions.value = []
  fetchPrescriptions()
}

const clearFilters = () => {
  Object.assign(filters, {
    patient_name: '',
    card_number: '',
    father_name: '',
    patient_id: '',
    token_filter: '',
    doctor_id: '',
    status: ''
  })
  
  pagination.current_page = 1
  selectedPrescriptions.value = []
  fetchPrescriptions()
}

const sortBy = (field) => {
  if (sorting.sortBy === field) {
    sorting.sortOrder = sorting.sortOrder === 'asc' ? 'desc' : 'asc'
  } else {
    sorting.sortBy = field
    sorting.sortOrder = 'asc'
  }
  fetchPrescriptions()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.last_page) {
    pagination.current_page = page
    fetchPrescriptions()
  }
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedPrescriptions.value = []
  } else {
    selectedPrescriptions.value = prescriptions.value.map(p => p.id)
  }
}

const exportData = async (format) => {
  try {
    const params = {
      format,
      ...filters,
      selected: selectedPrescriptions.value
    }
    
    const response = await axios.post('/prescriptions/export-prescriptions', params, {
      responseType: 'blob'
    })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `prescriptions.${format}`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Export error:', error)
  }
}

const bulkChangeStatus = async (status) => {
  if (selectedPrescriptions.value.length === 0) return
  
  const isCompleted = status === 'delivered'
  
  try {
    const response = await axios.post('/prescriptions/bulk-update-status', {
      prescription_ids: selectedPrescriptions.value,
      is_completed: isCompleted
    })
    
    if (response.data.success) {
      selectedPrescriptions.value = []
      fetchPrescriptions()
    }
  } catch (error) {
    console.error('Bulk status change error:', error)
  }
}

const bulkDelete = async () => {
  if (selectedPrescriptions.value.length === 0) return
  
  if (!confirm(localize('global.confirm_bulk_delete'))) return
  
  try {
    const response = await axios.post('/prescriptions/bulk-delete', {
      prescription_ids: selectedPrescriptions.value
    })
    
    if (response.data.success) {
      selectedPrescriptions.value = []
      fetchPrescriptions()
    }
  } catch (error) {
    console.error('Bulk delete error:', error)
  }
}

const bulkPrint = () => {
  if (selectedPrescriptions.value.length === 0) return
  
  // Open each prescription's thermal receipt in a new tab
  selectedPrescriptions.value.forEach(id => {
    window.open(`/prescriptions/thermal-receipt/${id}`, '_blank')
  })
}

const fetchDoctors = async () => {
  try {
    const res = await axios.get('/doctor-api/doctors')
    if (res.data?.success && Array.isArray(res.data.data)) {
      doctors.value = res.data.data
    }
  } catch (e) {
    console.error('Failed to load doctors for filter:', e)
  }
}

// Lifecycle
onMounted(() => {
  // Seed filters from route-provided default status if present
  if (!filters.status && props.defaultStatus !== '') {
    filters.status = String(props.defaultStatus)
  }
  fetchDoctors()
  fetchPrescriptions()
})

// Watch for filter changes with debounce (patient_name, card_number, father_name)
let filterTimeout
watch(
  () => [filters.patient_name, filters.card_number, filters.father_name, filters.patient_id],
  () => {
    clearTimeout(filterTimeout)
    filterTimeout = setTimeout(() => {
      applyFilters()
    }, 500)
  },
  { deep: true }
)
</script>

<style scoped>
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.loading-spinner {
  text-align: center;
}

.sortable {
  cursor: pointer;
  user-select: none;
}

.sortable:hover {
  background-color: #f8f9fa;
}

.prescription-index-page {
  position: relative;
}

.table-responsive {
  position: relative;
}

.badge {
  font-size: 0.75em;
}

.btn-group .btn {
  margin-right: 2px;
}

.btn-group .btn:last-child {
  margin-right: 0;
}

.pagination {
  margin-bottom: 0;
}

.dropdown-menu {
  z-index: 1050;
}
</style>
