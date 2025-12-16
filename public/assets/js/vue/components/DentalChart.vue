<template>
    <div class="dental-chart-container">
        <div class="chart-header text-center mb-4">
            <h5 class="mb-1">دندان‌ها</h5>
            <p class="text-muted mb-0 small">دندان‌های دائمی</p>
        </div>

        <!-- Upper Jaw Section -->
        <div class="jaw-section upper-jaw mb-4">
            <div class="jaw-label text-center mb-3">
                <strong>جوف فوقانی</strong>
            </div>
            
            <!-- Upper Right Quadrant (18-11) -->
            <div class="quadrant-row d-flex justify-content-center align-items-center mb-2">
                <svg-tooth
                    v-for="toothNum in [18, 17, 16, 15, 14, 13, 12, 11]"
                    :key="'upper-right-' + toothNum"
                    :tooth-number="toothNum"
                    :tooth-data="getToothData(toothNum)"
                    :tooth-type="getToothType(toothNum)"
                    :is-selected="selectedTooth === toothNum"
                    @click="handleToothClick(toothNum)"
                />
            </div>

            <!-- Upper Left Quadrant (21-28) -->
            <div class="quadrant-row d-flex justify-content-center align-items-center mb-2">
                <svg-tooth
                    v-for="toothNum in [21, 22, 23, 24, 25, 26, 27, 28]"
                    :key="'upper-left-' + toothNum"
                    :tooth-number="toothNum"
                    :tooth-data="getToothData(toothNum)"
                    :tooth-type="getToothType(toothNum)"
                    :is-selected="selectedTooth === toothNum"
                    @click="handleToothClick(toothNum)"
                />
            </div>
        </div>

        <!-- Spacer -->
        <div class="jaw-spacer my-4"></div>

        <!-- Lower Jaw Section -->
        <div class="jaw-section lower-jaw">
            <div class="jaw-label text-center mb-3">
                <strong>جوف تحتانی</strong>
            </div>
            
            <!-- Lower Left Quadrant (48-41) -->
            <div class="quadrant-row d-flex justify-content-center align-items-center mb-2">
                <svg-tooth
                    v-for="toothNum in [48, 47, 46, 45, 44, 43, 42, 41]"
                    :key="'lower-left-' + toothNum"
                    :tooth-number="toothNum"
                    :tooth-data="getToothData(toothNum)"
                    :tooth-type="getToothType(toothNum)"
                    :is-selected="selectedTooth === toothNum"
                    @click="handleToothClick(toothNum)"
                />
            </div>

            <!-- Lower Right Quadrant (31-38) -->
            <div class="quadrant-row d-flex justify-content-center align-items-center mb-2">
                <svg-tooth
                    v-for="toothNum in [31, 32, 33, 34, 35, 36, 37, 38]"
                    :key="'lower-right-' + toothNum"
                    :tooth-number="toothNum"
                    :tooth-data="getToothData(toothNum)"
                    :tooth-type="getToothType(toothNum)"
                    :is-selected="selectedTooth === toothNum"
                    @click="handleToothClick(toothNum)"
                />
            </div>
        </div>

        <!-- Legend -->
        <div class="chart-legend mt-4 pt-3 border-top">
            <h6 class="mb-2 text-center">راهنما:</h6>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <span class="badge" style="background-color: #008000; color: white;">
                    سالم
                </span>
                <span class="badge" style="background-color: #ffc107; color: #000;">
                    پوسیدگی
                </span>
                <span class="badge" style="background-color: #17a2b8; color: white;">
                    پرکردگی
                </span>
                <span class="badge" style="background-color: #6f42c1; color: white;">
                    پوش 
                </span>
                <span class="badge" style="background-color: #6c757d; color: white;">
                    فاقد دندان
                </span>
                <span class="badge" style="background-color: #dc3545; color: white;">
                    کشیده شده
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import SvgTooth from './SvgTooth.vue'

export default {
    name: 'DentalChart',
    components: {
        SvgTooth
    },
    props: {
        teethData: {
            type: Object,
            default: () => ({})
        },
        dentistRegistrationId: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            selectedTooth: null
        }
    },
    methods: {
        getToothData(toothNumber) {
            return this.teethData[toothNumber] || null
        },
        getToothType(toothNumber) {
            // Molars: 16-18, 26-28, 36-38, 46-48
            if ([16, 17, 18, 26, 27, 28, 36, 37, 38, 46, 47, 48].includes(toothNumber)) {
                return 'molar'
            }
            // Premolars: 14-15, 24-25, 34-35, 44-45
            if ([14, 15, 24, 25, 34, 35, 44, 45].includes(toothNumber)) {
                return 'premolar'
            }
            // Canines: 13, 23, 33, 43
            if ([13, 23, 33, 43].includes(toothNumber)) {
                return 'canine'
            }
            // Incisors: 11-12, 21-22, 31-32, 41-42
            return 'incisor'
        },
        handleToothClick(toothNumber) {
            console.log('Tooth clicked:', toothNumber);
            // Set selected tooth
            this.selectedTooth = toothNumber
            
            // Emit event and also call global handler if available
            this.$emit('tooth-clicked', toothNumber)
            
            // Get tooth data and chart ID
            const toothData = this.getToothData(toothNumber)
            const chartId = toothData ? toothData.id : null
            
            console.log('Tooth data:', toothData, 'Chart ID:', chartId);
            console.log('openToothModal available:', typeof window.openToothModal);
            
            // Call modal function - ensure it's available
            if (typeof window.openToothModal === 'function') {
                try {
                    window.openToothModal(toothNumber, chartId)
                } catch (error) {
                    console.error('Error calling openToothModal:', error);
                    alert('Error opening modal: ' + error.message);
                }
            } else {
                console.warn('openToothModal not available, retrying...');
                // Fallback: try again after a short delay
                setTimeout(() => {
                    if (typeof window.openToothModal === 'function') {
                        window.openToothModal(toothNumber, chartId)
                    } else {
                        console.error('openToothModal function still not available after retry');
                        alert('Unable to open modal. Please refresh the page.')
                    }
                }, 200)
            }
        },
        localize(key) {
            // This will be handled by the parent or a global localization function
            return window.localize ? window.localize(key) : key
        }
    }
}
</script>

<style scoped>
.dental-chart-container {
    padding: 20px;
    border-radius: 8px;
    max-width: 1000px;
    margin: 0 auto;
}

.jaw-section {
    margin: 20px 0;
}

.jaw-label {
    font-size: 1.1rem;
    font-weight: 600;
    color: #c9b6b6;
    margin-bottom: 15px;
}

.quadrant-row {
    gap: 8px;
    flex-wrap: wrap;
    padding: 10px 0;
}

.jaw-spacer {
    min-height: 30px;
}

.chart-legend {
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
}

.chart-header {
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.chart-header h5 {
    color: #333;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .quadrant-row {
        gap: 4px;
    }
}
</style>
