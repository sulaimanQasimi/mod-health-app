<template>
    <div class="nephrology-registration-section">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-body-secondary text-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">
                            <i class="bx bx-droplet me-2 text-primary"></i>
                            {{ localize('global.nephrology_registrations') }}
                        </h5>
                        <div class="d-flex gap-2" v-if="!appointmentCompleted && canOpenNephrology">
                            <a
                                :href="openUrl"
                                class="btn btn-success btn-sm"
                                :class="{ disabled: opening }"
                                @click.prevent="openNephrology"
                            >
                                <span v-if="opening" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bx bx-play-circle me-1"></i>
                                {{ localize('global.open_nephrology') }}
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" @click="openCreateModal">
                                <i class="bx bx-plus me-1"></i>
                                {{ localize('global.start_nephrology_visit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger">
            <i class="bx bx-error-circle me-2"></i>{{ errorMessage }}
        </div>

        <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ localize('global.start_nephrology_visit') }}</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="formError" class="alert alert-danger">{{ formError }}</div>
                        <form @submit.prevent="createRegistration">
                            <div class="form-group mb-3">
                                <label for="doctor_id">{{ localize('global.doctor') }}</label>
                                <select v-model="form.doctor_id" class="form-select" id="doctor_id">
                                    <option value="">{{ localize('global.select_doctor') }}</option>
                                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">{{ doctor.name }}</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="visit_date">{{ localize('global.visit_date') }} <span class="text-danger">*</span></label>
                                <input type="date" v-model="form.visit_date" class="form-control" id="visit_date" required />
                            </div>
                            <div class="form-group mb-3">
                                <label for="notes">{{ localize('global.notes') }}</label>
                                <textarea v-model="form.notes" class="form-control" id="notes" rows="2"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeCreateModal">{{ localize('global.cancel') }}</button>
                        <button type="button" class="btn btn-primary" @click="createRegistration" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ localize('global.create_and_continue') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div v-if="loadingList" class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                        </div>
                        <div v-else-if="registrations.length > 0" class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.ref_no') }}</th>
                                        <th>{{ localize('global.patient') }}</th>
                                        <th>{{ localize('global.doctor') }}</th>
                                        <th>{{ localize('global.visit_date') }}</th>
                                        <th>{{ localize('global.status') }}</th>
                                        <th>{{ localize('global.diagnosis') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="registration in registrations" :key="registration.id">
                                        <td><span class="badge bg-primary">{{ registration.ref_no || '—' }}</span></td>
                                        <td>
                                            <span v-if="registration.appointment && registration.appointment.patient">
                                                {{ registration.appointment.patient.name }} {{ registration.appointment.patient.last_name }}
                                            </span>
                                            <span v-else>—</span>
                                        </td>
                                        <td>{{ registration.doctor ? registration.doctor.name : '—' }}</td>
                                        <td dir="ltr">{{ formatDate(registration.visit_date) }}</td>
                                        <td><span :class="getStatusClass(registration.status)">{{ getStatusText(registration.status) }}</span></td>
                                        <td>{{ registration.diagnosis || '—' }}</td>
                                        <td>
                                            <a :href="registration.open_url" class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-4">
                            <div class="alert alert-info mb-0">
                                <i class="bx bx-info-circle me-2"></i>
                                {{ localize('global.no_nephrology_registrations_found') }}
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
    name: 'NephrologyRegistrationSection',
    props: {
        appointment: { type: Object, required: true },
        entityType: { type: String, default: 'appointment' },
        entityId: { type: [String, Number], default: null },
        openUrl: { type: String, default: '' },
        canOpenNephrology: { type: Boolean, default: true },
        appointmentCompleted: { type: Boolean, default: false },
    },
    data() {
        return {
            loading: false,
            loadingList: true,
            opening: false,
            showCreateModal: false,
            registrations: [],
            doctors: [],
            errorMessage: '',
            formError: '',
            form: {
                doctor_id: '',
                visit_date: new Date().toISOString().split('T')[0],
                notes: '',
            },
        };
    },
    mounted() {
        this.loadDoctors();
        this.loadRegistrations();
    },
    methods: {
        async loadDoctors() {
            try {
                const response = await fetch('/doctor-api/doctors?is_nephrologist=1&branch_id=' + (this.appointment.branch_id || ''), {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await response.json();
                if (data.success && data.data) {
                    this.doctors = data.data;
                } else {
                    const fallback = await fetch('/doctor-api/doctors', {
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const fallbackData = await fallback.json();
                    if (fallbackData.success && fallbackData.data) {
                        this.doctors = fallbackData.data;
                    }
                }
            } catch (error) {
                console.error(error);
            }
        },

        async loadRegistrations() {
            this.loadingList = true;
            this.errorMessage = '';
            try {
                const entityId = this.entityId || this.appointment.id;
                const response = await fetch(`/nephrology-ajax/registrations/${entityId}`, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await response.json();
                if (data.success) {
                    this.registrations = data.data;
                } else {
                    this.errorMessage = data.message || this.localize('global.failed_to_load_registrations');
                }
            } catch (error) {
                this.errorMessage = this.localize('global.failed_to_load_registrations');
            } finally {
                this.loadingList = false;
            }
        },

        openNephrology() {
            if (!this.openUrl || this.opening) {
                return;
            }
            this.opening = true;
            window.location.href = this.openUrl;
        },

        async createRegistration() {
            if (!this.form.visit_date) {
                this.formError = this.localize('global.please_select_visit_date');
                return;
            }

            this.loading = true;
            this.formError = '';

            try {
                const formData = new FormData();
                if (this.form.doctor_id) {
                    formData.append('doctor_id', this.form.doctor_id);
                }
                formData.append('visit_date', this.form.visit_date);
                formData.append('notes', this.form.notes || '');

                const entityId = this.entityId || this.appointment.id;
                const response = await fetch(`/nephrology-registrations/store/${entityId}`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                if (response.ok) {
                    window.location.reload();
                    return;
                }

                this.formError = data.message || this.localize('global.failed_to_create_registration');
            } catch (error) {
                this.formError = this.localize('global.failed_to_create_registration');
            } finally {
                this.loading = false;
            }
        },

        openCreateModal() {
            this.showCreateModal = true;
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.formError = '';
        },

        formatDate(dateString) {
            if (!dateString) return '—';
            return new Date(dateString).toLocaleDateString('fa-IR');
        },

        getStatusClass(status) {
            const classes = {
                pending: 'badge bg-warning',
                in_progress: 'badge bg-info',
                completed: 'badge bg-success',
                cancelled: 'badge bg-danger',
            };
            return classes[status] || 'badge bg-secondary';
        },

        getStatusText(status) {
            return this.localize('global.status_' + status) || status;
        },

        localize(key) {
            const translations = {
                'global.nephrology_registrations': 'Nephrology visits',
                'global.open_nephrology': 'Open Nephrology',
                'global.start_nephrology_visit': 'Start Nephrology Visit',
                'global.doctor': 'Doctor',
                'global.select_doctor': 'Select doctor',
                'global.visit_date': 'Visit date',
                'global.notes': 'Notes',
                'global.cancel': 'Cancel',
                'global.create_and_continue': 'Create and continue',
                'global.ref_no': 'Ref no',
                'global.patient': 'Patient',
                'global.status': 'Status',
                'global.diagnosis': 'Diagnosis',
                'global.actions': 'Actions',
                'global.no_nephrology_registrations_found': 'No nephrology visits found for this appointment.',
                'global.failed_to_load_registrations': 'Failed to load nephrology visits.',
                'global.failed_to_create_registration': 'Failed to create nephrology visit.',
                'global.please_select_visit_date': 'Please select a visit date.',
                'global.status_pending': 'Pending',
                'global.status_in_progress': 'In progress',
                'global.status_completed': 'Completed',
                'global.status_cancelled': 'Cancelled',
            };
            return translations[key] || key;
        },
    },
};
</script>
