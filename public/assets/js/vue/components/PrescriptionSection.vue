<template>
    <div class="prescription-section">
        <!-- Toast notifications -->
        <div v-if="toast.show" :class="['toast', 'show', 'position-fixed', 'top-0', 'end-0', 'p-3', toast.type === 'success' ? 'bg-success' : 'bg-danger']" style="z-index: 1055;">
            <div class="toast-body text-white">
                {{ toast.message }}
            </div>
        </div>

        <!-- Add Button and Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div> <!-- Empty div for spacing -->
            <div>
                <button 
                    v-if="!appointmentCompleted && canAddPrescription" 
                    type="button" 
                    class="btn btn-primary btn-sm" 
                    @click="showCreateModal = true">
                     <i class="bx bx-plus me-1"></i>
                     اضافه کردن
                </button>
            </div>
        </div>
        
        <!-- Prescription Table -->
        <div v-if="prescriptions.length > 0" class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-body-secondary">
                    <tr>
                         <th>شماره</th>
                         <th>نام بیمار</th>
                         <th>وضعیت</th>
                         <th>تاریخ ایجاد</th>
                         <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(prescription, index) in prescriptions" :key="prescription.id">
                        <td>
                            <span class="badge bg-success rounded-pill">{{ index + 1 }}</span>
                        </td>
                        <td>{{ prescription.patient?.name || 'N/A' }}</td>
                        <td>
                             <span 
                                 :class="prescription.is_completed ? 'badge bg-success' : 'badge bg-danger'">
                                 {{ prescription.is_completed ? 'تحویل شده' : 'تحویل نشده' }}
                             </span>
                        </td>
                        <td>{{ formatDate(prescription.created_at) }}</td>
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-outline-primary btn-sm" 
                                @click="viewPrescriptionItems(prescription.id)"
                                title="View Details">
                                 <i class="bx bx-expand me-1"></i>
                                 مشاهده
                            </button>
                            <button 
                                v-if="canEditPrescription"
                                type="button" 
                                class="btn btn-outline-warning btn-sm ms-1" 
                                @click="editPrescription(prescription)"
                                title="Edit">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button 
                                v-if="canDeletePrescription"
                                type="button" 
                                class="btn btn-outline-danger btn-sm ms-1" 
                                @click="deletePrescription(prescription.id)"
                                title="Delete">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- No Prescriptions Message -->
        <div v-else class="text-center py-4">
             <div class="alert alert-info">
                 <i class="bx bx-info-circle me-2"></i>
                 هیچ نسخه قبلی وجود ندارد
             </div>
        </div>

        <!-- Create Prescription Modal -->
        <div 
            v-if="showCreateModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">اضافه کردن نسخه</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitPrescription">
                            <!-- Pharmacy Selection -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">داروخانه</label>
                                    <multiselect
                                        v-model="form.pharmacy_id"
                                        :options="allPharmacies"
                                        :custom-label="pharmacy => pharmacy.name"
                                        :placeholder="'انتخاب داروخانه'"
                                        :allow-empty="true"
                                        :searchable="true"
                                        :close-on-select="true"
                                        :show-labels="false"
                                        :class="{ 'is-invalid': validationErrors.pharmacy_id }"
                                    ></multiselect>
                                    <div v-if="validationErrors.pharmacy_id" class="invalid-feedback d-block">
                                        {{ validationErrors.pharmacy_id }}
                                    </div>
                                </div>
                            </div>

                            <!-- Prescription Items Container -->
                            <div id="prescription-items">
                                <div 
                                    v-for="(item, index) in form.prescription_items" 
                                    :key="index" 
                                    class="row mb-3">
                                     <div class="col-md-2">
                                         <label class="form-label">نام دارو</label>
                                         <multiselect
                                             v-model="item.medicine_id"
                                             :options="allMedicines"
                                             :custom-label="medicine => medicine.name"
                                             :placeholder="'انتخاب کنید'"
                                             :allow-empty="false"
                                             :searchable="true"
                                             :close-on-select="true"
                                             :show-labels="false"
                                             :class="{ 'is-invalid': getFieldError(index, 'medicine_id') }"
                                         ></multiselect>
                                         <div v-if="getFieldError(index, 'medicine_id')" class="invalid-feedback d-block">
                                             {{ getFieldError(index, 'medicine_id') }}
                                         </div>
                                     </div>
                                     <div class="col-md-2">
                                         <label class="form-label">نوع مصرف</label>
                                         <multiselect
                                             v-model="item.usage_type_id"
                                             :options="medicineUsageTypes"
                                             :custom-label="usageType => usageType.name"
                                             :placeholder="'انتخاب کنید'"
                                             :allow-empty="false"
                                             :searchable="true"
                                             :close-on-select="true"
                                             :show-labels="false"
                                             :class="{ 'is-invalid': getFieldError(index, 'usage_type_id') }"
                                         ></multiselect>
                                         <div v-if="getFieldError(index, 'usage_type_id')" class="invalid-feedback d-block">
                                             {{ getFieldError(index, 'usage_type_id') }}
                                         </div>
                                     </div>
                                    <div class="col-md-2">
                                        <label class="form-label">دوز</label>
                                        <input 
                                            v-model="item.dosage" 
                                            type="number" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': getFieldError(index, 'dosage') }"
                                            :placeholder="'دوز'" 
                                            required>
                                        <div v-if="getFieldError(index, 'dosage')" class="invalid-feedback">
                                            {{ getFieldError(index, 'dosage') }}
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">تکرار</label>
                                        <input 
                                            v-model="item.frequency" 
                                            type="text" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': getFieldError(index, 'frequency') }"
                                            :placeholder="'مثال: هر 8 ساعت، دوبار در روز'" 
                                            required>
                                        <div v-if="getFieldError(index, 'frequency')" class="invalid-feedback">
                                            {{ getFieldError(index, 'frequency') }}
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">مقدار</label>
                                        <input 
                                            v-model="item.amount" 
                                            type="number" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': getFieldError(index, 'amount') }"
                                            :placeholder="'مقدار'" 
                                            required>
                                        <div v-if="getFieldError(index, 'amount')" class="invalid-feedback">
                                            {{ getFieldError(index, 'amount') }}
                                        </div>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button 
                                            v-if="form.prescription_items.length > 1"
                                            type="button" 
                                            class="btn btn-danger btn-sm" 
                                            @click="removePrescriptionItem(index)"
                                            title="حذف آیتم">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button 
                                type="button" 
                                class="btn btn-primary mt-2" 
                                @click="addPrescriptionItem">
                                 <i class="bx bx-plus"></i>
                                 اضافه کردن آیتم نسخه
                            </button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeCreateModal">
                            لغو
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="submitPrescription"
                            :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            ذخیره
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prescription Items Modal -->
        <div 
            v-if="showPrescriptionItemsModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">جزئیات نسخه</h5>
                        <button type="button" class="btn-close" @click="closePrescriptionItemsModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedPrescription">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>نام بیمار:</strong> {{ selectedPrescription.patient?.name }}
                                </div>
                                <div class="col-md-6">
                                    <strong>دکتر:</strong> {{ selectedPrescription.doctor?.name }}
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                             <th>نام دارو</th>
                                             <th>نوع مصرف</th>
                                             <th>دوز</th>
                                             <th>تکرار</th>
                                             <th>مقدار</th>
                                             <th>وضعیت</th>
                                             <th v-if="canEditPrescription">عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in selectedPrescription.prescription_items" :key="item.id">
                                            <td>{{ item.medicine?.name }}</td>
                                            <td>{{ item.usage_type?.name }}</td>
                                            <td>{{ item.dosage }}</td>
                                            <td>{{ item.frequency }}</td>
                                            <td>{{ item.amount }}</td>
                                            <td>
                                                 <span 
                                                     :class="item.is_delivered ? 'badge bg-success' : 'badge bg-danger'">
                                                     {{ item.is_delivered ? 'تحویل شده' : 'تحویل نشده' }}
                                                 </span>
                                            </td>
                                            <td v-if="canEditPrescription">
                                                <div class="d-flex gap-1">
                                                    <button 
                                                        v-if="!item.is_delivered"
                                                        type="button" 
                                                        class="btn btn-success btn-sm" 
                                                        @click="markAsDelivered(item.id)"
                                                        title="تحویل شده">
                                                        <i class="bx bx-check"></i>
                                                    </button>
                                                    <button 
                                                        v-else
                                                        type="button" 
                                                        class="btn btn-warning btn-sm" 
                                                        @click="markAsNotDelivered(item.id)"
                                                        title="تحویل نشده">
                                                        <i class="bx bx-x"></i>
                                                    </button>
                                                    <button 
                                                        v-if="!item.is_delivered"
                                                        type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        @click="deletePrescriptionItem(item.id)"
                                                        title="حذف آیتم">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closePrescriptionItemsModal">
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
    name: 'PrescriptionSection',
    components: {
        Multiselect
    },
    props: {
        appointment: {
            type: Object,
            required: false
        },
        icu: {
            type: Object,
            required: false
        },
        hospitalization: {
            type: Object,
            required: false
        },
        operation: {
            type: Object,
            required: false
        },
        underReviewId: {
            type: [String, Number],
            required: false,
            default: null
        },
        canAddPrescription: {
            type: Boolean,
            default: false
        },
        canEditPrescription: {
            type: Boolean,
            default: false
        },
        canDeletePrescription: {
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
            showPrescriptionItemsModal: false,
            prescriptions: [],
            medicineUsageTypes: [],
            allMedicines: [],
            allPharmacies: [],
            selectedPrescription: null,
            validationErrors: {},
            toast: {
                show: false,
                message: '',
                type: 'success'
            },
             form: {
                 pharmacy_id: null,
                 prescription_items: [{
                     medicine_id: null,
                     usage_type_id: null,
                     dosage: '',
                     frequency: '',
                     amount: ''
                 }]
             }
        }
    },
    computed: {
        contextData() {
            return this.operation || this.icu || this.hospitalization || this.appointment;
        },
        contextType() {
            if (this.operation) return 'operation';
            if (this.icu) return 'icu';
            if (this.hospitalization) return 'hospitalization';
            return 'appointment';
        },
        contextId() {
            if (this.operation) return this.operation.id;
            if (this.icu) return this.icu.id;
            if (this.hospitalization) return this.hospitalization.id;
            return this.appointment.id;
        }
    },
    mounted() {
        this.loadMedicineUsageTypes();
        this.loadAllMedicines();
        this.loadAllPharmacies();
        this.loadAppointmentPrescriptions();
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        },
    },
    methods: {
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        },

        async loadMedicineUsageTypes() {
            try {
                const response = await fetch('/prescription-ajax/medicine-usage-types');
                const data = await response.json();
                if (data.success) {
                    this.medicineUsageTypes = data.data;
                }
            } catch (error) {
                console.error('Error loading medicine usage types:', error);
            }
        },

        async loadAllMedicines() {
            try {
                const response = await fetch('/prescription-ajax/all-medicines');
                const data = await response.json();
                if (data.success) {
                    this.allMedicines = data.data;
                }
            } catch (error) {
                console.error('Error loading all medicines:', error);
            }
        },

        async loadAllPharmacies() {
            try {
                const response = await fetch('/prescription-ajax/all-pharmacies');
                const data = await response.json();
                if (data.success) {
                    this.allPharmacies = data.data;
                }
            } catch (error) {
                console.error('Error loading all pharmacies:', error);
            }
        },


        async loadAppointmentPrescriptions() {
            try {
                const response = await fetch(`/prescription-ajax/appointment-prescriptions/${this.contextId}/${this.contextType}`);
                const data = await response.json();
                if (data.success) {
                    this.prescriptions = data.data;
                }
            } catch (error) {
                console.error('Error loading prescriptions:', error);
            }
        },

         async viewPrescriptionItems(prescriptionId) {
             try {
                 const response = await fetch(`/prescription-ajax/prescription-items/${prescriptionId}`);
                 const data = await response.json();
                 if (data.success) {
                     this.selectedPrescription = data.data;
                     this.showPrescriptionItemsModal = true;
                 }
             } catch (error) {
                 console.error('Error loading prescription items:', error);
             }
         },

         async submitPrescription() {
             this.loading = true;
             try {
                 // Validate form before submission
                 if (!this.validateForm()) {
                     this.showToast('لطفاً خطاهای فرم را برطرف کنید', 'error');
                     this.loading = false;
                     return;
                 }

                 // Transform prescription items to extract IDs from objects and convert numbers to strings
                 const transformedItems = this.form.prescription_items.map(item => ({
                     medicine_id: item.medicine_id?.id || item.medicine_id,
                     usage_type_id: item.usage_type_id?.id || item.usage_type_id,
                     dosage: String(item.dosage || ''),
                     frequency: String(item.frequency || ''),
                     amount: String(item.amount || '')
                 }));

                 const formData = {
                     appointment_id: this.operation ? (this.contextData.appointment_id || this.contextData.appointment?.id) : (this.contextData.appointment_id || this.contextData.appointment?.id || (this.hospitalization ? this.hospitalization.appointment?.id || this.hospitalization.appointment_id : this.contextData.id)),
                     patient_id: this.contextData.patient_id,
                     branch_id: this.contextData.branch_id,
                     i_c_u_id: this.icu ? this.icu.id : null,
                     hospitalization_id: this.operation ? (this.contextData.hospitalization_id || null) : (this.hospitalization ? this.hospitalization.id : null),
                     under_review_id: this.underReviewId || this.appointment?.under_review_id || this.contextData.under_review_id || (this.appointment?.under_review ? this.appointment.under_review.id : null),
                     pharmacy_id: this.form.pharmacy_id?.id || this.form.pharmacy_id || null,
                     prescription_items: transformedItems
                 };

                 // Validate appointment_id is present (unless it's a hospitalization without appointment)
                 if (!formData.appointment_id && !this.hospitalization && !this.operation) {
                     this.showToast('شناسه نوبت یافت نشد. لطفاً صفحه را مجدداً بارگذاری کنید.', 'error');
                     this.loading = false;
                     return;
                 }

                const response = await fetch('/prescription-ajax/store', {
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
                     this.loadAppointmentPrescriptions();
                     this.showToast('نسخه با موفقیت ایجاد شد', 'success');
                 } else {
                     this.showToast(data.message || 'خطا در ایجاد نسخه', 'error');
                 }
             } catch (error) {
                 console.error('Error creating prescription:', error);
                 this.showToast('خطا در ایجاد نسخه', 'error');
            } finally {
                this.loading = false;
            }
        },

         async markAsDelivered(itemId) {
             try {
                 const response = await fetch(`/prescription-ajax/update-item-status/${itemId}`, {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                     },
                     body: JSON.stringify({ is_delivered: true })
                 });

                 const data = await response.json();
                 if (data.success) {
                     this.loadAppointmentPrescriptions();
                     // Refresh the prescription items view if modal is open
                     if (this.selectedPrescription) {
                         const prescriptionId = this.selectedPrescription.id;
                         this.viewPrescriptionItems(prescriptionId);
                     }
                     this.showToast('آیتم به عنوان تحویل شده علامت‌گذاری شد', 'success');
                 }
             } catch (error) {
                 console.error('Error updating item status:', error);
             }
         },

         async markAsNotDelivered(itemId) {
             try {
                 const response = await fetch(`/prescription-ajax/update-item-status/${itemId}`, {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                     },
                     body: JSON.stringify({ is_delivered: false })
                 });

                 const data = await response.json();
                 if (data.success) {
                     this.loadAppointmentPrescriptions();
                     // Refresh the prescription items view if modal is open
                     if (this.selectedPrescription) {
                         const prescriptionId = this.selectedPrescription.id;
                         this.viewPrescriptionItems(prescriptionId);
                     }
                     this.showToast('آیتم به عنوان تحویل نشده علامت‌گذاری شد', 'success');
                 }
             } catch (error) {
                 console.error('Error updating item status:', error);
             }
         },

         async deletePrescription(prescriptionId) {
             const result = await Swal.fire({
                 title: 'حذف نسخه',
                 text: 'آیا مطمئن هستید که می‌خواهید این نسخه را حذف کنید؟',
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
                const response = await fetch(`/prescription-ajax/delete/${prescriptionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                 const data = await response.json();
                 if (data.success) {
                     this.loadAppointmentPrescriptions();
                     this.showToast('نسخه با موفقیت حذف شد', 'success');
                 } else {
                     this.showToast(data.message || 'خطا در حذف نسخه', 'error');
                 }
             } catch (error) {
                 console.error('Error deleting prescription:', error);
                 this.showToast('خطا در حذف نسخه', 'error');
             }
         },

         async deletePrescriptionItem(itemId) {
             const result = await Swal.fire({
                 title: 'حذف آیتم نسخه',
                 text: 'آیا مطمئن هستید که می‌خواهید این آیتم نسخه را حذف کنید؟',
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
                 const response = await fetch(`/prescription-ajax/delete-item/${itemId}`, {
                     method: 'DELETE',
                     headers: {
                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                     }
                 });

                 const data = await response.json();
                 if (data.success) {
                     // Reload the prescription items to reflect the deletion
                     if (this.selectedPrescription) {
                         const prescriptionId = this.selectedPrescription.id;
                         this.viewPrescriptionItems(prescriptionId);
                     }
                     this.loadAppointmentPrescriptions();
                     this.showToast('آیتم نسخه با موفقیت حذف شد', 'success');
                 } else {
                     this.showToast(data.message || 'خطا در حذف آیتم نسخه', 'error');
                 }
             } catch (error) {
                 console.error('Error deleting prescription item:', error);
                 this.showToast('خطا در حذف آیتم نسخه', 'error');
             }
         },

         addPrescriptionItem() {
             this.form.prescription_items.push({
                 medicine_id: null,
                 usage_type_id: null,
                 dosage: '',
                 frequency: '',
                 amount: ''
             });
         },

        removePrescriptionItem(index) {
            if (this.form.prescription_items.length > 1) {
                this.form.prescription_items.splice(index, 1);
            }
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.clearValidationErrors();
        },

        closePrescriptionItemsModal() {
            this.showPrescriptionItemsModal = false;
            this.selectedPrescription = null;
        },

         resetForm() {
             this.form.pharmacy_id = null;
             this.form.prescription_items = [{
                 medicine_id: null,
                 usage_type_id: null,
                 dosage: '',
                 frequency: '',
                 amount: ''
             }];
             this.clearValidationErrors();
         },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },



        getFieldError(index, fieldName) {
            const errorKey = `prescription_items.${index}.${fieldName}`;
            return this.validationErrors[errorKey] || null;
        },

        setFieldError(index, fieldName, error) {
            const errorKey = `prescription_items.${index}.${fieldName}`;
            if (error) {
                this.validationErrors[errorKey] = error;
            } else {
                delete this.validationErrors[errorKey];
            }
        },

        clearValidationErrors() {
            this.validationErrors = {};
        },


        validateForm() {
            this.clearValidationErrors();
            let hasErrors = false;

            this.form.prescription_items.forEach((item, index) => {
                // Validate medicine
                if (!item.medicine_id) {
                    this.setFieldError(index, 'medicine_id', 'نام دارو الزامی است');
                    hasErrors = true;
                }

                // Validate usage type
                if (!item.usage_type_id) {
                    this.setFieldError(index, 'usage_type_id', 'نوع مصرف الزامی است');
                    hasErrors = true;
                }

                // Validate dosage
                if (!item.dosage || item.dosage === '' || item.dosage === null || item.dosage === undefined) {
                    this.setFieldError(index, 'dosage', 'دوز الزامی است');
                    hasErrors = true;
                } else if (isNaN(item.dosage) || parseFloat(item.dosage) <= 0) {
                    this.setFieldError(index, 'dosage', 'دوز باید عدد مثبت باشد');
                    hasErrors = true;
                }

                // Validate frequency (free text, just require non-empty)
                if (!item.frequency || item.frequency === '' || item.frequency === null || item.frequency === undefined) {
                    this.setFieldError(index, 'frequency', 'تکرار الزامی است');
                    hasErrors = true;
                } else {
                    this.setFieldError(index, 'frequency', null);
                }

                // Validate amount
                if (!item.amount || item.amount === '' || item.amount === null || item.amount === undefined) {
                    this.setFieldError(index, 'amount', 'مقدار الزامی است');
                    hasErrors = true;
                } else if (isNaN(item.amount) || parseFloat(item.amount) <= 0) {
                    this.setFieldError(index, 'amount', 'مقدار باید عدد مثبت باشد');
                    hasErrors = true;
                }
            });

            return !hasErrors;
        }
    }
}
</script>

<style scoped>
.toast {
    z-index: 1055;
}

.modal.show {
    display: block !important;
}

.prescription-section .table th {
    border-top: none;
}

.prescription-section .badge {
    font-size: 0.75em;
}

.prescription-section .btn-sm {
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
