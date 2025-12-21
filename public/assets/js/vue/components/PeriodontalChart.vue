<template>
    <div class="periodontal-chart">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ localize('global.periodontal_measurements') }}</h6>
            <button class="btn btn-sm btn-primary" @click="showForm = true" v-if="!showForm">
                <i class="bx bx-plus"></i> {{ localize('global.add_measurements') }}
            </button>
        </div>

        <!-- Measurement Form -->
        <div v-if="showForm" class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ localize('global.6_point_measurements') }}</h6>
                <button class="btn btn-sm btn-secondary" @click="showForm = false">
                    <i class="bx bx-x"></i>
                </button>
            </div>
            <div class="card-body">
                <form @submit.prevent="saveMeasurements">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ localize('global.measurement_date') }}</label>
                            <input type="text" 
                                   ref="measurementDateInput"
                                   v-model="measurementForm.measurement_date" 
                                   class="form-control datepicker_dari"
                                   readonly
                                   required>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.measurement_point') }}</th>
                                    <th>{{ localize('global.pocket_depth') }} (mm)</th>
                                    <th>{{ localize('global.recession') }} (mm)</th>
                                    <th>{{ localize('global.bleeding') }}</th>
                                    <th>{{ localize('global.plaque') }}</th>
                                    <th>{{ localize('global.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="point in measurementPoints" :key="point.value">
                                    <td><strong>{{ point.label }}</strong></td>
                                    <td>
                                        <input type="number" 
                                               step="0.1" 
                                               min="0" 
                                               max="20"
                                               v-model="measurementForm.measurements[point.value].pocket_depth"
                                               class="form-control form-control-sm"
                                               required>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               step="0.1" 
                                               min="0" 
                                               max="10"
                                               v-model="measurementForm.measurements[point.value].recession"
                                               class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <input type="checkbox" 
                                               v-model="measurementForm.measurements[point.value].bleeding"
                                               class="form-check-input">
                                    </td>
                                    <td>
                                        <input type="checkbox" 
                                               v-model="measurementForm.measurements[point.value].plaque"
                                               class="form-check-input">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               v-model="measurementForm.measurements[point.value].notes"
                                               class="form-control form-control-sm">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="resetForm">
                            {{ localize('global.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                            {{ localize('global.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Measurements Display -->
        <div v-if="measurements.length > 0" class="card">
            <div class="card-body">
                <div v-for="(group, date) in groupedMeasurements" :key="date" class="mb-4">
                    <h6 class="mb-2">{{ localize('global.date') }}: {{ date }}</h6>
                    <div class="row g-2">
                        <div v-for="measurement in group" 
                             :key="measurement.id" 
                             class="col-md-4 col-sm-6">
                            <div class="card" :class="getHealthClass(measurement.pocket_depth)">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>{{ getPointLabel(measurement.measurement_point) }}</strong>
                                        <span class="badge" :class="getDepthBadgeClass(measurement.pocket_depth)">
                                            {{ measurement.pocket_depth }}mm
                                        </span>
                                    </div>
                                    <div class="small mt-1">
                                        <span v-if="measurement.recession" class="text-muted">
                                            Recession: {{ measurement.recession }}mm
                                        </span>
                                        <span v-if="measurement.bleeding" class="badge bg-danger ms-1">B</span>
                                        <span v-if="measurement.plaque" class="badge bg-warning ms-1">P</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else-if="!showForm" class="text-center text-muted py-4">
            <p>{{ localize('global.no_measurements_recorded') }}</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PeriodontalChart',
    props: {
        dentalChartId: {
            type: Number,
            required: true
        },
        initialMeasurements: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            measurements: [...this.initialMeasurements],
            showForm: false,
            saving: false,
            measurementPoints: [
                { value: 'mesial', label: 'Mesial' },
                { value: 'mid_mesial', label: 'Mid-Mesial' },
                { value: 'mid', label: 'Mid' },
                { value: 'mid_distal', label: 'Mid-Distal' },
                { value: 'distal', label: 'Distal' },
                { value: 'lingual', label: 'Lingual' },
                { value: 'palatal', label: 'Palatal' }
            ],
            measurementForm: {
                measurement_date: '',
                measurements: {}
            }
        }
    },
    computed: {
        groupedMeasurements() {
            const grouped = {};
            this.measurements.forEach(m => {
                const date = m.measurement_date;
                if (!grouped[date]) {
                    grouped[date] = [];
                }
                grouped[date].push(m);
            });
            return grouped;
        }
    },
    mounted() {
        // Initialize measurement form
        const measurements = {};
        this.measurementPoints.forEach(point => {
            measurements[point.value] = {
                measurement_point: point.value,
                pocket_depth: '',
                recession: '',
                bleeding: false,
                plaque: false,
                notes: ''
            };
        });
        this.measurementForm.measurements = measurements;
    },
    watch: {
        showForm(newVal) {
            if (newVal) {
                this.$nextTick(() => {
                    this.initDatePicker();
                });
            }
        }
    },
    methods: {
        async saveMeasurements() {
            this.saving = true;
            
            const measurements = Object.values(this.measurementForm.measurements).map(m => ({
                measurement_point: m.measurement_point,
                pocket_depth: parseFloat(m.pocket_depth),
                recession: m.recession ? parseFloat(m.recession) : null,
                bleeding: m.bleeding || false,
                plaque: m.plaque || false,
                notes: m.notes || ''
            }));

            try {
                const response = await fetch(`/dental-periodontal/store/${this.dentalChartId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        measurement_date: this.measurementForm.measurement_date,
                        measurements: measurements
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Reload measurements
                    await this.loadMeasurements();
                    this.resetForm();
                    this.showForm = false;
                    this.$emit('measurements-saved', result.data);
                } else {
                    alert(result.message || this.localize('global.save_failed'));
                }
            } catch (error) {
                console.error('Save error:', error);
                alert(this.localize('global.save_failed'));
            } finally {
                this.saving = false;
            }
        },
        async loadMeasurements() {
            try {
                const response = await fetch(`/dental-periodontal/measurements/${this.dentalChartId}`);
                const result = await response.json();
                if (result.success) {
                    // Flatten grouped measurements
                    this.measurements = Object.values(result.data).flat();
                }
            } catch (error) {
                console.error('Load error:', error);
            }
        },
        resetForm() {
            this.measurementPoints.forEach(point => {
                this.measurementForm.measurements[point.value] = {
                    measurement_point: point.value,
                    pocket_depth: '',
                    recession: '',
                    bleeding: false,
                    plaque: false,
                    notes: ''
                };
            });
            // Reset date - will be set by datepicker
            this.measurementForm.measurement_date = '';
            this.$nextTick(() => {
                this.initDatePicker();
            });
        },
        initDatePicker() {
            if (typeof window.$ !== 'undefined' && window.$.fn.persianDatepicker) {
                const dateInput = this.$refs.measurementDateInput;
                if (dateInput) {
                    const $input = $(dateInput);
                    // Check if datepicker is already initialized
                    if (!$input.data('persianDatepicker')) {
                        $input.persianDatepicker({
                            formatDate: 'YYYY-MM-DD',
                            calendar: {
                                persian: {
                                    locale: 'en',
                                    showHint: true,
                                    leapYearMode: 'algorithmic'
                                }
                            },
                            checkDate: function(unix) {
                                return true;
                            },
                            onSelect: () => {
                                // Update v-model when date is selected
                                this.measurementForm.measurement_date = $input.val();
                            }
                        });
                    }
                }
            }
        },
        getPointLabel(point) {
            const pointObj = this.measurementPoints.find(p => p.value === point);
            return pointObj ? pointObj.label : point;
        },
        getHealthClass(depth) {
            if (depth < 3) return 'border-success';
            if (depth <= 5) return 'border-warning';
            return 'border-danger';
        },
        getDepthBadgeClass(depth) {
            if (depth < 3) return 'bg-success';
            if (depth <= 5) return 'bg-warning';
            return 'bg-danger';
        },
        localize(key) {
            return window.localize ? window.localize(key) : key;
        }
    }
}
</script>

<style scoped>
.periodontal-chart {
    margin-top: 15px;
}
</style>
