import {
    Badge,
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    TextInput,
} from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

interface HospitalizationVitalSignSectionProps {
    hospitalizationId: number;
    isDischarged?: boolean;
}

interface VitalSignListItem {
    schedule_id: number;
    vital_sign_id: number;
    type_name: string | null;
    date: string | null;
    morning_time: string | null;
    evening_time: string | null;
    nurse_name: string | null;
}

interface VitalSignDayGroup {
    date: string;
    items: VitalSignListItem[];
}

interface VitalSignTypeOption {
    id: number;
    name: string;
}

interface ScheduleRow {
    vital_sign_type_id: string;
    morning_time: string;
    evening_time: string;
    schedule_id?: number;
    vital_sign_id?: number;
}

interface MetaScheduleRow {
    vital_sign_type_id: number;
    morning_time: string | null;
    evening_time: string | null;
    schedule_id?: number;
    vital_sign_id?: number;
}

interface SectionData {
    items: VitalSignListItem[];
    count: number;
    permissions: { view?: boolean; manage?: boolean };
    urls?: { print?: string | null };
}

const EMPTY_ROW = (): ScheduleRow => ({
    vital_sign_type_id: '',
    morning_time: '',
    evening_time: '',
});

function mapMetaRows(rows: MetaScheduleRow[] | undefined): ScheduleRow[] {
    if (!rows?.length) {
        return [EMPTY_ROW()];
    }

    return rows.map((row) => ({
        vital_sign_type_id: String(row.vital_sign_type_id),
        morning_time: row.morning_time ?? '',
        evening_time: row.evening_time ?? '',
        schedule_id: row.schedule_id,
        vital_sign_id: row.vital_sign_id,
    }));
}

function groupByDate(items: VitalSignListItem[]): VitalSignDayGroup[] {
    const map = new Map<string, VitalSignListItem[]>();

    for (const item of items) {
        const key = item.date ?? '—';
        const group = map.get(key) ?? [];
        group.push(item);
        map.set(key, group);
    }

    return Array.from(map.entries())
        .map(([date, groupItems]) => ({ date, items: groupItems }))
        .sort((a, b) => b.date.localeCompare(a.date));
}

