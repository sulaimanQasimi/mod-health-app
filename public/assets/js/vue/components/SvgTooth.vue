<template>
    <div 
        class="tooth-wrapper" 
        :class="{ 
            'has-data': toothData, 
            'clickable': true,
            'selected': isSelected
        }"
        @click="handleClick"
        :title="tooltipText"
    >
        <!-- Simple oval tooth representation -->
        <div class="tooth-oval" :class="conditionClass" :style="toothStyle">
            <!-- Tooth number inside oval -->
            <span class="tooth-number-label">{{ toothNumber }}</span>
        </div>
        
        <!-- Tooth number below oval -->
        <div class="tooth-number-below">{{ toothNumber }}</div>
    </div>
</template>

<script>
export default {
    name: 'SvgTooth',
    props: {
        toothNumber: {
            type: Number,
            required: true
        },
        toothData: {
            type: Object,
            default: null
        },
        toothType: {
            type: String,
            default: 'incisor'
        },
        isSelected: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        condition() {
            return this.toothData?.tooth_condition || 'no_data'
        },
        conditionClass() {
            return `tooth-${this.condition}`
        },
        conditionColor() {
            const colors = {
                healthy: '#008000', // Green as shown in image
                cavity: '#ffc107', // Yellow
                filling: '#17a2b8', // Cyan
                crown: '#6f42c1', // Purple
                bridge: '#6610f2', // Indigo
                missing: '#6c757d', // Gray
                extraction: '#dc3545', // Red
                impacted: '#fd7e14', // Orange
                root_canal: '#20c997', // Teal
                implant: '#0d6efd', // Blue
                decay: '#e83e8c', // Pink
                fractured: '#ff6b6b', // Light red
                no_data: '#008000' // Default to green
            }
            return colors[this.condition] || '#008000'
        },
        toothStyle() {
            return {
                backgroundColor: this.conditionColor,
                borderColor: this.isSelected ? '#0066ff' : '#000000'
            }
        },
        tooltipText() {
            if (this.toothData) {
                return `Tooth ${this.toothNumber}: ${this.toothData.tooth_condition}`
            }
            return `Tooth ${this.toothNumber}: No data`
        }
    },
    methods: {
        handleClick() {
            this.$emit('click', this.toothNumber)
        }
    }
}
</script>

<style scoped>
.tooth-wrapper {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    margin: 0 4px;
    cursor: pointer;
    transition: transform 0.2s;
    position: relative;
}

.tooth-wrapper:hover {
    transform: scale(1.05);
    z-index: 10;
}

.tooth-wrapper.selected {
    z-index: 15;
}

.tooth-oval {
    width: 50px;
    height: 70px;
    border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
    border: 2px solid #000000;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.2s;
}

.tooth-wrapper.selected .tooth-oval {
    border: 3px solid #0066ff;
    box-shadow: 0 0 0 2px rgba(0, 102, 255, 0.3);
}

.tooth-number-label {
    font-size: 12px;
    font-weight: bold;
    color: #ffffff;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.tooth-number-below {
    margin-top: 4px;
    font-size: 11px;
    font-weight: 600;
    color: #333;
    text-align: center;
}

.tooth-wrapper.has-data {
    opacity: 1;
}

.tooth-wrapper:not(.has-data) {
    opacity: 0.9;
}

/* Condition-based styling */
.tooth-oval.tooth-healthy {
    background-color: #008000 !important;
}

.tooth-oval.tooth-cavity {
    background-color: #ffc107 !important;
}

.tooth-oval.tooth-filling {
    background-color: #17a2b8 !important;
}

.tooth-oval.tooth-crown {
    background-color: #6f42c1 !important;
}

.tooth-oval.tooth-missing {
    background-color: #6c757d !important;
    opacity: 0.6;
}

.tooth-oval.tooth-extraction {
    background-color: #dc3545 !important;
}

.tooth-oval.tooth-no_data {
    background-color: #008000 !important;
}
</style>
