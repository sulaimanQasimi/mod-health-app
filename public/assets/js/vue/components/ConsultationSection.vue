<template>
    <div class="consultation-section">
        <!-- Add Button and Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div> <!-- Empty div for spacing -->
            <div>
                <button 
                    v-if="!appointmentCompleted && canAddConsultation" 
                    type="button" 
                    class="btn btn-primary btn-sm" 
                    @click="showCreateModalHandler">
                     <i class="bx bx-plus me-1"></i>
                     اضافه کردن
                </button>
            </div>
        </div>
        
        <!-- Consultation Table -->
        <div v-if="consultations.length > 0" class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-body-secondary">
                    <tr>
                         <th>شماره</th>
                         <th>عنوان</th>
                         <th>بخش</th>
                         <th>نوع</th>
                         <th>تاریخ</th>
                         <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(consultation, index) in consultations" :key="consultation.id">
                        <td>
                            <span class="badge bg-primary rounded-pill">{{ index + 1 }}</span>
                        </td>
                        <td>{{ consultation.title }}</td>
                        <td>
                            <span 
                                v-for="department in consultation.associated_departments" 
                                :key="department.id"
                                class="badge bg-primary me-1">
                                {{ department.name }}
                            </span>
                        </td>
                        <td>
                            <span 
                                :class="consultation.consultation_type == 1 ? 'badge bg-danger' : 'badge bg-success'">
                                {{ consultation.consultation_type == 1 ? 'اضطراری' : 'عادی' }}
                            </span>
                        </td>
                        <td>{{ consultation.jalali_date || formatDate(consultation.date) }}</td>
                        <td>
                            <button 
                                v-if="canEditConsultation"
                                type="button" 
                                class="btn btn-outline-warning btn-sm" 
                                @click="editConsultation(consultation)"
                                title="Edit">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button 
                                v-if="canDeleteConsultation"
                                type="button" 
                                class="btn btn-outline-danger btn-sm ms-1" 
                                @click="deleteConsultation(consultation.id)"
                                title="Delete">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- No Consultations Message -->
        <div v-else class="text-center py-4">
             <div class="alert alert-info">
                 <i class="bx bx-info-circle me-2"></i>
                 هیچ مشاوره قبلی وجود ندارد
             </div>
        </div>

        <!-- Create Consultation Modal -->
        <div 
            v-if="showCreateModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">اضافه کردن مشاوره</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitConsultation">
                            <div class="form-group mb-3">
                                <label class="form-label">عنوان</label>
                                <input 
                                    v-model="form.title" 
                                    type="text" 
                                    class="form-control" 
                                    :class="{ 'is-invalid': getFieldError('title') }"
                                    placeholder="عنوان مشاوره" 
                                    required>
                                <div v-if="getFieldError('title')" class="invalid-feedback">
                                    {{ getFieldError('title') }}
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">شعبه</label>
                                <multiselect
                                    v-model="form.branch"
                                    :options="branches"
                                    :custom-label="branch => branch.name"
                                    :placeholder="'انتخاب کنید'"
                                    :allow-empty="false"
                                    :searchable="true"
                                    :close-on-select="true"
                                    :show-labels="false"
                                    :class="{ 'is-invalid': getFieldError('branch') }"
                                ></multiselect>
                                <div v-if="getFieldError('branch')" class="invalid-feedback d-block">
                                    {{ getFieldError('branch') }}
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">بخش</label>
                                <multiselect
                                    v-model="form.department_id"
                                    :options="departments"
                                    :custom-label="department => department.name"
                                    :placeholder="'انتخاب کنید'"
                                    :allow-empty="false"
                                    :searchable="true"
                                    :close-on-select="false"
                                    :multiple="true"
                                    :show-labels="false"
                                    :class="{ 'is-invalid': getFieldError('department_id') }"
                                ></multiselect>
                                <div v-if="getFieldError('department_id')" class="invalid-feedback d-block">
                                    {{ getFieldError('department_id') }}
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">نوع</label>
                                <multiselect
                                    v-model="form.consultation_type"
                                    :options="consultationTypes"
                                    :custom-label="type => type.name"
                                    :placeholder="'انتخاب کنید'"
                                    :allow-empty="false"
                                    :searchable="false"
                                    :close-on-select="true"
                                    :show-labels="false"
                                    :class="{ 'is-invalid': getFieldError('consultation_type') }"
                                ></multiselect>
                                <div v-if="getFieldError('consultation_type')" class="invalid-feedback d-block">
                                    {{ getFieldError('consultation_type') }}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">تاریخ</label>
                                        <input 
                                            v-model="form.date" 
                                            type="text" 
                                            class="form-control datepicker_dari pdp-el" 
                                            :class="{ 'is-invalid': getFieldError('date') }"
                                            placeholder="تاریخ را انتخاب کنید" 
                                            required>
                                        <div v-if="getFieldError('date')" class="invalid-feedback">
                                            {{ getFieldError('date') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">زمان</label>
                                        <input 
                                            v-model="form.time" 
                                            type="time" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': getFieldError('time') }"
                                            required>
                                        <div v-if="getFieldError('time')" class="invalid-feedback">
                                            {{ getFieldError('time') }}
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
                            @click="submitConsultation"
                            :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            ذخیره
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Consultation Modal -->
        <div 
            v-if="showEditModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">ویرایش مشاوره</h5>
                        <button type="button" class="btn-close" @click="closeEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="updateConsultation">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">عنوان</label>
                                        <input 
                                            v-model="editForm.title" 
                                            type="text" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': getFieldError('title') }"
                                            placeholder="عنوان مشاوره" 
                                            required>
                                        <div v-if="getFieldError('title')" class="invalid-feedback">
                                            {{ getFieldError('title') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">شعبه</label>
                                        <multiselect
                                            v-model="editForm.branch"
                                            :options="branches"
                                            :custom-label="branch => branch.name"
                                            placeholder="انتخاب شعبه"
                                            :allow-empty="false"
                                            :searchable="true"
                                            :close-on-select="true"
                                            :class="{ 'is-invalid': getFieldError('branch') }">
                                        </multiselect>
                                        <div v-if="getFieldError('branch')" class="invalid-feedback">
                                            {{ getFieldError('branch') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">بخش‌ها</label>
                                        <multiselect
                                            v-model="editForm.department_id"
                                            :options="departments"
                                            :custom-label="dept => dept.name"
                                            placeholder="انتخاب بخش‌ها"
                                            :allow-empty="false"
                                            :searchable="true"
                                            :close-on-select="false"
                                            :multiple="true"
                                            :class="{ 'is-invalid': getFieldError('department_id') }">
                                        </multiselect>
                                        <div v-if="getFieldError('department_id')" class="invalid-feedback">
                                            {{ getFieldError('department_id') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">نوع مشاوره</label>
                                        <multiselect
                                            v-model="editForm.consultation_type"
                                            :options="consultationTypes"
                                            :custom-label="type => type.name"
                                            placeholder="انتخاب کنید"
                                            :allow-empty="false"
                                            :searchable="false"
                                            :close-on-select="true"
                                            :class="{ 'is-invalid': getFieldError('consultation_type') }">
                                        </multiselect>
                                        <div v-if="getFieldError('consultation_type')" class="invalid-feedback">
                                            {{ getFieldError('consultation_type') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="closeEditModal">
                                    لغو
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-primary" 
                                    @click="updateConsultation"
                                    :disabled="loading">
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    به‌روزرسانی
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'ConsultationSection',
    components: {
        Multiselect
    },
    props: {
        appointment: {
            type: Object,
            required: true
        },
        canAddConsultation: {
            type: Boolean,
            default: false
        },
        canEditConsultation: {
            type: Boolean,
            default: false
        },
        canDeleteConsultation: {
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
            consultations: [],
            branches: [],
            departments: [],
            consultationTypes: [
                { id: 0, name: 'عادی' },
                { id: 1, name: 'اضطراری' }
            ],
            validationErrors: {},
            form: {
                title: '',
                branch: null,
                department_id: [],
                consultation_type: null,
                date: '',
                time: ''
            },
            showEditModal: false,
            editForm: {
                id: null,
                title: '',
                branch: null,
                department_id: [],
                consultation_type: null
            }
        }
    },
    mounted() {
        this.loadBranches();
        this.loadDepartments();
        this.loadAppointmentConsultations();
        
        // Initialize Persian datepicker
        this.$nextTick(() => {
            if (window.$ && window.$.fn.persianDatepicker) {
                const datepickerElement = $('.datepicker_dari');
                if (!datepickerElement.data('persianDatepicker')) {
                    datepickerElement.persianDatepicker({
                        'formatDate': 'YYYY-MM-DD',
                        'persianNumbers': true,
                        'showGregorianDate': false,
                        'selectedBefore': false,
                        'theme': 'default',
                        'alwaysShow': false,
                        'cellWidth': 45,
                        'cellHeight': 25,
                        'fontSize': 13
                    });
                    
                    // Bind change event to update Vue model
                    datepickerElement.on('change', (e) => {
                        this.form.date = $(e.target).val();
                    });
                }
            }
        });
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        }
    },
    methods: {
        async loadBranches() {
            try {
                const response = await fetch('/consultation-ajax/branches');
                const data = await response.json();
                if (data.success) {
                    this.branches = data.data;
                }
            } catch (error) {
                console.error('Error loading branches:', error);
            }
        },

        async loadDepartments() {
            try {
                const response = await fetch('/consultation-ajax/departments');
                const data = await response.json();
                if (data.success) {
                    this.departments = data.data;
                }
            } catch (error) {
                console.error('Error loading departments:', error);
            }
        },

        async loadAppointmentConsultations() {
            try {
                const response = await fetch(`/consultation-ajax/appointment-consultations/${this.appointment.id}`);
                const data = await response.json();
                if (data.success) {
                    this.consultations = data.data;
                }
            } catch (error) {
                console.error('Error loading consultations:', error);
            }
        },

        async submitConsultation() {
            this.loading = true;
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

                // Convert Persian date to Gregorian format
                let gregorianDate = this.form.date;
                if (this.form.date && typeof this.form.date === 'string') {
                    // If it's a Persian date string, convert it
                    try {
                        // Use moment-jalaali for conversion if available, otherwise use a simple conversion
                        if (window.moment && window.moment.jalaali) {
                            gregorianDate = window.moment.jalaali(this.form.date, 'jYYYY/jMM/jDD').format('YYYY-MM-DD');
                        } else {
                            // Fallback: assume the date is already in the correct format or convert manually
                            gregorianDate = this.form.date;
                        }
                    } catch (error) {
                        console.warn('Date conversion failed:', error);
                        gregorianDate = this.form.date;
                    }
                }

                // Transform form data
                const formData = {
                    appointment_id: this.appointment.id,
                    patient_id: this.appointment.patient_id,
                    branch_id: this.appointment.branch_id,
                    title: this.form.title,
                    branch: this.form.branch?.id || this.form.branch,
                    department_id: Array.isArray(this.form.department_id) 
                        ? this.form.department_id.map(dept => dept.id || dept)
                        : [this.form.department_id],
                    consultation_type: typeof this.form.consultation_type === 'object' ? this.form.consultation_type.id : this.form.consultation_type,
                    date: gregorianDate,
                    time: this.form.time
                };


                const response = await fetch('/consultation-ajax/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                if (data.success) {
                    this.showCreateModal = false;
                    this.loadAppointmentConsultations();
                    Swal.fire({
                        title: 'موفق',
                        text: 'مشاوره با موفقیت ایجاد شد',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: data.message || 'خطا در ایجاد مشاوره',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error creating consultation:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در ایجاد مشاوره',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            } finally {
                this.loading = false;
            }
        },

        async deleteConsultation(consultationId) {
            const result = await Swal.fire({
                title: 'حذف مشاوره',
                text: 'آیا مطمئن هستید که می‌خواهید این مشاوره را حذف کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'لغو'
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const response = await fetch(`/consultation-ajax/delete/${consultationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.loadAppointmentConsultations();
                    Swal.fire({
                        title: 'موفق',
                        text: 'مشاوره با موفقیت حذف شد',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: data.message || 'خطا در حذف مشاوره',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error deleting consultation:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در حذف مشاوره',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

        editConsultation(consultation) {
            try {
                // Populate edit form with consultation data
                this.editForm.id = consultation.id;
                this.editForm.title = consultation.title;
            
            // Date and time are not editable in edit form
            
            // Find and set the branch
            this.editForm.branch = this.branches.find(branch => branch.id === consultation.branch_id) || null;
            
            // Find and set the consultation type
            this.editForm.consultation_type = this.consultationTypes.find(type => type.id === consultation.consultation_type) || null;
            
            // Set departments from associated_departments
            this.editForm.department_id = consultation.associated_departments || [];
            
            // Show edit modal
            this.showEditModal = true;
            
            // No need for datepicker initialization since we're using HTML5 date input
            } catch (error) {
                console.error('Error in editConsultation:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در باز کردن فرم ویرایش',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
                // Still show the modal but with empty form
                this.showEditModal = true;
            }
        },

        closeEditModal() {
            this.showEditModal = false;
            this.clearValidationErrors();
            this.resetEditForm();
        },

        resetEditForm() {
            // Reset edit form data
            this.editForm.id = null;
            this.editForm.title = '';
            this.editForm.branch = null;
            this.editForm.department_id = [];
            this.editForm.consultation_type = null;
        },

        async updateConsultation() {
            this.loading = true;
            this.clearValidationErrors();

            try {
                // Validate form
                if (!this.validateEditForm()) {
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

                // Transform form data (date and time are not editable)
                const formData = {
                    title: this.editForm.title,
                    branch: this.editForm.branch?.id || this.editForm.branch,
                    department_id: Array.isArray(this.editForm.department_id) 
                        ? this.editForm.department_id.map(dept => dept.id || dept)
                        : [this.editForm.department_id],
                    consultation_type: typeof this.editForm.consultation_type === 'object' ? this.editForm.consultation_type.id : this.editForm.consultation_type
                };


                const response = await fetch(`/consultation-ajax/update/${this.editForm.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    this.closeEditModal();
                    this.loadAppointmentConsultations();
                    Swal.fire({
                        title: 'موفق',
                        text: 'مشاوره با موفقیت به‌روزرسانی شد',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    if (data.errors) {
                        this.validationErrors = data.errors;
                    }
                    Swal.fire({
                        title: 'خطا',
                        text: data.message || 'خطا در به‌روزرسانی مشاوره',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error updating consultation:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در به‌روزرسانی مشاوره',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            } finally {
                this.loading = false;
            }
        },

        validateEditForm() {
            this.clearValidationErrors();
            let hasErrors = false;

            if (!this.editForm.title.trim()) {
                this.setFieldError('title', 'عنوان الزامی است');
                hasErrors = true;
            }

            if (!this.editForm.branch) {
                this.setFieldError('branch', 'شعبه الزامی است');
                hasErrors = true;
            }

            if (!this.editForm.department_id || this.editForm.department_id.length === 0) {
                this.setFieldError('department_id', 'حداقل یک بخش انتخاب کنید');
                hasErrors = true;
            }

            if (!this.editForm.consultation_type) {
                this.setFieldError('consultation_type', 'نوع مشاوره الزامی است');
                hasErrors = true;
            }

            return !hasErrors;
        },

        showCreateModalHandler() {
            this.showCreateModal = true;
            this.$nextTick(() => {
                // Initialize Persian datepicker for the modal only if not already initialized
                if (window.$ && window.$.fn.persianDatepicker) {
                    const datepickerElement = $('.modal .datepicker_dari');
                    if (!datepickerElement.data('persianDatepicker')) {
                        datepickerElement.persianDatepicker({
                            'formatDate': 'YYYY-MM-DD',
                            'persianNumbers': true,
                            'showGregorianDate': false,
                            'selectedBefore': false,
                            'theme': 'default',
                            'alwaysShow': false,
                            'cellWidth': 45,
                            'cellHeight': 25,
                            'fontSize': 13
                        });
                        
                        // Bind change event to update Vue model
                        datepickerElement.on('change', (e) => {
                            this.form.date = $(e.target).val();
                        });
                    }
                }
            });
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.clearValidationErrors();
        },

        resetForm() {
            // Reset form data
            this.form.title = '';
            this.form.branch = null;
            this.form.department_id = [];
            this.form.consultation_type = null;
            this.form.date = '';
            this.form.time = '';
            this.clearValidationErrors();
            
            // Clear the datepicker value
            this.$nextTick(() => {
                if (window.$ && window.$.fn.persianDatepicker) {
                    $('.datepicker_dari').val('');
                }
            });
        },

        formatDate(dateString) {
            if (!dateString) return '';
            
            try {
                // Convert Gregorian date to Jalali (Persian) format
                if (window.moment && window.moment.jalaali) {
                    const gregorianDate = moment(dateString);
                    if (gregorianDate.isValid()) {
                        return gregorianDate.jalaali().format('jYYYY/jMM/jDD');
                    }
                }
                
                // Fallback to original format if conversion fails
                return new Date(dateString).toLocaleDateString();
            } catch (error) {
                console.warn('Date conversion failed:', error);
                return new Date(dateString).toLocaleDateString();
            }
        },


        getFieldError(fieldName) {
            return this.validationErrors[fieldName] || null;
        },

        setFieldError(fieldName, error) {
            if (error) {
                this.validationErrors[fieldName] = error;
            } else {
                delete this.validationErrors[fieldName];
            }
        },

        clearValidationErrors() {
            // Clear all validation errors by setting to empty object
            Object.keys(this.validationErrors).forEach(key => {
                delete this.validationErrors[key];
            });
        },

        validateForm() {
            this.clearValidationErrors();
            let hasErrors = false;

            // Validate title
            if (!this.form.title || this.form.title.trim() === '') {
                this.setFieldError('title', 'عنوان الزامی است');
                hasErrors = true;
            }

            // Validate branch
            if (!this.form.branch) {
                this.setFieldError('branch', 'شعبه الزامی است');
                hasErrors = true;
            }

            // Validate department
            if (!this.form.department_id || this.form.department_id.length === 0) {
                this.setFieldError('department_id', 'بخش الزامی است');
                hasErrors = true;
            }

            // Validate consultation type
            if (this.form.consultation_type === null || this.form.consultation_type === undefined) {
                this.setFieldError('consultation_type', 'نوع مشاوره الزامی است');
                hasErrors = true;
            }

            // Validate date
            if (!this.form.date || this.form.date.trim() === '') {
                this.setFieldError('date', 'تاریخ الزامی است');
                hasErrors = true;
            }

            // Validate time
            if (!this.form.time || this.form.time.trim() === '') {
                this.setFieldError('time', 'زمان الزامی است');
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

.consultation-section .table th {
    border-top: none;
}

.consultation-section .badge {
    font-size: 0.75em;
}

.consultation-section .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Vue Multiselect Styling */
.multiselect {
    min-height: 38px;
}

.multiselect__tags {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 38px;
    padding: 0.375rem 0.75rem;
}

.multiselect__tags:focus-within {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.multiselect__single {
    color: #212529;
    font-size: 1rem;
    line-height: 1.5;
    padding: 0;
    margin: 0;
}

.multiselect__placeholder {
    color: #6c757d;
    font-size: 1rem;
    line-height: 1.5;
    padding: 0;
    margin: 0;
}

.multiselect__content-wrapper {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-top: 0.125rem;
}

.multiselect__content {
    max-height: 200px;
}

.multiselect__option {
    color: #212529;
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

.multiselect__input {
    font-size: 1rem;
    line-height: 1.5;
    color: #212529;
}

.multiselect__input::placeholder {
    color: #6c757d;
}

/* Validation Error Styling */
.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.invalid-feedback.d-block {
    display: block !important;
}

.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Multiselect validation styling */
.multiselect.is-invalid .multiselect__tags {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.multiselect.is-invalid .multiselect__tags:focus-within {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>