export default function HospitalizationVitalSignSection({
    hospitalizationId,
    isDischarged = false,
}: HospitalizationVitalSignSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/hospitalizations/${hospitalizationId}/vital-signs`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [metaLoading, setMetaLoading] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [manageOpen, setManageOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [selectedDay, setSelectedDay] = useState<VitalSignDayGroup | null>(null);
    const [vitalSignTypes, setVitalSignTypes] = useState<VitalSignTypeOption[]>([]);
    const [schedulesByDate, setSchedulesByDate] = useState<Record<string, MetaScheduleRow[]>>({});
    const [scheduleDate, setScheduleDate] = useState('');
    const [rows, setRows] = useState<ScheduleRow[]>([EMPTY_ROW()]);

    const loadData = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch(baseUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setData(payload.data);
            }
        } finally {
            setLoading(false);
        }
    }, [baseUrl]);

    const loadMeta = useCallback(async () => {
        setMetaLoading(true);
        try {
            const response = await fetch(`${baseUrl}/meta`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setVitalSignTypes(payload.data.vital_sign_types ?? []);
                setSchedulesByDate(payload.data.schedules_by_date ?? {});
                return payload.data as {
                    default_schedule_date?: string;
                    schedules_by_date?: Record<string, MetaScheduleRow[]>;
                };
            }
        } finally {
            setMetaLoading(false);
        }
        return null;
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const dayGroups = useMemo(() => groupByDate(data?.items ?? []), [data?.items]);

    const typeOptions = useMemo(
        () =>
            vitalSignTypes.map((type) => ({
                value: String(type.id),
                label: type.name,
            })),
        [vitalSignTypes]
    );

    const usedTypeIds = useMemo(
        () => new Set(rows.map((row) => row.vital_sign_type_id).filter(Boolean)),
        [rows]
    );

    const openManageForDate = async (date?: string) => {
        setManageOpen(true);
        const meta = await loadMeta();
        const targetDate = date ?? meta?.default_schedule_date ?? '';
        setScheduleDate(targetDate);
        setRows(mapMetaRows(meta?.schedules_by_date?.[targetDate]));
    };

    const handleDateChange = (date: string) => {
        setScheduleDate(date);
        setRows(mapMetaRows(schedulesByDate[date]));
    };

    const closeManage = () => {
        setManageOpen(false);
        setRows([EMPTY_ROW()]);
        setScheduleDate('');
    };

    const viewDay = (group: VitalSignDayGroup) => {
        setSelectedDay(group);
        setDetailsOpen(true);
    };

    const updateRow = (index: number, patch: Partial<ScheduleRow>) => {
        setRows((current) => current.map((row, i) => (i === index ? { ...row, ...patch } : row)));
    };

    const addRow = () => {
        setRows((current) => [...current, EMPTY_ROW()]);
    };

    const removeRow = (index: number) => {
        setRows((current) => (current.length <= 1 ? current : current.filter((_, i) => i !== index)));
    };

    const handleSave = async (event: FormEvent) => {
        event.preventDefault();

        const scheduleRows = rows
            .filter((row) => row.vital_sign_type_id)
            .map((row) => ({
                vital_sign_type_id: Number(row.vital_sign_type_id),
                morning_time: row.morning_time,
                evening_time: row.evening_time,
                schedule_id: row.schedule_id ?? null,
                vital_sign_id: row.vital_sign_id ?? null,
            }));

        if (!scheduleDate || scheduleRows.length === 0) {
            return;
        }

        setSubmitting(true);
        try {
            const response = await fetch(baseUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    schedule_date: scheduleDate,
                    schedule_rows: scheduleRows,
                }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return;
            }
            closeManage();
            setDetailsOpen(false);
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <SectionShell
                id={`hospitalization-vital-signs-${hospitalizationId}`}
                icon="bx-pulse"
                iconClassName="text-rose-500"
                title={t('global.vital_signs')}
                count={data?.count}
                badgeColor="failure"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        <div className="mb-4 flex flex-wrap justify-end gap-2">
                            {data?.urls?.print && (
                                <Button
                                    as="a"
                                    href={data.urls.print}
                                    target="_blank"
                                    rel="noreferrer"
                                    size="sm"
                                    color="light"
                                >
                                    <i className="bx bx-printer me-2" />
                                    {t('global.print_vital_signs_chart')}
                                </Button>
                            )}
                            {data?.permissions.manage && !isDischarged && (
                                <Button size="sm" color="failure" onClick={() => openManageForDate()}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.add')}
                                </Button>
                            )}
                        </div>

                        {dayGroups.length > 0 ? (
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.number')}</TableHeader>
                                        <TableHeader>{t('global.date')}</TableHeader>
                                        <TableHeader>{t('global.vital_sign_type')}</TableHeader>
                                        <TableHeader>{t('global.status')}</TableHeader>
                                        <TableHeader>{t('global.nurse')}</TableHeader>
                                        <TableHeader align="center">{t('global.actions')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {dayGroups.map((group, index) => {
                                        const nurses = [
                                            ...new Set(
                                                group.items
                                                    .map((item) => item.nurse_name)
                                                    .filter(Boolean) as string[]
                                            ),
                                        ];
                                        const typeSummary = group.items
                                            .map((item) => item.type_name)
                                            .filter(Boolean)
                                            .join(', ');

                                        return (
                                            <TableRow key={group.date}>
                                                <TableCell>{index + 1}</TableCell>
                                                <TableCell muted dir="ltr">
                                                    {group.date}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex flex-wrap items-center gap-1.5">
                                                        <Badge color="failure" className="w-fit">
                                                            {group.items.length}
                                                        </Badge>
                                                        <span className="text-sm text-gray-700 dark:text-gray-300">
                                                            {typeSummary || '—'}
                                                        </span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge color="success">{t('global.completed')}</Badge>
                                                </TableCell>
                                                <TableCell muted>
                                                    {nurses.length > 0 ? nurses.join(' / ') : '—'}
                                                </TableCell>
                                                <TableCell align="center">
                                                    <div className="flex flex-wrap items-center justify-center gap-1">
                                                        <SectionActionButton
                                                            icon="bx-expand"
                                                            title={t('global.view')}
                                                            onClick={() => viewDay(group)}
                                                            colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                        />
                                                        {data?.permissions.manage && !isDischarged && (
                                                            <SectionActionButton
                                                                icon="bx-edit"
                                                                title={t('global.edit')}
                                                                onClick={() => openManageForDate(group.date)}
                                                                colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                            />
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        );
                                    })}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_vital_signs_found')} />
                        )}
                    </>
                )}
            </SectionShell>

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="4xl">
                <ModalHeader>{t('global.vital_signs')}</ModalHeader>
                <ModalBody>
                    {selectedDay && (
                        <div className="space-y-4">
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                <strong>{t('global.date')}:</strong>{' '}
                                <span dir="ltr">{selectedDay.date}</span>
                            </p>
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.vital_sign_type')}</TableHeader>
                                        <TableHeader>{t('global.morning_time')}</TableHeader>
                                        <TableHeader>{t('global.evening_time')}</TableHeader>
                                        <TableHeader>{t('global.nurse')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {selectedDay.items.map((item) => (
                                        <TableRow key={item.schedule_id}>
                                            <TableCell>
                                                <Badge color="gray">{item.type_name ?? '—'}</Badge>
                                            </TableCell>
                                            <TableCell>{item.morning_time ?? '—'}</TableCell>
                                            <TableCell>{item.evening_time ?? '—'}</TableCell>
                                            <TableCell muted>{item.nurse_name ?? '—'}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </ModalBody>
                <ModalFooter>
                    {data?.permissions.manage && !isDischarged && selectedDay && (
                        <Button
                            color="failure"
                            type="button"
                            onClick={() => {
                                setDetailsOpen(false);
                                openManageForDate(selectedDay.date);
                            }}
                        >
                            <i className="bx bx-edit me-2" />
                            {t('global.edit')}
                        </Button>
                    )}
                    <Button color="light" type="button" onClick={() => setDetailsOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>

            <Modal show={manageOpen} onClose={closeManage} size="4xl">
                <form onSubmit={handleSave}>
                    <ModalHeader>{t('global.manage_vital_signs')}</ModalHeader>
                    <ModalBody className="space-y-4">
                        {metaLoading ? (
                            <SectionLoadingState />
                        ) : (
                            <>
                                <div className="rounded-xl border border-gray-100 p-4 dark:border-gray-700">
                                    <Label htmlFor="vital-schedule-date">{t('global.date')}</Label>
                                    <TextInput
                                        id="vital-schedule-date"
                                        className="mt-2 max-w-xs"
                                        dir="ltr"
                                        placeholder="1403/01/01"
                                        required
                                        value={scheduleDate}
                                        onChange={(e) => handleDateChange(e.target.value)}
                                    />
                                </div>

                                <Table>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>{t('global.vital_sign_type')}</TableHeader>
                                            <TableHeader>{t('global.morning_time')}</TableHeader>
                                            <TableHeader>{t('global.evening_time')}</TableHeader>
                                            <TableHeader align="center">{t('global.actions')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {rows.map((row, index) => (
                                            <TableRow key={`row-${index}`}>
                                                <TableCell>
                                                    <SearchableSelect
                                                        value={row.vital_sign_type_id}
                                                        onChange={(value) =>
                                                            updateRow(index, { vital_sign_type_id: value })
                                                        }
                                                        options={typeOptions.map((option) => ({
                                                            ...option,
                                                            disabled:
                                                                usedTypeIds.has(option.value) &&
                                                                option.value !== row.vital_sign_type_id,
                                                        }))}
                                                        placeholder={t('global.select')}
                                                    />
                                                </TableCell>
                                                <TableCell>
                                                    <TextInput
                                                        placeholder={t('global.enter_morning_time')}
                                                        value={row.morning_time}
                                                        onChange={(e) =>
                                                            updateRow(index, { morning_time: e.target.value })
                                                        }
                                                    />
                                                </TableCell>
                                                <TableCell>
                                                    <TextInput
                                                        placeholder={t('global.enter_evening_time')}
                                                        value={row.evening_time}
                                                        onChange={(e) =>
                                                            updateRow(index, { evening_time: e.target.value })
                                                        }
                                                    />
                                                </TableCell>
                                                <TableCell align="center">
                                                    {rows.length > 1 && (
                                                        <SectionActionButton
                                                            icon="bx-trash"
                                                            title={t('global.delete')}
                                                            onClick={() => removeRow(index)}
                                                            colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                        />
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>

                                <Button type="button" size="sm" color="failure" onClick={addRow}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.add')}
                                </Button>
                            </>
                        )}
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={closeManage}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="failure" disabled={submitting || metaLoading}>
                            {submitting ? <Spinner size="sm" className="me-2" /> : null}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </>
    );
}
