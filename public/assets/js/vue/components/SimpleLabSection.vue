<template>
    <div class="lab-section">
        <!-- Lab Section Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-body-secondary text-body d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-hard-hat me-2 text-warning"></i>
                            معاینات (Lab Section)
                        </h5>
                        <button 
                            v-if="canAddLab && !appointmentCompleted" 
                            type="button" 
                            class="btn btn-primary btn-sm" 
                            @click="showCreateModal = true"
                        >
                            <i class="bx bx-plus me-1"></i>
                            اضافه کردن
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lab Tests List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                        <div class="card-body">
                            <div class="text-center py-4">
                                <div class="alert alert-success">
                                    <i class="bx bx-check-circle me-2"></i>
                                    <strong>Vue component loaded successfully!</strong> Lab section is working.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Appointment ID:</strong> {{ appointment.id }}</p>
                                        <p><strong>Patient ID:</strong> {{ appointment.patient_id }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Can Add Lab:</strong> {{ canAddLab ? 'Yes' : 'No' }}</p>
                                        <p><strong>Appointment Completed:</strong> {{ appointmentCompleted ? 'Yes' : 'No' }}</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-success" @click="testClick">
                                        <i class="bx bx-test-tube me-1"></i>
                                        Test Vue Click
                                    </button>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SimpleLabSection',
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
            showCreateModal: false,
            clickCount: 0
        }
    },
    mounted() {
        console.log('SimpleLabSection mounted successfully');
    },
    methods: {
        testClick() {
            this.clickCount++;
            alert(`Vue click event working! Click count: ${this.clickCount}`);
        }
    }
}
</script>

<style scoped>
.lab-section {
    margin: 20px 0;
}
</style>
