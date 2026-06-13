import { Badge, Button } from 'flowbite-react';
import { Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import HospitalizationPanel from '../HospitalizationPanel';
import { patientInitials } from '../hospitalizationUi';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    HospitalizationRoomBedRow,
    HospitalizationRoomManagementSelected,
} from '../../../types/hospitalization';

type BedFilter = 'all' | 'occupied' | 'empty';

interface RoomManagementBedGridProps {
    room: HospitalizationRoomManagementSelected;
    beds: HospitalizationRoomBedRow[];
}

export default function RoomManagementBedGrid({ room, beds }: RoomManagementBedGridProps) {
    const { t } = useTranslation();
    const [filter, setFilter] = useState<BedFilter>('all');

    const filteredBeds = useMemo(() => {
        if (filter === 'occupied') {
            return beds.filter((bed) => bed.is_occupied);
        }
        if (filter === 'empty') {
            return beds.filter((bed) => !bed.is_occupied);
        }

        return beds;
    }, [beds, filter]);

    const occupied = beds.filter((bed) => bed.is_occupied).length;
    const empty = beds.length - occupied;
    const occupancyRate = beds.length > 0 ? Math.round((occupied / beds.length) * 100) : 0;

    const filters: { key: BedFilter; label: string; count: number }[] = [
        { key: 'all', label: t('global.all'), count: beds.length },
        { key: 'occupied', label: t('global.occupied'), count: occupied },
        { key: 'empty', label: t('global.empty_bed'), count: empty },
    ];

    return (
        <HospitalizationPanel
            title={room.name}
            icon="bx-bed"
            description={
                room.department_name
                    ? `${room.department_name} · ${occupied} / ${beds.length} ${t('global.occupied')} (${occupancyRate}%)`
                    : `${occupied} / ${beds.length} ${t('global.occupied')} (${occupancyRate}%)`
            }
            variant="table"
            action={
                <Badge
                    color={occupancyRate >= 85 ? 'failure' : occupancyRate >= 55 ? 'warning' : 'success'}
                    className="font-normal"
                >
                    {occupancyRate}% {t('global.occupied')}
                </Badge>
            }
        >
            <div className="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                <div className="flex flex-wrap gap-2">
                    {filters.map((item) => (
                        <button
                            key={item.key}
                            type="button"
                            onClick={() => setFilter(item.key)}
                            className={`rounded-full border px-3 py-1 text-xs font-medium transition-colors ${
                                filter === item.key
                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300 dark:border-gray-600 dark:text-gray-300 dark:hover:border-gray-500'
                            }`}
                        >
                            {item.label}
                            <span className="ms-1 opacity-70">({item.count})</span>
                        </button>
                    ))}
                </div>
            </div>

            <div className="p-4">
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-3">
                    {filteredBeds.map((bed) => (
                        <div
                            key={bed.id}
                            className={`flex flex-col rounded-lg border p-4 ${
                                bed.is_occupied
                                    ? 'border-amber-200 bg-amber-50/50 dark:border-amber-900/50 dark:bg-amber-950/20'
                                    : 'border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/50 dark:bg-emerald-950/15'
                            }`}
                        >
                            <div className="flex items-start gap-3">
                                <div
                                    className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white ${
                                        bed.is_occupied ? 'bg-amber-500' : 'bg-emerald-600'
                                    }`}
                                >
                                    {bed.is_occupied ? patientInitials(bed.patient_name) : <i className="bx bx-bed text-lg" />}
                                </div>

                                <div className="min-w-0 flex-1">
                                    <div className="flex items-start justify-between gap-2">
                                        <p className="text-sm font-semibold text-gray-900 dark:text-white">
                                            {t('global.bed')} {bed.number}
                                        </p>
                                        <Badge color={bed.is_occupied ? 'failure' : 'success'} className="shrink-0">
                                            {bed.is_occupied ? t('global.occupied') : t('global.empty_bed')}
                                        </Badge>
                                    </div>

                                    {bed.is_occupied ? (
                                        <div className="mt-2 space-y-0.5">
                                            <p className="truncate text-sm text-gray-900 dark:text-white">
                                                {bed.patient_name ?? '—'}
                                            </p>
                                            {bed.father_name && (
                                                <p className="truncate text-xs text-gray-500 dark:text-gray-400">
                                                    {bed.father_name}
                                                </p>
                                            )}
                                            {bed.admission_date && (
                                                <p className="text-xs text-gray-500 dark:text-gray-400" dir="ltr">
                                                    {bed.admission_date}
                                                </p>
                                            )}
                                        </div>
                                    ) : (
                                        <p className="mt-2 text-xs text-emerald-700 dark:text-emerald-300">
                                            {t('global.empty_bed')}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {bed.hospitalization_url && (
                                <Button
                                    as={Link}
                                    href={bed.hospitalization_url}
                                    color="light"
                                    size="xs"
                                    className="mt-3 w-full"
                                >
                                    <i className="bx bx-show me-2" />
                                    {t('global.view')}
                                </Button>
                            )}
                        </div>
                    ))}
                </div>

                {filteredBeds.length === 0 && (
                    <p className="py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                        {t('global.no_records_found')}
                    </p>
                )}
            </div>
        </HospitalizationPanel>
    );
}
