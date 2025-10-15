<template>
    <div class="nursing-note-section">
        <!-- Add Button and Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button 
                    type="button" 
                    class="btn btn-outline-info btn-sm" 
                    @click="printAllNotes"
                    title="Print All Notes">
                     <i class="bx bx-printer me-1"></i>
                     چاپ همه یادداشت‌ها
                </button>
            </div>
            <div>
                <button 
                    v-if="!hospitalizationDischarged && canAddNote" 
                    type="button" 
                    class="btn btn-primary btn-sm" 
                    @click="handleCreateClick">
                     <i class="bx bx-plus me-1"></i>
                     اضافه کردن یادداشت
                </button>
            </div>
        </div>
        
        <!-- Nursing Notes Table -->
        <div v-if="nursingNotes.length > 0" class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-body-secondary">
                    <tr>
                         <th>شماره</th>
                         <th>تاریخ</th>
                         <th>زمان صبح</th>
                         <th>زمان عصر</th>
                         <th>یادداشت</th>
                         <th>پرستار</th>
                         <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(note, index) in nursingNotes" :key="note.id">
                        <td>
                            <span class="badge bg-success rounded-pill">{{ index + 1 }}</span>
                        </td>
                        <td>{{ formatDate(note.date) }}</td>
                        <td>{{ note.time_am || '-' }}</td>
                        <td>{{ note.time_pm || '-' }}</td>
                        <td>{{ note.note }}</td>
                        <td>{{ note.nurse?.first_name }} {{ note.nurse?.last_name }}</td>
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-outline-primary btn-sm me-1" 
                                @click="editNote(note)"
                                title="Edit">
                                 <i class="bx bx-edit me-1"></i>
                                 ویرایش
                            </button>
                            <button 
                                type="button" 
                                class="btn btn-outline-danger btn-sm" 
                                @click="deleteNote(note.id)"
                                title="Delete">
                                 <i class="bx bx-trash me-1"></i>
                                 حذف
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- No Nursing Notes Message -->
        <div v-else class="text-center py-4">
             <div class="alert alert-info">
                 <i class="bx bx-info-circle me-2"></i>
                 هیچ یادداشت پرستاری قبلی وجود ندارد
             </div>
        </div>

        <!-- Create Nursing Note Modal -->
        <div 
            v-if="showCreateModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">اضافه کردن یادداشت پرستاری</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitNote">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label">تاریخ</label>
                                    <input 
                                        id="date"
                                        v-model="form.date" 
                                        type="date" 
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.date }"
                                        required>
                                    <div v-if="errors.date" class="invalid-feedback">
                                        {{ errors.date[0] }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nurse_id" class="form-label">پرستار</label>
                                    <select 
                                        id="nurse_id"
                                        v-model="form.nurse_id" 
                                        class="form-select"
                                        :class="{ 'is-invalid': errors.nurse_id }"
                                        required>
                                        <option value="">انتخاب پرستار</option>
                                        <option v-for="nurse in nurses" :key="nurse.id" :value="nurse.id">
                                            {{ nurse.name }}
                                        </option>
                                    </select>
                                    <div v-if="errors.nurse_id" class="invalid-feedback">
                                        {{ errors.nurse_id[0] }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="time_am" class="form-label">زمان صبح</label>
                                    <input 
                                        id="time_am"
                                        v-model="form.time_am" 
                                        type="time" 
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.time_am }">
                                    <div v-if="errors.time_am" class="invalid-feedback">
                                        {{ errors.time_am[0] }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="time_pm" class="form-label">زمان عصر</label>
                                    <input 
                                        id="time_pm"
                                        v-model="form.time_pm" 
                                        type="time" 
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.time_pm }">
                                    <div v-if="errors.time_pm" class="invalid-feedback">
                                        {{ errors.time_pm[0] }}
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="note" class="form-label">یادداشت</label>
                                <textarea 
                                    id="note"
                                    v-model="form.note" 
                                    class="form-control" 
                                    rows="4"
                                    :class="{ 'is-invalid': errors.note }"
                                    placeholder="یادداشت پرستاری را وارد کنید"
                                    required>
                                </textarea>
                                <div v-if="errors.note" class="invalid-feedback">
                                    {{ errors.note[0] }}
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeCreateModal">انصراف</button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="submitNote"
                            :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                            ذخیره
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Nursing Note Modal -->
        <div 
            v-if="showEditModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                         <h5 class="modal-title">ویرایش یادداشت پرستاری</h5>
                        <button type="button" class="btn-close" @click="closeEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="updateNote">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit-date" class="form-label">تاریخ</label>
                                    <input 
                                        id="edit-date"
                                        v-model="selectedNote.date" 
                                        type="date" 
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.date }"
                                        required>
                                    <div v-if="errors.date" class="invalid-feedback">
                                        {{ errors.date[0] }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit-nurse_id" class="form-label">پرستار</label>
                                    <select 
                                        id="edit-nurse_id"
                                        v-model="selectedNote.nurse_id" 
                                        class="form-select"
                                        :class="{ 'is-invalid': errors.nurse_id }"
                                        required>
                                        <option value="">انتخاب پرستار</option>
                                        <option v-for="nurse in nurses" :key="nurse.id" :value="nurse.id">
                                            {{ nurse.name }}
                                        </option>
                                    </select>
                                    <div v-if="errors.nurse_id" class="invalid-feedback">
                                        {{ errors.nurse_id[0] }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit-time_am" class="form-label">زمان صبح</label>
                                    <input 
                                        id="edit-time_am"
                                        v-model="selectedNote.time_am" 
                                        type="time" 
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.time_am }">
                                    <div v-if="errors.time_am" class="invalid-feedback">
                                        {{ errors.time_am[0] }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit-time_pm" class="form-label">زمان عصر</label>
                                    <input 
                                        id="edit-time_pm"
                                        v-model="selectedNote.time_pm" 
                                        type="time" 
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.time_pm }">
                                    <div v-if="errors.time_pm" class="invalid-feedback">
                                        {{ errors.time_pm[0] }}
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit-note" class="form-label">یادداشت</label>
                                <textarea 
                                    id="edit-note"
                                    v-model="selectedNote.note" 
                                    class="form-control" 
                                    rows="4"
                                    :class="{ 'is-invalid': errors.note }"
                                    placeholder="یادداشت پرستاری را وارد کنید"
                                    required>
                                </textarea>
                                <div v-if="errors.note" class="invalid-feedback">
                                    {{ errors.note[0] }}
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeEditModal">انصراف</button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="updateNote"
                            :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                            به‌روزرسانی
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Swal from 'sweetalert2'

export default {
    name: 'HospitalizationNursingNoteSection',
    props: {
        hospitalization: {
            type: Object,
            required: true
        },
        canAddNote: {
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
            nursingNotes: [],
            nurses: [],
            showCreateModal: false,
            showEditModal: false,
            selectedNote: null,
            loading: false,
            errors: {},
            form: {
                time_am: '',
                time_pm: '',
                note: '',
                date: '',
                nurse_id: ''
            }
        }
    },
    mounted() {
        console.log('Nursing Note component mounted with props:', {
            hospitalization: this.hospitalization,
            canAddNote: this.canAddNote,
            hospitalizationDischarged: this.hospitalizationDischarged
        });
        this.loadNurses()
        this.loadHospitalizationNursingNotes()
    },
    methods: {
        handleCreateClick() {
            console.log('Create button clicked for nursing note');
            console.log('Current state:', {
                showCreateModal: this.showCreateModal,
                hospitalizationDischarged: this.hospitalizationDischarged,
                canAddNote: this.canAddNote
            });
            this.showCreateModal = true;
        },

        printAllNotes() {
            const printUrl = `/nurse-notes/print?morphable_type=App%5CModels%5CHospitalization&morphable_id=${this.hospitalization.id}`;
            window.open(printUrl, '_blank');
        },

        async loadNurses() {
            try {
                const response = await fetch('/nursing-note-ajax/nurses')
                const data = await response.json()
                
                if (data.success) {
                    this.nurses = data.data
                } else {
                    console.error('Error loading nurses:', data.message)
                }
            } catch (error) {
                console.error('Error loading nurses:', error)
            }
        },

        async loadHospitalizationNursingNotes() {
            try {
                this.loading = true
                const response = await fetch(`/nursing-note-ajax/hospitalization-notes/${this.hospitalization.id}`)
                const data = await response.json()
                
                if (data.success) {
                    this.nursingNotes = data.data
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: data.message || 'خطا در دریافت یادداشت‌های پرستاری'
                    })
                }
            } catch (error) {
                console.error('Error loading nursing notes:', error)
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'خطا در دریافت یادداشت‌های پرستاری'
                })
            } finally {
                this.loading = false
            }
        },

        async submitNote() {
            try {
                this.loading = true
                this.errors = {}

                const response = await fetch('/nursing-note-ajax/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        time_am: this.form.time_am,
                        time_pm: this.form.time_pm,
                        note: this.form.note,
                        date: this.form.date,
                        nurse_id: this.form.nurse_id,
                        hospitalization_id: this.hospitalization.id
                    })
                })

                const data = await response.json()

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'موفق',
                        text: data.message
                    })
                    this.closeCreateModal()
                    this.loadHospitalizationNursingNotes()
                } else {
                    if (data.errors) {
                        this.errors = data.errors
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: data.message || 'خطا در ایجاد یادداشت پرستاری'
                    })
                }
            } catch (error) {
                console.error('Error creating nursing note:', error)
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'خطا در ایجاد یادداشت پرستاری'
                })
            } finally {
                this.loading = false
            }
        },

        editNote(note) {
            this.selectedNote = { ...note }
            this.showEditModal = true
        },

        async updateNote() {
            try {
                this.loading = true
                this.errors = {}

                const response = await fetch(`/nursing-note-ajax/update/${this.selectedNote.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        time_am: this.selectedNote.time_am,
                        time_pm: this.selectedNote.time_pm,
                        note: this.selectedNote.note,
                        date: this.selectedNote.date,
                        nurse_id: this.selectedNote.nurse_id
                    })
                })

                const data = await response.json()

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'موفق',
                        text: data.message
                    })
                    this.closeEditModal()
                    this.loadHospitalizationNursingNotes()
                } else {
                    if (data.errors) {
                        this.errors = data.errors
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: data.message || 'خطا در به‌روزرسانی یادداشت پرستاری'
                    })
                }
            } catch (error) {
                console.error('Error updating nursing note:', error)
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'خطا در به‌روزرسانی یادداشت پرستاری'
                })
            } finally {
                this.loading = false
            }
        },

        async deleteNote(noteId) {
            const result = await Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: 'این عمل قابل بازگشت نیست!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'انصراف'
            })

            if (result.isConfirmed) {
                try {
                    this.loading = true
                    const response = await fetch(`/nursing-note-ajax/delete/${noteId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })

                    const data = await response.json()

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'موفق',
                            text: data.message
                        })
                        this.loadHospitalizationNursingNotes()
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطا',
                            text: data.message || 'خطا در حذف یادداشت پرستاری'
                        })
                    }
                } catch (error) {
                    console.error('Error deleting nursing note:', error)
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: 'خطا در حذف یادداشت پرستاری'
                    })
                } finally {
                    this.loading = false
                }
            }
        },

        closeCreateModal() {
            this.showCreateModal = false
            this.form = {
                time_am: '',
                time_pm: '',
                note: '',
                date: '',
                nurse_id: ''
            }
            this.errors = {}
        },

        closeEditModal() {
            this.showEditModal = false
            this.selectedNote = null
            this.errors = {}
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A'
            const date = new Date(dateString)
            return date.toLocaleDateString('fa-IR')
        }
    }
}
</script>

<style scoped>
.nursing-note-section {
    direction: rtl;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.btn-sm {
    font-size: 0.875rem;
}

.modal {
    z-index: 1055;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>
