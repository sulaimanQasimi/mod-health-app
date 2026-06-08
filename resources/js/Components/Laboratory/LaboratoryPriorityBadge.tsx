import { LaboratoryPriority } from '../../types/laboratory';
import { useTranslation } from '../../hooks/useTranslation';

const priorityClasses: Record<LaboratoryPriority, string> = {
    normal: 'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300',
    urgent: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
    stat: 'animate-pulse border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300',
};

interface LaboratoryPriorityBadgeProps {
    priority: LaboratoryPriority;
}

export default function LaboratoryPriorityBadge({ priority }: LaboratoryPriorityBadgeProps) {
    const { t } = useTranslation();

    return (
        <span
            className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ${priorityClasses[priority]}`}
        >
            {priority === 'stat' && <i className="bx bx-error-circle me-1" />}
            {t(`global.${priority}`) || priority}
        </span>
    );
}
