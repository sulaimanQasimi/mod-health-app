export const EYE_GLASSES_STATUS_COLORS: Record<
    string,
    'warning' | 'info' | 'purple' | 'success' | 'failure' | 'gray'
> = {
    requested: 'warning',
    processing: 'info',
    paid: 'purple',
    delivered: 'success',
    cancelled: 'failure',
};

export function eyeGlassesStatusLabel(status: string, t: (key: string) => string): string {
    return (
        {
            requested: t('global.eye_glasses_status_requested'),
            processing: t('global.eye_glasses_status_processing'),
            paid: t('global.eye_glasses_status_paid'),
            delivered: t('global.eye_glasses_status_delivered'),
            cancelled: t('global.status_cancelled'),
        }[status] ?? status
    );
}
