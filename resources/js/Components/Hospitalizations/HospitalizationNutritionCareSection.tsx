import {
    Badge,
    Button,
    Checkbox,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    Textarea,
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
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

interface HospitalizationNutritionCareSectionProps {
    hospitalizationId: number;
    isDischarged?: boolean;
    baseUrl?: string;
}

const OBSERVATION_FIELDS = [
    'cough',
    'sound',
    'fluid_swallowing_ability',
    'weight',
    'amount_and_type_of_nutrition',
    'diarrhea',
    'heart_failure_and_kidney_disease',
    'remaining_materials',
    'type_of_tube',
] as const;

const INTERVENTION_FIELDS = [
    'constipation',
    'nutrition_is_provided',
    'mouth_hygiene',
    'oral_nutrition_advices',
    'voice_exercise',
    'swallowing_exercise',
    'aspiration_prevention_proceeded',
] as const;

type ObservationField = (typeof OBSERVATION_FIELDS)[number];
type InterventionField = (typeof INTERVENTION_FIELDS)[number];
type CareField = ObservationField | InterventionField;

interface NutritionCareListItem {
    id: number;
    patient_name: string | null;
    nurse_name: string | null;
    recorded_at: string | null;
    nutrition_care_full_note: string | null;
    permissions: { edit?: boolean; delete?: boolean };
    urls?: { print?: string };
    [key: string]: unknown;
}

interface CareFormState {
    nutrition_care_full_note: string;
    [key: string]: string | boolean;
}

interface SectionData {
    items: NutritionCareListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
    };
}

function emptyForm(): CareFormState {
    const form: CareFormState = { nutrition_care_full_note: '' };
    for (const field of [...OBSERVATION_FIELDS, ...INTERVENTION_FIELDS]) {
        form[field] = false;
    }
    return form;
}

function careToForm(care: NutritionCareListItem): CareFormState {
    const form = emptyForm();
    form.nutrition_care_full_note = care.nutrition_care_full_note ?? '';
    for (const field of [...OBSERVATION_FIELDS, ...INTERVENTION_FIELDS]) {
        form[field] = Boolean(care[field]);
    }
    return form;
}

function activeFieldLabels(item: NutritionCareListItem, fields: readonly CareField[], t: (key: string) => string) {
    return fields
        .filter((field) => Boolean(item[field]))
        .map((field) => t(`global.${field}`));
}

const MODAL_BODY_CLASS = 'max-h-[min(72vh,760px)] overflow-y-auto';

function FlagBadges({ labels }: { labels: string[] }) {
    if (labels.length === 0) {
        return <span className="text-gray-400">—</span>;
    }

    return (
        <div className="flex flex-wrap gap-1">
            {labels.map((label) => (
                <Badge key={label} color="info" className="whitespace-nowrap">
                    {label}
                </Badge>
            ))}
        </div>
    );
}

function FieldChecklist({
    fields,
    item,
    t,
}: {
    fields: readonly CareField[];
    item: NutritionCareListItem;
    t: (key: string) => string;
}) {
    const activeLabels = activeFieldLabels(item, fields, t);

    if (activeLabels.length === 0) {
        return <p className="text-sm text-gray-400">—</p>;
    }

    return (
        <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            {fields
                .filter((field) => Boolean(item[field]))
                .map((field) => (
                    <div
                        key={field}
                        className="flex items-start gap-2 rounded-lg border border-emerald-100 bg-emerald-50/70 px-3 py-2 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100"
                    >
                        <i className="bx bx-check-circle mt-0.5 shrink-0 text-base text-emerald-600 dark:text-emerald-400" />
                        <span>{t(`global.${field}`)}</span>
                    </div>
                ))}
        </div>
    );
}

