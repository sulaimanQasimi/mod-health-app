import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { DentistRegistrationStatus } from '../../types/dentistRegistration';

const STATUS_COLORS: Record<DentistRegistrationStatus, 'warning' | 'info' | 'success' | 'failure'> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

interface DentistRegistrationStatusBadgeProps {
    status: DentistRegistrationStatus | string;
}

export default function DentistRegistrationStatusBadge({ status }: DentistRegistrationStatusBadgeProps) {
    const { t } = useTranslation();

    const labels: Record<string, string> = {
        pending: t('global.status_pending'),
        in_progress: t('global.status_in_progress'),
        completed: t('global.status_completed'),
        cancelled: t('global.status_cancelled'),
        planned: t('global.planned'),
    };

    const color = STATUS_COLORS[status as DentistRegistrationStatus] ?? 'gray';

    return <Badge color={color}>{labels[status] ?? status}</Badge>;
}
