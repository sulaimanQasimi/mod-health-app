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
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

interface HospitalizationNutritionCareSectionProps {
    hospitalizationId: number;
    isDischarged?: boolean;
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

function CheckboxGroup({
    title,
    fields,
    form,
    setForm,
    t,
}: {
    title: string;
    fields: readonly CareField[];
    form: CareFormState;
    setForm: React.Dispatch<React.SetStateAction<CareFormState>>;
    t: (key: string) => string;
}) {
    return (
        <div>
            <p className="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{title}</p>
            <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                {fields.map((field) => (
                    <label
                        key={field}
                        className="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2 text-sm dark:border-gray-700"
                    >
                        <Checkbox
                            checked={Boolean(form[field])}
                            onChange={(e) =>
                                setForm((prev) => ({ ...prev, [field]: e.target.checked }))
                            }
                        />
                        <span>{t(`global.${field}`)}</span>
                    </label>
                ))}
            </div>
        </div>
    );
}

function CareFormFields({
    form,
    setForm,
    patientName,
    currentNurseName,
    t,
}: {
    form: CareFormState;
    setForm: React.Dispatch<React.SetStateAction<CareFormState>>;
    patientName: string | null;
    currentNurseName: string | null;
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
                {currentNurseName && (
                    <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                        <p className="text-xs font-medium uppercase text-gray-500">{t('global.nurse')}</p>
                        <p className="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                            {currentNurseName}
                        </p>
                    </div>
                )}
            </div>

            <CheckboxGroup
                title={t('global.observations')}
                fields={OBSERVATION_FIELDS}
                form={form}
                setForm={setForm}
                t={t}
            />

            <CheckboxGroup
                title={t('global.interventions')}
                fields={INTERVENTION_FIELDS}
                form={form}
                setForm={setForm}
                t={t}
            />

            <div>
                <Label htmlFor="nutrition-care-note">{t('global.nutrition_care_full_note')}</Label>
                <Textarea
                    id="nutrition-care-note"
                    rows={5}
                    maxLength={5000}
                    className="mt-2"
                    value={form.nutrition_care_full_note}
                    onChange={(e) =>
                        setForm((prev) => ({ ...prev, nutrition_care_full_note: e.target.value }))
                    }
                />
            </div>
        </div>
    );
}

export default function HospitalizationNutritionCareSection({
    hospitalizationId,
    isDischarged = false,
}: HospitalizationNutritionCareSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/hospitalizations/${hospitalizationId}/nutrition-cares`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [patientName, setPatientName] = useState<string | null>(null);
    const [currentNurseName, setCurrentNurseName] = useState<string | null>(null);
    const [formOpen, setFormOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [editingCareId, setEditingCareId] = useState<number | null>(null);
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
                return;
            }
            const payload = await response.json();
            if (payload.success) {
                setPatientName(payload.data.patient_name ?? null);
                setCurrentNurseName(payload.data.current_nurse?.name ?? null);
            }
        } catch {
            // Meta is optional for read-only users.
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
        await loadMeta();
        setEditingCareId(null);
        setForm(emptyForm());
        setFormOpen(true);
    };

    const openEdit = (care: NutritionCareListItem) => {
        setEditingCareId(care.id);
        setForm(careToForm(care));
        setDetailsOpen(false);
        setFormOpen(true);
        void loadMeta();
    };

    const closeForm = () => {
        setFormOpen(false);
        setEditingCareId(null);
        setForm(emptyForm());
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
                        {data?.permissions.create && !isDischarged && (
                            <div className="mb-4 flex justify-end">
                                <Button size="sm" color="success" onClick={openCreate}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.create_nutrition_care')}
                                </Button>
                            </div>
                        )}

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
                <form onSubmit={handleSubmit}>
                    <ModalHeader>
                        {editingCareId
                            ? t('global.edit_nutrition_care')
                            : t('global.create_nutrition_care')}
                    </ModalHeader>
                    <ModalBody>
                        <CareFormFields
                            form={form}
                            setForm={setForm}
                            patientName={patientName}
                            currentNurseName={currentNurseName}
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

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="7xl">
                <ModalHeader>{t('global.nutrition_care')}</ModalHeader>
                <ModalBody className="space-y-4">
                    {selectedCare && (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.patient_name')}
                                    </p>
                                    <p className="mt-1">{selectedCare.patient_name ?? '—'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.nurse')}
                                    </p>
                                    <p className="mt-1">{selectedCare.nurse_name ?? '—'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.date')}
                                    </p>
                                    <p className="mt-1" dir="ltr">
                                        {selectedCare.recorded_at ?? '—'}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <p className="mb-2 text-sm font-semibold">{t('global.observations')}</p>
                                <FlagBadges
                                    labels={activeFieldLabels(selectedCare, OBSERVATION_FIELDS, t)}
                                />
                            </div>

                            <div>
                                <p className="mb-2 text-sm font-semibold">{t('global.interventions')}</p>
                                <FlagBadges
                                    labels={activeFieldLabels(selectedCare, INTERVENTION_FIELDS, t)}
                                />
                            </div>

                            <div>
                                <p className="text-xs font-medium uppercase text-gray-500">
                                    {t('global.nutrition_care_full_note')}
                                </p>
                                <p className="mt-1 whitespace-pre-wrap">
                                    {selectedCare.nutrition_care_full_note ?? '—'}
                                </p>
                            </div>
                        </>
                    )}
                </ModalBody>
                <ModalFooter>
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
                    <Button color="light" onClick={() => setDetailsOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>
        </>
    );
}
