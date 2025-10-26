<template>
    <div class="lab-section">
        <!-- Lab Section Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-body-secondary text-body d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-hard-hat me-2 text-warning"></i>
                            معاینات
                        </h5>
                        <button 
                            v-if="canAddLab && !appointmentCompleted" 
                            type="button" 
                            class="btn btn-primary btn-sm" 
                            @click="openCreateModal"
                        >
                            <i class="bx bx-plus me-1"></i>
                            اضافه کردن
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Lab Modal -->
        <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1055;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">اضافه کردن آزمایش</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="createLabTest">
                            <input type="hidden" name="patient_id" :value="appointment.patient_id">
                            <input type="hidden" name="doctor_id" :value="appointment.doctor_id">
                            <input type="hidden" name="branch_id" :value="appointment.branch_id">
                            <input type="hidden" name="entity_id" :value="entityId">
                            <input type="hidden" name="entity_type" :value="entityType">


                            <div class="form-group mb-3">
                                <label for="lab_type_id">نوع آزمایش</label>
                                <multiselect
                                    v-model="selectedLabType"
                                    :options="labTypes"
                                    :searchable="true"
                                    :close-on-select="true"
                                    :show-labels="false"
                                    placeholder="انتخاب نوع آزمایش"
                                    label="name"
                                    track-by="id"
                                    @select="onLabTypeSelect"
                                    @clear="onLabTypeClear"
                                >
                                </multiselect>
                            </div>

                            <div v-if="labTypeTests.length > 0" class="form-group mb-3">
                                <label>انتخاب آزمایشات</label>
                                <div class="row">
                                    <div 
                                        v-for="test in labTypeTests" 
                                        :key="test.id" 
                                        class="col-md-6 mb-2"
                                    >
                                        <div class="form-check">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                :value="test.id" 
                                                v-model="form.selected_tests"
                                                :id="'test_' + test.id"
                                            >
                                            <label class="form-check-label" :for="'test_' + test.id">
                                                {{ test.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info message about modal behavior -->
                            <div class="alert alert-info mt-3">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>نکته:</strong> پس از ایجاد آزمایش، مودال باز می‌ماند و فیلدها حفظ می‌شوند تا بتوانید آزمایشات مشابه اضافه کنید. برای بستن مودال از دکمه "لغو" یا "ایجاد و بستن" استفاده کنید.
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeCreateModal">
                            لغو
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-success" 
                            @click="createLabTest"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            ایجاد و ادامه
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="createLabTestAndClose"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            ایجاد و بستن
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lab Tests List -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div v-if="labs.length > 0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-body-secondary">
                                        <tr>
                                            <th>شماره</th>
                                            <th>بیمار</th>
                                            <th>نوع آزمایش</th>
                                            <th>وضعیت</th>
                                            <th>تاریخ ایجاد</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="lab in labs" :key="lab.id">
                                            <td>
                                                <span class="badge bg-warning rounded-pill">{{ labs.indexOf(lab) + 1 }}</span>
                                            </td>
                                            <td>{{ lab.patient.name }}</td>
                                            <td>{{ lab.lab_type.name }}</td>
                                            <td>
                                                <span 
                                                    :class="lab.status ? 'badge bg-success' : 'badge bg-danger'"
                                                >
                                                    {{ lab.status ? 'تکمیل شده' : 'آزمایش نشده' }}
                                                </span>
                                            </td>
                                            <td dir="ltr">{{ formatDate(lab.created_at) }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button 
                                                        class="btn btn-outline-primary btn-sm" 
                                                        @click="viewLabItems(lab.id)"
                                                        :title="'مشاهده'"
                                                    >
                                                        <i class="bx bx-expand"></i>
                                                    </button>
                                                    <button 
                                                        v-if="canEditLab" 
                                                        class="btn btn-outline-warning btn-sm" 
                                                        @click="editLab(lab)"
                                                        :title="'ویرایش'"
                                                    >
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <button 
                                                        v-if="canDeleteLab" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        @click="deleteLab(lab.id)"
                                                        :title="'حذف'"
                                                    >
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                هیچ آزمایش قبلی وجود ندارد
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lab Items Modal -->
        <div v-if="showLabItemsModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">آیتم های آزمایش</h5>
                        <button type="button" class="btn-close" @click="closeLabItemsModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedLab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body small mb-1">بیمار</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-user me-1 text-primary"></i>
                                            {{ selectedLab.patient.name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">دکتر</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-user-check me-1 text-primary"></i>
                                            {{ selectedLab.doctor.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">بخش</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-category me-1 text-primary"></i>
                                            {{ selectedLab.lab_type_section.section }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">وضعیت</div>
                                        <div class="fw-bold">
                                            <span :class="selectedLab.status ? 'badge bg-success' : 'badge bg-danger'">
                                                {{ selectedLab.status ? 'تکمیل شده' : 'آزمایش نشده' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="selectedLab.lab_items && selectedLab.lab_items.length > 0">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-body-secondary text-body">
                                        <h5 class="mb-0 text-center">
                                            <i class="bx bx-list-ul me-2 text-info"></i>
                                            آیتم های آزمایش
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-body-secondary">
                                                    <tr>
                                                        <th>شماره</th>
                                                        <th>نام آزمایش</th>
                                                        <th>وضعیت</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="item in selectedLab.lab_items" :key="item.id">
                                                        <td>
                                                            <span class="badge bg-info rounded-pill">{{ selectedLab.lab_items.indexOf(item) + 1 }}</span>
                                                        </td>
                                                        <td>{{ item.lab_type.name }}</td>
                                                        <td>
                                                            <span :class="item.status ? 'badge bg-success' : 'badge bg-danger'">
                                                                {{ item.status ? 'تکمیل شده' : 'آزمایش نشده' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeLabItemsModal">
                            بستن
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
    name: 'LabSection',
    props: {
        appointment: {
            type: Object,
            required: true
        },
        canAddLab: {
            type: Boolean,
            default: false
        },
        canEditLab: {
            type: Boolean,
            default: false
        },
        canDeleteLab: {
            type: Boolean,
            default: false
        },
        appointmentCompleted: {
            type: Boolean,
            default: false
        },
        entityType: {
            type: String,
            default: 'appointment'
        },
        entityId: {
            type: [String, Number],
            default: null
        }
    },
    data() {
        return {
            loading: false,
            showCreateModal: false,
            showLabItemsModal: false,
            labs: [],
            labTypes: [],
            labTypeTests: [],
            selectedLab: null,
            selectedLabType: null,
            form: {
                lab_type_id: '',
                selected_tests: []
            }
        }
    },
    computed: {
        isICUContext() {
            // Check if this is an ICU context (has ICU-specific properties)
            return this.appointment.is_discharged !== undefined || this.appointment.icu_enterance_note !== undefined;
        }
    },
    mounted() {
        this.loadLabTypes();
        this.loadEntityLabs();
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                // Reset form when modal closes
                this.resetForm();
            }
        }
    },
    methods: {

        async loadLabTypes() {
            try {
                const response = await fetch('/api/select/lab-types', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.labTypes = data.data;
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError('Failed to load lab types');
            }
        },

        async loadLabTypeTests() {
            if (!this.form.lab_type_id) {
                this.labTypeTests = [];
                return;
            }

            try {
                const response = await fetch(`/lab-ajax/lab-type-tests/${this.form.lab_type_id}`, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.labTypeTests = data.data;
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError('Failed to load lab type tests');
            }
        },

        async loadEntityLabs() {
            try {
                // Use the provided entity type and ID
                const entityType = this.entityType;
                const entityId = this.entityId || this.appointment.id;

                // Use the unified endpoint
                const endpoint = `/lab-ajax/labs/${entityId}/${entityType}`;

                const response = await fetch(endpoint, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.labs = data.data;
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError('Failed to load labs');
            }
        },

        // Keep the old method for backward compatibility
        async loadAppointmentLabs() {
            return this.loadEntityLabs();
        },

        async createLabTest() {
            if (!this.form.lab_type_section_id || !this.form.lab_type_id) {
                this.showError('Please select lab type section and lab type');
                return;
            }

            // Check if at least one test is selected (main lab type or additional tests)
            if (this.form.selected_tests.length === 0 && !this.form.lab_type_id) {
                this.showError('Please select at least one lab test');
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('lab_type_section_id', this.form.lab_type_section_id);
                
                // Pass entity ID and type to controller - let controller handle the logic
                formData.append('entity_id', this.entityId);
                formData.append('entity_type', this.entityType);
                
                formData.append('patient_id', this.appointment.patient_id);
                formData.append('doctor_id', this.appointment.doctor_id);
                formData.append('branch_id', this.appointment.branch_id);

                // Add the main lab type and selected tests as lab_type_id array
                const labTypeIds = [this.form.lab_type_id];
                if (this.form.selected_tests.length > 0) {
                    labTypeIds.push(...this.form.selected_tests);
                }
                
                // Send all lab type IDs as array
                labTypeIds.forEach(labTypeId => {
                    formData.append('lab_type_id[]', labTypeId);
                });

                const response = await fetch(`/lab-ajax/store/${this.entityType}/${this.entityId}`, {
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
                    this.showSuccess(data.message + ' - Modal will stay open for adding more labs');
                    // Don't close the modal - keep it open for adding more labs
                    // this.closeCreateModal();
                    this.loadEntityLabs();
                    // Only reset selected tests, keep other fields
                    this.form.selected_tests = [];
                    this.labTypeTests = [];
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError('Failed to create lab test');
            } finally {
                this.loading = false;
            }
        },

        async createLabTestAndClose() {
            if (!this.form.lab_type_section_id || !this.form.lab_type_id) {
                this.showError('Please select lab type section and lab type');
                return;
            }

            // Check if at least one test is selected (main lab type or additional tests)
            if (this.form.selected_tests.length === 0 && !this.form.lab_type_id) {
                this.showError('Please select at least one lab test');
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('lab_type_section_id', this.form.lab_type_section_id);
                
                // Pass entity ID and type to controller - let controller handle the logic
                formData.append('entity_id', this.entityId);
                formData.append('entity_type', this.entityType);
                
                formData.append('patient_id', this.appointment.patient_id);
                formData.append('doctor_id', this.appointment.doctor_id);
                formData.append('branch_id', this.appointment.branch_id);

                // Add the main lab type and selected tests as lab_type_id array
                const labTypeIds = [this.form.lab_type_id];
                if (this.form.selected_tests.length > 0) {
                    labTypeIds.push(...this.form.selected_tests);
                }
                
                // Send all lab type IDs as array
                labTypeIds.forEach(labTypeId => {
                    formData.append('lab_type_id[]', labTypeId);
                });

                const response = await fetch(`/lab-ajax/store/${this.entityType}/${this.entityId}`, {
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
                    this.loadEntityLabs();
                    this.resetForm();
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError('Failed to create lab test');
            } finally {
                this.loading = false;
            }
        },

        async viewLabItems(labId) {
            try {
                const response = await fetch(`/lab-ajax/lab-items/${labId}`, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.selectedLab = data.data;
                    this.showLabItemsModal = true;
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError('Failed to load lab items');
            }
        },

        async deleteLab(labId) {
            const result = await Swal.fire({
                title: 'حذف آزمایش',
                text: 'آیا از حذف این آزمایش اطمینان دارید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'لغو',
                customClass: { 
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const response = await fetch(`/lab-ajax/delete/${labId}`, {
                    method: 'DELETE',
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
                    this.loadEntityLabs();
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError('Failed to delete lab test');
            }
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.resetForm();
        },

        openCreateModal() {
            this.showCreateModal = true;
        },

        closeLabItemsModal() {
            this.showLabItemsModal = false;
            this.selectedLab = null;
        },

        resetForm() {
            // Reset all form fields
            this.form.lab_type_id = '';
            this.form.selected_tests = [];
            this.selectedLabType = null;
            this.labTypes = [];
            this.labTypeTests = [];
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fa-IR') + ' ' + date.toLocaleTimeString('fa-IR');
        },

        showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'موفقیت',
                text: message,
                customClass: { confirmButton: 'btn btn-success' },
                buttonsStyling: false,
                confirmButtonText: 'تأیید'
            });
        },

        showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: message,
                customClass: { confirmButton: 'btn btn-danger' },
                buttonsStyling: false,
                confirmButtonText: 'تأیید'
            });
        },


        // Vue Multiselect event handlers
        onSectionSelect(selectedSection) {
            this.form.lab_type_section_id = selectedSection.id;
            this.loadLabTypes();
        },

        onSectionClear() {
            this.form.lab_type_section_id = '';
            this.selectedLabType = null;
            this.form.lab_type_id = '';
            this.labTypes = [];
            this.labTypeTests = [];
        },

        onLabTypeSelect(selectedLabType) {
            this.form.lab_type_id = selectedLabType.id;
            this.loadLabTypeTests();
        },

        onLabTypeClear() {
            this.form.lab_type_id = '';
            this.labTypeTests = [];
        }
    }
}
</script>

<style scoped>
.lab-section .modal {
    z-index: 1055;
}

.lab-section .modal.show {
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
</style>
