import { HOSPITALIZATION_CARD_CLASS } from '../hospitalizationUi';
import { useTranslation } from '../../../hooks/useTranslation';
import { HospitalizationRoomManagementOverview } from '../../../types/hospitalization';

interface RoomManagementOverviewStatsProps {
    overview: HospitalizationRoomManagementOverview;
}

const cards = [
    { key: 'rooms_count' as const, labelKey: 'global.rooms', icon: 'bx-building-house', gradient: 'from-slate-500 to-gray-600' },
    { key: 'beds_count' as const, labelKey: 'global.beds', icon: 'bx-grid-alt', gradient: 'from-violet-500 to-purple-600' },
    { key: 'occupied_beds_count' as const, labelKey: 'global.occupied', icon: 'bx-user-pin', gradient: 'from-amber-500 to-orange-500' },
    { key: 'empty_beds_count' as const, labelKey: 'global.empty_bed', icon: 'bx-check-circle', gradient: 'from-emerald-500 to-teal-600' },
    { key: 'occupancy_rate' as const, labelKey: 'global.bed_occupancy', icon: 'bx-pie-chart-alt-2', gradient: 'from-cyan-500 to-blue-600', suffix: '%' },
];

export default function RoomManagementOverviewStats({ overview }: RoomManagementOverviewStatsProps) {
    const { t } = useTranslation();

    return (
        <div className="grid grid-cols-2 gap-3 lg:grid-cols-5">
            {cards.map((card) => (
                <div key={card.key} className={`${HOSPITALIZATION_CARD_CLASS} bg-white p-4 dark:bg-gray-900`}>
                    <div className="flex items-center justify-between gap-2">
                        <div className="min-w-0">
                            <p className="truncate text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {t(card.labelKey)}
                            </p>
                            <p className="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                {overview[card.key]}
                                {card.suffix ?? ''}
                            </p>
                        </div>
                        <div
                            className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br ${card.gradient} text-white shadow-sm`}
                        >
                            <i className={`bx ${card.icon} text-lg`} />
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}
