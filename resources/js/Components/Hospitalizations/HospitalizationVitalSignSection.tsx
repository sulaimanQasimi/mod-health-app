import {
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
import AppointmentSectionAccordion, {
    SectionEmptyState,
    SectionLoadingState,
} from '../Appointments/Sections/AppointmentSectionAccordion';

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
                const defaultDate = payload.data.default_schedule_date ?? '';
                setScheduleDate(defaultDate);
                setRows(mapMetaRows(payload.data.schedules_by_date?.[defaultDate]));
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    useEffect(() => {
        if (manageOpen) {
            loadMeta();
        }
    }, [manageOpen, loadMeta]);

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

    const handleDateChange = (date: string) => {
        setScheduleDate(date);
        setRows(mapMetaRows(schedulesByDate[date]));
    };

    const openManage = () => {
        setManageOpen(true);
    };

    const closeManage = () => {
        setManageOpen(false);
        setRows([EMPTY_ROW()]);
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
            <AppointmentSectionAccordion
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
                                <Button size="sm" color="failure" onClick={openManage}>
                                    <i className="bx bx-plus-medical me-2" />
                                    {t('global.manage_vital_signs')}
                                </Button>
                            )}
                        </div>

                        {data && data.items.length > 0 ? (
                            <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>{t('global.date')}</TableHead>
                                            <TableHead>{t('global.vital_sign_type')}</TableHead>
                                            <TableHead>{t('global.morning_time')}</TableHead>
                                            <TableHead>{t('global.evening_time')}</TableHead>
                                            <TableHead>{t('global.nurse')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {data.items.map((item) => (
                                            <TableRow key={item.schedule_id}>
                                                <TableCell dir="ltr" className="text-gray-600">
                                                    {item.date ?? '—'}
                                                </TableCell>
                                                <TableCell className="font-medium">
                                                    {item.type_name ?? '—'}
                                                </TableCell>
                                                <TableCell>{item.morning_time ?? '—'}</TableCell>
                                                <TableCell>{item.evening_time ?? '—'}</TableCell>
                                                <TableCell className="text-gray-600">
                                                    {item.nurse_name ?? '—'}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <SectionEmptyState message={t('global.no_vital_signs_found')} />
                        )}
                    </>
                )}
            </AppointmentSectionAccordion>

            <Modal show={manageOpen} onClose={closeManage} size="4xl">
                <form onSubmit={handleSave}>
                    <ModalHeader>{t('global.manage_vital_signs')}</ModalHeader>
                    <ModalBody className="space-y-4">
                        {metaLoading ? (
                            <div className="flex justify-center py-10">
                                <Spinner size="lg" />
                            </div>
                        ) : (
                            <>
                                <div className="rounded-xl border border-rose-100 bg-rose-50/50 p-4 dark:border-rose-900/40 dark:bg-rose-950/20">
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
                                    <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        {t('global.add_first_vital_sign')}
                                    </p>
                                </div>

                                <div className="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                    <div className="grid grid-cols-[1fr_1fr_1fr_auto] gap-3 border-b border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                                        <span>{t('global.vital_sign_type')}</span>
                                        <span>{t('global.morning_time')}</span>
                                        <span>{t('global.evening_time')}</span>
                                        <span className="w-8" />
                                    </div>
                                    <div className="divide-y divide-gray-100 dark:divide-gray-800">
                                        {rows.map((row, index) => (
                                            <div
                                                key={`row-${index}`}
                                                className="grid grid-cols-1 items-end gap-3 px-4 py-3 md:grid-cols-[1fr_1fr_1fr_auto]"
                                            >
                                                <div>
                                                    <Label className="mb-1 md:sr-only">
                                                        {t('global.vital_sign_type')}
                                                    </Label>
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
                                                </div>
                                                <div>
                                                    <Label className="mb-1 md:sr-only">
                                                        {t('global.morning_time')}
                                                    </Label>
                                                    <TextInput
                                                        placeholder={t('global.enter_morning_time')}
                                                        value={row.morning_time}
                                                        onChange={(e) =>
                                                            updateRow(index, { morning_time: e.target.value })
                                                        }
                                                    />
                                                </div>
                                                <div>
                                                    <Label className="mb-1 md:sr-only">
                                                        {t('global.evening_time')}
                                                    </Label>
                                                    <TextInput
                                                        placeholder={t('global.enter_evening_time')}
                                                        value={row.evening_time}
                                                        onChange={(e) =>
                                                            updateRow(index, { evening_time: e.target.value })
                                                        }
                                                    />
                                                </div>
                                                <Button
                                                    type="button"
                                                    color="light"
                                                    size="sm"
                                                    className="mb-0.5 shrink-0"
                                                    disabled={rows.length <= 1}
                                                    onClick={() => removeRow(index)}
                                                    aria-label={t('global.delete')}
                                                >
                                                    <i className="bx bx-trash text-red-500" />
                                                </Button>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <Button type="button" color="light" size="sm" onClick={addRow}>
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
