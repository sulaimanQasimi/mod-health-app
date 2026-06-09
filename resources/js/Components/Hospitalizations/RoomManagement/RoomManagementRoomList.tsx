import { Badge, TextInput } from 'flowbite-react';
import { useMemo, useState } from 'react';
import { HOSPITALIZATION_CARD_CLASS } from '../hospitalizationUi';
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
        <div className={`${HOSPITALIZATION_CARD_CLASS} flex h-full flex-col`}>
            <div className="border-b border-gray-100 p-4 dark:border-gray-800">
                <h2 className="text-sm font-semibold text-gray-900 dark:text-white">{t('global.rooms')}</h2>
                <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    {filteredRooms.length} / {rooms.length}
                </p>
                <TextInput
                    sizing="sm"
                    className="mt-3"
                    icon={() => <i className="bx bx-search text-gray-400" />}
                    placeholder={t('global.search')}
                    value={query}
                    onChange={(event) => setQuery(event.target.value)}
                />
            </div>

            <div className="max-h-[32rem] flex-1 overflow-y-auto p-2">
                {filteredRooms.map((room) => {
                    const isSelected = room.id === selectedRoomId;

                    return (
                        <button
                            key={room.id}
                            type="button"
                            onClick={() => onSelect(room.id)}
                            className={`mb-2 w-full rounded-xl border p-3 text-start transition-all ${
                                isSelected
                                    ? 'border-emerald-300 bg-emerald-50 shadow-sm ring-1 ring-emerald-200 dark:border-emerald-800 dark:bg-emerald-950/30 dark:ring-emerald-900/50'
                                    : 'border-transparent bg-gray-50/80 hover:border-gray-200 hover:bg-white dark:bg-gray-800/40 dark:hover:border-gray-700 dark:hover:bg-gray-800'
                            }`}
                        >
                            <div className="flex items-start justify-between gap-2">
                                <div className="min-w-0">
                                    <p className="truncate font-medium text-gray-900 dark:text-white">{room.name}</p>
                                    {room.department_name && (
                                        <Badge color="info" className="mt-1 w-fit font-normal">
                                            {room.department_name}
                                        </Badge>
                                    )}
                                </div>
                                <span className="shrink-0 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    {room.occupancy_rate}%
                                </span>
                            </div>

                            <div className="mt-3 h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
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
                    <p className="px-2 py-8 text-center text-sm text-gray-500">{t('global.no_records_found')}</p>
                )}
            </div>
        </div>
    );
}
