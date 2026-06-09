import { Head, router } from '@inertiajs/react';
import { Badge, Button, Card, Label } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
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

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.current, roomId ? { room_id: roomId } : {}, {
            preserveScroll: true,
        });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.room_management')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.room_management')}
                    icon="bx-building-house"
                    accent="from-emerald-600 to-emerald-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                <Card>
                    <form onSubmit={handleSubmit} className="flex flex-col gap-4 md:flex-row md:items-end">
                        <div className="min-w-0 flex-1">
                            <Label htmlFor="room_id">{t('global.rooms')}</Label>
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
                            {t('global.search')}
                        </Button>
                    </form>
                </Card>

                {selectedRoomId && (
                    <Card>
                        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('global.bed')}</TableHead>
                                        <TableHead>{t('global.status')}</TableHead>
                                        <TableHead>{t('global.patient_name')}</TableHead>
                                        <TableHead className="text-end">{t('global.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {beds.map((bed) => (
                                        <TableRow key={bed.id}>
                                            <TableCell className="font-medium">{bed.number}</TableCell>
                                            <TableCell>
                                                <Badge color={bed.is_occupied ? 'failure' : 'success'}>
                                                    {bed.is_occupied ? t('global.occupied') : t('global.empty_bed')}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>{bed.patient_name ?? '—'}</TableCell>
                                            <TableCell className="text-end">
                                                {bed.hospitalization_id && (
                                                    <TableActionButton
                                                        kind="view"
                                                        href={`/react/hospitalizations/${bed.hospitalization_id}`}
                                                        title={t('global.view')}
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {beds.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={4} className="py-10 text-center text-gray-500">
                                                {t('global.no_records_found')}
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </Card>
                )}
            </div>
        </DashboardLayout>
    );
}
