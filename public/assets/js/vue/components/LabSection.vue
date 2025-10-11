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
                            <input type="hidden" name="appointment_id" :value="appointment.id">
                            <input type="hidden" name="doctor_id" :value="appointment.doctor_id">
                            <input type="hidden" name="branch_id" :value="appointment.branch_id">

                            <div class="form-group mb-3">
                                <label for="lab_type_section">بخش آزمایش</label>
                                <select 
                                    v-model="form.lab_type_section_id" 
                                    class="form-control" 
                                    @change="loadLabTypes"
                                    required
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option 
                                        v-for="section in labTypeSections" 
                                        :key="section.id" 
                                        :value="section.id"
                                    >
                                        {{ section.section }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="lab_type_id">نوع آزمایش</label>
                                <select 
                                    v-model="form.lab_type_id" 
                                    class="form-control" 
                                    @change="loadLabTypeTests"
                                    required
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option 
                                        v-for="labType in labTypes" 
                                        :key="labType.id" 
                                        :value="labType.id"
                                    >
                                        {{ labType.name }}
                                    </option>
                                </select>
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

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeCreateModal">
                            لغو
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="createLabTest"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            ایجاد
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
export default {
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
        }
    },
    data() {
        return {
            loading: false,
            showCreateModal: false,
            showLabItemsModal: false,
            labs: [],
            labTypeSections: [],
            labTypes: [],
            labTypeTests: [],
            selectedLab: null,
            form: {
                lab_type_section_id: '',
                lab_type_id: '',
                selected_tests: []
            }
        }
    },
    mounted() {
        console.log('LabSection component mounted');
        this.loadLabTypeSections();
        this.loadAppointmentLabs();
    },
    watch: {
        showCreateModal(newVal) {
            console.log('showCreateModal changed to:', newVal);
        },
        labTypes(newVal) {
            console.log('labTypes changed to:', newVal);
        }
    },
    methods: {
        async loadLabTypeSections() {
            try {
                const response = await fetch('/lab-ajax/lab-type-sections', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.labTypeSections = data.data;
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                console.error('Error loading lab type sections:', error);
                this.showError('Failed to load lab type sections');
            }
        },

        async loadLabTypes() {
            if (!this.form.lab_type_section_id) {
                this.labTypes = [];
                this.labTypeTests = [];
                return;
            }

            try {
                const response = await fetch(`/lab-ajax/lab-types/${this.form.lab_type_section_id}`, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                console.log('Lab types response:', data);
                
                if (data.success) {
                    console.log('Setting labTypes to:', data.data);
                    this.labTypes = data.data;
                    console.log('labTypes after setting:', this.labTypes);
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                console.error('Error loading lab types:', error);
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
                console.error('Error loading lab type tests:', error);
                this.showError('Failed to load lab type tests');
            }
        },

        async loadAppointmentLabs() {
            try {
                const response = await fetch(`/lab-ajax/appointment-labs/${this.appointment.id}`, {
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
                console.error('Error loading appointment labs:', error);
                this.showError('Failed to load appointment labs');
            }
        },

        async createLabTest() {
            if (!this.form.lab_type_section_id || !this.form.lab_type_id) {
                this.showError('Please select lab type section and lab type');
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('lab_type_section_id', this.form.lab_type_section_id);
                formData.append('lab_type_id', this.form.lab_type_id);
                formData.append('appointment_id', this.appointment.id);
                formData.append('patient_id', this.appointment.patient_id);
                formData.append('doctor_id', this.appointment.doctor_id);
                formData.append('branch_id', this.appointment.branch_id);

                // Add selected tests if any
                if (this.form.selected_tests.length > 0) {
                    this.form.selected_tests.forEach(testId => {
                        formData.append('lab_type_id[]', testId);
                    });
                }

                const response = await fetch('/lab-ajax/store', {
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
                    this.loadAppointmentLabs();
                    this.resetForm();
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                console.error('Error creating lab test:', error);
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
                console.error('Error loading lab items:', error);
                this.showError('Failed to load lab items');
            }
        },

        async deleteLab(labId) {
            if (!confirm('Are you sure you want to delete this lab test?')) {
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
                    this.loadAppointmentLabs();
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                console.error('Error deleting lab test:', error);
                this.showError('Failed to delete lab test');
            }
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.resetForm();
        },

        openCreateModal() {
            console.log('Opening create modal');
            this.showCreateModal = true;
        },

        closeLabItemsModal() {
            this.showLabItemsModal = false;
            this.selectedLab = null;
        },

        resetForm() {
            this.form = {
                lab_type_section_id: '',
                lab_type_id: '',
                selected_tests: [],
                status: '0'
            };
            this.labTypes = [];
            this.labTypeTests = [];
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fa-IR') + ' ' + date.toLocaleTimeString('fa-IR');
        },

        showSuccess(message) {
            // You can implement your own success notification here
            alert('Success: ' + message);
        },

        showError(message) {
            // You can implement your own error notification here
            alert('Error: ' + message);
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
</style>
