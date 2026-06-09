import { HospitalizationDashboardStats } from '../../types/hospitalization';
import { useTranslation } from '../../hooks/useTranslation';
import { HOSPITALIZATION_CARD_CLASS } from './hospitalizationUi';

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
        tint: 'bg-emerald-50 dark:bg-emerald-950/30',
    },
    {
        key: 'discharged' as const,
        labelKey: 'global.discharged_hospitalizations',
        icon: 'bx-exit',
        gradient: 'from-slate-500 to-gray-600',
        tint: 'bg-slate-50 dark:bg-slate-900/40',
    },
    {
        key: 'occupied_beds' as const,
        labelKey: 'global.occupied',
        icon: 'bx-user-pin',
        gradient: 'from-amber-500 to-orange-500',
        tint: 'bg-amber-50 dark:bg-amber-950/30',
    },
    {
        key: 'total_beds' as const,
        labelKey: 'global.beds',
        icon: 'bx-grid-alt',
        gradient: 'from-violet-500 to-purple-600',
        tint: 'bg-violet-50 dark:bg-violet-950/30',
    },
];

const dischargedCards = [
    {
        key: 'discharged' as const,
        labelKey: 'global.discharged_hospitalizations',
        icon: 'bx-exit',
        gradient: 'from-slate-500 to-gray-600',
        tint: 'bg-slate-50 dark:bg-slate-900/40',
    },
    {
        key: 'active' as const,
        labelKey: 'global.hospitalized_patients',
        icon: 'bx-bed',
        gradient: 'from-emerald-500 to-teal-600',
        tint: 'bg-emerald-50 dark:bg-emerald-950/30',
    },
    {
        key: 'recovered' as const,
        labelKey: 'global.recovered',
        icon: 'bx-check-circle',
        gradient: 'from-emerald-500 to-green-600',
        tint: 'bg-emerald-50 dark:bg-emerald-950/30',
    },
    {
        key: 'moved' as const,
        labelKey: 'global.moved',
        icon: 'bx-transfer',
        gradient: 'from-cyan-500 to-blue-600',
        tint: 'bg-cyan-50 dark:bg-cyan-950/30',
    },
];

function formatStatValue(
    key: string,
    stats: HospitalizationDashboardStats
): string {
    if (key === 'occupied_beds') {
        const total = stats.total_beds ?? 0;
        const occupied = stats.occupied_beds ?? 0;
        return total > 0 ? `${occupied} / ${total}` : String(occupied);
    }

    return String(stats[key as keyof HospitalizationDashboardStats] ?? 0);
}

function formatStatHint(
    key: string,
    stats: HospitalizationDashboardStats,
    t: (key: string) => string
): string | null {
    if (key === 'occupied_beds' && (stats.total_beds ?? 0) > 0) {
        const rate = Math.round(((stats.occupied_beds ?? 0) / stats.total_beds) * 100);
        return `${rate}% ${t('global.occupied')}`;
    }

    if (key === 'total_beds') {
        const available = Math.max((stats.total_beds ?? 0) - (stats.occupied_beds ?? 0), 0);
        return `${available} ${t('global.empty_bed')}`;
    }

    return null;
}

export default function HospitalizationStatsCards({
    stats,
    variant = 'active',
}: HospitalizationStatsCardsProps) {
    const { t } = useTranslation();
    const cards = variant === 'discharged' ? dischargedCards : activeCards;

    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {cards.map((card) => {
                const hint = formatStatHint(card.key, stats, t);

                return (
                    <div key={card.key} className={`${HOSPITALIZATION_CARD_CLASS} ${card.tint} p-4`}>
                        <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {t(card.labelKey)}
                                </p>
                                <p className="mt-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    {formatStatValue(card.key, stats)}
                                </p>
                                {hint && (
                                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">{hint}</p>
                                )}
                            </div>
                            <div
                                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${card.gradient} text-white shadow-sm`}
                            >
                                <i className={`bx ${card.icon} text-lg`} />
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
