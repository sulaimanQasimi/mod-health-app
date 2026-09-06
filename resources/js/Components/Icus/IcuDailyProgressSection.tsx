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
import { FormEvent, useCallback, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import SearchableMultiSelect from '../ui/SearchableMultiSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

interface IcuDailyProgressSectionProps {
    icuId: number;
    isDischarged?: boolean;
    iconClassName?: string;
}

interface LabTypeOption {
    id: number;
    name: string;
}

interface ProgressListItem {
    id: number;
    icu_day: string | null;
    created_by_name: string | null;
    created_at: string | null;
}

interface ProgressDetail extends ProgressListItem {
    icu_diagnose: string | null;
    daily_events: string | null;
    hr: string | null;
    bp: string | null;
    spo2: string | null;
    t: string | null;
    rr: string | null;
    gcs: string | null;
    cvs: string | null;
    pupils: string | null;
    s1s2: string | null;
    rs: string | null;
    gi: string | null;
    renal: string | null;
    musculoskeletal_system: string | null;
    extremities: string | null;
    assesment: string | null;
    plan: string | null;
    lab_ids: number[];
    lab_type_names: string[];
}

interface ProgressFormState {
    icu_day: string;
    icu_diagnose: string;
    daily_events: string;
    hr: string;
    bp: string;
    spo2: string;
    t: string;
    rr: string;
    gcs: string;
    cvs: string;
    pupils: string;
    s1s2: string;
    rs: string;
    gi: string;
    renal: string;
    musculoskeletal_system: string;
    extremities: string;
    assesment: string;
    plan: string;
    lab_ids: string[];
}

interface SectionData {
    items: ProgressListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
}

const EMPTY_FORM: ProgressFormState = {
    icu_day: '',
    icu_diagnose: '',
    daily_events: '',
    hr: '',
    bp: '',
    spo2: '',
    t: '',
    rr: '',
    gcs: '',
    cvs: '',
    pupils: '',
    s1s2: '',
    rs: '',
    gi: '',
    renal: '',
    musculoskeletal_system: '',
    extremities: '',
    assesment: '',
    plan: '',
    lab_ids: [],
};

const DETAIL_FIELDS: Array<{ key: keyof ProgressDetail; labelKey: string }> = [
    { key: 'icu_day', labelKey: 'global.icu_day' },
    { key: 'icu_diagnose', labelKey: 'global.icu_diagnose' },
    { key: 'daily_events', labelKey: 'global.daily_events' },
    { key: 'hr', labelKey: 'global.hr' },
    { key: 'bp', labelKey: 'global.bp' },
    { key: 'spo2', labelKey: 'global.spo2' },
    { key: 't', labelKey: 'global.t' },
    { key: 'rr', labelKey: 'global.rr' },
    { key: 'gcs', labelKey: 'global.gcs' },
    { key: 'cvs', labelKey: 'global.cvs' },
    { key: 'pupils', labelKey: 'global.pupils' },
    { key: 's1s2', labelKey: 'global.s1s2' },
    { key: 'rs', labelKey: 'global.rs' },
    { key: 'gi', labelKey: 'global.gi' },
    { key: 'renal', labelKey: 'global.renal' },
    { key: 'musculoskeletal_system', labelKey: 'global.musculoskeletal_system' },
    { key: 'extremities', labelKey: 'global.extremities' },
    { key: 'assesment', labelKey: 'global.assesment' },
    { key: 'plan', labelKey: 'global.icu_daily_plan' },
];

function SectionHeading({ children }: { children: string }) {
    return (
        <p className="border-b border-gray-200 pb-2 text-sm font-semibold text-blue-700 dark:border-gray-700 dark:text-blue-300">
            {children}
        </p>
    );
}

function FormField({
    id,
    label,
    value,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
}) {
    return (
        <div>
            <Label htmlFor={id}>{label}</Label>
            <TextInput
                id={id}
                value={value}
                onChange={(e) => onChange(e.target.value)}
            />
        </div>
    );
}

function detailToForm(detail: ProgressDetail): ProgressFormState {
    return {
        icu_day: detail.icu_day ?? '',
        icu_diagnose: detail.icu_diagnose ?? '',
        daily_events: detail.daily_events ?? '',
        hr: detail.hr ?? '',
        bp: detail.bp ?? '',
        spo2: detail.spo2 ?? '',
        t: detail.t ?? '',
        rr: detail.rr ?? '',
        gcs: detail.gcs ?? '',
        cvs: detail.cvs ?? '',
        pupils: detail.pupils ?? '',
        s1s2: detail.s1s2 ?? '',
        rs: detail.rs ?? '',
        gi: detail.gi ?? '',
        renal: detail.renal ?? '',
        musculoskeletal_system: detail.musculoskeletal_system ?? '',
        extremities: detail.extremities ?? '',
        assesment: detail.assesment ?? '',
        plan: detail.plan ?? '',
        lab_ids: detail.lab_ids.map(String),
    };
}

export default function IcuDailyProgressSection({
    icuId,
    isDischarged = false,
    iconClassName = 'text-sky-500',
}: IcuDailyProgressSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/icus/${icuId}/daily-progress`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [detailLoading, setDetailLoading] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [labTypes, setLabTypes] = useState<LabTypeOption[]>([]);
    const [formOpen, setFormOpen] = useState(false);
    const [viewOpen, setViewOpen] = useState(false);
    const [editingProgressId, setEditingProgressId] = useState<number | null>(null);
    const [selectedDetail, setSelectedDetail] = useState<ProgressDetail | null>(null);
    const [form, setForm] = useState<ProgressFormState>(EMPTY_FORM);

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
        const response = await fetch(`${baseUrl}/meta`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (payload.success) {
            setLabTypes(payload.data.lab_types ?? []);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
        loadMeta();
    }, [loadData, loadMeta]);

    const postJson = async (url: string, method: string, body?: Record<string, unknown>) => {
        setSubmitting(true);
        try {
            const response = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: body ? JSON.stringify(body) : undefined,
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return false;
            }
            await loadData();
            return true;
        } finally {
            setSubmitting(false);
        }
    };

    const loadDetail = async (progressId: number) => {
        setDetailLoading(true);
        try {
            const response = await fetch(`${baseUrl}/${progressId}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                return payload.data as ProgressDetail;
            }
        } finally {
            setDetailLoading(false);
        }
        return null;
    };

    const openCreate = () => {
        setEditingProgressId(null);
        setForm(EMPTY_FORM);
        setFormOpen(true);
    };

    const openEdit = async (progressId: number) => {
        const detail = await loadDetail(progressId);
        if (!detail) {
            return;
        }
        setEditingProgressId(progressId);
        setForm(detailToForm(detail));
        setFormOpen(true);
    };

    const openView = async (progressId: number) => {
        const detail = await loadDetail(progressId);
        if (!detail) {
            return;
        }
        setSelectedDetail(detail);
        setViewOpen(true);
    };

    const closeForm = () => {
        setFormOpen(false);
        setEditingProgressId(null);
        setForm(EMPTY_FORM);
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();

        const payload = {
            ...form,
            lab_ids: form.lab_ids.map(Number).filter((id) => id > 0),
        };

        const ok = editingProgressId
            ? await postJson(`${baseUrl}/${editingProgressId}`, 'PUT', payload)
            : await postJson(baseUrl, 'POST', payload);

        if (ok) {
            closeForm();
        }
    };

    const handleDelete = async (progressId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        await postJson(`${baseUrl}/${progressId}`, 'DELETE');
    };

    const updateField = (key: keyof ProgressFormState, value: string | string[]) => {
        setForm((prev) => ({ ...prev, [key]: value }));
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <SectionShell
                id={`icu-daily-progress-${icuId}`}
                icon="bx-hourglass"
                iconClassName={iconClassName}
                title={t('global.daily_icu_progress')}
                count={data?.count}
                badgeColor="info"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        {data?.permissions.create && !isDischarged && (
                            <div className="mb-4 flex justify-end">
                                <Button size="sm" color="success" onClick={openCreate}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.add_daily_progress')}
                                </Button>
                            </div>
                        )}

                        {(data?.items.length ?? 0) > 0 ? (
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.number')}</TableHeader>
                                        <TableHeader>{t('global.icu_day')}</TableHeader>
                                        <TableHeader>{t('global.created_by')}</TableHeader>
                                        <TableHeader>{t('global.created_at')}</TableHeader>
                                        <TableHeader align="center">{t('global.actions')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data?.items.map((progress, index) => (
                                        <TableRow key={progress.id}>
                                            <TableCell>{index + 1}</TableCell>
                                            <TableCell>{progress.icu_day ?? '—'}</TableCell>
                                            <TableCell muted>{progress.created_by_name ?? '—'}</TableCell>
                                            <TableCell muted dir="ltr">
                                                {progress.created_at ?? '—'}
                                            </TableCell>
                                            <TableCell align="center">
                                                <div className="flex items-center justify-center gap-1">
                                                    <SectionActionButton
                                                        icon="bx-expand"
                                                        title={t('global.view')}
                                                        onClick={() => openView(progress.id)}
                                                        colorClass="text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/30"
                                                    />
                                                    {data?.permissions.edit && (
                                                        <SectionActionButton
                                                            icon="bx-edit"
                                                            title={t('global.edit')}
                                                            onClick={() => openEdit(progress.id)}
                                                            colorClass="text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30"
                                                        />
                                                    )}
                                                    {data?.permissions.delete && (
                                                        <SectionActionButton
                                                            icon="bx-trash"
                                                            title={t('global.delete')}
                                                            onClick={() => handleDelete(progress.id)}
                                                            colorClass="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30"
                                                        />
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_previous_progress')} />
                        )}
                    </>
                )}
            </SectionShell>

            <Modal show={viewOpen} onClose={() => setViewOpen(false)} size="4xl">
                <ModalHeader>{t('global.daily_icu_progress')}</ModalHeader>
                <ModalBody className="max-h-[min(72vh,760px)] space-y-4 overflow-y-auto">
                    {detailLoading || !selectedDetail ? (
                        <div className="flex justify-center py-8">
                            <Spinner size="lg" />
                        </div>
                    ) : (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                {DETAIL_FIELDS.map(({ key, labelKey }) => (
                                    <div
                                        key={key}
                                        className="rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/60"
                                    >
                                        <p className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            {t(labelKey)}
                                        </p>
                                        <p className="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {(selectedDetail[key] as string | null) || '—'}
                                        </p>
                                    </div>
                                ))}
                            </div>
                            <div className="rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/60">
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {t('global.lab_ids')}
                                </p>
                                <p className="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {selectedDetail.lab_type_names.length > 0
                                        ? selectedDetail.lab_type_names.join(' · ')
                                        : '—'}
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                                <span>
                                    {t('global.created_by')}: {selectedDetail.created_by_name ?? '—'}
                                </span>
                                <span dir="ltr">
                                    {t('global.created_at')}: {selectedDetail.created_at ?? '—'}
                                </span>
                            </div>
                        </>
                    )}
                </ModalBody>
                <ModalFooter>
                    <Button color="light" onClick={() => setViewOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>

            <Modal show={formOpen} onClose={() => !submitting && closeForm()} size="4xl">
                <form onSubmit={handleSubmit}>
                    <ModalHeader>
                        {editingProgressId
                            ? t('global.edit_daily_icu_progress')
                            : t('global.add_daily_progress')}
                    </ModalHeader>
                    <ModalBody className="max-h-[min(72vh,760px)] space-y-5 overflow-y-auto">
                        <div className="space-y-4">
                            <SectionHeading>{t('global.basic_information')}</SectionHeading>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <FormField
                                    id="icu-day"
                                    label={t('global.icu_day')}
                                    value={form.icu_day}
                                    onChange={(value) => updateField('icu_day', value)}
                                />
                                <FormField
                                    id="icu-diagnose"
                                    label={t('global.icu_diagnose')}
                                    value={form.icu_diagnose}
                                    onChange={(value) => updateField('icu_diagnose', value)}
                                />
                                <FormField
                                    id="daily-events"
                                    label={t('global.daily_events')}
                                    value={form.daily_events}
                                    onChange={(value) => updateField('daily_events', value)}
                                />
                                <FormField
                                    id="hr"
                                    label={t('global.hr')}
                                    value={form.hr}
                                    onChange={(value) => updateField('hr', value)}
                                />
                            </div>
                        </div>

                        <div className="space-y-4">
                            <SectionHeading>{t('global.vital_signs')}</SectionHeading>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <FormField id="bp" label={t('global.bp')} value={form.bp} onChange={(v) => updateField('bp', v)} />
                                <FormField id="spo2" label={t('global.spo2')} value={form.spo2} onChange={(v) => updateField('spo2', v)} />
                                <FormField id="t" label={t('global.t')} value={form.t} onChange={(v) => updateField('t', v)} />
                                <FormField id="rr" label={t('global.rr')} value={form.rr} onChange={(v) => updateField('rr', v)} />
                            </div>
                        </div>

                        <div className="space-y-4">
                            <SectionHeading>{t('global.neurological_assessment')}</SectionHeading>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <FormField id="gcs" label={t('global.gcs')} value={form.gcs} onChange={(v) => updateField('gcs', v)} />
                                <FormField id="cvs" label={t('global.cvs')} value={form.cvs} onChange={(v) => updateField('cvs', v)} />
                                <FormField id="pupils" label={t('global.pupils')} value={form.pupils} onChange={(v) => updateField('pupils', v)} />
                                <FormField id="s1s2" label={t('global.s1s2')} value={form.s1s2} onChange={(v) => updateField('s1s2', v)} />
                            </div>
                        </div>

                        <div className="space-y-4">
                            <SectionHeading>{t('global.system_assessment')}</SectionHeading>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <FormField id="rs" label={t('global.rs')} value={form.rs} onChange={(v) => updateField('rs', v)} />
                                <FormField id="gi" label={t('global.gi')} value={form.gi} onChange={(v) => updateField('gi', v)} />
                                <FormField id="renal" label={t('global.renal')} value={form.renal} onChange={(v) => updateField('renal', v)} />
                                <FormField
                                    id="musculoskeletal"
                                    label={t('global.musculoskeletal_system')}
                                    value={form.musculoskeletal_system}
                                    onChange={(v) => updateField('musculoskeletal_system', v)}
                                />
                            </div>
                        </div>

                        <div className="space-y-4">
                            <SectionHeading>{t('global.additional_information')}</SectionHeading>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <FormField
                                    id="extremities"
                                    label={t('global.extremities')}
                                    value={form.extremities}
                                    onChange={(v) => updateField('extremities', v)}
                                />
                                <FormField
                                    id="assesment"
                                    label={t('global.assesment')}
                                    value={form.assesment}
                                    onChange={(v) => updateField('assesment', v)}
                                />
                                <FormField
                                    id="plan"
                                    label={t('global.icu_daily_plan')}
                                    value={form.plan}
                                    onChange={(v) => updateField('plan', v)}
                                />
                                <div>
                                    <Label>{t('global.lab_ids')}</Label>
                                    <SearchableMultiSelect
                                        values={form.lab_ids}
                                        onChange={(value) => updateField('lab_ids', value)}
                                        options={labTypes.map((type) => ({
                                            value: String(type.id),
                                            label: type.name,
                                        }))}
                                        placeholder={t('global.select')}
                                    />
                                </div>
                            </div>
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" disabled={submitting} onClick={closeForm}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="success" disabled={submitting}>
                            {submitting && <Spinner size="sm" className="me-2" />}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </>
    );
}
