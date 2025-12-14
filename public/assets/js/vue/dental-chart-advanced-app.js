import { createApp } from 'vue'
import ImageGallery from './components/ImageGallery.vue'
import PeriodontalChart from './components/PeriodontalChart.vue'

// Initialize Vue apps for advanced features
document.addEventListener('DOMContentLoaded', function() {
    // Image Gallery
    const imageGalleryContainer = document.getElementById('image-gallery-container');
    if (imageGalleryContainer) {
        const dentalChartId = parseInt(imageGalleryContainer.dataset.dentalChartId || '0');
        const imagesRaw = imageGalleryContainer.dataset.images || '[]';
        let images = [];
        
        try {
            images = JSON.parse(imagesRaw);
        } catch (e) {
            console.error('Error parsing images data:', e);
        }

        const app = createApp(ImageGallery, {
            dentalChartId: dentalChartId,
            initialImages: images
        });
        
        app.mount('#image-gallery-container');
    }

    // Periodontal Chart
    const periodontalContainer = document.getElementById('periodontal-chart-container');
    if (periodontalContainer) {
        const dentalChartId = parseInt(periodontalContainer.dataset.dentalChartId || '0');
        const measurementsRaw = periodontalContainer.dataset.measurements || '[]';
        let measurements = [];
        
        try {
            measurements = JSON.parse(measurementsRaw);
        } catch (e) {
            console.error('Error parsing measurements data:', e);
        }

        const app = createApp(PeriodontalChart, {
            dentalChartId: dentalChartId,
            initialMeasurements: measurements
        });
        
        app.mount('#periodontal-chart-container');
    }
});
