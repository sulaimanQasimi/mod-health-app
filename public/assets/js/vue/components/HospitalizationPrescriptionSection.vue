<template>
    <div class="prescription-section">
        <!-- Add Button and Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div> <!-- Empty div for spacing -->
            <div>
                <button 
                    v-if="!hospitalizationDischarged && canAddPrescription" 
                    type="button" 
                    class="btn btn-primary btn-sm" 
                    @click="showCreateModal = true">
                     <i class="bx bx-plus me-1"></i>
                     اضافه کردن نسخه
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
                                            type="number" 
                                            class="form-control" 
                                            :class="{ 'is-invalid': getFieldError(index, 'frequency') }"
                                            :placeholder="'تکرار'" 
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="selectedPrescription.prescription_items && selectedPrescription.prescription_items.length > 0" v-for="item in selectedPrescription.prescription_items" :key="item.id">
                                            <td>{{ item.medicine?.name || 'N/A' }}</td>
                                            <td>{{ item.usage_type?.name || item.usageType?.name || 'N/A' }}</td>
                                            <td>{{ item.dosage }}</td>
                                            <td>{{ item.frequency }}</td>
                                            <td>{{ item.amount }}</td>
                                            <td>
                                                 <span 
                                                     :class="item.is_delivered ? 'badge bg-success' : 'badge bg-danger'">
                                                     {{ item.is_delivered ? 'تحویل شده' : 'تحویل نشده' }}
                                                 </span>
                                            </td>
                                        </tr>
                                        <tr v-else>
                                            <td colspan="7" class="text-center">
                                                <div class="alert alert-info">
                                                    <i class="bx bx-info-circle me-2"></i>
                                                    هیچ آیتم نسخه‌ای یافت نشد
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
import 'vue-multiselect/dist/vue-multiselect.css'
import Swal from 'sweetalert2'

export default {
    name: 'HospitalizationPrescriptionSection',
    components: {
        Multiselect
    },
    props: {
        hospitalization: {
            type: Object,
            required: true
        },
        canAddPrescription: {
            type: Boolean,
            default: true
        },
        hospitalizationDischarged: {
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
            selectedPrescription: null,
            validationErrors: {},
             form: {
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
    mounted() {
        this.loadMedicineUsageTypes();
        this.loadAllMedicines();
        this.loadHospitalizationPrescriptions();
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        },
    },
    methods: {

        async loadMedicineUsageTypes() {
            try {
                const response = await fetch('/hospitalization-prescription-ajax/medicine-usage-types');
                const data = await response.json();
                if (data.success) {
                    this.medicineUsageTypes = data.data;
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: 'خطا در بارگذاری انواع مصرف',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error loading medicine usage types:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در بارگذاری انواع مصرف',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

        async loadAllMedicines() {
            try {
                const response = await fetch('/hospitalization-prescription-ajax/all-medicines');
                const data = await response.json();
                if (data.success) {
                    this.allMedicines = data.data;
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: 'خطا در بارگذاری داروها',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error loading all medicines:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در بارگذاری داروها',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

        async loadHospitalizationPrescriptions() {
            try {
                const response = await fetch(`/hospitalization-prescription-ajax/hospitalization-prescriptions/${this.hospitalization.id}`);
                const data = await response.json();
                if (data.success) {
                    this.prescriptions = data.data;
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: 'خطا در بارگذاری نسخه‌ها',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error loading prescriptions:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در بارگذاری نسخه‌ها',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

         async viewPrescriptionItems(prescriptionId) {
             try {
                 const response = await fetch(`/hospitalization-prescription-ajax/prescription-items/${prescriptionId}`);
                 const data = await response.json();
                 if (data.success) {
                     this.selectedPrescription = data.data;
                     this.showPrescriptionItemsModal = true;
                 } else {
                     Swal.fire({
                         title: 'خطا',
                         text: 'خطا در بارگذاری جزئیات نسخه',
                         icon: 'error',
                         timer: 3000,
                         showConfirmButton: false
                     });
                 }
             } catch (error) {
                 console.error('Error loading prescription items:', error);
                 Swal.fire({
                     title: 'خطا',
                     text: 'خطا در بارگذاری جزئیات نسخه',
                     icon: 'error',
                     timer: 3000,
                     showConfirmButton: false
                 });
             }
         },

         async submitPrescription() {
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

                 // Transform prescription items to extract IDs from objects and convert numbers to strings
                 const transformedItems = this.form.prescription_items.map(item => ({
                     medicine_id: item.medicine_id?.id || item.medicine_id,
                     usage_type_id: item.usage_type_id?.id || item.usage_type_id,
                     dosage: String(item.dosage || ''),
                     frequency: String(item.frequency || ''),
                     amount: String(item.amount || '')
                 }));

                 const formData = {
                     hospitalization_id: this.hospitalization.id,
                     patient_id: this.hospitalization.patient_id,
                     doctor_id: this.hospitalization.doctor_id,
                     branch_id: this.hospitalization.branch_id,
                     prescription_items: transformedItems
                 };

                const response = await fetch('/hospitalization-prescription-ajax/store', {
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
                     this.loadHospitalizationPrescriptions();
                     Swal.fire({
                         title: 'موفق',
                         text: 'نسخه با موفقیت ایجاد شد',
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
                         text: data.message || 'خطا در ایجاد نسخه',
                         icon: 'error',
                         timer: 3000,
                         showConfirmButton: false
                     });
                 }
             } catch (error) {
                 console.error('Error creating prescription:', error);
                 Swal.fire({
                     title: 'خطا',
                     text: 'خطا در ایجاد نسخه',
                     icon: 'error',
                     timer: 3000,
                     showConfirmButton: false
                 });
            } finally {
                this.loading = false;
            }
        },


        // Form handling methods
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


        validateForm() {
            this.validationErrors = {};
            let isValid = true;

            this.form.prescription_items.forEach((item, index) => {
                if (!item.medicine_id) {
                    this.validationErrors[`${index}.medicine_id`] = 'لطفاً دارو را انتخاب کنید';
                    isValid = false;
                }
                if (!item.usage_type_id) {
                    this.validationErrors[`${index}.usage_type_id`] = 'لطفاً نوع مصرف را انتخاب کنید';
                    isValid = false;
                }
                if (!item.dosage) {
                    this.validationErrors[`${index}.dosage`] = 'لطفاً دوز را وارد کنید';
                    isValid = false;
                }
                if (!item.frequency) {
                    this.validationErrors[`${index}.frequency`] = 'لطفاً تکرار را وارد کنید';
                    isValid = false;
                }
                if (!item.amount) {
                    this.validationErrors[`${index}.amount`] = 'لطفاً مقدار را وارد کنید';
                    isValid = false;
                }
            });

            return isValid;
        },

        getFieldError(index, field) {
            return this.validationErrors[`${index}.${field}`];
        },

        resetForm() {
            this.form = {
                prescription_items: [{
                    medicine_id: null,
                    usage_type_id: null,
                    dosage: '',
                    frequency: '',
                    amount: ''
                }]
            };
            this.validationErrors = {};
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.resetForm();
        },

        closePrescriptionItemsModal() {
            this.showPrescriptionItemsModal = false;
            this.selectedPrescription = null;
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('fa-IR');
        }
    }
}
</script>
