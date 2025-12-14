<template>
    <div class="dentist-registration-section">
        <!-- Dentist Registration Section Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-body-secondary text-body d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-tooth me-2 text-primary"></i>
                            {{ localize('global.dentist_registrations') }}
                        </h5>
                        <button 
                            v-if="!appointmentCompleted" 
                            type="button" 
                            class="btn btn-primary btn-sm" 
                            @click="openCreateModal"
                        >
                            <i class="bx bx-plus me-1"></i>
                            {{ localize('global.register_to_dentist') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Dentist Registration Modal -->
        <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ localize('global.register_to_dentist') }}</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="createDentistRegistration">
                            <div class="form-group mb-3">
                                <label for="dentist_id">{{ localize('global.dentist') }} <span class="text-danger">*</span></label>
                                <select 
                                    v-model="form.dentist_id" 
                                    class="form-select" 
                                    id="dentist_id"
                                    :required="true"
                                >
                                    <option value="">{{ localize('global.select_dentist') }}</option>
                                    <option v-for="dentist in dentists" :key="dentist.id" :value="dentist.id">
                                        {{ dentist.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="registration_date">{{ localize('global.registration_date') }} <span class="text-danger">*</span></label>
                                <input 
                                    type="date" 
                                    v-model="form.registration_date" 
                                    class="form-control" 
                                    id="registration_date"
                                    :value="form.registration_date"
                                    required
                                />
                            </div>

                            <div class="form-group mb-3">
                                <label for="notes">{{ localize('global.notes') }}</label>
                                <textarea 
                                    v-model="form.notes" 
                                    class="form-control" 
                                    id="notes"
                                    rows="3" 
                                    :placeholder="localize('global.optional_notes')"
                                ></textarea>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>{{ localize('global.note') }}:</strong> {{ localize('global.dentist_registration_modal_info') }}
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeCreateModal">
                            {{ localize('global.cancel') }}
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-success" 
                            @click="createDentistRegistration"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ localize('global.create_and_continue') }}
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="createDentistRegistrationAndClose"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ localize('global.create_and_close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dentist Registrations List -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div v-if="dentistRegistrations.length > 0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-body-secondary">
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.ref_no') }}</th>
                                            <th>{{ localize('global.patient') }}</th>
                                            <th>{{ localize('global.dentist') }}</th>
                                            <th>{{ localize('global.registration_date') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th>{{ localize('global.examinations') }}</th>
                                            <th>{{ localize('global.treatments') }}</th>
                                            <th>{{ localize('global.xrays') }}</th>
                                            <th>{{ localize('global.notes') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="registration in dentistRegistrations" :key="registration.id">
                                            <td>
                                                <span class="badge bg-warning rounded-pill">{{ dentistRegistrations.indexOf(registration) + 1 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ registration.ref_no || '—' }}</span>
                                            </td>
                                            <td>
                                                <span v-if="registration.appointment && registration.appointment.patient">
                                                    {{ registration.appointment.patient.name }} {{ registration.appointment.patient.last_name }}
                                                </span>
                                                <span v-else>—</span>
                                            </td>
                                            <td>{{ registration.dentist ? registration.dentist.name : '—' }}</td>
                                            <td dir="ltr">{{ formatDate(registration.registration_date) }}</td>
                                            <td>
                                                <span :class="getStatusClass(registration.status)">
                                                    {{ getStatusText(registration.status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ registration.examinations_count || 0 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ registration.treatments_count || 0 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">{{ registration.xrays_count || 0 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ registration.notes_count || 0 }}</span>
                                            </td>
                                            <td>
                                                <a 
                                                    :href="`/dentist-registrations/show/${registration.id}`"
                                                    class="btn btn-outline-primary btn-sm"
                                                    :title="localize('global.view_details')"
                                                >
                                                    <i class="bx bx-show"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                {{ localize('global.no_dentist_registrations_found') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DentistRegistrationSection',
    props: {
        appointment: {
            type: Object,
            required: true
        },
        entityType: {
            type: String,
            default: 'appointment'
        },
        entityId: {
            type: [String, Number],
            default: null
        },
        canAddDentistRegistration: {
            type: Boolean,
            default: true
        },
        appointmentCompleted: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            loading: false,
            showCreateModal: false,
            dentistRegistrations: [],
            dentists: [],
            form: {
                dentist_id: '',
                registration_date: new Date().toISOString().split('T')[0],
                notes: ''
            }
        }
    },
    mounted() {
        this.loadDentists();
        this.loadDentistRegistrations();
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        }
    },
    methods: {
        async loadDentists() {
            try {
                const response = await fetch('/doctor-api/hospital-doctors?branch_id=' + (this.appointment.branch_id || ''), {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Load all doctors (not just dentists)
                    this.dentists = data.data;
                } else {
                    // Fallback: try to get all doctors
                    const fallbackResponse = await fetch('/doctor-api/doctors', {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const fallbackData = await fallbackResponse.json();
                    if (fallbackData.success && fallbackData.data) {
                        this.dentists = fallbackData.data;
                    } else {
                        this.showError(this.localize('global.failed_to_load_dentists'));
                    }
                }
            } catch (error) {
                console.error('Error loading doctors:', error);
                this.showError(this.localize('global.failed_to_load_dentists'));
            }
        },

        async loadDentistRegistrations() {
            try {
                const entityId = this.entityId || this.appointment.id;
                const endpoint = `/dentist-ajax/registrations/${entityId}`;

                const response = await fetch(endpoint, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.dentistRegistrations = data.data;
                } else {
                    this.showError(data.message || this.localize('global.failed_to_load_registrations'));
                }
            } catch (error) {
                console.error('Error loading dentist registrations:', error);
                this.showError(this.localize('global.failed_to_load_registrations'));
            }
        },

        async createDentistRegistration() {
            if (!this.form.dentist_id) {
                this.showError(this.localize('global.please_select_dentist'));
                return;
            }

            if (!this.form.registration_date) {
                this.showError(this.localize('global.please_select_registration_date'));
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('dentist_id', this.form.dentist_id);
                formData.append('registration_date', this.form.registration_date);
                formData.append('notes', this.form.notes || '');

                const entityId = this.entityId || this.appointment.id;
                const response = await fetch(`/dentist-registrations/store/${entityId}`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success || response.ok) {
                    this.showSuccess(data.message || this.localize('global.registration_created_successfully'));
                    this.loadDentistRegistrations();
                    this.resetForm();
                } else {
                    this.showError(data.message || this.localize('global.failed_to_create_registration'));
                }
            } catch (error) {
                console.error('Error creating dentist registration:', error);
                this.showError(this.localize('global.failed_to_create_registration'));
            } finally {
                this.loading = false;
            }
        },

        async createDentistRegistrationAndClose() {
            await this.createDentistRegistration();
            if (!this.loading) {
                this.closeCreateModal();
            }
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.resetForm();
        },

        openCreateModal() {
            this.showCreateModal = true;
        },

        resetForm() {
            this.form = {
                dentist_id: '',
                registration_date: new Date().toISOString().split('T')[0],
                notes: ''
            };
        },

        formatDate(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            return date.toLocaleDateString('fa-IR');
        },

        getStatusClass(status) {
            const classes = {
                'pending': 'badge bg-warning',
                'in_progress': 'badge bg-info',
                'completed': 'badge bg-success',
                'cancelled': 'badge bg-danger'
            };
            return classes[status] || 'badge bg-secondary';
        },

        getStatusText(status) {
            const texts = {
                'pending': this.localize('global.status_pending'),
                'in_progress': this.localize('global.status_in_progress'),
                'completed': this.localize('global.status_completed'),
                'cancelled': this.localize('global.status_cancelled')
            };
            return texts[status] || status;
        },

        showSuccess(message) {
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: this.localize('global.success'),
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                alert(message);
            }
        },

        showError(message) {
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: this.localize('global.error'),
                    text: message
                });
            } else {
                alert(message);
            }
        },

        localize(key) {
            const translations = {
                'global.dentist_registrations': 'ثبت نام بخش دندان',
                'global.register_to_dentist': 'ثبت نام به بخش دندان',
                'global.dentist': 'بخش دندان',
                'global.select_dentist': 'انتخاب بخش دندان',
                'global.registration_date': 'تاریخ ثبت نام',
                'global.notes': 'یادداشت‌ها',
                'global.optional_notes': 'یادداشت‌های اختیاری...',
                'global.cancel': 'لغو',
                'global.create_and_continue': 'ایجاد و ادامه',
                'global.create_and_close': 'ایجاد و بستن',
                'global.number': 'شماره',
                'global.ref_no': 'شماره مرجع',
                'global.patient': 'بیمار',
                'global.status': 'وضعیت',
                'global.examinations': 'معاینات',
                'global.treatments': 'درمان‌ها',
                'global.xrays': 'عکس‌ها',
                'global.notes': 'یادداشت‌ها',
                'global.actions': 'عملیات',
                'global.view_details': 'مشاهده جزئیات',
                'global.no_dentist_registrations_found': 'هیچ ثبت نام بخش دندان یافت نشد',
                'global.success': 'موفقیت',
                'global.error': 'خطا',
                'global.status_pending': 'در انتظار',
                'global.status_in_progress': 'در حال انجام',
                'global.status_completed': 'تکمیل شده',
                'global.status_cancelled': 'لغو شده',
                'global.failed_to_load_dentists': 'بارگذاری بخش دندان ها ناموفق بود',
                'global.failed_to_load_registrations': 'بارگذاری ثبت نام‌ها ناموفق بود',
                'global.please_select_dentist': 'لطفاً بخش دندان را انتخاب کنید',
                'global.please_select_registration_date': 'لطفاً تاریخ ثبت نام را انتخاب کنید',
                'global.failed_to_create_registration': 'ایجاد ثبت نام ناموفق بود',
                'global.registration_created_successfully': 'ثبت نام با موفقیت ایجاد شد',
                'global.dentist_registration_modal_info': 'بیمار از نوبت به بخش دندان معرفی می‌شود',
                'global.note': 'یادداشت'
            };
            return translations[key] || key;
        }
    }
}
</script>

<style scoped>
.dentist-registration-section .modal {
    z-index: 9999 !important;
}

.dentist-registration-section .modal.show {
    display: block !important;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>
