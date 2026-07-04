import StatCard from '../ui/StatCard';
import { useTranslation } from '../../hooks/useTranslation';
import { HemodialysisSessionStats as Stats } from '../../types/hemodialysisSession';

interface HemodialysisSessionStatsProps {
    stats: Stats;
}

const cards: Array<{
    key: keyof Stats;
    labelKey: string;
    iconClass: string;
    iconBgClass: string;
    borderClass: string;
    valueClass: string;
}> = [
    {
        key: 'total',
        labelKey: 'global.total',
        iconClass: 'bx bx-list-ul',
        iconBgClass: 'bg-blue-600',
        borderClass: 'border-blue-200 dark:border-blue-800',
        valueClass: 'text-blue-700 dark:text-blue-300',
    },
    {
        key: 'pending',
        labelKey: 'global.pending',
        iconClass: 'bx bx-time',
        iconBgClass: 'bg-amber-500',
        borderClass: 'border-amber-200 dark:border-amber-800',
        valueClass: 'text-amber-700 dark:text-amber-300',
    },
    {
        key: 'in_progress',
        labelKey: 'global.in_progress',
        iconClass: 'bx bx-loader-circle',
        iconBgClass: 'bg-cyan-600',
        borderClass: 'border-cyan-200 dark:border-cyan-800',
        valueClass: 'text-cyan-700 dark:text-cyan-300',
    },
    {
        key: 'completed',
        labelKey: 'global.completed',
        iconClass: 'bx bx-check-circle',
        iconBgClass: 'bg-emerald-600',
        borderClass: 'border-emerald-200 dark:border-emerald-800',
        valueClass: 'text-emerald-700 dark:text-emerald-300',
    },
    {
        key: 'cancelled',
        labelKey: 'global.cancelled',
        iconClass: 'bx bx-x-circle',
        iconBgClass: 'bg-red-600',
        borderClass: 'border-red-200 dark:border-red-800',
        valueClass: 'text-red-700 dark:text-red-300',
    },
];

export default function HemodialysisSessionStats({ stats }: HemodialysisSessionStatsProps) {
    const { t } = useTranslation();

    return (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
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
