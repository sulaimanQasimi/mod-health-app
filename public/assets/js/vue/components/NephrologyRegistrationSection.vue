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
                            <button type="button" class="btn btn-primary btn-sm" @click="openNephrology" :disabled="opening">
                                <span v-if="opening" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="bx bx-plus me-1"></i>
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
                                        <td>{{ registration.diagnosis || (registration.disease ? registration.disease.name : '—') }}</td>
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
        translations: { type: Object, default: () => ({}) },
        canOpenNephrology: { type: Boolean, default: true },
        appointmentCompleted: { type: Boolean, default: false },
    },
    data() {
        return {
            loadingList: true,
            opening: false,
            registrations: [],
            errorMessage: '',
        };
    },
    mounted() {
        this.loadRegistrations();
    },
    methods: {
        async loadRegistrations() {
            this.loadingList = true;
            this.errorMessage = '';
            try {
                const entityId = this.entityId || this.appointment.id;
                const response = await fetch(`/nephrology-ajax/registrations/${entityId}`, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) {
                    this.errorMessage = this.localize('global.failed_to_load_registrations');
                    return;
                }

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
            return this.translations[key] || key;
        },
    },
};
</script>
