import {
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    Textarea,
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
} from '../../ui/Table';
import PersianDateInput from '../../ui/PersianDateInput';
import SearchableSelect from '../../ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import AppointmentSectionAccordion, {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
} from './AppointmentSectionAccordion';
import TableBadge from '../../ui/TableBadge';
import { SectionActionButton } from './SimpleTableSection';

interface PhysiotherapySectionProps {
    appointmentId: number;
}

interface LookupOption {
    id: number;
    name: string;
}

interface PhysiotherapyListItem {
    id: number;
    type_name: string | null;
    physiotherapist_name: string | null;
    type: string | null;
    duration: number | null;
    days_count: number | null;
    counter: number | null;
    progress_counter: number | null;
    progress_total: number | null;
    progress_percentage: number;
    status: string;
    start_date: string | null;
    end_date: string | null;
    reviews_count: number;
    urls?: { show?: string };
}

interface PhysiotherapyDetail {
    id: number;
    physiotherapy_type_name: string | null;
    physiotherapist_name: string | null;
    patient_name: string | null;
    type: string | null;
    duration: number | null;
    days_count: number | null;
    counter: number | null;
    progress_percentage: number;
    description: string | null;
    notes: string | null;
    status: string;
    start_date: string | null;
    end_date: string | null;
    reviews_count: number;
    created_by_name: string | null;
    created_at: string | null;
    urls?: { show?: string };
}

interface PhysiotherapySectionData {
    items: PhysiotherapyListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
    };
}

const STATUS_COLORS: Record<string, 'warning' | 'info' | 'success' | 'failure' | 'gray'> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

const EMPTY_FORM = {
    physiotherapy_type_id: '',
    doctor_id: '',
    type: '',
    duration: '',
    days_count: '',
    start_date: '',
    end_date: '',
    description: '',
    notes: '',
};

