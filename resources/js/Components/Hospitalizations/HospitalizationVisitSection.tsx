import {
    Badge,
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
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    AccordionActionBar,
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

interface HospitalizationVisitSectionProps {
    hospitalizationId?: number;
    icuId?: number;
    isDischarged?: boolean;
    iconClassName?: string;
}

interface FoodTypeOption {
    id: number;
    name: string;
}

interface VisitListItem {
    id: number;
    description: string | null;
    doctor_name: string | null;
    visit_date: string | null;
    visit_time: string | null;
    bp: string | null;
    pr: string | null;
    rr: string | null;
    t: string | null;
    spo2: string | null;
    pain: string | null;
    antibiotic: string | null;
    food_type_ids: number[];
    food_type_names: string[];
    intake: string | null;
    output: string | null;
}

interface VisitFormState {
    description: string;
    bp: string;
    pr: string;
    rr: string;
    t: string;
    spo2: string;
    pain: string;
    antibiotic: string;
    food_type_ids: number[];
    intake: string;
    output: string;
}

interface SectionData {
    items: VisitListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
}

const EMPTY_FORM: VisitFormState = {
    description: '',
    bp: '',
    pr: '',
    rr: '',
    t: '',
    spo2: '',
    pain: '',
    antibiotic: '',
    food_type_ids: [],
    intake: '',
    output: '',
};

const VITAL_SIGN_FIELDS = [
    ['bp', 'global.bp'],
    ['pr', 'global.pr'],
    ['rr', 'global.rr'],
    ['t', 'global.t'],
    ['spo2', 'global.spo2'],
    ['pain', 'global.pain'],
] as const;

function visitVitalSignItems(visit: VisitListItem, t: (key: string) => string) {
    return VITAL_SIGN_FIELDS.map(([field, labelKey]) => ({
        key: field,
        label: t(labelKey),
        value: visit[field],
    })).filter((item) => item.value);
}

function VitalSignBadges({ visit, t }: { visit: VisitListItem; t: (key: string) => string }) {
    const items = visitVitalSignItems(visit, t);

    if (items.length === 0) {
        return <span className="text-gray-400">—</span>;
    }

    return (
        <div className="flex flex-wrap gap-1">
            {items.map((item) => (
                <Badge key={item.key} color="info" className="whitespace-nowrap">
                    {item.label}: {item.value}
                </Badge>
            ))}
        </div>
    );
}

function VitalSignDetails({ visit, t }: { visit: VisitListItem; t: (key: string) => string }) {
    const items = visitVitalSignItems(visit, t);

    if (items.length === 0) {
        return <p className="text-sm text-gray-500">—</p>;
    }

    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {items.map((item) => (
                <div
                    key={item.key}
                    className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <p className="text-xs font-medium uppercase text-gray-500">{item.label}</p>
                    <p className="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{item.value}</p>
                </div>
            ))}
        </div>
    );
}

function VisitFormFields({
    form,
    setForm,
    foodTypes,
    t,
}: {
    form: VisitFormState;
    setForm: React.Dispatch<React.SetStateAction<VisitFormState>>;
    foodTypes: FoodTypeOption[];
    t: (key: string) => string;
}) {
    const toggleFoodType = (foodTypeId: number) => {
        setForm((prev) => ({
            ...prev,
            food_type_ids: prev.food_type_ids.includes(foodTypeId)
                ? prev.food_type_ids.filter((id) => id !== foodTypeId)
                : [...prev.food_type_ids, foodTypeId],
        }));
    };

    return (
        <div className="space-y-5">
            <div>
                <Label htmlFor="visit-description">{t('global.description')}</Label>
                <Textarea
                    id="visit-description"
                    rows={3}
                    required
                    className="mt-2"
                    value={form.description}
                    onChange={(e) => setForm((prev) => ({ ...prev, description: e.target.value }))}
                />
            </div>

            <div>
                <p className="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                    {t('global.vital_signs')}
                </p>
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    {VITAL_SIGN_FIELDS.map(([field, labelKey]) => (
                        <div key={field}>
                            <Label htmlFor={`visit-${field}`}>{t(labelKey)}</Label>
                            <TextInput
                                id={`visit-${field}`}
                                className="mt-1"
                                value={form[field]}
                                onChange={(e) =>
                                    setForm((prev) => ({ ...prev, [field]: e.target.value }))
                                }
                            />
                        </div>
                    ))}
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="visit-antibiotic">{t('global.antibiotic')}</Label>
                    <TextInput
                        id="visit-antibiotic"
                        className="mt-1"
                        value={form.antibiotic}
                        onChange={(e) => setForm((prev) => ({ ...prev, antibiotic: e.target.value }))}
                    />
                </div>
                <div>
                    <Label>{t('global.food_type')}</Label>
                    <div className="mt-2 flex flex-wrap gap-2">
                        {foodTypes.map((foodType) => {
                            const selected = form.food_type_ids.includes(foodType.id);
                            return (
                                <button
                                    key={foodType.id}
                                    type="button"
                                    onClick={() => toggleFoodType(foodType.id)}
                                    className={`rounded-full border px-3 py-1 text-sm font-medium transition-all ${
                                        selected
                                            ? 'border-cyan-500 bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-200'
                                            : 'border-gray-200 text-gray-600 hover:border-cyan-300 dark:border-gray-600 dark:text-gray-300'
                                    }`}
                                >
                                    {foodType.name}
                                </button>
                            );
                        })}
                    </div>
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="visit-intake">{t('global.intake')}</Label>
                    <TextInput
                        id="visit-intake"
                        className="mt-1"
                        value={form.intake}
                        onChange={(e) => setForm((prev) => ({ ...prev, intake: e.target.value }))}
                    />
                </div>
                <div>
                    <Label htmlFor="visit-output">{t('global.output')}</Label>
                    <TextInput
                        id="visit-output"
                        className="mt-1"
                        value={form.output}
                        onChange={(e) => setForm((prev) => ({ ...prev, output: e.target.value }))}
                    />
                </div>
            </div>
        </div>
    );
}

