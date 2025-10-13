<template>
    <div class="prescription-section">
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
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">اضافه کردن نسخه</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitPrescription">
                            <!-- Prescription Items Container -->
                            <div id="prescription-items">
                                <div 
                                    v-for="(item, index) in form.prescription_items" 
                                    :key="index" 
                                    class="row mb-3">
                                     <div class="col-md-2">
                                         <label class="form-label">نوع دارو</label>
                                         <multiselect
                                             v-model="item.medicine_type_id"
                                             :options="medicineTypes"
                                             :custom-label="type => type.type"
                                             :placeholder="'انتخاب کنید'"
                                             @select="(selectedOption) => loadMedicinesByType(selectedOption, index)"
                                             :allow-empty="false"
                                             :searchable="true"
                                             :close-on-select="true"
                                             :show-labels="false"
                                         ></multiselect>
                                     </div>
                                     <div class="col-md-2">
                                         <label class="form-label">نام دارو</label>
                                         <multiselect
                                             v-model="item.medicine_id"
                                             :options="item.medicines"
                                             :custom-label="medicine => medicine.name"
                                             :placeholder="'انتخاب کنید'"
                                             :allow-empty="false"
                                             :searchable="true"
                                             :close-on-select="true"
                                             :show-labels="false"
                                             :disabled="!item.medicine_type_id"
                                         ></multiselect>
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
                                         ></multiselect>
                                     </div>
                                    <div class="col-md-2">
                                        <label class="form-label">دوز</label>
                                        <input 
                                            v-model="item.dosage" 
                                            type="number" 
                                            class="form-control" 
                                            :placeholder="'دوز'" 
                                            required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">تکرار</label>
                                        <input 
                                            v-model="item.frequency" 
                                            type="number" 
                                            class="form-control" 
                                            :placeholder="'تکرار'" 
                                            required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">مقدار</label>
                                        <input 
                                            v-model="item.amount" 
                                            type="number" 
                                            class="form-control" 
                                            :placeholder="'مقدار'" 
                                            required>
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
            <div class="modal-dialog modal-xl">
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
                                             <th>نوع دارو</th>
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
                                            <td>{{ item.medicine_type?.type }}</td>
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
            required: true
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
            medicineTypes: [],
            medicineUsageTypes: [],
            selectedPrescription: null,
             form: {
                 prescription_items: [{
                     medicine_type_id: null,
                     medicine_id: null,
                     usage_type_id: null,
                     dosage: '',
                     frequency: '',
                     amount: '',
                     medicines: []
                 }]
             }
        }
    },
    mounted() {
        this.loadMedicineTypes();
        this.loadMedicineUsageTypes();
        this.loadAppointmentPrescriptions();
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        }
    },
    methods: {
        async loadMedicineTypes() {
            try {
                const response = await fetch('/prescription-ajax/medicine-types');
                const data = await response.json();
                if (data.success) {
                    this.medicineTypes = data.data;
                }
            } catch (error) {
                console.error('Error loading medicine types:', error);
            }
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

         async loadMedicinesByType(selectedOption, index) {
             const item = this.form.prescription_items[index];
             if (!selectedOption || !selectedOption.id) {
                 item.medicines = [];
                 item.medicine_id = null;
                 return;
             }

             try {
                 const response = await fetch(`/prescription-ajax/medicines-by-type/${selectedOption.id}`);
                 const data = await response.json();
                 if (data.success) {
                     item.medicines = data.data;
                     // Reset medicine selection when type changes
                     item.medicine_id = null;
                 }
             } catch (error) {
                 console.error('Error loading medicines:', error);
             }
         },

        async loadAppointmentPrescriptions() {
            try {
                const response = await fetch(`/prescription-ajax/appointment-prescriptions/${this.appointment.id}`);
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
                 const hasEmptyFields = this.form.prescription_items.some(item => 
                     !item.medicine_type_id || !item.medicine_id || !item.usage_type_id || 
                     !item.dosage.trim() || !item.frequency.trim() || !item.amount.trim()
                 );

                 if (hasEmptyFields) {
                     this.showNotification('لطفاً تمام فیلدها را پر کنید', 'error');
                     this.loading = false;
                     return;
                 }

                 // Transform prescription items to extract IDs from objects
                 const transformedItems = this.form.prescription_items.map(item => ({
                     medicine_type_id: item.medicine_type_id?.id || item.medicine_type_id,
                     medicine_id: item.medicine_id?.id || item.medicine_id,
                     usage_type_id: item.usage_type_id?.id || item.usage_type_id,
                     dosage: item.dosage,
                     frequency: item.frequency,
                     amount: item.amount
                 }));

                 const formData = {
                     appointment_id: this.appointment.id,
                     patient_id: this.appointment.patient_id,
                     doctor_id: this.appointment.doctor_id,
                     branch_id: this.appointment.branch_id,
                     prescription_items: transformedItems
                 };

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
                     this.showNotification('نسخه با موفقیت ایجاد شد', 'success');
                 } else {
                     this.showNotification(data.message || 'خطا در ایجاد نسخه', 'error');
                 }
             } catch (error) {
                 console.error('Error creating prescription:', error);
                 this.showNotification('خطا در ایجاد نسخه', 'error');
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
                     this.showNotification('آیتم به عنوان تحویل شده علامت‌گذاری شد', 'success');
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
                     this.showNotification('آیتم به عنوان تحویل نشده علامت‌گذاری شد', 'success');
                 }
             } catch (error) {
                 console.error('Error updating item status:', error);
             }
         },

         async deletePrescription(prescriptionId) {
             if (!confirm('آیا مطمئن هستید که می‌خواهید این نسخه را حذف کنید؟')) {
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
                     this.showNotification('نسخه با موفقیت حذف شد', 'success');
                 } else {
                     this.showNotification(data.message || 'خطا در حذف نسخه', 'error');
                 }
             } catch (error) {
                 console.error('Error deleting prescription:', error);
                 this.showNotification('خطا در حذف نسخه', 'error');
             }
         },

         async deletePrescriptionItem(itemId) {
             if (!confirm('آیا مطمئن هستید که می‌خواهید این آیتم نسخه را حذف کنید؟')) {
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
                     this.showNotification('آیتم نسخه با موفقیت حذف شد', 'success');
                 } else {
                     this.showNotification(data.message || 'خطا در حذف آیتم نسخه', 'error');
                 }
             } catch (error) {
                 console.error('Error deleting prescription item:', error);
                 this.showNotification('خطا در حذف آیتم نسخه', 'error');
             }
         },

         addPrescriptionItem() {
             this.form.prescription_items.push({
                 medicine_type_id: null,
                 medicine_id: null,
                 usage_type_id: null,
                 dosage: '',
                 frequency: '',
                 amount: '',
                 medicines: []
             });
         },

        removePrescriptionItem(index) {
            if (this.form.prescription_items.length > 1) {
                this.form.prescription_items.splice(index, 1);
            }
        },

        closeCreateModal() {
            this.showCreateModal = false;
        },

        closePrescriptionItemsModal() {
            this.showPrescriptionItemsModal = false;
            this.selectedPrescription = null;
        },

         resetForm() {
             this.form.prescription_items = [{
                 medicine_type_id: null,
                 medicine_id: null,
                 usage_type_id: null,
                 dosage: '',
                 frequency: '',
                 amount: '',
                 medicines: []
             }];
         },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },


        showNotification(message, type) {
            // This should be connected to your notification system
            if (window.showNotification) {
                window.showNotification(message, type);
            } else {
                alert(message);
            }
        }
    }
}
</script>

<style scoped>
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
</style>
