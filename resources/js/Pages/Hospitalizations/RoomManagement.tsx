import { Head, router } from '@inertiajs/react';
import { Badge, Button } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import HospitalizationPanel from '../../Components/Hospitalizations/HospitalizationPanel';
import { HOSPITALIZATION_CARD_CLASS } from '../../Components/Hospitalizations/hospitalizationUi';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import TableActionButton from '../../Components/ui/TableActionButton';
import { useTranslation } from '../../hooks/useTranslation';
import { HospitalizationOption, HospitalizationRoomBedRow } from '../../types/hospitalization';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface RoomManagementProps {
    rooms: HospitalizationOption[];
    selectedRoomId: number | null;
    beds: HospitalizationRoomBedRow[];
    filters: { room_id: string };
    urls: { current: string; index: string; legacy: string };
}

export default function HospitalizationsRoomManagement({
    rooms,
    selectedRoomId,
    beds,
    filters,
    urls,
}: RoomManagementProps) {
    const { t } = useTranslation();
    const [roomId, setRoomId] = useState(filters.room_id);

    const occupancy = useMemo(() => {
        const occupied = beds.filter((bed) => bed.is_occupied).length;
        return { occupied, empty: beds.length - occupied, total: beds.length };
    }, [beds]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.current, roomId ? { room_id: roomId } : {}, {
            preserveScroll: true,
        });
    };

    const selectedRoomName = rooms.find((room) => String(room.id) === roomId)?.name;

    return (
        <DashboardLayout>
            <Head title={t('global.room_management')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.room_management')}
                    subtitle={selectedRoomName ?? t('global.rooms')}
                    icon="bx-building-house"
                    accent="from-emerald-600 to-teal-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                <HospitalizationPanel variant="filter" title={t('global.rooms')} icon="bx-filter-alt">
                    <form onSubmit={handleSubmit} className="flex flex-col gap-4 md:flex-row md:items-end">
                        <div className="min-w-0 flex-1">
                            <SearchableSelect
                                id="room_id"
                                value={roomId}
                                onChange={setRoomId}
                                options={rooms.map((room) => ({
                                    value: String(room.id),
                                    label: room.name,
                                }))}
                                placeholder={t('global.select')}
                            />
                        </div>
                        <Button type="submit" color="success" size="sm">
                            <i className="bx bx-search me-2" />
                            {t('global.search')}
                        </Button>
                    </form>
                </HospitalizationPanel>

                {selectedRoomId && (
                    <>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            {[
                                {
                                    label: t('global.beds'),
                                    value: occupancy.total,
                                    icon: 'bx-grid-alt',
                                    color: 'from-violet-500 to-purple-600',
                                },
                                {
                                    label: t('global.occupied'),
                                    value: occupancy.occupied,
                                    icon: 'bx-user-pin',
                                    color: 'from-amber-500 to-orange-500',
                                },
                                {
                                    label: t('global.empty_bed'),
                                    value: occupancy.empty,
                                    icon: 'bx-check-circle',
                                    color: 'from-emerald-500 to-teal-600',
                                },
                            ].map((stat) => (
                                <div key={stat.label} className={`${HOSPITALIZATION_CARD_CLASS} p-4`}>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {stat.label}
                                            </p>
                                            <p className="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                                {stat.value}
                                            </p>
                                        </div>
                                        <div
                                            className={`flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br ${stat.color} text-white shadow`}
                                        >
                                            <i className={`bx ${stat.icon} text-lg`} />
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>

                        <HospitalizationPanel
                            title={selectedRoomName ?? t('global.beds')}
                            icon="bx-bed"
                            description={`${occupancy.occupied} / ${occupancy.total} ${t('global.occupied')}`}
                        >
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                {beds.map((bed) => (
                                    <div
                                        key={bed.id}
                                        className={`rounded-xl border p-4 transition-shadow hover:shadow-md ${
                                            bed.is_occupied
                                                ? 'border-amber-200 bg-amber-50/50 dark:border-amber-900/50 dark:bg-amber-950/20'
                                                : 'border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/50 dark:bg-emerald-950/15'
                                        }`}
                                    >
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <p className="text-lg font-bold text-gray-900 dark:text-white">
                                                    {t('global.bed')} {bed.number}
                                                </p>
                                                <Badge
                                                    color={bed.is_occupied ? 'failure' : 'success'}
                                                    className="mt-2 w-fit"
                                                >
                                                    {bed.is_occupied ? t('global.occupied') : t('global.empty_bed')}
                                                </Badge>
                                            </div>
                                            <i
                                                className={`bx ${bed.is_occupied ? 'bx-user' : 'bx-bed'} text-2xl ${
                                                    bed.is_occupied
                                                        ? 'text-amber-600 dark:text-amber-400'
                                                        : 'text-emerald-600 dark:text-emerald-400'
                                                }`}
                                            />
                                        </div>
                                        {bed.patient_name && (
                                            <p className="mt-3 truncate text-sm font-medium text-gray-800 dark:text-gray-200">
                                                {bed.patient_name}
                                            </p>
                                        )}
                                        {bed.hospitalization_id && (
                                            <div className="mt-3">
                                                <TableActionButton
                                                    kind="view"
                                                    href={`/react/hospitalizations/${bed.hospitalization_id}`}
                                                    title={t('global.view')}
                                                />
                                            </div>
                                        )}
                                    </div>
                                ))}
                                {beds.length === 0 && (
                                    <p className="col-span-full py-10 text-center text-sm text-gray-500">
                                        {t('global.no_records_found')}
                                    </p>
                                )}
                            </div>
                        </HospitalizationPanel>
                    </>
                )}
            </div>
        </DashboardLayout>
    );
}
