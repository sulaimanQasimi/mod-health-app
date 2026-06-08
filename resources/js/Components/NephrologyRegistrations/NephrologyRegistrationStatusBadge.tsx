import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { NephrologyRegistrationStatus } from '../../types/nephrologyRegistration';

const STATUS_COLORS: Record<NephrologyRegistrationStatus, 'warning' | 'info' | 'success' | 'failure'> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

interface NephrologyRegistrationStatusBadgeProps {
    status: NephrologyRegistrationStatus | string;
}

export default function NephrologyRegistrationStatusBadge({ status }: NephrologyRegistrationStatusBadgeProps) {
    const { t } = useTranslation();

    const labels: Record<string, string> = {
        pending: t('global.pending'),
        in_progress: t('global.in_progress'),
        completed: t('global.completed'),
        cancelled: t('global.cancelled'),
    };

    const color = STATUS_COLORS[status as NephrologyRegistrationStatus] ?? 'gray';

    return <Badge color={color}>{labels[status] ?? status}</Badge>;
}
