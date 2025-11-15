<template>
    <div class="advice-section">
        <!-- Add Button and Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div> <!-- Empty div for spacing -->
            <div>
                <button 
                    v-if="!appointmentCompleted && canAddAdvice" 
                    type="button" 
                    class="btn btn-primary btn-sm" 
                    @click="showCreateModal = true">
                     <i class="bx bx-plus me-1"></i>
                     اضافه کردن
                </button>
            </div>
        </div>
        
        <!-- Advice Table -->
        <div v-if="advices.length > 0" class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-body-secondary">
                    <tr>
                         <th>شماره</th>
                         <th>توضیحات</th>
                         <th>توسط</th>
                         <th>تاریخ ایجاد</th>
                         <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(advice, index) in advices" :key="advice.id">
                        <td>
                            <span class="badge bg-info rounded-pill">{{ index + 1 }}</span>
                        </td>
                        <td>{{ advice.description }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ advice.doctor?.name || 'N/A' }}</span>
                        </td>
                        <td>{{ formatDate(advice.created_at) }}</td>
                        <td>
                            <button 
                                v-if="canEditAdvice"
                                type="button" 
                                class="btn btn-outline-primary btn-sm" 
                                @click="editAdvice(advice)"
                                title="ویرایش">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button 
                                v-if="canDeleteAdvice"
                                type="button" 
                                class="btn btn-outline-danger btn-sm ms-1" 
                                @click="deleteAdvice(advice.id)"
                                title="حذف">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- No Advices Message -->
        <div v-else class="text-center py-4">
             <div class="alert alert-info">
                 <i class="bx bx-info-circle me-2"></i>
                 هیچ توصیه قبلی وجود ندارد
             </div>
        </div>

        <!-- Create/Edit Advice Modal -->
        <div 
            v-if="showCreateModal || showEditModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">{{ isEditing ? 'ویرایش توصیه' : 'اضافه کردن توصیه' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitAdvice">
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">توضیحات</label>
                                <textarea 
                                    v-model="form.description" 
                                    class="form-control" 
                                    :class="{ 'is-invalid': errors.description }"
                                    id="description"
                                    rows="4" 
                                    placeholder="توضیحات توصیه را وارد کنید..."
                                    required></textarea>
                                <div v-if="errors.description" class="invalid-feedback">
                                    {{ errors.description }}
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">
                            لغو
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="submitAdvice"
                            :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ isEditing ? 'ویرایش' : 'ذخیره' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdviceSection',
    props: {
        appointment: {
            type: Object,
            required: true
        },
        canAddAdvice: {
            type: Boolean,
            default: false
        },
        canEditAdvice: {
            type: Boolean,
            default: false
        },
        canDeleteAdvice: {
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
            showEditModal: false,
            isEditing: false,
            advices: [],
            editingAdvice: null,
            validationErrors: {},
            form: {
                description: ''
            },
            errors: {}
        }
    },
    mounted() {
        this.loadAppointmentAdvices();
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        },
        showEditModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        }
    },
    methods: {
        async loadAppointmentAdvices() {
            try {
                const response = await fetch(`/advice-ajax/appointment-advices/${this.appointment.id}`);
                const data = await response.json();
                if (data.success) {
                    this.advices = data.data;
                }
            } catch (error) {
                console.error('Error loading advices:', error);
            }
        },

        async submitAdvice() {
            this.loading = true;
            this.clearErrors();
            
            try {
                // Validate form before submission
                if (!this.validateForm()) {
                    Swal.fire({
                        title: 'خطا در اعتبارسنجی',
                        text: 'لطفاً خطاهای فرم را برطرف کنید',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    this.loading = false;
                    return;
                }

                const formData = {
                    description: this.form.description,
                    appointment_id: this.appointment.id,
                    patient_id: this.appointment.patient_id
                    // doctor_id is now fetched from appointment on the backend
                };

                const url = this.isEditing 
                    ? `/advice-ajax/update/${this.editingAdvice.id}`
                    : '/advice-ajax/store';
                
                const method = this.isEditing ? 'PUT' : 'POST';

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
                    this.closeModal();
                    this.loadAppointmentAdvices();
                    Swal.fire({
                        title: 'موفق',
                        text: this.isEditing ? 'توصیه با موفقیت ویرایش شد' : 'توصیه با موفقیت ایجاد شد',
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
                        text: data.message || 'خطا در ذخیره توصیه',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error saving advice:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در ذخیره توصیه',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            } finally {
                this.loading = false;
            }
        },

        editAdvice(advice) {
            this.editingAdvice = advice;
            this.form.description = advice.description;
            this.isEditing = true;
            this.showEditModal = true;
        },

        async deleteAdvice(adviceId) {
            const result = await Swal.fire({
                title: 'حذف توصیه',
                text: 'آیا مطمئن هستید که می‌خواهید این توصیه را حذف کنید؟',
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
                const response = await fetch(`/advice-ajax/delete/${adviceId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.loadAppointmentAdvices();
                    Swal.fire({
                        title: 'موفق',
                        text: 'توصیه با موفقیت حذف شد',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: data.message || 'خطا در حذف توصیه',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error deleting advice:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در حذف توصیه',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

        closeModal() {
            this.showCreateModal = false;
            this.showEditModal = false;
            this.isEditing = false;
            this.editingAdvice = null;
            this.resetForm();
        },

        resetForm() {
            this.form = {
                description: ''
            };
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

            if (!this.form.description || this.form.description.trim() === '') {
                this.errors.description = 'توضیحات الزامی است';
                hasErrors = true;
            } else if (this.form.description.length > 1000) {
                this.errors.description = 'توضیحات نمی‌تواند بیش از 1000 کاراکتر باشد';
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

.advice-section .table th {
    border-top: none;
}

.advice-section .badge {
    font-size: 0.75em;
}

.advice-section .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Validation Error Styling */
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