export default function HospitalizationVisitSection({
    hospitalizationId,
    icuId,
    isDischarged = false,
    iconClassName = 'text-cyan-500',
}: HospitalizationVisitSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const sectionKey = icuId ?? hospitalizationId;
    const baseUrl = icuId
        ? `/react/icus/${icuId}/visits`
        : `/react/hospitalizations/${hospitalizationId}/visits`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [foodTypes, setFoodTypes] = useState<FoodTypeOption[]>([]);
    const [formOpen, setFormOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [editingVisitId, setEditingVisitId] = useState<number | null>(null);
    const [selectedVisit, setSelectedVisit] = useState<VisitListItem | null>(null);
    const [form, setForm] = useState<VisitFormState>(EMPTY_FORM);

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
            setFoodTypes(payload.data.food_types ?? []);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const ensureMetaLoaded = async () => {
        if (foodTypes.length > 0) {
            return;
        }

        await loadMeta();
    };

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

    const openCreate = async () => {
        await ensureMetaLoaded();
        setEditingVisitId(null);
        setForm(EMPTY_FORM);
        setFormOpen(true);
    };

    const openEdit = async (visit: VisitListItem) => {
        await ensureMetaLoaded();
        setEditingVisitId(visit.id);
        setForm({
            description: visit.description ?? '',
            bp: visit.bp ?? '',
            pr: visit.pr ?? '',
            rr: visit.rr ?? '',
            t: visit.t ?? '',
            spo2: visit.spo2 ?? '',
            pain: visit.pain ?? '',
            antibiotic: visit.antibiotic ?? '',
            food_type_ids: visit.food_type_ids ?? [],
            intake: visit.intake ?? '',
            output: visit.output ?? '',
        });
        setDetailsOpen(false);
        setFormOpen(true);
    };

    const closeForm = () => {
        setFormOpen(false);
        setEditingVisitId(null);
        setForm(EMPTY_FORM);
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        if (!form.description.trim()) {
            return;
        }

        const payload = {
            description: form.description,
            bp: form.bp || null,
            pr: form.pr || null,
            rr: form.rr || null,
            t: form.t || null,
            spo2: form.spo2 || null,
            pain: form.pain || null,
            antibiotic: form.antibiotic || null,
            food_type_id: form.food_type_ids,
            intake: form.intake || null,
            output: form.output || null,
        };

        const ok = editingVisitId
            ? await postJson(`${baseUrl}/${editingVisitId}`, 'PUT', payload)
            : await postJson(baseUrl, 'POST', payload);

        if (ok) {
            closeForm();
        }
    };

    const handleDelete = async (visitId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        const ok = await postJson(`${baseUrl}/${visitId}`, 'DELETE');
        if (ok) {
            setDetailsOpen(false);
            setSelectedVisit(null);
        }
    };

    const openDetails = async (visit: VisitListItem) => {
        setSelectedVisit(visit);
        setDetailsOpen(true);

        try {
            const response = await fetch(`${baseUrl}/${visit.id}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setSelectedVisit(payload.data);
            }
        } catch {
            // Keep row data when detail fetch fails.
        }
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <SectionShell
                id={`${icuId ? 'icu' : 'hospitalization'}-visits-${sectionKey}`}
                icon="bx-glasses"
                iconClassName={iconClassName}
                title={t('global.visits')}
                count={data?.count}
                badgeColor="info"
                defaultOpen
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        <AccordionButton
                            onClick={openCreate}
                            permission={Boolean(data?.permissions.create && !isDischarged)}
                        >
                            {t('global.add_visit')}
                        </AccordionButton>

                        {(data?.items.length ?? 0) > 0 ? (
                            <Table embedded className="min-w-[1280px]">
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader className="w-12">{t('global.number')}</TableHeader>
                                        <TableHeader className="min-w-[180px]">{t('global.description')}</TableHeader>
                                        <TableHeader>{t('global.by')}</TableHeader>
                                        <TableHeader>{t('global.created_at')}</TableHeader>
                                        <TableHeader>{t('global.time')}</TableHeader>
                                        <TableHeader className="min-w-[180px]">{t('global.vital_signs')}</TableHeader>
                                        <TableHeader>{t('global.antibiotic')}</TableHeader>
                                        <TableHeader>{t('global.food_type')}</TableHeader>
                                        <TableHeader>{t('global.intake')}</TableHeader>
                                        <TableHeader>{t('global.output')}</TableHeader>
                                        <TableHeader align="right" className="w-28">
                                            {t('global.actions')}
                                        </TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data?.items.map((visit, index) => (
                                        <TableRow key={visit.id}>
                                            <TableCell className="text-gray-500">{index + 1}</TableCell>
                                            <TableCell>{visit.description ?? '—'}</TableCell>
                                            <TableCell muted>{visit.doctor_name ?? '—'}</TableCell>
                                            <TableCell muted dir="ltr">
                                                {visit.visit_date ?? '—'}
                                            </TableCell>
                                            <TableCell muted dir="ltr">
                                                {visit.visit_time ?? '—'}
                                            </TableCell>
                                            <TableCell>
                                                <VitalSignBadges visit={visit} t={t} />
                                            </TableCell>
                                            <TableCell muted>{visit.antibiotic ?? '—'}</TableCell>
                                            <TableCell>
                                                {visit.food_type_names.length > 0 ? (
                                                    <div className="flex flex-wrap gap-1">
                                                        {visit.food_type_names.map((name) => (
                                                            <Badge key={name} color="purple">
                                                                {name}
                                                            </Badge>
                                                        ))}
                                                    </div>
                                                ) : (
                                                    '—'
                                                )}
                                            </TableCell>
                                            <TableCell muted>{visit.intake ?? '—'}</TableCell>
                                            <TableCell muted>{visit.output ?? '—'}</TableCell>
                                            <TableCell align="right">
                                                <SectionActionButton
                                                    icon="bx-expand"
                                                    title={t('global.view')}
                                                    onClick={() => openDetails(visit)}
                                                    colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                                />
                                                {data?.permissions.edit && !isDischarged && (
                                                    <SectionActionButton
                                                        icon="bx-edit"
                                                        title={t('global.edit')}
                                                        onClick={() => openEdit(visit)}
                                                        colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                    />
                                                )}
                                                {data?.permissions.delete && !isDischarged && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => handleDelete(visit.id)}
                                                        colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_previous_visits')} />
                        )}
                    </>
                )}
            </SectionShell>

            <Modal show={formOpen} onClose={closeForm} size="7xl">
                <form onSubmit={handleSubmit}>
                    <ModalHeader>
                        {editingVisitId ? t('global.edit') : t('global.add_visit')}
                    </ModalHeader>
                    <ModalBody>
                        <VisitFormFields form={form} setForm={setForm} foodTypes={foodTypes} t={t} />
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={closeForm}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="success" disabled={submitting}>
                            {submitting && <Spinner size="sm" className="me-2" />}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="7xl">
                <ModalHeader>{t('global.visits')}</ModalHeader>
                <ModalBody className="space-y-4">
                    {selectedVisit && (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.by')}
                                    </p>
                                    <p className="mt-1">{selectedVisit.doctor_name ?? '—'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.created_at')}
                                    </p>
                                    <p className="mt-1" dir="ltr">
                                        {selectedVisit.visit_date ?? '—'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.time')}
                                    </p>
                                    <p className="mt-1" dir="ltr">
                                        {selectedVisit.visit_time ?? '—'}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <p className="text-xs font-medium uppercase text-gray-500">
                                    {t('global.description')}
                                </p>
                                <p className="mt-1 whitespace-pre-wrap">{selectedVisit.description ?? '—'}</p>
                            </div>

                            <div>
                                <p className="mb-2 text-sm font-semibold">{t('global.vital_signs')}</p>
                                <VitalSignDetails visit={selectedVisit} t={t} />
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.antibiotic')}
                                    </p>
                                    <p className="mt-1">{selectedVisit.antibiotic ?? '—'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.food_type')}
                                    </p>
                                    <div className="mt-1 flex flex-wrap gap-1">
                                        {selectedVisit.food_type_names.length > 0
                                            ? selectedVisit.food_type_names.map((name) => (
                                                  <Badge key={name} color="purple">
                                                      {name}
                                                  </Badge>
                                              ))
                                            : '—'}
                                    </div>
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.intake')}
                                    </p>
                                    <p className="mt-1">{selectedVisit.intake ?? '—'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.output')}
                                    </p>
                                    <p className="mt-1">{selectedVisit.output ?? '—'}</p>
                                </div>
                            </div>
                        </>
                    )}
                </ModalBody>
                <ModalFooter>
                    {selectedVisit && data?.permissions.edit && !isDischarged && (
                        <Button color="warning" onClick={() => openEdit(selectedVisit)}>
                            {t('global.edit')}
                        </Button>
                    )}
                    {selectedVisit && data?.permissions.delete && !isDischarged && (
                        <Button color="failure" onClick={() => handleDelete(selectedVisit.id)}>
                            {t('global.delete')}
                        </Button>
                    )}
                    <Button color="light" onClick={() => setDetailsOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>
        </>
    );
}
