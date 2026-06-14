import StatCard from '../ui/StatCard';
import { HospitalizationDashboardStats } from '../../types/hospitalization';
import { useTranslation } from '../../hooks/useTranslation';

interface HospitalizationStatsCardsProps {
    stats: HospitalizationDashboardStats;
    variant?: 'active' | 'discharged';
    dischargeStatusFilter?: string;
    onDischargeStatusClick?: (status: '' | 'recovered' | 'moved' | 'died') => void;
}

interface StatCardConfig {
    key: keyof HospitalizationDashboardStats | 'occupied_beds' | 'recovered' | 'moved' | 'died';
    labelKey: string;
    iconClass: string;
    iconBgClass: string;
    borderClass: string;
    valueClass: string;
    filterValue?: '' | 'recovered' | 'moved' | 'died';
}

const activeCards: StatCardConfig[] = [
    {
        key: 'active',
        labelKey: 'global.hospitalized_patients',
        iconClass: 'bx bx-bed',
        iconBgClass: 'bg-emerald-600',
        borderClass: 'border-emerald-200 dark:border-emerald-800',
        valueClass: 'text-emerald-700 dark:text-emerald-300',
    },
    {
        key: 'discharged',
        labelKey: 'global.discharged_hospitalizations',
        iconClass: 'bx bx-exit',
        iconBgClass: 'bg-slate-600',
        borderClass: 'border-slate-200 dark:border-slate-700',
        valueClass: 'text-slate-700 dark:text-slate-300',
    },
    {
        key: 'occupied_beds',
        labelKey: 'global.occupied',
        iconClass: 'bx bx-user-pin',
        iconBgClass: 'bg-amber-500',
        borderClass: 'border-amber-200 dark:border-amber-800',
        valueClass: 'text-amber-700 dark:text-amber-300',
    },
    {
        key: 'total_beds',
        labelKey: 'global.beds',
        iconClass: 'bx bx-grid-alt',
        iconBgClass: 'bg-violet-600',
        borderClass: 'border-violet-200 dark:border-violet-800',
        valueClass: 'text-violet-700 dark:text-violet-300',
    },
];

const dischargedCards: StatCardConfig[] = [
    {
        key: 'discharged',
        labelKey: 'global.discharged_hospitalizations',
        iconClass: 'bx bx-exit',
        iconBgClass: 'bg-slate-600',
        borderClass: 'border-slate-200 dark:border-slate-700',
        valueClass: 'text-slate-700 dark:text-slate-300',
        filterValue: '',
    },
    {
        key: 'recovered',
        labelKey: 'global.recovered',
        iconClass: 'bx bx-check-circle',
        iconBgClass: 'bg-green-600',
        borderClass: 'border-green-200 dark:border-green-800',
        valueClass: 'text-green-700 dark:text-green-300',
        filterValue: 'recovered',
    },
    {
        key: 'moved',
        labelKey: 'global.moved',
        iconClass: 'bx bx-transfer',
        iconBgClass: 'bg-cyan-600',
        borderClass: 'border-cyan-200 dark:border-cyan-800',
        valueClass: 'text-cyan-700 dark:text-cyan-300',
        filterValue: 'moved',
    },
    {
        key: 'died',
        labelKey: 'global.died',
        iconClass: 'bx bx-heart',
        iconBgClass: 'bg-red-600',
        borderClass: 'border-red-200 dark:border-red-800',
        valueClass: 'text-red-700 dark:text-red-300',
        filterValue: 'died',
    },
];

function formatStatValue(key: string, stats: HospitalizationDashboardStats): string {
    if (key === 'occupied_beds') {
        const total = stats.total_beds ?? 0;
        const occupied = stats.occupied_beds ?? 0;
        return total > 0 ? `${occupied} / ${total}` : String(occupied);
    }

    return String(stats[key as keyof HospitalizationDashboardStats] ?? 0);
}

function formatStatSubtitle(
    key: string,
    stats: HospitalizationDashboardStats,
    t: (key: string) => string
): string {
    if (key === 'occupied_beds' && (stats.total_beds ?? 0) > 0) {
        const rate = Math.round(((stats.occupied_beds ?? 0) / stats.total_beds) * 100);
        return `${rate}% ${t('global.occupied')}`;
    }

    if (key === 'total_beds') {
        const available = Math.max((stats.total_beds ?? 0) - (stats.occupied_beds ?? 0), 0);
        return `${available} ${t('global.empty_bed')}`;
    }

    return '';
}

export default function HospitalizationStatsCards({
    stats,
    variant = 'active',
    dischargeStatusFilter = '',
    onDischargeStatusClick,
}: HospitalizationStatsCardsProps) {
    const { t } = useTranslation();
    const cards = variant === 'discharged' ? dischargedCards : activeCards;

    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {cards.map((card) => {
                const isDischargeCard = variant === 'discharged' && card.filterValue !== undefined;
                const isActive =
                    isDischargeCard && (dischargeStatusFilter || '') === (card.filterValue ?? '');

                return (
                    <StatCard
                        key={card.key}
                        title={t(card.labelKey)}
                        value={formatStatValue(card.key, stats)}
                        subtitle={formatStatSubtitle(card.key, stats, t)}
                        iconClass={card.iconClass}
                        iconBgClass={card.iconBgClass}
                        borderClass={card.borderClass}
                        valueClass={card.valueClass}
                        active={isActive}
                        onClick={
                            isDischargeCard && onDischargeStatusClick
                                ? () => onDischargeStatusClick(card.filterValue ?? '')
                                : undefined
                        }
                    />
                );
            })}
        </div>
    );
}