export default function PhysiotherapySection({ appointmentId }: PhysiotherapySectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/physiotherapy`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [metaLoading, setMetaLoading] = useState(false);
    const [data, setData] = useState<PhysiotherapySectionData | null>(null);
    const [physiotherapyTypes, setPhysiotherapyTypes] = useState<LookupOption[]>([]);
    const [physiotherapists, setPhysiotherapists] = useState<LookupOption[]>([]);
    const [createOpen, setCreateOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [selectedProcedure, setSelectedProcedure] = useState<PhysiotherapyDetail | null>(null);
    const [form, setForm] = useState(EMPTY_FORM);

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
                setPhysiotherapyTypes(payload.data.physiotherapy_types ?? []);
                setPhysiotherapists(payload.data.physiotherapists ?? []);
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const typeOptions = useMemo(
        () =>
            physiotherapyTypes.map((item) => ({
                value: String(item.id),
                label: item.name,
            })),
        [physiotherapyTypes],
    );

    const physiotherapistOptions = useMemo(
        () =>
            physiotherapists.map((item) => ({
                value: String(item.id),
                label: item.name,
            })),
        [physiotherapists],
    );

    const resetForm = () => setForm(EMPTY_FORM);

    const openCreate = async () => {
        resetForm();
        setCreateOpen(true);
        if (physiotherapyTypes.length === 0) {
            await loadMeta();
        }
    };

    const closeCreate = () => {
        setCreateOpen(false);
        resetForm();
    };

    const handleCreate = async (event: FormEvent) => {
        event.preventDefault();
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
                    physiotherapy_type_id: Number(form.physiotherapy_type_id),
                    doctor_id: Number(form.doctor_id),
                    type: form.type,
                    duration: Number(form.duration),
                    days_count: Number(form.days_count),
                    start_date: form.start_date,
                    end_date: form.end_date || null,
                    description: form.description || null,
                    notes: form.notes || null,
                }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return;
            }
            closeCreate();
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    const viewProcedure = async (procedureId: number) => {
        const response = await fetch(`${baseUrl}/${procedureId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (payload.success) {
            setSelectedProcedure(payload.data);
            setDetailsOpen(true);
        }
    };

    const statusLabel = (status: string) => {
        const labels: Record<string, string> = {
            pending: t('global.status_pending'),
            in_progress: t('global.status_in_progress'),
            completed: t('global.status_completed'),
            cancelled: t('global.status_cancelled'),
        };
        return labels[status] ?? status;
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <AppointmentSectionAccordion
            id={`physiotherapy-${appointmentId}`}
            icon="bx-health"
            iconClassName="text-cyan-500"
            title={t('global.physiotherapy_procedures')}
            count={data?.count}
            badgeColor="info"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    <AccordionButton onClick={openCreate} permission={data?.permissions.create}>
                        {t('global.add_physiotherapy_procedure')}
                    </AccordionButton>

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.physiotherapy_type')}</TableHeader>
                                    <TableHeader>{t('global.physiotherapist')}</TableHeader>
                                    <TableHeader>{t('global.type')}</TableHeader>
                                    <TableHeader>{t('global.duration')}</TableHeader>
                                    <TableHeader>{t('global.progress')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.start_date')}</TableHeader>
                                    <TableHeader>{t('global.reviews')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.type_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.physiotherapist_name ?? '—'}</TableCell>
                                        <TableCell>{item.type ?? '—'}</TableCell>
                                        <TableCell>
                                            {item.duration != null
                                                ? `${item.duration} ${t('global.minutes')}`
                                                : '—'}
                                        </TableCell>
                                        <TableCell>
                                            <div className="min-w-[120px]">
                                                <div className="mb-1 flex items-center justify-between text-xs text-gray-500">
                                                    <span>
                                                        {item.progress_counter ?? 0}/{item.progress_total ?? 0}
                                                    </span>
                                                    <span>{item.progress_percentage}%</span>
                                                </div>
                                                <div className="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                                    <div
                                                        className="h-full rounded-full bg-gradient-to-r from-cyan-500 to-teal-500"
                                                        style={{ width: `${item.progress_percentage}%` }}
                                                    />
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <TableBadge color={STATUS_COLORS[item.status] ?? 'gray'}>
                                                {statusLabel(item.status)}
                                            </TableBadge>
                                        </TableCell>
                                        <TableCell muted>{item.start_date ?? '—'}</TableCell>
                                        <TableCell>
                                            <TableBadge color={item.reviews_count > 0 ? 'info' : 'gray'}>
                                                {item.reviews_count}
                                            </TableBadge>
                                        </TableCell>
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                <SectionActionButton
                                                    icon="bx-show"
                                                    title={t('global.view')}
                                                    onClick={() => viewProcedure(item.id)}
                                                    colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                />
                                                {item.urls?.show && (
                                                    <SectionActionButton
                                                        icon="bx-expand"
                                                        title={t('global.view_physiotherapy_procedure')}
                                                        href={item.urls.show}
                                                        colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                                    />
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SectionEmptyState message={t('global.no_physiotherapy_procedures')} />
                    )}
                </>
            )}

            <Modal show={createOpen} onClose={closeCreate} size="3xl">
                <ModalHeader>{t('global.add_physiotherapy_procedure')}</ModalHeader>
                <form onSubmit={handleCreate}>
                    <ModalBody className="space-y-4">
                        {metaLoading ? (
                            <SectionLoadingState />
                        ) : (
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label>{t('global.physiotherapy_type')} *</Label>
                                    <SearchableSelect
                                        value={form.physiotherapy_type_id}
                                        onChange={(value) =>
                                            setForm((current) => ({
                                                ...current,
                                                physiotherapy_type_id: value,
                                            }))
                                        }
                                        options={typeOptions}
                                        placeholder={t('global.select_physiotherapy_type')}
                                        required
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.physiotherapist')} *</Label>
                                    <SearchableSelect
                                        value={form.doctor_id}
                                        onChange={(value) =>
                                            setForm((current) => ({ ...current, doctor_id: value }))
                                        }
                                        options={physiotherapistOptions}
                                        placeholder={t('global.select_physiotherapist')}
                                        required
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.type')} *</Label>
                                    <TextInput
                                        value={form.type}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                type: event.target.value,
                                            }))
                                        }
                                        required
                                    />
                                </div>
                                <div>
                                    <Label>
                                        {t('global.duration')} ({t('global.minutes')}) *
                                    </Label>
                                    <TextInput
                                        type="number"
                                        min={1}
                                        value={form.duration}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                duration: event.target.value,
                                            }))
                                        }
                                        required
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.total_sessions')} *</Label>
                                    <TextInput
                                        type="number"
                                        min={1}
                                        value={form.days_count}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                days_count: event.target.value,
                                            }))
                                        }
                                        required
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.start_date')} *</Label>
                                    <PersianDateInput
                                        value={form.start_date}
                                        onChange={(start_date) =>
                                            setForm((current) => ({ ...current, start_date }))
                                        }
                                        placeholder="1403/01/01"
                                        required
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.end_date')}</Label>
                                    <PersianDateInput
                                        value={form.end_date}
                                        onChange={(end_date) =>
                                            setForm((current) => ({ ...current, end_date }))
                                        }
                                        placeholder="1403/01/01"
                                    />
                                </div>
                                <div className="md:col-span-2">
                                    <Label>{t('global.description')}</Label>
                                    <Textarea
                                        rows={3}
                                        value={form.description}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                description: event.target.value,
                                            }))
                                        }
                                    />
                                </div>
                                <div className="md:col-span-2">
                                    <Label>{t('global.notes')}</Label>
                                    <Textarea
                                        rows={2}
                                        value={form.notes}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                notes: event.target.value,
                                            }))
                                        }
                                    />
                                </div>
                            </div>
                        )}
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={closeCreate} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={submitting || metaLoading}>
                            {submitting ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="4xl">
                <ModalHeader>{t('global.view_physiotherapy_procedure')}</ModalHeader>
                <ModalBody>
                    {selectedProcedure && (
                        <div className="space-y-4">
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                {[
                                    [t('global.physiotherapy_type'), selectedProcedure.physiotherapy_type_name],
                                    [t('global.physiotherapist'), selectedProcedure.physiotherapist_name],
                                    [t('global.patient_name'), selectedProcedure.patient_name],
                                    [t('global.type'), selectedProcedure.type],
                                    [t('global.duration'), selectedProcedure.duration != null ? `${selectedProcedure.duration} ${t('global.minutes')}` : '—'],
                                    [t('global.total_sessions'), selectedProcedure.days_count],
                                    [t('global.start_date'), selectedProcedure.start_date],
                                    [t('global.end_date'), selectedProcedure.end_date],
                                ].map(([label, value]) => (
                                    <div
                                        key={String(label)}
                                        className="rounded-xl border border-gray-100 bg-gray-50 p-3 text-sm dark:border-gray-700 dark:bg-gray-800/40"
                                    >
                                        <p className="text-xs text-gray-500">{label}</p>
                                        <p>{value ?? '—'}</p>
                                    </div>
                                ))}
                            </div>

                            <div className="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800/40">
                                <div className="mb-2 flex items-center justify-between gap-3">
                                    <p className="text-sm font-medium">{t('global.progress')}</p>
                                    <TableBadge color={STATUS_COLORS[selectedProcedure.status] ?? 'gray'}>
                                        {statusLabel(selectedProcedure.status)}
                                    </TableBadge>
                                </div>
                                <div className="mb-1 flex justify-between text-xs text-gray-500">
                                    <span>
                                        {selectedProcedure.counter ?? 0}/{selectedProcedure.days_count ?? 0}
                                    </span>
                                    <span>{selectedProcedure.progress_percentage}%</span>
                                </div>
                                <div className="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div
                                        className="h-full rounded-full bg-gradient-to-r from-cyan-500 to-teal-500"
                                        style={{ width: `${selectedProcedure.progress_percentage}%` }}
                                    />
                                </div>
                            </div>

                            {selectedProcedure.description && (
                                <div className="rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-sm dark:border-cyan-900/40 dark:bg-cyan-900/20">
                                    <strong>{t('global.description')}:</strong> {selectedProcedure.description}
                                </div>
                            )}

                            {selectedProcedure.notes && (
                                <div className="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-800/40">
                                    <strong>{t('global.notes')}:</strong> {selectedProcedure.notes}
                                </div>
                            )}

                            <div className="flex flex-wrap gap-3 text-sm text-gray-500">
                                <span>
                                    {t('global.reviews')}: {selectedProcedure.reviews_count}
                                </span>
                                {selectedProcedure.created_by_name && (
                                    <span>
                                        {t('global.created_by')}: {selectedProcedure.created_by_name}
                                    </span>
                                )}
                                {selectedProcedure.created_at && (
                                    <span>
                                        {t('global.created_at')}: {selectedProcedure.created_at}
                                    </span>
                                )}
                            </div>
                        </div>
                    )}
                </ModalBody>
                <ModalFooter>
                    {selectedProcedure?.urls?.show && (
                        <Button color="light" as="a" href={selectedProcedure.urls.show} target="_blank">
                            <i className="bx bx-expand me-2" />
                            {t('global.view_physiotherapy_procedure')}
                        </Button>
                    )}
                    <Button color="gray" type="button" onClick={() => setDetailsOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>
        </AppointmentSectionAccordion>
    );
}
