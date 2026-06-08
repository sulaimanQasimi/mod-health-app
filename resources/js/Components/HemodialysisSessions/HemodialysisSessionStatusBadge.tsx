import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { HemodialysisSessionStatus } from '../../types/hemodialysisSession';

const STATUS_COLORS: Record<HemodialysisSessionStatus, 'warning' | 'info' | 'success' | 'failure'> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

interface HemodialysisSessionStatusBadgeProps {
    status: HemodialysisSessionStatus | string;
}

export default function HemodialysisSessionStatusBadge({ status }: HemodialysisSessionStatusBadgeProps) {
    const { t } = useTranslation();

    const labels: Record<string, string> = {
        pending: t('global.pending'),
        in_progress: t('global.in_progress'),
        completed: t('global.completed'),
        cancelled: t('global.cancelled'),
    };

    const color = STATUS_COLORS[status as HemodialysisSessionStatus] ?? 'gray';

    return <Badge color={color}>{labels[status] ?? status}</Badge>;
}
