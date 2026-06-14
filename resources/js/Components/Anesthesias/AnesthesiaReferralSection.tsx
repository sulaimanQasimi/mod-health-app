import { Badge, Button, Label, Modal, ModalBody, ModalFooter, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, ReactNode, useCallback, useEffect, useMemo, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
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
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';
import {
    ANESTHESIA_APPLY_BTN_CLASS,
    anesthesiaStatusBadgeColor,
    anesthesiaStatusLabel,
} from './anesthesiaUi';

interface SelectOption {
    id: number;
    name: string;
}

interface AnesthesiaListItem {
    id: number;
    operation_type: string | null;
    patient_name: string | null;
    status: string;
    date: string | null;
    urls?: { show?: string; edit?: string };
}

interface SectionData {
    items: AnesthesiaListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
}

interface AnesthesiaReferralSectionProps {
    baseUrl: string;
    accordionId: string;
    isDischarged?: boolean;
    reloadPageOnSuccess?: boolean;
}

const MODAL_BODY_CLASS = 'max-h-[min(72vh,760px)] overflow-y-auto';

function formatValidationMessage(payload: {
    message?: string;
    errors?: Record<string, string | string[]>;
}): string {
    if (payload.errors) {
        const lines = Object.values(payload.errors).flatMap((messages) =>
            Array.isArray(messages) ? messages : [String(messages)],
        );
        if (lines.length > 0) {
            return lines.join('\n');
        }
    }

    return typeof payload.message === 'string' ? payload.message : '';
}

const EMPTY_FORM = {
    plan: '',
    other_problems: '',
    operation_type_id: '',
    anesthesia_type: '',
    operation_surgion_id: '',
    operation_assistants_id: [] as string[],
    date: '',
    time: '',
    planned_duration: '',
    position_on_bed: '',
    estimated_blood_waste: '',
};

function FormSection({
    title,
    icon,
    children,
}: {
    title: string;
    icon: string;
    children: ReactNode;
}) {
    return (
        <section className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900/40">
            <div className="flex items-center gap-2 border-b border-gray-100 bg-gradient-to-r from-violet-50 to-indigo-50 px-4 py-3 dark:border-gray-800 dark:from-violet-950/40 dark:to-indigo-950/30">
                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-300">
                    <i className={`bx ${icon} text-lg`} />
                </span>
                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">{title}</h3>
            </div>
            <div className="space-y-4 p-4">{children}</div>
        </section>
    );
}

function FieldLabel({ htmlFor, icon, children }: { htmlFor?: string; icon: string; children: ReactNode }) {
    return (
        <Label
            htmlFor={htmlFor}
            className="mb-2 flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            <i className={`bx ${icon} text-base text-violet-500`} />
            {children}
        </Label>
    );
}

function InfoTile({
    label,
    value,
    icon,
    accent = 'violet',
}: {
    label: string;
    value: string;
    icon: string;
    accent?: 'violet' | 'sky' | 'amber';
}) {
    const accentClasses = {
        violet: 'from-violet-500/10 to-indigo-500/10 border-violet-200/80 dark:border-violet-800/50',
        sky: 'from-sky-500/10 to-blue-500/10 border-sky-200/80 dark:border-sky-800/50',
        amber: 'from-amber-500/10 to-orange-500/10 border-amber-200/80 dark:border-amber-800/50',
    }[accent];

    const iconClasses = {
        violet: 'bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-300',
        sky: 'bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-300',
        amber: 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300',
    }[accent];

    return (
        <div
            className={`rounded-xl border bg-gradient-to-br px-4 py-3 ${accentClasses}`}
        >
            <div className="flex items-start gap-3">
                <span className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${iconClasses}`}>
                    <i className={`bx ${icon} text-lg`} />
                </span>
                <div className="min-w-0">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {label}
                    </p>
                    <p className="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white">
                        {value || '—'}
                    </p>
                </div>
            </div>
        </div>
    );
}

export default function AnesthesiaReferralSection({
    baseUrl,
    accordionId,
    isDischarged = false,
    reloadPageOnSuccess = false,
}: AnesthesiaReferralSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [metaLoading, setMetaLoading] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [formError, setFormError] = useState<string | null>(null);
    const [patientName, setPatientName] = useState<string | null>(null);
    const [currentRoomName, setCurrentRoomName] = useState<string | null>(null);
    const [currentBedNumber, setCurrentBedNumber] = useState<string | number | null>(null);
    const [willClearBed, setWillClearBed] = useState(false);
    const [operationTypes, setOperationTypes] = useState<SelectOption[]>([]);
    const [hospitalDoctors, setHospitalDoctors] = useState<SelectOption[]>([]);
    const [assistantSearch, setAssistantSearch] = useState('');
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
            if (!response.ok) {
                return;
            }
            const payload = await response.json();
            if (payload.success) {
                setPatientName(payload.data.patient_name ?? null);
                setCurrentRoomName(payload.data.current_room_name ?? null);
                setCurrentBedNumber(payload.data.current_bed_number ?? null);
                setWillClearBed(Boolean(payload.data.will_clear_bed));
                setOperationTypes(payload.data.operation_types ?? []);
                setHospitalDoctors(payload.data.hospital_doctors ?? []);
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const operationTypeOptions = useMemo(
        () => operationTypes.map((type) => ({ value: String(type.id), label: type.name })),
        [operationTypes],
    );

    const hospitalDoctorOptions = useMemo(
        () => hospitalDoctors.map((doctor) => ({ value: String(doctor.id), label: doctor.name })),
        [hospitalDoctors],
    );

    const anesthesiaTypeOptions = useMemo(
        () => [
            { value: 'local', label: t('global.local') },
            { value: 'spinal', label: t('global.spinal') },
            { value: 'general', label: t('global.general') },
        ],
        [t],
    );

    const filteredAssistants = useMemo(() => {
        const term = assistantSearch.trim().toLowerCase();
        if (!term) {
            return hospitalDoctorOptions;
        }
        return hospitalDoctorOptions.filter((doctor) => doctor.label.toLowerCase().includes(term));
    }, [hospitalDoctorOptions, assistantSearch]);

    const openCreate = async () => {
        setFormError(null);
        setAssistantSearch('');
        setForm(EMPTY_FORM);
        setCreateOpen(true);
        await loadMeta();
    };

    const closeCreate = () => {
        setCreateOpen(false);
        setFormError(null);
        setAssistantSearch('');
        setForm(EMPTY_FORM);
    };

    const toggleAssistant = (doctorId: string) => {
        setForm((prev) => ({
            ...prev,
            operation_assistants_id: prev.operation_assistants_id.includes(doctorId)
                ? prev.operation_assistants_id.filter((id) => id !== doctorId)
                : [...prev.operation_assistants_id, doctorId],
        }));
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        if (
            !form.plan.trim() ||
            !form.other_problems.trim() ||
            !form.operation_type_id ||
            !form.date ||
            !form.time ||
            !form.planned_duration.trim() ||
            !form.position_on_bed.trim() ||
            !form.estimated_blood_waste.trim()
        ) {
            setFormError(t('global.request_failed'));
            return;
        }

        setSubmitting(true);
        setFormError(null);

        try {
            const response = await fetch(baseUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(form),
            });
            const payload = await response.json();
            if (!response.ok || payload.success === false) {
                setFormError(formatValidationMessage(payload) || t('global.request_failed'));
                return;
            }
            closeCreate();
            await loadData();
            if (reloadPageOnSuccess) {
                router.reload({ only: ['hospitalization'] });
            }
        } finally {
            setSubmitting(false);
        }
    };

    const handleDelete = async (anesthesiaId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        setSubmitting(true);
        try {
            const response = await fetch(`${baseUrl}/${anesthesiaId}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const payload = await response.json();
            if (payload.success) {
                await loadData();
                if (reloadPageOnSuccess) {
                    router.reload({ only: ['hospitalization'] });
                }
            }
        } finally {
            setSubmitting(false);
        }
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    const shellProps = {
        id: accordionId,
        icon: 'bx-plus-medical',
        iconClassName: 'text-violet-500',
        title: t('global.refere_to_anasthesia'),
        count: data?.count,
        badgeColor: 'failure' as const,
    };

    const content = loading ? (
        <SectionLoadingState />
    ) : (
        <>
            {!isDischarged && (
                <div className="mb-4">
                    <AccordionButton onClick={openCreate} permission={data?.permissions.create}>
                        {t('global.refere_to_anasthesia')}
                    </AccordionButton>
                </div>
            )}

            {(data?.items.length ?? 0) > 0 ? (
                <div className="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <Table embedded className="min-w-[900px]">
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader className="w-12">{t('global.number')}</TableHeader>
                                <TableHeader>{t('global.operation_type')}</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                                <TableHeader>{t('global.date')}</TableHeader>
                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {data?.items.map((item, index) => (
                                <TableRow key={item.id}>
                                    <TableCell className="font-medium text-gray-500 dark:text-gray-400">
                                        {index + 1}
                                    </TableCell>
                                    <TableCell>
                                        {item.operation_type ? (
                                            <Badge color="success" className="w-fit font-normal">
                                                {item.operation_type}
                                            </Badge>
                                        ) : (
                                            '—'
                                        )}
                                    </TableCell>
                                    <TableCell className="font-medium text-gray-900 dark:text-white">
                                        {item.patient_name ?? '—'}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            color={anesthesiaStatusBadgeColor(item.status)}
                                            className="w-fit font-normal"
                                        >
                                            {anesthesiaStatusLabel(item.status, t)}
                                        </Badge>
                                    </TableCell>
                                    <TableCell muted dir="ltr">
                                        {item.date ?? '—'}
                                    </TableCell>
                                    <TableCell align="center">
                                        <div className="flex items-center justify-center gap-1">
                                            {item.urls?.show && (
                                                <SectionActionButton
                                                    icon="bx-show"
                                                    title={t('global.view')}
                                                    href={item.urls.show}
                                                    colorClass="text-violet-600 hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-900/30"
                                                />
                                            )}
                                            {item.urls?.edit && data?.permissions.edit && (
                                                <SectionActionButton
                                                    icon="bx-edit"
                                                    title={t('global.edit')}
                                                    href={item.urls.edit}
                                                    colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                />
                                            )}
                                            {data?.permissions.delete && !isDischarged && (
                                                <SectionActionButton
                                                    icon="bx-trash"
                                                    title={t('global.delete')}
                                                    onClick={() => handleDelete(item.id)}
                                                    colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                />
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            ) : (
                <SectionEmptyState message={t('global.not_referred_to_anesthesia')} />
            )}
        </>
    );

    return (
        <>
            <SectionShell {...shellProps}>{content}</SectionShell>

            <Modal show={createOpen} onClose={closeCreate} size="4xl">
                <div className="overflow-hidden rounded-lg">
                    <div className="bg-gradient-to-r from-violet-600 to-indigo-700 px-6 py-4 text-white">
                        <div className="flex items-center gap-3">
                            <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25">
                                <i className="bx bx-plus-medical text-2xl" />
                            </span>
                            <div>
                                <p className="text-lg font-semibold">{t('global.refere_to_anasthesia')}</p>
                                <p className="text-sm text-violet-100">{patientName ?? t('global.patient_name')}</p>
                            </div>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit}>
                        <ModalBody className={`space-y-5 bg-gray-50/60 p-5 dark:bg-gray-950/40 ${MODAL_BODY_CLASS}`}>
                            {metaLoading ? (
                                <SectionLoadingState />
                            ) : (
                                <>
                                    {formError && (
                                        <div className="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                                            <i className="bx bx-error-circle mt-0.5 text-lg" />
                                            <span className="whitespace-pre-line">{formError}</span>
                                        </div>
                                    )}

                                    <div
                                        className={`grid gap-3 ${willClearBed ? 'md:grid-cols-3' : 'md:grid-cols-1'}`}
                                    >
                                        <InfoTile
                                            label={t('global.patient_name')}
                                            value={patientName ?? '—'}
                                            icon="bx-user"
                                            accent="violet"
                                        />
                                        {willClearBed && (
                                            <>
                                                <InfoTile
                                                    label={t('global.current_room')}
                                                    value={currentRoomName ?? '—'}
                                                    icon="bx-building-house"
                                                    accent="sky"
                                                />
                                                <InfoTile
                                                    label={t('global.current_bed')}
                                                    value={String(currentBedNumber ?? '—')}
                                                    icon="bx-bed"
                                                    accent="amber"
                                                />
                                            </>
                                        )}
                                    </div>

                                    {willClearBed && (
                                        <div className="flex items-start gap-3 rounded-xl border border-amber-200/90 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/40 dark:from-amber-950/40 dark:to-orange-950/30 dark:text-amber-100">
                                            <i className="bx bx-info-circle mt-0.5 text-lg text-amber-600 dark:text-amber-400" />
                                            <span>{t('global.anesthesia_clears_hospitalization_bed')}</span>
                                        </div>
                                    )}

                                    <FormSection title={t('global.operation_plan')} icon="bx-clipboard">
                                        <div className="grid gap-4 lg:grid-cols-2">
                                            <div>
                                                <FieldLabel htmlFor={`${accordionId}-plan`} icon="bx-note">
                                                    {t('global.plan')}
                                                </FieldLabel>
                                                <Textarea
                                                    id={`${accordionId}-plan`}
                                                    rows={4}
                                                    required
                                                    value={form.plan}
                                                    onChange={(e) =>
                                                        setForm((prev) => ({ ...prev, plan: e.target.value }))
                                                    }
                                                    className="resize-none"
                                                />
                                            </div>
                                            <div>
                                                <FieldLabel
                                                    htmlFor={`${accordionId}-other-problems`}
                                                    icon="bx-error-circle"
                                                >
                                                    {t('global.other_problems')}
                                                </FieldLabel>
                                                <Textarea
                                                    id={`${accordionId}-other-problems`}
                                                    rows={4}
                                                    required
                                                    value={form.other_problems}
                                                    onChange={(e) =>
                                                        setForm((prev) => ({
                                                            ...prev,
                                                            other_problems: e.target.value,
                                                        }))
                                                    }
                                                    className="resize-none"
                                                />
                                            </div>
                                        </div>
                                    </FormSection>

                                    <FormSection title={t('global.operation_team')} icon="bx-group">
                                        <div className="grid gap-4 md:grid-cols-3">
                                            <div>
                                                <FieldLabel
                                                    htmlFor={`${accordionId}-operation-type`}
                                                    icon="bx-plus-medical"
                                                >
                                                    {t('global.operation_type')}
                                                </FieldLabel>
                                                <SearchableSelect
                                                    id={`${accordionId}-operation-type`}
                                                    value={form.operation_type_id}
                                                    onChange={(value) =>
                                                        setForm((prev) => ({
                                                            ...prev,
                                                            operation_type_id: value,
                                                        }))
                                                    }
                                                    options={operationTypeOptions}
                                                    placeholder={t('global.select')}
                                                    required
                                                />
                                            </div>
                                            <div>
                                                <FieldLabel
                                                    htmlFor={`${accordionId}-anesthesia-type`}
                                                    icon="bx-pulse"
                                                >
                                                    {t('global.anesthesia_type')}
                                                </FieldLabel>
                                                <SearchableSelect
                                                    id={`${accordionId}-anesthesia-type`}
                                                    value={form.anesthesia_type}
                                                    onChange={(value) =>
                                                        setForm((prev) => ({
                                                            ...prev,
                                                            anesthesia_type: value,
                                                        }))
                                                    }
                                                    options={anesthesiaTypeOptions}
                                                    placeholder={t('global.select')}
                                                />
                                            </div>
                                            <div>
                                                <FieldLabel htmlFor={`${accordionId}-surgion`} icon="bx-user-md">
                                                    {t('global.operation_surgion')}
                                                </FieldLabel>
                                                <SearchableSelect
                                                    id={`${accordionId}-surgion`}
                                                    value={form.operation_surgion_id}
                                                    onChange={(value) =>
                                                        setForm((prev) => ({
                                                            ...prev,
                                                            operation_surgion_id: value,
                                                        }))
                                                    }
                                                    options={hospitalDoctorOptions}
                                                    placeholder={t('global.select')}
                                                />
                                            </div>
                                        </div>

                                        {hospitalDoctorOptions.length > 0 && (
                                            <div className="rounded-xl border border-dashed border-violet-200 bg-violet-50/40 p-4 dark:border-violet-800/60 dark:bg-violet-950/20">
                                                <FieldLabel icon="bx-user-plus">
                                                    {t('global.operation_assistants')}
                                                </FieldLabel>
                                                <TextInput
                                                    sizing="sm"
                                                    placeholder={t('global.search')}
                                                    value={assistantSearch}
                                                    onChange={(e) => setAssistantSearch(e.target.value)}
                                                    icon={() => <i className="bx bx-search text-gray-400" />}
                                                    className="mb-3"
                                                />
                                                <div className="flex max-h-36 flex-wrap gap-2 overflow-y-auto">
                                                    {filteredAssistants.map((doctor) => {
                                                        const selected = form.operation_assistants_id.includes(
                                                            doctor.value,
                                                        );
                                                        return (
                                                            <button
                                                                key={doctor.value}
                                                                type="button"
                                                                onClick={() => toggleAssistant(doctor.value)}
                                                                className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition ${
                                                                    selected
                                                                        ? 'border-violet-600 bg-violet-600 text-white shadow-sm'
                                                                        : 'border-gray-200 bg-white text-gray-700 hover:border-violet-300 hover:bg-violet-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-violet-700 dark:hover:bg-violet-950/30'
                                                                }`}
                                                            >
                                                                {selected && <i className="bx bx-check text-sm" />}
                                                                {doctor.label}
                                                            </button>
                                                        );
                                                    })}
                                                    {filteredAssistants.length === 0 && (
                                                        <p className="text-sm text-gray-500 dark:text-gray-400">
                                                            {t('global.no_records_found')}
                                                        </p>
                                                    )}
                                                </div>
                                                {form.operation_assistants_id.length > 0 && (
                                                    <p className="mt-2 text-xs text-violet-700 dark:text-violet-300">
                                                        {form.operation_assistants_id.length}{' '}
                                                        {t('global.selected')}
                                                    </p>
                                                )}
                                            </div>
                                        )}
                                    </FormSection>

                                    <FormSection title={t('global.date')} icon="bx-calendar">
                                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                            <div>
                                                <FieldLabel icon="bx-calendar">{t('global.date')}</FieldLabel>
                                                <PersianDateInput
                                                    value={form.date}
                                                    onChange={(date) => setForm((prev) => ({ ...prev, date }))}
                                                />
                                            </div>
                                            <div>
                                                <FieldLabel htmlFor={`${accordionId}-time`} icon="bx-time">
                                                    {t('global.time')}
                                                </FieldLabel>
                                                <TextInput
                                                    id={`${accordionId}-time`}
                                                    type="time"
                                                    required
                                                    value={form.time}
                                                    onChange={(e) =>
                                                        setForm((prev) => ({ ...prev, time: e.target.value }))
                                                    }
                                                />
                                            </div>
                                            <div>
                                                <FieldLabel htmlFor={`${accordionId}-duration`} icon="bx-timer">
                                                    {t('global.planned_duration')}
                                                </FieldLabel>
                                                <TextInput
                                                    id={`${accordionId}-duration`}
                                                    required
                                                    value={form.planned_duration}
                                                    onChange={(e) =>
                                                        setForm((prev) => ({
                                                            ...prev,
                                                            planned_duration: e.target.value,
                                                        }))
                                                    }
                                                />
                                            </div>
                                            <div>
                                                <FieldLabel htmlFor={`${accordionId}-position`} icon="bx-bed">
                                                    {t('global.position_on_bed')}
                                                </FieldLabel>
                                                <TextInput
                                                    id={`${accordionId}-position`}
                                                    required
                                                    value={form.position_on_bed}
                                                    onChange={(e) =>
                                                        setForm((prev) => ({
                                                            ...prev,
                                                            position_on_bed: e.target.value,
                                                        }))
                                                    }
                                                />
                                            </div>
                                            <div className="sm:col-span-2 lg:col-span-4">
                                                <FieldLabel
                                                    htmlFor={`${accordionId}-blood-waste`}
                                                    icon="bx-droplet"
                                                >
                                                    {t('global.estimated_blood_waste')}
                                                </FieldLabel>
                                                <TextInput
                                                    id={`${accordionId}-blood-waste`}
                                                    required
                                                    value={form.estimated_blood_waste}
                                                    onChange={(e) =>
                                                        setForm((prev) => ({
                                                            ...prev,
                                                            estimated_blood_waste: e.target.value,
                                                        }))
                                                    }
                                                />
                                            </div>
                                        </div>
                                    </FormSection>
                                </>
                            )}
                        </ModalBody>
                        <ModalFooter className="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                            <Button type="button" color="light" onClick={closeCreate} disabled={submitting}>
                                {t('global.cancel')}
                            </Button>
                            <button
                                type="submit"
                                disabled={submitting || metaLoading}
                                className={ANESTHESIA_APPLY_BTN_CLASS}
                            >
                                {submitting ? <Spinner size="sm" /> : <i className="bx bx-save" />}
                                {t('global.save')}
                            </button>
                        </ModalFooter>
                    </form>
                </div>
            </Modal>
        </>
    );
}
