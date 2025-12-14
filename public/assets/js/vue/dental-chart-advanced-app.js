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
            // Clean the data string
            const cleanData = imagesRaw.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, '&');
            images = JSON.parse(cleanData);
        } catch (e) {
            console.error('Error parsing images data:', e, 'Raw:', imagesRaw);
            images = [];
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
            // Clean the data string
            const cleanData = measurementsRaw.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, '&');
            measurements = JSON.parse(cleanData);
        } catch (e) {
            console.error('Error parsing measurements data:', e, 'Raw:', measurementsRaw);
            measurements = [];
        }

        const app = createApp(PeriodontalChart, {
            dentalChartId: dentalChartId,
            initialMeasurements: measurements
        });
        
        app.mount('#periodontal-chart-container');
    }
});
