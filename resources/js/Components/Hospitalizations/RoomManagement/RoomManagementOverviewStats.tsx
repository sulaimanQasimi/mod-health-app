import StatCard from '../../ui/StatCard';
import { useTranslation } from '../../../hooks/useTranslation';
import { HospitalizationRoomManagementOverview } from '../../../types/hospitalization';

interface RoomManagementOverviewStatsProps {
    overview: HospitalizationRoomManagementOverview;
}

interface StatCardConfig {
    key: keyof HospitalizationRoomManagementOverview;
    labelKey: string;
    iconClass: string;
    iconBgClass: string;
    borderClass: string;
    valueClass: string;
    suffix?: string;
}

const cards: StatCardConfig[] = [
    {
        key: 'rooms_count',
        labelKey: 'global.rooms',
        iconClass: 'bx bx-building-house',
        iconBgClass: 'bg-slate-600',
        borderClass: 'border-slate-200 dark:border-slate-700',
        valueClass: 'text-slate-700 dark:text-slate-300',
    },
    {
        key: 'beds_count',
        labelKey: 'global.beds',
        iconClass: 'bx bx-grid-alt',
        iconBgClass: 'bg-violet-600',
        borderClass: 'border-violet-200 dark:border-violet-800',
        valueClass: 'text-violet-700 dark:text-violet-300',
    },
    {
        key: 'occupied_beds_count',
        labelKey: 'global.occupied',
        iconClass: 'bx bx-user-pin',
        iconBgClass: 'bg-amber-500',
        borderClass: 'border-amber-200 dark:border-amber-800',
        valueClass: 'text-amber-700 dark:text-amber-300',
    },
    {
        key: 'empty_beds_count',
        labelKey: 'global.empty_bed',
        iconClass: 'bx bx-check-circle',
        iconBgClass: 'bg-emerald-600',
        borderClass: 'border-emerald-200 dark:border-emerald-800',
        valueClass: 'text-emerald-700 dark:text-emerald-300',
    },
    {
        key: 'occupancy_rate',
        labelKey: 'global.bed_occupancy',
        iconClass: 'bx bx-pie-chart-alt-2',
        iconBgClass: 'bg-cyan-600',
        borderClass: 'border-cyan-200 dark:border-cyan-800',
        valueClass: 'text-cyan-700 dark:text-cyan-300',
        suffix: '%',
    },
];

export default function RoomManagementOverviewStats({ overview }: RoomManagementOverviewStatsProps) {
    const { t } = useTranslation();

    return (
        <div className="grid grid-cols-2 gap-3 lg:grid-cols-5">
            {cards.map((card) => (
                <StatCard
                    key={card.key}
                    title={t(card.labelKey)}
                    value={`${overview[card.key]}${card.suffix ?? ''}`}
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
