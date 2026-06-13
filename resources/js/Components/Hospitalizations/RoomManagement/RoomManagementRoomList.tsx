import { Badge, TextInput } from 'flowbite-react';
import { useMemo, useState } from 'react';
import HospitalizationPanel from '../HospitalizationPanel';
import { useTranslation } from '../../../hooks/useTranslation';
import { HospitalizationRoomSummary } from '../../../types/hospitalization';

interface RoomManagementRoomListProps {
    rooms: HospitalizationRoomSummary[];
    selectedRoomId: number | null;
    onSelect: (roomId: number) => void;
}

function occupancyTone(rate: number): string {
    if (rate >= 85) {
        return 'bg-red-500';
    }
    if (rate >= 55) {
        return 'bg-amber-500';
    }

    return 'bg-emerald-500';
}

export default function RoomManagementRoomList({
    rooms,
    selectedRoomId,
    onSelect,
}: RoomManagementRoomListProps) {
    const { t } = useTranslation();
    const [query, setQuery] = useState('');

    const filteredRooms = useMemo(() => {
        const normalized = query.trim().toLowerCase();
        if (!normalized) {
            return rooms;
        }

        return rooms.filter(
            (room) =>
                room.name.toLowerCase().includes(normalized) ||
                room.department_name?.toLowerCase().includes(normalized)
        );
    }, [rooms, query]);

    return (
        <HospitalizationPanel
            title={t('global.rooms')}
            icon="bx-building-house"
            description={`${filteredRooms.length} / ${rooms.length}`}
            variant="table"
        >
            <div className="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                <TextInput
                    sizing="sm"
                    icon={() => <i className="bx bx-search text-gray-400" />}
                    placeholder={t('global.search')}
                    value={query}
                    onChange={(event) => setQuery(event.target.value)}
                />
            </div>

            <div className="max-h-[min(18rem,45vh)] overflow-y-auto p-3 md:max-h-[calc(100vh-14rem)]">
                {filteredRooms.map((room) => {
                    const isSelected = room.id === selectedRoomId;

                    return (
                        <button
                            key={room.id}
                            type="button"
                            onClick={() => onSelect(room.id)}
                            className={`mb-2 w-full rounded-lg border p-3 text-start transition-colors last:mb-0 ${
                                isSelected
                                    ? 'border-emerald-300 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/30'
                                    : 'border-gray-100 bg-gray-50/80 hover:border-gray-200 hover:bg-white dark:border-gray-800 dark:bg-gray-800/40 dark:hover:border-gray-700 dark:hover:bg-gray-800'
                            }`}
                        >
                            <div className="flex items-start justify-between gap-2">
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-medium text-gray-900 dark:text-white">
                                        {room.name}
                                    </p>
                                    {room.department_name && (
                                        <p className="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                            {room.department_name}
                                        </p>
                                    )}
                                </div>
                                <Badge
                                    color={room.occupancy_rate >= 85 ? 'failure' : room.occupancy_rate >= 55 ? 'warning' : 'success'}
                                    className="shrink-0 font-normal"
                                >
                                    {room.occupancy_rate}%
                                </Badge>
                            </div>

                            <div className="mt-2.5 h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    className={`h-full rounded-full transition-all ${occupancyTone(room.occupancy_rate)}`}
                                    style={{ width: `${room.occupancy_rate}%` }}
                                />
                            </div>

                            <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {room.occupied_beds_count} / {room.beds_count} {t('global.occupied')}
                                {' · '}
                                {room.empty_beds_count} {t('global.empty_bed')}
                            </p>
                        </button>
                    );
                })}

                {filteredRooms.length === 0 && (
                    <p className="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        {t('global.no_records_found')}
                    </p>
                )}
            </div>
        </HospitalizationPanel>
    );
}
