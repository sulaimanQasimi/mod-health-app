import { Card } from 'flowbite-react';
import { HospitalizationDashboardStats } from '../../types/hospitalization';
import { useTranslation } from '../../hooks/useTranslation';

interface HospitalizationStatsCardsProps {
    stats: HospitalizationDashboardStats;
    variant?: 'active' | 'discharged';
}

const activeCards = [
    {
        key: 'active' as const,
        labelKey: 'global.hospitalized_patients',
        icon: 'bx-bed',
        gradient: 'from-emerald-500 to-teal-600',
        bg: 'bg-emerald-50/80 dark:bg-emerald-950/25',
        ring: 'ring-emerald-100 dark:ring-emerald-900/40',
    },
    {
        key: 'discharged' as const,
        labelKey: 'global.discharged_hospitalizations',
        icon: 'bx-exit',
        gradient: 'from-slate-500 to-gray-600',
        bg: 'bg-slate-50/80 dark:bg-slate-950/25',
        ring: 'ring-slate-100 dark:ring-slate-800/40',
    },
    {
        key: 'occupied_beds' as const,
        labelKey: 'global.occupied',
        icon: 'bx-user-pin',
        gradient: 'from-amber-500 to-orange-500',
        bg: 'bg-amber-50/80 dark:bg-amber-950/25',
        ring: 'ring-amber-100 dark:ring-amber-900/40',
    },
    {
        key: 'total_beds' as const,
        labelKey: 'global.beds',
        icon: 'bx-grid-alt',
        gradient: 'from-violet-500 to-purple-600',
        bg: 'bg-violet-50/80 dark:bg-violet-950/25',
        ring: 'ring-violet-100 dark:ring-violet-900/40',
    },
];

const dischargedCards = [
    {
        key: 'discharged' as const,
        labelKey: 'global.discharged_hospitalizations',
        icon: 'bx-exit',
        gradient: 'from-slate-500 to-gray-600',
        bg: 'bg-slate-50/80 dark:bg-slate-950/25',
        ring: 'ring-slate-100 dark:ring-slate-800/40',
    },
    {
        key: 'active' as const,
        labelKey: 'global.hospitalized_patients',
        icon: 'bx-bed',
        gradient: 'from-emerald-500 to-teal-600',
        bg: 'bg-emerald-50/80 dark:bg-emerald-950/25',
        ring: 'ring-emerald-100 dark:ring-emerald-900/40',
    },
    {
        key: 'recovered' as const,
        labelKey: 'global.recovered',
        icon: 'bx-check-circle',
        gradient: 'from-emerald-500 to-green-600',
        bg: 'bg-emerald-50/80 dark:bg-emerald-950/25',
        ring: 'ring-emerald-100 dark:ring-emerald-900/40',
    },
    {
        key: 'moved' as const,
        labelKey: 'global.moved',
        icon: 'bx-transfer',
        gradient: 'from-cyan-500 to-blue-600',
        bg: 'bg-cyan-50/80 dark:bg-cyan-950/25',
        ring: 'ring-cyan-100 dark:ring-cyan-900/40',
    },
];

export default function HospitalizationStatsCards({
    stats,
    variant = 'active',
}: HospitalizationStatsCardsProps) {
    const { t } = useTranslation();
    const cards = variant === 'discharged' ? dischargedCards : activeCards;

    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {cards.map((card) => (
                <Card
                    key={card.key}
                    className={`${card.bg} ring-1 ${card.ring} shadow-sm transition-shadow hover:shadow-md`}
                >
                    <div className="flex items-start justify-between gap-3">
                        <div className="min-w-0">
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {t(card.labelKey)}
                            </p>
                            <p className="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {stats[card.key] ?? 0}
                            </p>
                        </div>
                        <div
                            className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${card.gradient} text-white shadow-md`}
                        >
                            <i className={`bx ${card.icon} text-xl`} />
                        </div>
                    </div>
                </Card>
            ))}
        </div>
    );
}
