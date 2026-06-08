import { Badge } from 'flowbite-react';
import { LaboratoryStatus } from '../../types/laboratory';
import { useTranslation } from '../../hooks/useTranslation';

const statusColors: Record<LaboratoryStatus, string> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

interface LaboratoryStatusBadgeProps {
    status: LaboratoryStatus;
}

export default function LaboratoryStatusBadge({ status }: LaboratoryStatusBadgeProps) {
    const { t } = useTranslation();

    return (
        <Badge color={statusColors[status] ?? 'gray'} className="w-fit">
            {t(`global.${status}`) || status}
        </Badge>
    );
}
