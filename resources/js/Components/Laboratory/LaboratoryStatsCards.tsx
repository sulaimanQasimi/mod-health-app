import StatCard from '../ui/StatCard';
import { LaboratoryGroupedStats } from '../../types/laboratory';
import { useTranslation } from '../../hooks/useTranslation';

interface LaboratoryStatsCardsProps {
    stats: LaboratoryGroupedStats;
}

const cards = [
    {
        key: 'pending' as const,
        labelKey: 'global.pending_tests',
        iconClass: 'bx bx-hourglass',
        iconBgClass: 'bg-amber-500',
        borderClass: 'border-amber-200 dark:border-amber-800',
        valueClass: 'text-amber-700 dark:text-amber-300',
    },
    {
        key: 'completed' as const,
        labelKey: 'global.completed_tests',
        iconClass: 'bx bx-check-double',
        iconBgClass: 'bg-emerald-600',
        borderClass: 'border-emerald-200 dark:border-emerald-800',
        valueClass: 'text-emerald-700 dark:text-emerald-300',
    },
    {
        key: 'in_progress' as const,
        labelKey: 'global.in_progress_tests',
        iconClass: 'bx bx-time-five',
        iconBgClass: 'bg-cyan-600',
        borderClass: 'border-cyan-200 dark:border-cyan-800',
        valueClass: 'text-cyan-700 dark:text-cyan-300',
    },
    {
        key: 'total' as const,
        labelKey: 'global.total_tests',
        iconClass: 'bx bx-clipboard',
        iconBgClass: 'bg-violet-600',
        borderClass: 'border-violet-200 dark:border-violet-800',
        valueClass: 'text-violet-700 dark:text-violet-300',
    },
];

export default function LaboratoryStatsCards({ stats }: LaboratoryStatsCardsProps) {
    const { t } = useTranslation();

    return (
        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {cards.map((card) => (
                <StatCard
                    key={card.key}
                    title={t(card.labelKey)}
                    value={stats[card.key]}
                    subtitle=""
                    iconClass={card.iconClass}
                    iconBgClass={card.iconBgClass}
                    borderClass={card.borderClass}
                    valueClass={card.valueClass}
                />
            ))}
        </div>
    );
}
