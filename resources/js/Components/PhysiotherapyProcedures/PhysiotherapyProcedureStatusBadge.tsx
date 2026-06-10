import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { PhysiotherapyProcedureStatus } from '../../types/physiotherapyProcedure';

const STATUS_COLORS: Record<PhysiotherapyProcedureStatus, 'warning' | 'info' | 'success' | 'failure' | 'gray'> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

interface PhysiotherapyProcedureStatusBadgeProps {
    status: PhysiotherapyProcedureStatus | string;
}

export default function PhysiotherapyProcedureStatusBadge({ status }: PhysiotherapyProcedureStatusBadgeProps) {
    const { t } = useTranslation();

    const labels: Record<string, string> = {
        pending: t('global.status_pending'),
        in_progress: t('global.status_in_progress'),
        completed: t('global.status_completed'),
        cancelled: t('global.status_cancelled'),
    };

    const color = STATUS_COLORS[status as PhysiotherapyProcedureStatus] ?? 'gray';

    return (
        <Badge color={color} className="w-fit font-normal">
            {labels[status] ?? status}
        </Badge>
    );
}
