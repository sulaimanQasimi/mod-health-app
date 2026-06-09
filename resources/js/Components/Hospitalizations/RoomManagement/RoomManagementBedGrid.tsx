import { Badge, Button } from 'flowbite-react';
import { Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { HOSPITALIZATION_AVATAR_CLASS, HOSPITALIZATION_CARD_CLASS, patientInitials } from '../hospitalizationUi';
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
        <div className={`${HOSPITALIZATION_CARD_CLASS} flex h-full flex-col`}>
            <div className="border-b border-gray-100 p-5 dark:border-gray-800">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <h2 className="text-lg font-bold text-gray-900 dark:text-white">{room.name}</h2>
                            {room.department_name && (
                                <Badge color="info" className="w-fit font-normal">
                                    {room.department_name}
                                </Badge>
                            )}
                        </div>
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {occupied} / {beds.length} {t('global.occupied')} ({occupancyRate}%)
                        </p>
                    </div>

                    <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md">
                        <div className="text-center">
                            <p className="text-lg font-bold leading-none">{occupancyRate}%</p>
                            <p className="mt-0.5 text-[9px] uppercase tracking-wide opacity-90">{t('global.occupied')}</p>
                        </div>
                    </div>
                </div>

                <div className="mt-4 flex flex-wrap gap-2">
                    {filters.map((item) => (
                        <button
                            key={item.key}
                            type="button"
                            onClick={() => setFilter(item.key)}
                            className={`rounded-full border px-3 py-1.5 text-xs font-medium transition-colors ${
                                filter === item.key
                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                    : 'border-gray-200 text-gray-600 hover:border-emerald-300 dark:border-gray-600 dark:text-gray-300'
                            }`}
                        >
                            {item.label}
                            <span className="ms-1 opacity-70">({item.count})</span>
                        </button>
                    ))}
                </div>
            </div>

            <div className="flex-1 overflow-y-auto p-4">
                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    {filteredBeds.map((bed) => (
                        <div
                            key={bed.id}
                            className={`rounded-xl border p-4 transition-all hover:shadow-md ${
                                bed.is_occupied
                                    ? 'border-amber-200/90 bg-gradient-to-br from-amber-50 to-orange-50/60 dark:border-amber-900/50 dark:from-amber-950/25 dark:to-orange-950/10'
                                    : 'border-emerald-200/90 bg-gradient-to-br from-emerald-50/80 to-teal-50/40 dark:border-emerald-900/50 dark:from-emerald-950/20 dark:to-teal-950/10'
                            }`}
                        >
                            <div className="flex items-start gap-3">
                                <div
                                    className={`${HOSPITALIZATION_AVATAR_CLASS} ${
                                        bed.is_occupied
                                            ? 'from-amber-500 to-orange-500'
                                            : 'from-emerald-500 to-teal-600'
                                    }`}
                                >
                                    {bed.is_occupied ? patientInitials(bed.patient_name) : <i className="bx bx-bed" />}
                                </div>

                                <div className="min-w-0 flex-1">
                                    <div className="flex items-start justify-between gap-2">
                                        <p className="font-semibold text-gray-900 dark:text-white">
                                            {t('global.bed')} {bed.number}
                                        </p>
                                        <Badge color={bed.is_occupied ? 'failure' : 'success'} className="shrink-0">
                                            {bed.is_occupied ? t('global.occupied') : t('global.empty_bed')}
                                        </Badge>
                                    </div>

                                    {bed.is_occupied ? (
                                        <div className="mt-2 space-y-1">
                                            <p className="truncate text-sm font-medium text-gray-900 dark:text-white">
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
                                        <p className="mt-2 text-sm text-emerald-700 dark:text-emerald-300">
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
                                    className="mt-4 w-full"
                                >
                                    <i className="bx bx-show me-2" />
                                    {t('global.view')}
                                </Button>
                            )}
                        </div>
                    ))}
                </div>

                {filteredBeds.length === 0 && (
                    <p className="py-12 text-center text-sm text-gray-500">{t('global.no_records_found')}</p>
                )}
            </div>
        </div>
    );
}
