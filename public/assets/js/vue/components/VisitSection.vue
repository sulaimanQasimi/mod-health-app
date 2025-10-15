<template>
    <div class="visit-section">
        <!-- Add Button and Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div> <!-- Empty div for spacing -->
            <div>
                <button 
                    v-if="!hospitalization.is_discharged" 
                    type="button" 
                    class="btn btn-primary btn-sm" 
                    @click="showCreateModal = true">
                     <i class="bx bx-plus me-1"></i>
                     اضافه کردن
                </button>
            </div>
        </div>
        
        <!-- Visit Table -->
        <div v-if="visits.length > 0" class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-body-secondary">
                    <tr>
                         <th>شماره</th>
                         <th>توضیحات</th>
                         <th>توسط</th>
                         <th>تاریخ ایجاد</th>
                         <th>علائم حیاتی</th>
                         <th>آنتی بیوتیک</th>
                         <th>نوع غذا</th>
                         <th>ورودی</th>
                         <th>خروجی</th>
                         <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(visit, index) in visits" :key="visit.id">
                        <td>
                            <span class="badge bg-success rounded-pill">{{ index + 1 }}</span>
                        </td>
                        <td>{{ visit.description }}</td>
                        <td>{{ visit.doctor?.name || 'N/A' }}</td>
                        <td>{{ formatDate(visit.created_at) }}</td>
                        <td>
                            <div class="vital-signs-compact">
                                <span v-if="visit.bp" class="badge bg-primary me-1">BP: {{ visit.bp }}</span>
                                <span v-if="visit.pr" class="badge bg-primary me-1">PR: {{ visit.pr }}</span>
                                <span v-if="visit.rr" class="badge bg-primary me-1">RR: {{ visit.rr }}</span>
                                <span v-if="visit.t" class="badge bg-primary me-1">T: {{ visit.t }}</span>
                                <span v-if="visit.spo2" class="badge bg-primary me-1">SPO2: {{ visit.spo2 }}</span>
                                <span v-if="visit.pain" class="badge bg-primary me-1">Pain: {{ visit.pain }}</span>
                            </div>
                        </td>
                        <td>{{ visit.antibiotic || '-' }}</td>
                        <td>
                            <div v-if="visit.food_type_id">
                                <span 
                                    v-for="foodType in getFoodTypesFromJson(visit.food_type_id)" 
                                    :key="foodType.id"
                                    class="badge bg-info me-1">
                                    {{ foodType.name }}
                                </span>
                            </div>
                            <span v-else>-</span>
                        </td>
                        <td>{{ visit.intake || '-' }}</td>
                        <td>{{ visit.output || '-' }}</td>
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-outline-primary btn-sm" 
                                @click="viewVisitDetails(visit.id)"
                                title="View Details">
                                 <i class="bx bx-expand me-1"></i>
                                 مشاهده
                            </button>
                            <button 
                                v-if="!hospitalization.is_discharged"
                                type="button" 
                                class="btn btn-outline-warning btn-sm ms-1" 
                                @click="editVisit(visit)"
                                title="Edit">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button 
                                v-if="!hospitalization.is_discharged"
                                type="button" 
                                class="btn btn-outline-danger btn-sm ms-1" 
                                @click="deleteVisit(visit.id)"
                                title="Delete">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- No Visits Message -->
        <div v-else class="text-center py-4">
             <div class="alert alert-info">
                 <i class="bx bx-info-circle me-2"></i>
                 هیچ بازدید قبلی وجود ندارد
             </div>
        </div>

        <!-- Create Visit Modal -->
        <div 
            v-if="showCreateModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">اضافه کردن بازدید</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitVisit">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">توضیحات</label>
                                    <textarea 
                                        v-model="form.description" 
                                        class="form-control" 
                                        :class="{ 'is-invalid': getFieldError('description') }"
                                        rows="3" 
                                        placeholder="توضیحات بازدید" 
                                        required>
                                    </textarea>
                                    <div v-if="getFieldError('description')" class="invalid-feedback">
                                        {{ getFieldError('description') }}
                                    </div>
                                </div>
                            </div>

                            <h5 class="mt-3 mb-3">علائم حیاتی</h5>
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label class="form-label">فشار خون (BP)</label>
                                    <input 
                                        v-model="form.bp" 
                                        type="text" 
                                        class="form-control" 
                                        placeholder="BP">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">نبض (PR)</label>
                                    <input 
                                        v-model="form.pr" 
                                        type="text" 
                                        class="form-control" 
                                        placeholder="PR">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">تنفس (RR)</label>
                                    <input 
                                        v-model="form.rr" 
                                        type="text" 
                                        class="form-control" 
                                        placeholder="RR">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">دما (T)</label>
                                    <input 
                                        v-model="form.t" 
                                        type="text" 
                                        class="form-control" 
                                        placeholder="T">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">اکسیژن (SPO2)</label>
                                    <input 
                                        v-model="form.spo2" 
                                        type="text" 
                                        class="form-control" 
                                        placeholder="SPO2">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">درد (Pain)</label>
                                    <input 
                                        v-model="form.pain" 
                                        type="text" 
                                        class="form-control" 
                                        placeholder="Pain">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">آنتی بیوتیک</label>
                                    <input 
                                        v-model="form.antibiotic" 
                                        type="text" 
                                        class="form-control" 
                                        placeholder="آنتی بیوتیک">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">نوع غذا</label>
                                    <multiselect
                                        v-model="form.food_type_id"
                                        :options="foodTypes"
                                        :custom-label="foodType => foodType.name"
                                        :placeholder="'انتخاب نوع غذا'"
                                        :allow-empty="true"
                                        :searchable="true"
                                        :close-on-select="false"
                                        :show-labels="false"
                                        :multiple="true"
                                        :clear-on-select="false"
                                        :preserve-search="true"
                                        :class="{ 'is-invalid': validationErrors.food_type_id }"
                                    ></multiselect>
                                    <div v-if="validationErrors.food_type_id" class="invalid-feedback d-block">
                                        {{ validationErrors.food_type_id[0] }}
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">ورودی</label>
                                    <input 
                                        v-model="form.intake" 
                                        type="text" 
                                        class="form-control" 
                                        placeholder="ورودی">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">خروجی</label>
                                    <input 
                                        v-model="form.output" 
                                        type="text" 
                                        class="form-control" 
                                        placeholder="خروجی">
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
                            @click="submitVisit"
                            :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            ذخیره
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visit Details Modal -->
        <div 
            v-if="showVisitDetailsModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">جزئیات بازدید</h5>
                        <button type="button" class="btn-close" @click="closeVisitDetailsModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedVisit">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>توسط:</strong> {{ selectedVisit.doctor?.name }}
                                </div>
                                <div class="col-md-6">
                                    <strong>تاریخ ایجاد:</strong> {{ formatDate(selectedVisit.created_at) }}
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong>توضیحات:</strong>
                                    <p class="mt-2">{{ selectedVisit.description }}</p>
                                </div>
                            </div>

                            <h5 class="mt-3 mb-3">علائم حیاتی</h5>
                            <div class="row mb-3">
                                <div class="col-md-2" v-if="selectedVisit.bp">
                                    <span class="badge bg-primary me-2">فشار خون:</span> {{ selectedVisit.bp }}
                                </div>
                                <div class="col-md-2" v-if="selectedVisit.pr">
                                    <span class="badge bg-primary me-2">نبض:</span> {{ selectedVisit.pr }}
                                </div>
                                <div class="col-md-2" v-if="selectedVisit.rr">
                                    <span class="badge bg-primary me-2">تنفس:</span> {{ selectedVisit.rr }}
                                </div>
                                <div class="col-md-2" v-if="selectedVisit.t">
                                    <span class="badge bg-primary me-2">دما:</span> {{ selectedVisit.t }}
                                </div>
                                <div class="col-md-2" v-if="selectedVisit.spo2">
                                    <span class="badge bg-primary me-2">اکسیژن:</span> {{ selectedVisit.spo2 }}
                                </div>
                                <div class="col-md-2" v-if="selectedVisit.pain">
                                    <span class="badge bg-primary me-2">درد:</span> {{ selectedVisit.pain }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6" v-if="selectedVisit.antibiotic">
                                    <strong>آنتی بیوتیک:</strong> {{ selectedVisit.antibiotic }}
                                </div>
                                <div class="col-md-6" v-if="selectedVisit.food_type_id">
                                    <strong>نوع غذا:</strong>
                                    <div class="mt-2">
                                        <span 
                                            v-for="foodType in getFoodTypesFromJson(selectedVisit.food_type_id)" 
                                            :key="foodType.id"
                                            class="badge bg-info me-1">
                                            {{ foodType.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6" v-if="selectedVisit.intake">
                                    <strong>ورودی:</strong> {{ selectedVisit.intake }}
                                </div>
                                <div class="col-md-6" v-if="selectedVisit.output">
                                    <strong>خروجی:</strong> {{ selectedVisit.output }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button 
                            v-if="!hospitalization.is_discharged"
                            type="button" 
                            class="btn btn-warning me-2" 
                            @click="editVisit(selectedVisit)">
                            ویرایش
                        </button>
                        <button 
                            v-if="!hospitalization.is_discharged"
                            type="button" 
                            class="btn btn-danger me-2" 
                            @click="deleteVisit(selectedVisit.id)">
                            حذف
                        </button>
                        <button type="button" class="btn btn-secondary" @click="closeVisitDetailsModal">
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
    name: 'VisitSection',
    components: {
        Multiselect
    },
    props: {
        hospitalization: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            loading: false,
            showCreateModal: false,
            showVisitDetailsModal: false,
            visits: [],
            foodTypes: [],
            selectedVisit: null,
            validationErrors: {},
            form: {
                description: '',
                bp: '',
                pr: '',
                rr: '',
                t: '',
                spo2: '',
                pain: '',
                antibiotic: '',
                food_type_id: [],
                intake: '',
                output: ''
            },
            editingVisit: null
        }
    },
    mounted() {
        this.loadFoodTypes();
        this.loadHospitalizationVisits();
        // Show a subtle success message when component loads
        setTimeout(() => {
            if (this.visits.length > 0) {
                console.log('Visit section loaded successfully');
            }
        }, 1000);
    },
    watch: {
        showCreateModal(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        }
    },
    methods: {
        async loadFoodTypes() {
            try {
                const response = await fetch('/visit-ajax/food-types');
                const data = await response.json();
                if (data.success) {
                    this.foodTypes = data.data;
                    console.log('Food types loaded:', this.foodTypes);
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: 'خطا در بارگذاری انواع غذا',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error loading food types:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در بارگذاری انواع غذا',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

        async loadHospitalizationVisits() {
            try {
                const response = await fetch(`/visit-ajax/hospitalization-visits/${this.hospitalization.id}`);
                const data = await response.json();
                if (data.success) {
                    this.visits = data.data;
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: 'خطا در بارگذاری ویزیت‌ها',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error loading visits:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در بارگذاری ویزیت‌ها',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

        async viewVisitDetails(visitId) {
            try {
                const response = await fetch(`/visit-ajax/visit-details/${visitId}`);
                const data = await response.json();
                if (data.success) {
                    this.selectedVisit = data.data;
                    this.showVisitDetailsModal = true;
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: data.message || 'خطا در بارگذاری جزئیات ویزیت',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error loading visit details:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در بارگذاری جزئیات ویزیت',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

        editVisit(visit) {
            console.log('Editing visit:', visit);
            this.editingVisit = visit;
            const foodTypes = this.getFoodTypesFromJson(visit.food_type_id);
            console.log('Food types for edit:', foodTypes);
            this.form = {
                description: visit.description || '',
                bp: visit.bp || '',
                pr: visit.pr || '',
                rr: visit.rr || '',
                t: visit.t || '',
                spo2: visit.spo2 || '',
                pain: visit.pain || '',
                antibiotic: visit.antibiotic || '',
                food_type_id: foodTypes,
                intake: visit.intake || '',
                output: visit.output || ''
            };
            this.showCreateModal = true;
            this.showVisitDetailsModal = false;
            
            // Show edit confirmation
            Swal.fire({
                title: 'ویرایش ویزیت',
                text: 'آیا می‌خواهید این ویزیت را ویرایش کنید؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بله، ویرایش کن',
                cancelButtonText: 'لغو'
            }).then((result) => {
                if (!result.isConfirmed) {
                    this.closeCreateModal();
                }
            });
        },

        async submitVisit() {
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

                const formData = {
                    description: this.form.description,
                    patient_id: this.hospitalization.patient_id,
                    doctor_id: this.hospitalization.doctor_id,
                    hospitalization_id: this.hospitalization.id,
                    bp: this.form.bp,
                    pr: this.form.pr,
                    rr: this.form.rr,
                    t: this.form.t,
                    spo2: this.form.spo2,
                    pain: this.form.pain,
                    antibiotic: this.form.antibiotic,
                    food_type_id: this.form.food_type_id.map(item => typeof item === 'object' ? item.id : item),
                    intake: this.form.intake,
                    output: this.form.output
                };

                const url = this.editingVisit 
                    ? `/visit-ajax/update/${this.editingVisit.id}`
                    : '/visit-ajax/store';
                const method = this.editingVisit ? 'PUT' : 'POST';

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
                    this.editingVisit = null;
                    this.loadHospitalizationVisits();
                    Swal.fire({
                        title: 'موفق',
                        text: this.editingVisit ? 'بازدید با موفقیت ویرایش شد' : 'بازدید با موفقیت ایجاد شد',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: data.message || 'خطا در ذخیره بازدید',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error saving visit:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در ذخیره بازدید',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            } finally {
                this.loading = false;
            }
        },

        async deleteVisit(visitId) {
            const result = await Swal.fire({
                title: 'حذف بازدید',
                text: 'آیا مطمئن هستید که می‌خواهید این بازدید را حذف کنید؟',
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
                const response = await fetch(`/visit-ajax/delete/${visitId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.loadHospitalizationVisits();
                    this.showVisitDetailsModal = false;
                    Swal.fire({
                        title: 'موفق',
                        text: 'بازدید با موفقیت حذف شد',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: data.message || 'خطا در حذف بازدید',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error deleting visit:', error);
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در حذف بازدید',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.editingVisit = null;
            this.clearValidationErrors();
        },

        closeVisitDetailsModal() {
            this.showVisitDetailsModal = false;
            this.selectedVisit = null;
        },

        resetForm() {
            this.form = {
                description: '',
                bp: '',
                pr: '',
                rr: '',
                t: '',
                spo2: '',
                pain: '',
                antibiotic: '',
                food_type_id: [],
                intake: '',
                output: ''
            };
            this.editingVisit = null;
            this.clearValidationErrors();
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },

        getFoodTypesFromJson(foodTypeJson) {
            if (!foodTypeJson) return [];
            try {
                const foodTypeIds = JSON.parse(foodTypeJson);
                return this.foodTypes.filter(foodType => foodTypeIds.includes(foodType.id));
            } catch (error) {
                return [];
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
            this.validationErrors = {};
        },

        validateForm() {
            this.clearValidationErrors();
            let hasErrors = false;

            // Validate description
            if (!this.form.description || this.form.description.trim() === '') {
                this.setFieldError('description', 'توضیحات الزامی است');
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

.visit-section .table th {
    border-top: none;
}

.visit-section .badge {
    font-size: 0.75em;
}

.visit-section .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.vital-signs-compact {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
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
