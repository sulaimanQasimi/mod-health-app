import { createApp } from 'vue'
import NephrologyRegistrationSection from './components/NephrologyRegistrationSection.vue'

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('nephrology-registration-section-container');

    if (!container) {
        return;
    }

    const entity = JSON.parse(container.dataset.entity || '{}');
    const entityType = container.dataset.entityType || 'appointment';
    const entityId = container.dataset.entityId || null;
    const storeUrl = container.dataset.storeUrl || '';
    const todayVisitDate = container.dataset.todayVisitDate || '';
    const todayVisitDateDisplay = container.dataset.todayVisitDateDisplay || '';
    const defaultDoctorId = container.dataset.defaultDoctorId || '';
    const permissions = JSON.parse(container.dataset.permissions || '{}');
    const translations = JSON.parse(container.dataset.translations || '{}');
    const appointmentCompleted = container.dataset.appointmentCompleted === 'true';

    const app = createApp(NephrologyRegistrationSection, {
        appointment: entity,
        entityType,
        entityId,
        storeUrl,
        todayVisitDate,
        todayVisitDateDisplay,
        defaultDoctorId,
        translations,
        canOpenNephrology: permissions.canOpenNephrology || false,
        appointmentCompleted,
    });

    app.mount('#nephrology-registration-section-container');
});
