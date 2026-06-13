import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import RoomManagementBedGrid from '../../Components/Hospitalizations/RoomManagement/RoomManagementBedGrid';
import RoomManagementOverviewStats from '../../Components/Hospitalizations/RoomManagement/RoomManagementOverviewStats';
import RoomManagementRoomList from '../../Components/Hospitalizations/RoomManagement/RoomManagementRoomList';
import { HOSPITALIZATION_MUTED_NOTE_CLASS } from '../../Components/Hospitalizations/hospitalizationUi';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HospitalizationRoomBedRow,
    HospitalizationRoomManagementOverview,
    HospitalizationRoomManagementSelected,
    HospitalizationRoomSummary,
} from '../../types/hospitalization';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface RoomManagementProps {
    rooms: HospitalizationRoomSummary[];
    overview: HospitalizationRoomManagementOverview;
    selectedRoom: HospitalizationRoomManagementSelected | null;
    beds: HospitalizationRoomBedRow[];
    filters: { room_id: string };
    urls: { current: string; index: string };
}

export default function HospitalizationsRoomManagement({
    rooms,
    overview,
    selectedRoom,
    beds,
    urls,
}: RoomManagementProps) {
    const { t } = useTranslation();

    const selectRoom = (roomId: number) => {
        router.get(urls.current, { room_id: roomId }, { preserveScroll: true, preserveState: true });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.room_management')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.room_management')}
                    subtitle={
                        selectedRoom
                            ? `${selectedRoom.name}${selectedRoom.department_name ? ` · ${selectedRoom.department_name}` : ''}`
                            : t('global.rooms')
                    }
                    icon="bx-building-house"
                    accent="from-emerald-600 to-teal-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                {rooms.length > 0 && <RoomManagementOverviewStats overview={overview} />}

                {rooms.length === 0 ? (
                    <p className={HOSPITALIZATION_MUTED_NOTE_CLASS}>{t('global.no_records_found')}</p>
                ) : (
                    <div className="grid gap-5 md:grid-cols-12 md:items-start">
                        <div className="min-w-0 md:col-span-4 md:sticky md:top-4 md:self-start xl:col-span-3">
                            <RoomManagementRoomList
                                rooms={rooms}
                                selectedRoomId={selectedRoom?.id ?? null}
                                onSelect={selectRoom}
                            />
                        </div>

                        <div className="min-w-0 md:col-span-8 xl:col-span-9">
                            {selectedRoom ? (
                                <RoomManagementBedGrid room={selectedRoom} beds={beds} />
                            ) : (
                                <p className={HOSPITALIZATION_MUTED_NOTE_CLASS}>{t('global.select')}</p>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
