<template>
    <div class="diagnosis-section">
        <!-- Add Button and Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div> <!-- Empty div for spacing -->
            <div>
                <button 
                    v-if="!appointmentCompleted && canAddDiagnosis" 
                    type="button" 
                    class="btn btn-primary btn-sm" 
                    @click="showCreateModal = true">
                     <i class="bx bx-plus me-1"></i>
                     اضافه کردن تشخیص
                </button>
            </div>
        </div>
        
        <!-- Diagnosis Table -->
        <div v-if="diagnoses.length > 0" class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-body-secondary">
                    <tr>
                         <th>شماره</th>
                         <th>توضیحات</th>
                         <th>نوع تشخیص</th>
                         <th>تاریخ ایجاد</th>
                         <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(diagnosis, index) in diagnoses" :key="diagnosis.id">
                        <td>
                            <span class="badge bg-primary rounded-pill">{{ index + 1 }}</span>
                        </td>
                        <td>{{ diagnosis.description }}</td>
                        <td>
                             <span 
                                 :class="diagnosis.type == '0' ? 'badge bg-warning text-dark' : 'badge bg-primary'">
                                 {{ diagnosis.type == '0' ? 'اولیه' : 'نهایی' }}
                             </span>
                        </td>
                        <td>{{ formatDate(diagnosis.created_at) }}</td>
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-outline-info btn-sm" 
                                @click="viewDiagnosis(diagnosis)"
                                title="مشاهده جزئیات">
                                <i class="bx bx-show"></i>
                            </button>
                            <button 
                                v-if="canEditDiagnosis"
                                type="button" 
                                class="btn btn-outline-warning btn-sm ms-1" 
                                @click="editDiagnosis(diagnosis)"
                                title="ویرایش">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button 
                                v-if="canDeleteDiagnosis"
                                type="button" 
                                class="btn btn-outline-danger btn-sm ms-1" 
                                @click="deleteDiagnosis(diagnosis.id)"
                                title="حذف">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- No Diagnoses Message -->
        <div v-else class="text-center py-4">
             <div class="alert alert-info">
                 <i class="bx bx-info-circle me-2"></i>
                 هیچ تشخیص قبلی وجود ندارد
             </div>
        </div>

        <!-- View Diagnosis Modal -->
        <div 
            v-if="showViewModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">جزئیات تشخیص</h5>
                        <button type="button" class="btn-close" @click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedDiagnosis">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>نوع تشخیص:</strong> 
                                    <span :class="selectedDiagnosis.type == '0' ? 'badge bg-warning text-dark' : 'badge bg-primary'">
                                        {{ selectedDiagnosis.type == '0' ? 'اولیه' : 'نهایی' }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>تاریخ ایجاد:</strong> {{ formatDate(selectedDiagnosis.created_at) }}
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>توضیحات:</strong>
                                <div class="mt-2 p-3 bg-light rounded">
                                    {{ selectedDiagnosis.description }}
                                </div>
                            </div>

                            <h6 class="mb-3">علائم حیاتی</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div v-if="selectedDiagnosis.bp" class="mb-2">
                                        <span class="badge bg-info me-2">فشار خون (BP):</span>
                                        <span>{{ selectedDiagnosis.bp }}</span>
                                    </div>
                                    <div v-if="selectedDiagnosis.pr" class="mb-2">
                                        <span class="badge bg-info me-2">نبض (PR):</span>
                                        <span>{{ selectedDiagnosis.pr }}</span>
                                    </div>
                                    <div v-if="selectedDiagnosis.weight" class="mb-2">
                                        <span class="badge bg-info me-2">وزن:</span>
                                        <span>{{ selectedDiagnosis.weight }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div v-if="selectedDiagnosis.t" class="mb-2">
                                        <span class="badge bg-info me-2">درجه حرارت (T):</span>
                                        <span>{{ selectedDiagnosis.t }}</span>
                                    </div>
                                    <div v-if="selectedDiagnosis.spo2" class="mb-2">
                                        <span class="badge bg-info me-2">اشباع اکسیژن (SpO2):</span>
                                        <span>{{ selectedDiagnosis.spo2 }}</span>
                                    </div>
                                    <div v-if="selectedDiagnosis.pain" class="mb-2">
                                        <span class="badge bg-info me-2">میزان درد:</span>
                                        <span>{{ selectedDiagnosis.pain }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="!selectedDiagnosis.bp && !selectedDiagnosis.pr && !selectedDiagnosis.weight && 
                                       !selectedDiagnosis.t && !selectedDiagnosis.spo2 && !selectedDiagnosis.pain" 
                                 class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                هیچ علائم حیاتی ثبت نشده است
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeViewModal">
                            بستن
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Diagnosis Modal -->
        <div 
            v-if="showCreateModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">{{ editingDiagnosis ? 'ویرایش تشخیص' : 'اضافه کردن تشخیص' }}</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitDiagnosis">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">نوع تشخیص</label>
                                        <multiselect
                                            v-model="form.type"
                                            :options="diagnosisTypeOptions"
                                            :searchable="false"
                                            :allow-empty="false"
                                            placeholder="انتخاب کنید"
                                            label="label"
                                            track-by="value"
                                            :class="{ 'is-invalid': errors.type }"
                                            required>
                                        </multiselect>
                                        <div v-if="errors.type" class="invalid-feedback">
                                            {{ errors.type }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">توضیحات</label>
                                        <textarea 
                                            v-model="form.description" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': errors.description }"
                                            rows="3" 
                                            placeholder="توضیحات تشخیص و بیماری‌ها"
                                            required></textarea>
                                        <div v-if="errors.description" class="invalid-feedback">
                                            {{ errors.description }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mt-3 mb-3">علائم حیاتی</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">فشار خون (BP)</label>
                                        <input 
                                            v-model="form.bp" 
                                            type="text" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': errors.bp }"
                                            placeholder="مثال: 120/80">
                                        <div v-if="errors.bp" class="invalid-feedback">
                                            {{ errors.bp }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">نبض (PR)</label>
                                        <input 
                                            v-model="form.pr" 
                                            type="text" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': errors.pr }"
                                            placeholder="مثال: 72">
                                        <div v-if="errors.pr" class="invalid-feedback">
                                            {{ errors.pr }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">وزن</label>
                                        <input 
                                            v-model="form.weight" 
                                            type="text" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': errors.weight }"
                                            placeholder="مثال: 70 کیلوگرم">
                                        <div v-if="errors.weight" class="invalid-feedback">
                                            {{ errors.weight }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">درجه حرارت (T)</label>
                                        <input 
                                            v-model="form.t" 
                                            type="text" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': errors.t }"
                                            placeholder="مثال: 36.5°C">
                                        <div v-if="errors.t" class="invalid-feedback">
                                            {{ errors.t }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">اشباع اکسیژن (SpO2)</label>
                                        <input 
                                            v-model="form.spo2" 
                                            type="text" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': errors.spo2 }"
                                            placeholder="مثال: 98%">
                                        <div v-if="errors.spo2" class="invalid-feedback">
                                            {{ errors.spo2 }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">میزان درد</label>
                                        <input 
                                            v-model="form.pain" 
                                            type="text" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': errors.pain }"
                                            placeholder="مثال: 3/10">
                                        <div v-if="errors.pain" class="invalid-feedback">
                                            {{ errors.pain }}
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
                            @click="submitDiagnosis"
                            :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ editingDiagnosis ? 'ویرایش' : 'ذخیره' }}
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
    name: 'DiagnosisSection',
    props: {
        appointment: {
            type: Object,
            required: true
        },
        canAddDiagnosis: {
            type: Boolean,
            default: false
        },
        canEditDiagnosis: {
            type: Boolean,
            default: false
        },
        canDeleteDiagnosis: {
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
            showViewModal: false,
            diagnoses: [],
            editingDiagnosis: null,
            selectedDiagnosis: null,
            errors: {},
            diagnosisTypeOptions: [
                { value: '0', label: 'اولیه' },
                { value: '1', label: 'نهایی' }
            ],
            form: {
                type: '',
                description: '',
                bp: '',
                pr: '',
                weight: '',
                t: '',
                spo2: '',
                pain: ''
            }
        }
    },
    mounted() {
        this.loadAppointmentDiagnoses();
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        }
    },
    methods: {
        async loadAppointmentDiagnoses() {
            try {
                const response = await fetch(`/diagnosis-ajax/appointment-diagnoses/${this.appointment.id}`);
                const data = await response.json();
                if (data.success) {
                    this.diagnoses = data.data;
                }
            } catch (error) {
                console.error('Error loading diagnoses:', error);
            }
        },

        async submitDiagnosis() {
            this.loading = true;
            this.clearErrors();
            
            try {
                // Validate form
                if (!this.validateForm()) {
                    this.loading = false;
                    return;
                }

                const formData = {
                    appointment_id: this.appointment.id,
                    patient_id: this.appointment.patient_id,
                    department_id: this.appointment.department_id || null,
                    type: this.form.type ? this.form.type.value : '',
                    description: this.form.description,
                    bp: this.form.bp,
                    pr: this.form.pr,
                    weight: this.form.weight,
                    t: this.form.t,
                    spo2: this.form.spo2,
                    pain: this.form.pain
                };

                const url = this.editingDiagnosis 
                    ? `/diagnosis-ajax/update/${this.editingDiagnosis.id}`
                    : '/diagnosis-ajax/store';
                
                const method = this.editingDiagnosis ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                if (data.success) {
                    this.showCreateModal = false;
                    this.loadAppointmentDiagnoses();
                    Swal.fire({
                        title: 'موفق',
                        text: this.editingDiagnosis ? 'تشخیص با موفقیت ویرایش شد' : 'تشخیص با موفقیت ایجاد شد',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                    Swal.fire({
                        title: 'خطا',
                        text: data.message || 'خطا در ذخیره تشخیص',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error saving diagnosis:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در ذخیره تشخیص',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            } finally {
                this.loading = false;
            }
        },

        editDiagnosis(diagnosis) {
            this.editingDiagnosis = diagnosis;
            this.form = {
                type: this.diagnosisTypeOptions.find(option => option.value === diagnosis.type) || '',
                description: diagnosis.description,
                bp: diagnosis.bp || '',
                pr: diagnosis.pr || '',
                weight: diagnosis.weight || '',
                t: diagnosis.t || '',
                spo2: diagnosis.spo2 || '',
                pain: diagnosis.pain || ''
            };
            this.showCreateModal = true;
        },

        async deleteDiagnosis(diagnosisId) {
            const result = await Swal.fire({
                title: 'حذف تشخیص',
                text: 'آیا مطمئن هستید که می‌خواهید این تشخیص را حذف کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'انصراف',
                reverseButtons: true
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const response = await fetch(`/diagnosis-ajax/delete/${diagnosisId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.loadAppointmentDiagnoses();
                    Swal.fire({
                        title: 'موفق',
                        text: 'تشخیص با موفقیت حذف شد',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: data.message || 'خطا در حذف تشخیص',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error deleting diagnosis:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در حذف تشخیص',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

        viewDiagnosis(diagnosis) {
            this.selectedDiagnosis = diagnosis;
            this.showViewModal = true;
        },

        closeViewModal() {
            this.showViewModal = false;
            this.selectedDiagnosis = null;
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.editingDiagnosis = null;
            this.clearErrors();
        },

        resetForm() {
            this.form = {
                type: '',
                description: '',
                bp: '',
                pr: '',
                weight: '',
                t: '',
                spo2: '',
                pain: ''
            };
            this.editingDiagnosis = null;
            this.clearErrors();
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fa-IR');
        },


        clearErrors() {
            this.errors = {};
        },

        validateForm() {
            this.clearErrors();
            let hasErrors = false;

            if (!this.form.type || !this.form.type.value) {
                this.errors.type = 'نوع تشخیص الزامی است';
                hasErrors = true;
            }

            if (!this.form.description || this.form.description.trim() === '') {
                this.errors.description = 'توضیحات تشخیص الزامی است';
                hasErrors = true;
            }

            return !hasErrors;
        }
    }
}
</script>

<style scoped>
.modal.show {
    display: block !important;
}

.diagnosis-section .table th {
    border-top: none;
}

.diagnosis-section .badge {
    font-size: 0.75em;
}

.diagnosis-section .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>

<style>
/* Vue Multiselect Styles */
.multiselect {
    min-height: 38px;
}

.multiselect__tags {
    min-height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem;
}

.multiselect__tags:focus-within {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.multiselect__placeholder {
    color: #6c757d;
    padding-top: 0.375rem;
    margin-bottom: 0;
}

.multiselect__single {
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
    margin-bottom: 0;
    line-height: 1.5;
}

.multiselect__input {
    padding: 0;
    margin: 0;
    border: none;
    background: transparent;
    font-size: 1rem;
    line-height: 1.5;
}

.multiselect__input:focus {
    outline: none;
    box-shadow: none;
}

.multiselect__content-wrapper {
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.multiselect__option {
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
}

.multiselect__option--highlight {
    background-color: #0d6efd;
    color: white;
}

.multiselect__option--selected {
    background-color: #e9ecef;
    color: #495057;
}

.multiselect__option--selected.multiselect__option--highlight {
    background-color: #0d6efd;
    color: white;
}

/* RTL Support */
[dir="rtl"] .multiselect__tags {
    text-align: right;
}

[dir="rtl"] .multiselect__placeholder {
    text-align: right;
}

[dir="rtl"] .multiselect__single {
    text-align: right;
}

[dir="rtl"] .multiselect__input {
    text-align: right;
}

/* Error state */
.multiselect.is-invalid .multiselect__tags {
    border-color: #dc3545;
}

.multiselect.is-invalid .multiselect__tags:focus-within {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}
</style>
