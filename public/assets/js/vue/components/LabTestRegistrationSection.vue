<template>
    <div class="lab-test-registration-section">
        <!-- Lab Test Registration Section Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-body-secondary text-body d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-test-tube me-2 text-warning"></i>
                            {{ localize('global.lab_test_registrations') }}
                        </h5>
                        <button 
                            v-if="canAddTestRegistration && !appointmentCompleted" 
                            type="button" 
                            class="btn btn-primary btn-sm" 
                            @click="openCreateModal"
                        >
                            <i class="bx bx-plus me-1"></i>
                            {{ localize('global.add_lab_test_registration') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Lab Test Registration Modal -->
        <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ localize('global.add_lab_test_registration') }}</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="createTestRegistration">
                            <input type="hidden" name="entity_id" :value="entityId">
                            <input type="hidden" name="entity_type" :value="entityType">
                            <input type="hidden" name="patient_id" :value="appointment.patient_id">
                            <input type="hidden" name="doctor_id" :value="appointment.doctor_id">
                            <input type="hidden" name="branch_id" :value="appointment.branch_id">

                            <div class="form-group mb-3">
                                <label for="lab_type_ids">{{ localize('global.lab_type') }} ({{ localize('global.select_multiple') }})</label>
                                <multiselect
                                    v-model="selectedLabTypes"
                                    :options="allLabTypes"
                                    :searchable="true"
                                    :close-on-select="false"
                                    :multiple="true"
                                    :show-labels="false"
                                    :placeholder="localize('global.select_lab_types')"
                                    label="name"
                                    track-by="id"
                                    @select="onLabTypeSelect"
                                    @remove="onLabTypeRemove"
                                    @clear="onLabTypeClear"
                                >
                                </multiselect>
                            </div>

                            <div class="form-group mb-3">
                                <label for="priority">{{ localize('global.priority') }}</label>
                                <select v-model="form.priority" class="form-select" required>
                                    <option value="normal">{{ localize('global.normal') }}</option>
                                    <option value="urgent">{{ localize('global.urgent') }}</option>
                                    <option value="stat">{{ localize('global.stat') }}</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="notes">{{ localize('global.notes') }}</label>
                                <textarea 
                                    v-model="form.notes" 
                                    class="form-control" 
                                    rows="3" 
                                    :placeholder="localize('global.optional_notes')"
                                ></textarea>
                            </div>


                            <!-- Info message about modal behavior -->
                            <div class="alert alert-info mt-3">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>{{ localize('global.note') }}:</strong> {{ localize('global.registration_modal_info') }}
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
                            @click="createTestRegistration"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ localize('global.create_and_continue') }}
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="createTestRegistrationAndClose"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ localize('global.create_and_close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lab Test Registrations List -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div v-if="testRegistrations.length > 0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-body-secondary">
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.ref_number') }}</th>
                                            <th>{{ localize('global.patient') }}</th>
                                            <th>{{ localize('global.lab_type') }}</th>
                                            <th>{{ localize('global.category') }}</th>
                                            <th>{{ localize('global.parameters_count') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th>{{ localize('global.priority') }}</th>
                                            <th>{{ localize('global.doctor') }}</th>
                                            <th>{{ localize('global.assigned_to') }}</th>
                                            <th>{{ localize('global.created_date') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="registration in testRegistrations" :key="registration.id">
                                            <td>
                                                <span class="badge bg-warning rounded-pill">{{ testRegistrations.indexOf(registration) + 1 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ registration.ref_no || '—' }}</span>
                                            </td>
                                            <td>
                                                <span v-if="registration.testable && registration.testable.patient">
                                                    {{ registration.testable.patient.first_name }} {{ registration.testable.patient.last_name }}
                                                </span>
                                                <span v-else>—</span>
                                            </td>
                                            <td>{{ registration.lab_type ? registration.lab_type.name : '—' }}</td>
                                            <td>{{ registration.lab_type?.category?.name ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ registration.lab_type?.direct_lab_test_parameters?.length || 0 }} {{ localize('global.parameters') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span 
                                                    :class="getStatusClass(registration.status)"
                                                >
                                                    {{ getStatusText(registration.status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span 
                                                    :class="getPriorityClass(registration.priority)"
                                                >
                                                    {{ getPriorityText(registration.priority) }}
                                                </span>
                                            </td>
                                            <td>{{ registration.doctor ? registration.doctor.name : '—' }}</td>
                                            <td>
                                                <span v-if="registration.assigned_to" class="badge bg-success">
                                                    <i class="bx bx-user me-1"></i>
                                                    {{ registration.assigned_to.name }}
                                                </span>
                                                <span v-else class="text-muted">—</span>
                                            </td>
                                            <td dir="ltr">{{ formatDate(registration.created_at) }}</td>
                                            <td>
                                                <!-- Print Report Button -->
                                                <a 
                                                    v-if="registration.status === 'completed'"
                                                    :href="`/laboratory/reports/print/${registration.ref_no}`"
                                                    class="btn btn-outline-info btn-sm"
                                                    :title="localize('global.print_report')"
                                                    target="_blank"
                                                >
                                                    <i class="bx bx-printer"></i>
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
                                {{ localize('global.no_test_registrations_found') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Parameters Modal -->
        <div v-if="showParametersModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ localize('global.test_parameters') }}</h5>
                        <button type="button" class="btn-close" @click="closeParametersModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedRegistration">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body small mb-1">{{ localize('global.patient') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-user me-1 text-primary"></i>
                                            <span v-if="selectedRegistration.testable && selectedRegistration.testable.patient">
                                                {{ selectedRegistration.testable.patient.first_name }} {{ selectedRegistration.testable.patient.last_name }}
                                            </span>
                                            <span v-else>—</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.doctor') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-user-check me-1 text-primary"></i>
                                            {{ selectedRegistration.doctor ? selectedRegistration.doctor.name : '—' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                        <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.lab_type') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-test-tube me-1 text-primary"></i>
                                            {{ selectedRegistration.lab_type ? selectedRegistration.lab_type.name : '—' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.status') }}</div>
                                        <div class="fw-bold">
                                            <span :class="getStatusClass(selectedRegistration.status)">
                                                {{ getStatusText(selectedRegistration.status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Assigned User and Section Information -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.assigned_to') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-user-plus me-1 text-success"></i>
                                            <span v-if="selectedRegistration.assigned_to">
                                                {{ selectedRegistration.assigned_to.name || '—' }}
                                            </span>
                                            <span v-else class="text-muted">—</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.assigned_section') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-building me-1 text-info"></i>
                                            <span v-if="selectedRegistration.assigned_section">
                                                {{ selectedRegistration.assigned_section.name || '—' }}
                                            </span>
                                            <span v-else class="text-muted">—</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignment Date Information -->
                            <div v-if="selectedRegistration.assigned_at" class="row mb-3">
                                <div class="col-12">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.assigned_date') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-calendar me-1 text-warning"></i>
                                            {{ formatDate(selectedRegistration.assigned_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes Section -->
                            <div v-if="selectedRegistration.notes" class="row mb-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="bx bx-note me-2"></i>
                                                {{ localize('global.notes') }}
                                            </h6>
                                        </div>
                                        <div class="card-body" dir="ltr" style="text-align: left;">
                                            <div v-html="selectedRegistration.notes" class="mb-0"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Notes Section -->
                            <div v-if="selectedRegistration.detailed_notes" class="row mb-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="bx bx-note me-2"></i>
                                                {{ localize('global.detailed_notes') }}
                                            </h6>
                                        </div>
                                        <div class="card-body" dir="ltr" style="text-align: left;">
                                            <div v-html="selectedRegistration.detailed_notes" class="mb-0"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Metadata Section -->
                            <div v-if="selectedRegistration.metadata && Object.keys(selectedRegistration.metadata).length > 0" class="row mb-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0">
                                                <i class="bx bx-data me-2"></i>
                                                {{ localize('global.metadata') }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div v-for="(value, key) in selectedRegistration.metadata" :key="key" class="col-md-6 mb-2">
                                                    <strong>{{ key }}:</strong>
                                                    <p class="mb-0">{{ value }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Parameters Section -->
                            <div class="card shadow-sm">
                                <div class="card-header bg-body-secondary text-body">
                                    <h5 class="mb-0 text-center">
                                        <i class="bx bx-list-ul me-2 text-info"></i>
                                        {{ localize('global.test_parameters') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Show parameters table if parameters exist -->
                                    <div v-if="selectedRegistration.lab_type && selectedRegistration.lab_type.direct_lab_test_parameters && selectedRegistration.lab_type.direct_lab_test_parameters.length > 0">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-body-secondary">
                                                    <tr>
                                                        <th width="5%">{{ localize('global.number') }}</th>
                                                        <th width="15%">{{ localize('global.parameter_name') }}</th>
                                                        <th width="8%">{{ localize('global.unit') }}</th>
                                                        <th width="12%">{{ localize('global.normal_range') }}</th>
                                                        <th width="10%">Critical Low</th>
                                                        <th width="10%">Critical High</th>
                                                        <th width="10%">Panic Low</th>
                                                        <th width="10%">Panic High</th>
                                                        <th width="10%">{{ localize('global.result') }}</th>
                                                        <th width="10%">{{ localize('global.status') }}</th>
                                                        <th width="2%">{{ localize('global.actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(parameter, index) in selectedRegistration.lab_type.direct_lab_test_parameters" :key="parameter.id || index">
                                                        <td>
                                                            <span class="badge bg-info rounded-pill">{{ index + 1 }}</span>
                                                        </td>
                                                        <td><strong>{{ parameter.parameter_name || '—' }}</strong></td>
                                                        <td>{{ parameter.unit || '—' }}</td>
                                                        <td>{{ parameter.normal_range || '—' }}</td>
                                                        <td>
                                                            <span v-if="parameter.critical_low" class="badge bg-warning">
                                                                {{ parameter.critical_low }}
                                                            </span>
                                                            <span v-else>—</span>
                                                        </td>
                                                        <td>
                                                            <span v-if="parameter.critical_high" class="badge bg-warning">
                                                                {{ parameter.critical_high }}
                                                            </span>
                                                            <span v-else>—</span>
                                                        </td>
                                                        <td>
                                                            <span v-if="parameter.panic_low" class="badge bg-danger">
                                                                {{ parameter.panic_low }}
                                                            </span>
                                                            <span v-else>—</span>
                                                        </td>
                                                        <td>
                                                            <span v-if="parameter.panic_high" class="badge bg-danger">
                                                                {{ parameter.panic_high }}
                                                            </span>
                                                            <span v-else>—</span>
                                                        </td>
                                                        <td>
                                                            <span v-if="parameter.result" class="fw-bold text-primary">
                                                                {{ parameter.result }}
                                                            </span>
                                                            <span v-else class="text-muted">—</span>
                                                        </td>
                                                        <td>
                                                            <span v-if="parameter.result" class="badge bg-success">
                                                                {{ localize('global.completed') }}
                                                            </span>
                                                            <span v-else class="badge bg-secondary">
                                                                {{ localize('global.pending') }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button 
                                                                class="btn btn-outline-info btn-sm" 
                                                                @click="viewParameterDetails(parameter)"
                                                                :title="localize('global.view_details')"
                                                            >
                                                                <i class="bx bx-info-circle"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- Show message if no parameters -->
                                    <div v-else class="text-center py-4">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            {{ localize('global.no_parameters_found') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeParametersModal">
                            {{ localize('global.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parameter Details Modal -->
        <div v-if="showParameterDetailsModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ localize('global.parameter_details') }}</h5>
                        <button type="button" class="btn-close" @click="closeParameterDetailsModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedParameter">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">{{ localize('global.basic_information') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>{{ localize('global.parameter_name') }}:</strong>
                                                    <p>{{ selectedParameter.parameter_name }}</p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>{{ localize('global.unit') }}:</strong>
                                                    <p>{{ selectedParameter.unit || '—' }}</p>
                                                </div>
                                                <div class="col-12">
                                                    <strong>{{ localize('global.normal_range') }}:</strong>
                                                    <p>{{ selectedParameter.normal_range || '—' }}</p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>{{ localize('global.result') }}:</strong>
                                                    <p v-if="selectedParameter.result" class="fw-bold text-primary fs-5">
                                                        {{ selectedParameter.result }}
                                                    </p>
                                                    <p v-else class="text-muted">—</p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>{{ localize('global.status') }}:</strong>
                                                    <p>
                                                        <span v-if="selectedParameter.result" class="badge bg-success fs-6">
                                                            {{ localize('global.completed') }}
                                                        </span>
                                                        <span v-else class="badge bg-secondary fs-6">
                                                            {{ localize('global.pending') }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">Critical Values</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Critical Low:</strong>
                                                    <p>{{ selectedParameter.critical_low || '—' }}</p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Critical High:</strong>
                                                    <p>{{ selectedParameter.critical_high || '—' }}</p>
                                                </div>
                                                <div class="col-12">
                                                    <strong>Critical Comment:</strong>
                                                    <p>{{ selectedParameter.critical_comment || '—' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="mb-0">Panic Values</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Panic Low:</strong>
                                                    <p>{{ selectedParameter.panic_low || '—' }}</p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Panic High:</strong>
                                                    <p>{{ selectedParameter.panic_high || '—' }}</p>
                                                </div>
                                                <div class="col-12">
                                                    <strong>Panic Comment:</strong>
                                                    <p>{{ selectedParameter.panic_comment || '—' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">Additional Settings</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Delta Check:</strong>
                                                    <p>
                                                        <span v-if="selectedParameter.delta_check_enabled" class="badge bg-success">Enabled</span>
                                                        <span v-else class="badge bg-secondary">Disabled</span>
                                                    </p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Delta Threshold:</strong>
                                                    <p>{{ selectedParameter.delta_check_threshold || '—' }}</p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Verification Required:</strong>
                                                    <p>
                                                        <span v-if="selectedParameter.requires_verification" class="badge bg-warning">Yes</span>
                                                        <span v-else class="badge bg-secondary">No</span>
                                                    </p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Verification Level:</strong>
                                                    <p>{{ selectedParameter.verification_level || '—' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeParameterDetailsModal">
                            {{ localize('global.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
    components: {
        Multiselect
    },
    name: 'LabTestRegistrationSection',
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
        canAddTestRegistration: {
            type: Boolean,
            default: false
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
            showParametersModal: false,
            showParameterDetailsModal: false,
            testRegistrations: [],
            allLabTypes: [],
            selectedLabTypes: [],
            selectedRegistration: null,
            selectedParameter: null,
            form: {
                lab_type_ids: [],
                priority: 'normal',
                notes: ''
            }
        }
    },
    mounted() {
        this.loadAllLabTypes();
        this.loadTestRegistrations();
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        }
    },
    methods: {
        async loadAllLabTypes() {
            try {
                const response = await fetch('/lab-test-registration-ajax/all-lab-types', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.allLabTypes = data.data;
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError(this.localize('global.failed_to_load_lab_types'));
            }
        },

        async loadTestRegistrations() {
            try {
                const entityType = this.entityType;
                const entityId = this.entityId || this.appointment.id;

                const endpoint = `/lab-test-registration-ajax/registrations/${entityId}/${entityType}`;

                const response = await fetch(endpoint, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.testRegistrations = data.data;
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError(this.localize('global.failed_to_load_test_registrations'));
            }
        },

        async createTestRegistration() {
            if (!this.form.lab_type_ids.length) {
                this.showError(this.localize('global.please_select_at_least_one_lab_type'));
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('lab_type_ids', JSON.stringify(this.form.lab_type_ids));
                formData.append('priority', this.form.priority);
                formData.append('notes', this.form.notes);
                formData.append('entity_id', this.entityId);
                formData.append('entity_type', this.entityType);
                formData.append('patient_id', this.appointment.patient_id);
                formData.append('doctor_id', this.appointment.doctor_id);
                formData.append('branch_id', this.appointment.branch_id);

                const response = await fetch(`/lab-test-registration-ajax/store/${this.entityType}/${this.entityId}`, {
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

                if (data.success) {
                    this.showSuccess(data.message);
                    this.loadTestRegistrations();
                    this.resetForm();
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError(this.localize('global.failed_to_create_test_registration'));
            } finally {
                this.loading = false;
            }
        },

        async createTestRegistrationAndClose() {
            if (!this.form.lab_type_ids.length) {
                this.showError(this.localize('global.please_select_at_least_one_lab_type'));
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('lab_type_ids', JSON.stringify(this.form.lab_type_ids));
                formData.append('priority', this.form.priority);
                formData.append('notes', this.form.notes);
                formData.append('entity_id', this.entityId);
                formData.append('entity_type', this.entityType);
                formData.append('patient_id', this.appointment.patient_id);
                formData.append('doctor_id', this.appointment.doctor_id);
                formData.append('branch_id', this.appointment.branch_id);

                const response = await fetch(`/lab-test-registration-ajax/store/${this.entityType}/${this.entityId}`, {
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

                if (data.success) {
                    this.showSuccess(data.message);
                    this.closeCreateModal();
                    this.loadTestRegistrations();
                    this.resetForm();
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError(this.localize('global.failed_to_create_test_registration'));
            } finally {
                this.loading = false;
            }
        },

        async viewParameters(registrationId) {
            try {
                const response = await fetch(`/lab-test-registration-ajax/registration-parameters/${registrationId}`, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    // Ensure the registration data is properly structured
                    if (data.data) {
                        // Initialize lab_type if it doesn't exist
                        if (!data.data.lab_type) {
                            data.data.lab_type = {
                                name: '—',
                                direct_lab_test_parameters: []
                            };
                        }
                        // Initialize direct_lab_test_parameters if it doesn't exist
                        if (!data.data.lab_type.direct_lab_test_parameters) {
                            data.data.lab_type.direct_lab_test_parameters = [];
                        }
                        this.selectedRegistration = data.data;
                        this.showParametersModal = true;
                    } else {
                        this.showError('Registration data not found');
                    }
                } else {
                    this.showError(data.message || 'Failed to load registration parameters');
                }
            } catch (error) {
                console.error('Error loading registration parameters:', error);
                this.showError('Failed to load registration parameters');
            }
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.resetForm();
        },

        openCreateModal() {
            this.showCreateModal = true;
        },

        closeParametersModal() {
            this.showParametersModal = false;
            this.selectedRegistration = null;
        },

        resetForm() {
            this.form.lab_type_ids = [];
            this.form.priority = 'normal';
            this.form.notes = '';
            this.selectedLabTypes = [];
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fa-IR') + ' ' + date.toLocaleTimeString('fa-IR');
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

        getPriorityClass(priority) {
            const classes = {
                'normal': 'badge bg-secondary',
                'urgent': 'badge bg-warning',
                'stat': 'badge bg-danger'
            };
            return classes[priority] || 'badge bg-secondary';
        },

        getPriorityText(priority) {
            const texts = {
                'normal': this.localize('global.normal'),
                'urgent': this.localize('global.urgent'),
                'stat': this.localize('global.stat')
            };
            return texts[priority] || priority;
        },

        showSuccess(message) {
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else {
                // Fallback to alert if toastr is not available
                alert(message);
            }
        },

        showError(message) {
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                // Fallback to alert if toastr is not available
                alert(message);
            }
        },

        localize(key) {
            // Simple localization function - in real app this would use proper i18n
            const translations = {
                "global.registration_modal_info":"معلومات مدل ثبت نام آزمایش",
                'global.lab_test_registrations': 'معاینات',
                'global.add_lab_test_registration': 'اضافه کردن ثبت نام آزمایش',
                'global.test_category': 'دسته بندی آزمایش',
                'global.select_category': 'انتخاب دسته بندی',
                'global.test_name': 'نام آزمایش',
                'global.select_test': 'انتخاب آزمایش',
                'global.priority': 'اولویت',
                'global.normal': 'عادی',
                'global.urgent': 'فوری',
                'global.stat': 'فوری',
                'global.notes': 'یادداشت‌ها',
                'global.optional_notes': 'یادداشت‌های اختیاری...',
                'global.cancel': 'لغو',
                'global.create_and_continue': 'ایجاد و ادامه',
                'global.create_and_close': 'ایجاد و بستن',
                'global.number': 'شماره',
                'global.ref_number': 'شماره مرجع',
                'global.patient': 'بیمار',
                'global.status': 'وضعیت',
                'global.doctor': 'دکتر',
                'global.created_date': 'تاریخ ایجاد',
                'global.actions': 'عملیات',
                'global.view_test_parameters': 'مشاهده پارامترهای آزمایش',
                'global.no_test_registrations_found': 'هیچ ثبت نام آزمایشی یافت نشد',
                'global.test_parameters': 'پارامترهای آزمایش',
                'global.parameter_name': 'نام پارامتر',
                'global.unit': 'واحد',
                'global.normal_range': 'محدوده طبیعی',
                'global.no_parameters_found': 'هیچ پارامتری یافت نشد',
                'global.close': 'بستن',
                'global.success': 'موفقیت',
                'global.error': 'خطا',
                'global.confirm': 'تأیید',
                'global.status_pending': 'در انتظار',
                'global.status_in_progress': 'در حال انجام',
                'global.status_completed': 'تکمیل شده',
                'global.status_cancelled': 'لغو شده',
                'global.print_report': 'چاپ گزارش',
                'global.view_details': 'مشاهده جزئیات',
                'global.parameter_details': 'جزئیات پارامتر',
                'global.basic_information': 'اطلاعات پایه',
                'global.result': 'نتیجه',
                'global.completed': 'تکمیل شده',
                'global.pending': 'در انتظار',
                'global.lab_type': 'نوع آزمایش',
                'global.lab_type_section': 'بخش آزمایش',
                'global.test_type': 'نوع تست',
                'global.parametered': 'پارامتری',
                'global.text_based': 'متنی',
                'global.select_lab_types': 'انتخاب نوع آزمایش',
                'global.category': 'دسته بندی',
                'global.parameters_count': 'تعداد پارامترها',
                'global.parameters': 'پارامترها',
                'global.detailed_notes': 'یادداشت‌های تفصیلی',
                'global.metadata': 'اطلاعات اضافی',
                'global.select_multiple': 'انتخاب چندین',
                'global.note': 'یادداشت',
                'global.failed_to_load_lab_types': 'بارگذاری انواع آزمایش ناموفق بود',
                'global.failed_to_load_test_registrations': 'بارگذاری ثبت نام‌های آزمایش ناموفق بود',
                'global.please_select_at_least_one_lab_type': 'لطفاً حداقل یک نوع آزمایش انتخاب کنید',
                'global.failed_to_create_test_registration': 'ایجاد ثبت نام آزمایش ناموفق بود',
                'global.assigned_to': 'مسول',
                'global.assigned_section': 'بخش واگذار شده',
                'global.assigned_date': 'تاریخ واگذاری'
            };
            return translations[key] || key;
        },

        // Vue Multiselect event handlers
        onLabTypeSelect(selectedLabType) {
            if (!this.selectedLabTypes.find(labType => labType.id === selectedLabType.id)) {
                this.selectedLabTypes.push(selectedLabType);
            }
            this.form.lab_type_ids = this.selectedLabTypes.map(labType => labType.id);
        },

        onLabTypeRemove(removedLabType) {
            this.selectedLabTypes = this.selectedLabTypes.filter(labType => labType.id !== removedLabType.id);
            this.form.lab_type_ids = this.selectedLabTypes.map(labType => labType.id);
        },

        onLabTypeClear() {
            this.selectedLabTypes = [];
            this.form.lab_type_ids = [];
        },


        // Parameter details modal methods
        viewParameterDetails(parameter) {
            this.selectedParameter = parameter;
            this.showParameterDetailsModal = true;
        },

        closeParameterDetailsModal() {
            this.showParameterDetailsModal = false;
            this.selectedParameter = null;
        }
    }
}
</script>

<style scoped>
.lab-test-registration-section .modal {
    z-index: 9999 !important;
}

.lab-test-registration-section .modal.show {
    display: block !important;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Vue Multiselect Styles */
.multiselect {
    min-height: 38px;
}

.multiselect__tags {
    min-height: 38px;
    border: 1px solid #d9dee3;
    border-radius: 0.375rem;
}

.multiselect__tags:focus-within {
    border-color: #696cff;
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.multiselect__placeholder {
    color: #a1acb8;
    font-size: 0.875rem;
}

.multiselect__single {
    font-size: 0.875rem;
    color: #5f6e7f;
}

.multiselect__input {
    font-size: 0.875rem;
}

.multiselect__option {
    font-size: 0.875rem;
    padding: 8px 12px;
}

.multiselect__option--highlight {
    background: #696cff;
}

.multiselect__option--selected {
    background: #f8f9fa;
    color: #696cff;
    font-weight: 600;
}

.multiselect__option--selected.multiselect__option--highlight {
    background: #696cff;
    color: white;
}

/* Dark Mode Styles for Vue Multiselect */
.dark-style .multiselect__tags,
body[data-theme="dark"] .multiselect__tags,
html[data-theme="dark"] .multiselect__tags,
.theme-dark .multiselect__tags {
    background-color: #444564 !important;
    border-color: #444564 !important;
    color: #a3a4cc !important;
}

.dark-style .multiselect__tags:focus-within,
body[data-theme="dark"] .multiselect__tags:focus-within,
html[data-theme="dark"] .multiselect__tags:focus-within,
.theme-dark .multiselect__tags:focus-within {
    border-color: #696cff !important;
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25) !important;
}

.dark-style .multiselect__single,
body[data-theme="dark"] .multiselect__single,
html[data-theme="dark"] .multiselect__single,
.theme-dark .multiselect__single {
    color: #a3a4cc !important;
}

.dark-style .multiselect__placeholder,
body[data-theme="dark"] .multiselect__placeholder,
html[data-theme="dark"] .multiselect__placeholder,
.theme-dark .multiselect__placeholder {
    color: #7c7db6 !important;
}

.dark-style .multiselect__input,
body[data-theme="dark"] .multiselect__input,
html[data-theme="dark"] .multiselect__input,
.theme-dark .multiselect__input {
    background-color: transparent !important;
    color: #a3a4cc !important;
}

.dark-style .multiselect__input::placeholder,
body[data-theme="dark"] .multiselect__input::placeholder,
html[data-theme="dark"] .multiselect__input::placeholder,
.theme-dark .multiselect__input::placeholder {
    color: #7c7db6 !important;
}

.dark-style .multiselect__content-wrapper,
body[data-theme="dark"] .multiselect__content-wrapper,
html[data-theme="dark"] .multiselect__content-wrapper,
.theme-dark .multiselect__content-wrapper {
    background-color: #444564 !important;
    border-color: #444564 !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.3) !important;
}

.dark-style .multiselect__option,
body[data-theme="dark"] .multiselect__option,
html[data-theme="dark"] .multiselect__option,
.theme-dark .multiselect__option {
    color: #a3a4cc !important;
    background-color: #444564 !important;
}

.dark-style .multiselect__option:hover,
body[data-theme="dark"] .multiselect__option:hover,
html[data-theme="dark"] .multiselect__option:hover,
.theme-dark .multiselect__option:hover {
    background-color: #4a4b6b !important;
}

.dark-style .multiselect__option--highlight,
body[data-theme="dark"] .multiselect__option--highlight,
html[data-theme="dark"] .multiselect__option--highlight,
.theme-dark .multiselect__option--highlight {
    background-color: #696cff !important;
    color: white !important;
}

.dark-style .multiselect__option--selected,
body[data-theme="dark"] .multiselect__option--selected,
html[data-theme="dark"] .multiselect__option--selected,
.theme-dark .multiselect__option--selected {
    background-color: rgba(124, 125, 182, 0.16) !important;
    color: #a3a4cc !important;
}

.dark-style .multiselect__option--selected.multiselect__option--highlight,
body[data-theme="dark"] .multiselect__option--selected.multiselect__option--highlight,
html[data-theme="dark"] .multiselect__option--selected.multiselect__option--highlight,
.theme-dark .multiselect__option--selected.multiselect__option--highlight {
    background-color: #696cff !important;
    color: white !important;
}

/* HTML Content Styling */
.lab-test-registration-section .card-body div[v-html] {
    line-height: 1.6;
    direction: ltr;
    text-align: left;
}

.lab-test-registration-section .card-body div[v-html] p {
    margin-bottom: 0.5rem;
    direction: ltr;
    text-align: left;
}

.lab-test-registration-section .card-body div[v-html] p:last-child {
    margin-bottom: 0;
}

.lab-test-registration-section .card-body div[v-html] ul,
.lab-test-registration-section .card-body div[v-html] ol {
    margin-bottom: 0.5rem;
    padding-left: 1.5rem;
    direction: ltr;
    text-align: left;
}

.lab-test-registration-section .card-body div[v-html] strong {
    font-weight: 600;
}

.lab-test-registration-section .card-body div[v-html] em {
    font-style: italic;
}

/* LTR Notes Styling */
.lab-test-registration-section .card-body[dir="ltr"] {
    direction: ltr;
    text-align: left;
}

.lab-test-registration-section .card-body[dir="ltr"] div[v-html] {
    direction: ltr;
    text-align: left;
}
</style>
