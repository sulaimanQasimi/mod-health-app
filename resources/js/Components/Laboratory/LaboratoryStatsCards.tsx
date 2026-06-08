import { Card } from 'flowbite-react';
import { LaboratoryGroupedStats } from '../../types/laboratory';
import { useTranslation } from '../../hooks/useTranslation';

interface LaboratoryStatsCardsProps {
    stats: LaboratoryGroupedStats;
}

const cards = [
    {
        key: 'pending' as const,
        labelKey: 'global.pending_tests',
        icon: 'bx-hourglass',
        gradient: 'from-amber-500 to-orange-500',
        bg: 'bg-amber-50 dark:bg-amber-950/20',
    },
    {
        key: 'completed' as const,
        labelKey: 'global.completed_tests',
        icon: 'bx-check-double',
        gradient: 'from-emerald-500 to-green-600',
        bg: 'bg-emerald-50 dark:bg-emerald-950/20',
    },
    {
        key: 'in_progress' as const,
        labelKey: 'global.in_progress_tests',
        icon: 'bx-time-five',
        gradient: 'from-cyan-500 to-blue-600',
        bg: 'bg-cyan-50 dark:bg-cyan-950/20',
    },
    {
        key: 'total' as const,
        labelKey: 'global.total_tests',
        icon: 'bx-clipboard',
        gradient: 'from-violet-500 to-purple-600',
        bg: 'bg-violet-50 dark:bg-violet-950/20',
    },
];

export default function LaboratoryStatsCards({ stats }: LaboratoryStatsCardsProps) {
    const { t } = useTranslation();

    return (
        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {cards.map((card) => (
                <Card key={card.key} className={`${card.bg} shadow-sm`}>
                    <div className="flex items-start justify-between">
                        <div>
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {t(card.labelKey)}
                            </p>
                            <p className="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                {stats[card.key]}
                            </p>
                        </div>
                        <div
                            className={`flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br ${card.gradient} text-white shadow`}
                        >
                            <i className={`bx ${card.icon} text-xl`} />
                        </div>
                    </div>
                </Card>
            ))}
        </div>
    );
}
