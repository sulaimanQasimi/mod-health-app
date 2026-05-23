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
    const openUrl = container.dataset.openUrl || '';
    const permissions = JSON.parse(container.dataset.permissions || '{}');
    const appointmentCompleted = container.dataset.appointmentCompleted === 'true';

    const app = createApp(NephrologyRegistrationSection, {
        appointment: entity,
        entityType,
        entityId,
        openUrl,
        canOpenNephrology: permissions.canOpenNephrology || false,
        appointmentCompleted,
    });

    app.mount('#nephrology-registration-section-container');
});