function CheckboxGroup({
    title,
    fields,
    form,
    setForm,
    idPrefix,
    t,
}: {
    title: string;
    fields: readonly CareField[];
    form: CareFormState;
    setForm: React.Dispatch<React.SetStateAction<CareFormState>>;
    idPrefix: string;
    t: (key: string) => string;
}) {
    return (
        <div className="rounded-xl border border-gray-100 p-4 dark:border-gray-700">
            <p className="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{title}</p>
            <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                {fields.map((field) => {
                    const inputId = `${idPrefix}-${field}`;

                    return (
                        <div
                            key={field}
                            className="flex items-start gap-2 rounded-lg border border-gray-100 px-3 py-2.5 dark:border-gray-700"
                        >
                            <Checkbox
                                id={inputId}
                                className="mt-0.5 shrink-0"
                                checked={Boolean(form[field])}
                                onChange={(e) =>
                                    setForm((prev) => ({ ...prev, [field]: e.target.checked }))
                                }
                            />
                            <Label htmlFor={inputId} className="cursor-pointer text-sm font-normal leading-snug">
                                {t(`global.${field}`)}
                            </Label>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

function CareFormFields({
    form,
    setForm,
    patientName,
    nurseName,
    idPrefix,
    t,
}: {
    form: CareFormState;
    setForm: React.Dispatch<React.SetStateAction<CareFormState>>;
    patientName: string | null;
    nurseName: string | null;
    idPrefix: string;
    t: (key: string) => string;
}) {
    return (
        <div className="space-y-5">
            <div className="grid gap-4 md:grid-cols-2">
                <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                    <p className="text-xs font-medium uppercase text-gray-500">
                        {t('global.patient_name')}
                    </p>
                    <p className="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {patientName ?? '—'}
                    </p>
                </div>
                <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                    <p className="text-xs font-medium uppercase text-gray-500">{t('global.nurse')}</p>
                    <p className="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {nurseName ?? '—'}
                    </p>
                </div>
            </div>

            <CheckboxGroup
                title={t('global.observations')}
                fields={OBSERVATION_FIELDS}
                form={form}
                setForm={setForm}
                idPrefix={`${idPrefix}-observation`}
                t={t}
            />

            <CheckboxGroup
                title={t('global.interventions')}
                fields={INTERVENTION_FIELDS}
                form={form}
                setForm={setForm}
                idPrefix={`${idPrefix}-intervention`}
                t={t}
            />

            <div className="rounded-xl border border-gray-100 p-4 dark:border-gray-700">
                <Label htmlFor={`${idPrefix}-note`}>{t('global.nutrition_care_full_note')}</Label>
                <Textarea
                    id={`${idPrefix}-note`}
                    rows={5}
                    maxLength={5000}
                    className="mt-2"
                    value={form.nutrition_care_full_note}
                    onChange={(e) =>
                        setForm((prev) => ({ ...prev, nutrition_care_full_note: e.target.value }))
                    }
                />
                <p className="mt-2 text-xs text-gray-500">
                    {t('global.max_5000_characters')}
                </p>
            </div>
        </div>
    );
}

export default function HospitalizationNutritionCareSection({
    hospitalizationId,
    isDischarged = false,
    baseUrl: baseUrlProp,
}: HospitalizationNutritionCareSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = baseUrlProp ?? `/react/hospitalizations/${hospitalizationId}/nutrition-cares`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [patientName, setPatientName] = useState<string | null>(null);
    const [currentNurseName, setCurrentNurseName] = useState<string | null>(null);
    const [formOpen, setFormOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [detailsLoading, setDetailsLoading] = useState(false);
    const [formError, setFormError] = useState<string | null>(null);
    const [editingCareId, setEditingCareId] = useState<number | null>(null);
    const [formPatientName, setFormPatientName] = useState<string | null>(null);
    const [formNurseName, setFormNurseName] = useState<string | null>(null);
    const [selectedCare, setSelectedCare] = useState<NutritionCareListItem | null>(null);
    const [form, setForm] = useState<CareFormState>(emptyForm);

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
        try {
            const response = await fetch(`${baseUrl}/meta`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) {
                return undefined;
            }
            const payload = await response.json();
            if (payload.success) {
                const nextPatientName = payload.data.patient_name ?? null;
                const nextNurseName = payload.data.current_nurse?.name ?? null;
                setPatientName(nextPatientName);
                setCurrentNurseName(nextNurseName);
                return { patient_name: nextPatientName, nurse_name: nextNurseName };
            }
        } catch {
            // Meta is optional for read-only users.
        }

        return undefined;
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

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
                setFormError(
                    typeof payload.message === 'string'
                        ? payload.message
                        : t('global.request_failed'),
                );
                return false;
            }
            setFormError(null);
            await loadData();
            return true;
        } finally {
            setSubmitting(false);
        }
    };

    const buildPayload = () => {
        const payload: Record<string, unknown> = {
            nutrition_care_full_note: form.nutrition_care_full_note || null,
        };
        for (const field of [...OBSERVATION_FIELDS, ...INTERVENTION_FIELDS]) {
            payload[field] = Boolean(form[field]);
        }
        return payload;
    };

    const openCreate = async () => {
        setFormError(null);
        setEditingCareId(null);
        setForm(emptyForm());
        setFormOpen(true);
        setDetailsOpen(false);

        const meta = await loadMeta();
        setFormPatientName(meta?.patient_name ?? patientName);
        setFormNurseName(meta?.nurse_name ?? currentNurseName);
    };

    const openEdit = (care: NutritionCareListItem) => {
        setFormError(null);
        setEditingCareId(care.id);
        setForm(careToForm(care));
        setFormPatientName(care.patient_name);
        setFormNurseName(care.nurse_name);
        setDetailsOpen(false);
        setFormOpen(true);
    };

    const closeForm = () => {
        setFormOpen(false);
        setEditingCareId(null);
        setForm(emptyForm());
        setFormError(null);
        setFormPatientName(null);
        setFormNurseName(null);
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        const ok = editingCareId
            ? await postJson(`${baseUrl}/${editingCareId}`, 'PUT', buildPayload())
            : await postJson(baseUrl, 'POST', buildPayload());

        if (ok) {
            closeForm();
        }
    };

    const handleDelete = async (careId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        const ok = await postJson(`${baseUrl}/${careId}`, 'DELETE');
        if (ok) {
            setDetailsOpen(false);
            setSelectedCare(null);
        }
    };

    const openDetails = async (care: NutritionCareListItem) => {
        setSelectedCare(care);
        setDetailsOpen(true);
        setDetailsLoading(true);

        try {
            const response = await fetch(`${baseUrl}/${care.id}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setSelectedCare(payload.data);
            }
        } catch {
            // Keep row data when detail fetch fails.
        } finally {
            setDetailsLoading(false);
        }
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <SectionShell
                id={`hospitalization-nutrition-cares-${hospitalizationId}`}
                icon="bx-food-menu"
                iconClassName="text-pink-500"
                title={t('global.nutrition_care')}
                count={data?.count}
                badgeColor="pink"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        <AccordionButton
                            onClick={openCreate}
                            permission={Boolean(data?.permissions.create && !isDischarged)}
                        >
                            {t('global.create_nutrition_care')}
                        </AccordionButton>

                        {(data?.items.length ?? 0) > 0 ? (
                            <Table embedded className="min-w-[1100px]">
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader className="w-12">{t('global.number')}</TableHeader>
                                        <TableHeader>{t('global.patient_name')}</TableHeader>
                                        <TableHeader>{t('global.nurse')}</TableHeader>
                                        <TableHeader>{t('global.observations')}</TableHeader>
                                        <TableHeader>{t('global.interventions')}</TableHeader>
                                        <TableHeader>{t('global.notes')}</TableHeader>
                                        <TableHeader>{t('global.date')}</TableHeader>
                                        <TableHeader align="right" className="w-32">
                                            {t('global.actions')}
                                        </TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data?.items.map((care, index) => (
                                        <TableRow key={care.id}>
                                            <TableCell className="text-gray-500">{index + 1}</TableCell>
                                            <TableCell>{care.patient_name ?? '—'}</TableCell>
                                            <TableCell muted>{care.nurse_name ?? '—'}</TableCell>
                                            <TableCell>
                                                <FlagBadges
                                                    labels={activeFieldLabels(
                                                        care,
                                                        OBSERVATION_FIELDS,
                                                        t
                                                    )}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <FlagBadges
                                                    labels={activeFieldLabels(
                                                        care,
                                                        INTERVENTION_FIELDS,
                                                        t
                                                    )}
                                                />
                                            </TableCell>
                                            <TableCell muted>
                                                {care.nutrition_care_full_note
                                                    ? care.nutrition_care_full_note.slice(0, 80)
                                                    : '—'}
                                            </TableCell>
                                            <TableCell muted dir="ltr">
                                                {care.recorded_at ?? '—'}
                                            </TableCell>
                                            <TableCell align="right">
                                                <SectionActionButton
                                                    icon="bx-expand"
                                                    title={t('global.view')}
                                                    onClick={() => openDetails(care)}
                                                    colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                                />
                                                {care.urls?.print && (
                                                    <SectionActionButton
                                                        icon="bx-printer"
                                                        title={t('global.print')}
                                                        href={care.urls.print}
                                                        colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                    />
                                                )}
                                                {care.permissions.edit && !isDischarged && (
                                                    <SectionActionButton
                                                        icon="bx-edit"
                                                        title={t('global.edit')}
                                                        onClick={() => openEdit(care)}
                                                        colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                    />
                                                )}
                                                {care.permissions.delete && !isDischarged && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => handleDelete(care.id)}
                                                        colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_records_found')} />
                        )}
                    </>
                )}
            </SectionShell>

            <Modal show={formOpen} onClose={closeForm} size="7xl">
                <ModalHeader>
                    {editingCareId
                        ? t('global.edit_nutrition_care')
                        : t('global.create_nutrition_care')}
                </ModalHeader>
                <form onSubmit={handleSubmit}>
                    <ModalBody className={MODAL_BODY_CLASS}>
                        {formError && (
                            <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                                {formError}
                            </div>
                        )}
                        <CareFormFields
                            form={form}
                            setForm={setForm}
                            patientName={formPatientName ?? patientName}
                            nurseName={formNurseName ?? currentNurseName}
                            idPrefix={editingCareId ? `nutrition-care-edit-${editingCareId}` : 'nutrition-care-create'}
                            t={t}
                        />
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

            <Modal
                show={detailsOpen}
                onClose={() => {
                    setDetailsOpen(false);
                    setDetailsLoading(false);
                }}
                size="7xl"
            >
                <ModalHeader>{t('global.view_nutrition_care_details')}</ModalHeader>
                <ModalBody className={`space-y-5 ${MODAL_BODY_CLASS}`}>
                    {detailsLoading ? (
                        <SectionLoadingState />
                    ) : selectedCare ? (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.patient_name')}
                                    </p>
                                    <p className="mt-1 font-medium">{selectedCare.patient_name ?? '—'}</p>
                                </div>
                                <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.nurse')}
                                    </p>
                                    <p className="mt-1 font-medium">{selectedCare.nurse_name ?? '—'}</p>
                                </div>
                                <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.created_at')}
                                    </p>
                                    <p className="mt-1 font-medium" dir="ltr">
                                        {selectedCare.recorded_at ?? '—'}
                                    </p>
                                </div>
                            </div>

                            <div className="rounded-xl border border-gray-100 p-4 dark:border-gray-700">
                                <p className="mb-3 text-sm font-semibold">{t('global.observations')}</p>
                                <FieldChecklist
                                    fields={OBSERVATION_FIELDS}
                                    item={selectedCare}
                                    t={t}
                                />
                            </div>

                            <div className="rounded-xl border border-gray-100 p-4 dark:border-gray-700">
                                <p className="mb-3 text-sm font-semibold">{t('global.interventions')}</p>
                                <FieldChecklist
                                    fields={INTERVENTION_FIELDS}
                                    item={selectedCare}
                                    t={t}
                                />
                            </div>

                            <div className="rounded-xl border border-gray-100 p-4 dark:border-gray-700">
                                <p className="text-xs font-medium uppercase text-gray-500">
                                    {t('global.nutrition_care_full_note')}
                                </p>
                                <p className="mt-2 whitespace-pre-wrap text-sm">
                                    {selectedCare.nutrition_care_full_note ?? '—'}
                                </p>
                            </div>
                        </>
                    ) : null}
                </ModalBody>
                <ModalFooter>
                    {selectedCare?.urls?.print && (
                        <Button
                            color="blue"
                            as="a"
                            href={selectedCare.urls.print}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <i className="bx bx-printer me-2" />
                            {t('global.print')}
                        </Button>
                    )}
                    {selectedCare?.permissions.edit && !isDischarged && (
                        <Button color="warning" onClick={() => openEdit(selectedCare)}>
                            {t('global.edit')}
                        </Button>
                    )}
                    {selectedCare?.permissions.delete && !isDischarged && (
                        <Button color="failure" onClick={() => handleDelete(selectedCare.id)}>
                            {t('global.delete')}
                        </Button>
                    )}
                    <Button
                        color="light"
                        onClick={() => {
                            setDetailsOpen(false);
                            setDetailsLoading(false);
                        }}
                    >
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>
        </>
    );
}
