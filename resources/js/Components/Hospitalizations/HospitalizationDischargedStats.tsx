import StatCard from '../ui/StatCard';
import { useTranslation } from '../../hooks/useTranslation';
import { HospitalizationDashboardStats } from '../../types/hospitalization';

interface HospitalizationDischargedStatsProps {
    stats: HospitalizationDashboardStats;
    dischargeStatusFilter?: string;
    onDischargeStatusClick?: (status: '' | 'recovered' | 'moved' | 'died') => void;
}

interface DischargedStatConfig {
    key: keyof HospitalizationDashboardStats;
    labelKey: string;
    subtitleKey: string;
    iconClass: string;
    iconBgClass: string;
    borderClass: string;
    valueClass: string;
    filterValue: '' | 'recovered' | 'moved' | 'died';
}

const cards: DischargedStatConfig[] = [
    {
        key: 'discharged',
        labelKey: 'global.discharged_hospitalizations',
        subtitleKey: 'global.all',
        iconClass: 'bx bx-exit',
        iconBgClass: 'bg-slate-600',
        borderClass: 'border-slate-200 dark:border-slate-700',
        valueClass: 'text-slate-700 dark:text-slate-300',
        filterValue: '',
    },
    {
        key: 'recovered',
        labelKey: 'global.recovered',
        subtitleKey: '',
        iconClass: 'bx bx-check-circle',
        iconBgClass: 'bg-green-600',
        borderClass: 'border-green-200 dark:border-green-800',
        valueClass: 'text-green-700 dark:text-green-300',
        filterValue: 'recovered',
    },
    {
        key: 'moved',
        labelKey: 'global.moved',
        subtitleKey: '',
        iconClass: 'bx bx-transfer',
        iconBgClass: 'bg-cyan-600',
        borderClass: 'border-cyan-200 dark:border-cyan-800',
        valueClass: 'text-cyan-700 dark:text-cyan-300',
        filterValue: 'moved',
    },
    {
        key: 'died',
        labelKey: 'global.died',
        subtitleKey: '',
        iconClass: 'bx bx-heart',
        iconBgClass: 'bg-red-600',
        borderClass: 'border-red-200 dark:border-red-800',
        valueClass: 'text-red-700 dark:text-red-300',
        filterValue: 'died',
    },
];

export default function HospitalizationDischargedStats({
    stats,
    dischargeStatusFilter = '',
    onDischargeStatusClick,
}: HospitalizationDischargedStatsProps) {
    const { t } = useTranslation();

    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {cards.map((card) => {
                const isActive = (dischargeStatusFilter || '') === card.filterValue;

                return (
                    <StatCard
                        key={card.key}
                        title={t(card.labelKey)}
                        value={String(stats[card.key] ?? 0)}
                        subtitle={card.subtitleKey ? t(card.subtitleKey) : ''}
                        iconClass={card.iconClass}
                        iconBgClass={card.iconBgClass}
                        borderClass={card.borderClass}
                        valueClass={card.valueClass}
                        active={isActive}
                        onClick={
                            onDischargeStatusClick
                                ? () => onDischargeStatusClick(card.filterValue)
                                : undefined
                        }
                    />
                );
            })}
        </div>
    );
}
