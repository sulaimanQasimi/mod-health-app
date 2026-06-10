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
import PersianDateInput from '../ui/PersianDateInput';
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

const MODAL_BODY_CLASS = 'max-h-[min(72vh,760px)] overflow-y-auto';
const SCHEDULE_DATE_INPUT_ID = 'vital-schedule-date';

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
    const [formError, setFormError] = useState<string | null>(null);
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
        setFormError(null);
        setDetailsOpen(false);
        setManageOpen(true);

        const meta = await loadMeta();
        const targetDate = date ?? meta?.default_schedule_date ?? '';
        setScheduleDate(targetDate);
        setRows(mapMetaRows(meta?.schedules_by_date?.[targetDate]));
    };

    const handleDateChange = (date: string) => {
        setScheduleDate(date);
        setFormError(null);
        setRows(mapMetaRows(schedulesByDate[date]));
    };

    const closeManage = () => {
        setManageOpen(false);
        setRows([EMPTY_ROW()]);
        setScheduleDate('');
        setFormError(null);
    };

    const resolveScheduleDate = () => {
        const input = document.getElementById(SCHEDULE_DATE_INPUT_ID) as HTMLInputElement | null;
        return (input?.value ?? scheduleDate).trim();
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

        const resolvedDate = resolveScheduleDate();
        const scheduleRows = rows
            .filter((row) => row.vital_sign_type_id)
            .map((row) => ({
                vital_sign_type_id: Number(row.vital_sign_type_id),
                morning_time: row.morning_time,
                evening_time: row.evening_time,
                schedule_id: row.schedule_id ?? null,
                vital_sign_id: row.vital_sign_id ?? null,
            }));

        if (!resolvedDate) {
            setFormError(t('global.select_date'));
            return;
        }

        if (scheduleRows.length === 0) {
            setFormError(`${t('global.select')} ${t('global.vital_sign_type')}`);
            return;
        }

        setScheduleDate(resolvedDate);
        setFormError(null);
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
                    schedule_date: resolvedDate,
                    schedule_rows: scheduleRows,
                }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                setFormError(
                    typeof payload.message === 'string'
                        ? payload.message
                        : t('global.request_failed'),
                );
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
                                    {t('global.manage_vital_signs')}
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

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="7xl">
                <ModalHeader>{t('global.view_vital_signs')}</ModalHeader>
                <ModalBody className={`space-y-4 ${MODAL_BODY_CLASS}`}>
                    {selectedDay && (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.date')}
                                    </p>
                                    <p className="mt-1 font-medium" dir="ltr">
                                        {selectedDay.date}
                                    </p>
                                </div>
                                <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.vital_sign_type')}
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {selectedDay.items.length} {t('global.records')}
                                    </p>
                                </div>
                            </div>

                            <div className="rounded-xl border border-gray-100 dark:border-gray-700">
                                <Table embedded className="min-w-[720px]">
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
                                                    <Badge color="failure">{item.type_name ?? '—'}</Badge>
                                                </TableCell>
                                                <TableCell dir="ltr">{item.morning_time ?? '—'}</TableCell>
                                                <TableCell dir="ltr">{item.evening_time ?? '—'}</TableCell>
                                                <TableCell muted>{item.nurse_name ?? '—'}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </>
                    )}
                </ModalBody>
                <ModalFooter>
                    {data?.urls?.print && (
                        <Button
                            color="blue"
                            as="a"
                            href={data.urls.print}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <i className="bx bx-printer me-2" />
                            {t('global.print_vital_signs_chart')}
                        </Button>
                    )}
                    {data?.permissions.manage && !isDischarged && selectedDay && (
                        <Button
                            color="warning"
                            type="button"
                            onClick={() => openManageForDate(selectedDay.date)}
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

            <Modal show={manageOpen} onClose={closeManage} size="7xl">
                <ModalHeader>{t('global.manage_vital_signs')}</ModalHeader>
                <form onSubmit={handleSave}>
                    <ModalBody className={`space-y-4 ${MODAL_BODY_CLASS}`}>
                        {metaLoading ? (
                            <SectionLoadingState />
                        ) : (
                            <>
                                {formError && (
                                    <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                                        {formError}
                                    </div>
                                )}

                                <div className="rounded-xl border border-gray-100 p-4 dark:border-gray-700">
                                    <Label htmlFor={SCHEDULE_DATE_INPUT_ID}>{t('global.date')}</Label>
                                    <PersianDateInput
                                        id={SCHEDULE_DATE_INPUT_ID}
                                        className="mt-2 max-w-xs"
                                        required
                                        value={scheduleDate}
                                        onChange={handleDateChange}
                                    />
                                </div>

                                <div className="rounded-xl border border-gray-100 dark:border-gray-700">
                                    <Table embedded className="min-w-[760px]">
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.vital_sign_type')}</TableHeader>
                                                <TableHeader>{t('global.morning_time')}</TableHeader>
                                                <TableHeader>{t('global.evening_time')}</TableHeader>
                                                <TableHeader align="center" className="w-20">
                                                    {t('global.actions')}
                                                </TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {rows.map((row, index) => (
                                                <TableRow key={`row-${index}`}>
                                                    <TableCell className="min-w-[220px]">
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
                                                            dir="ltr"
                                                            placeholder={t('global.enter_morning_time')}
                                                            value={row.morning_time}
                                                            onChange={(e) =>
                                                                updateRow(index, {
                                                                    morning_time: e.target.value,
                                                                })
                                                            }
                                                        />
                                                    </TableCell>
                                                    <TableCell>
                                                        <TextInput
                                                            dir="ltr"
                                                            placeholder={t('global.enter_evening_time')}
                                                            value={row.evening_time}
                                                            onChange={(e) =>
                                                                updateRow(index, {
                                                                    evening_time: e.target.value,
                                                                })
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
                                </div>

                                <Button type="button" size="sm" color="light" onClick={addRow}>
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
