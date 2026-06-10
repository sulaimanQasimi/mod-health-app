import {
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Select,
    Spinner,
    TextInput,
    Textarea,
} from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
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
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

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

    const assistantOptions = useMemo(
        () => hospitalDoctors.map((doctor) => ({ value: String(doctor.id), label: doctor.name })),
        [hospitalDoctors],
    );

    const openCreate = async () => {
        setFormError(null);
        setForm(EMPTY_FORM);
        setCreateOpen(true);
        await loadMeta();
    };

    const closeCreate = () => {
        setCreateOpen(false);
        setFormError(null);
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
            if (!response.ok || !payload.success) {
                setFormError(
                    typeof payload.message === 'string'
                        ? payload.message
                        : t('global.request_failed'),
                );
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
                <AccordionButton onClick={openCreate} permission={data?.permissions.create}>
                    {t('global.refere_to_anasthesia')}
                </AccordionButton>
            )}

            {(data?.items.length ?? 0) > 0 ? (
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
                                <TableCell className="text-gray-500">{index + 1}</TableCell>
                                <TableCell>{item.operation_type ?? '—'}</TableCell>
                                <TableCell>{item.patient_name ?? '—'}</TableCell>
                                <TableCell>{item.status ?? '—'}</TableCell>
                                <TableCell dir="ltr">{item.date ?? '—'}</TableCell>
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
            ) : (
                <SectionEmptyState message={t('global.not_referred_to_anesthesia')} />
            )}
        </>
    );

    return (
        <>
            <SectionShell {...shellProps}>{content}</SectionShell>

            <Modal show={createOpen} onClose={closeCreate} size="4xl">
                <ModalHeader>{t('global.refere_to_anasthesia')}</ModalHeader>
                <form onSubmit={handleSubmit}>
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

                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                        <p className="text-xs font-medium uppercase text-gray-500">
                                            {t('global.patient_name')}
                                        </p>
                                        <p className="mt-1 text-sm font-medium">{patientName ?? '—'}</p>
                                    </div>
                                    {willClearBed && (
                                        <>
                                            <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                                <p className="text-xs font-medium uppercase text-gray-500">
                                                    {t('global.current_room')}
                                                </p>
                                                <p className="mt-1 text-sm font-medium">{currentRoomName ?? '—'}</p>
                                            </div>
                                            <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                                <p className="text-xs font-medium uppercase text-gray-500">
                                                    {t('global.current_bed')}
                                                </p>
                                                <p className="mt-1 text-sm font-medium">{currentBedNumber ?? '—'}</p>
                                            </div>
                                        </>
                                    )}
                                </div>

                                {willClearBed && (
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        {t('global.anesthesia_clears_hospitalization_bed')}
                                    </p>
                                )}

                                <div className="grid gap-4 lg:grid-cols-2">
                                    <div>
                                        <Label htmlFor={`${accordionId}-plan`}>{t('global.plan')}</Label>
                                        <Textarea
                                            id={`${accordionId}-plan`}
                                            rows={3}
                                            className="mt-2"
                                            required
                                            value={form.plan}
                                            onChange={(e) => setForm((prev) => ({ ...prev, plan: e.target.value }))}
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor={`${accordionId}-other-problems`}>
                                            {t('global.other_problems')}
                                        </Label>
                                        <Textarea
                                            id={`${accordionId}-other-problems`}
                                            rows={3}
                                            className="mt-2"
                                            required
                                            value={form.other_problems}
                                            onChange={(e) =>
                                                setForm((prev) => ({ ...prev, other_problems: e.target.value }))
                                            }
                                        />
                                    </div>
                                </div>

                                <div className="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <Label htmlFor={`${accordionId}-operation-type`}>
                                            {t('global.operation_type')}
                                        </Label>
                                        <Select
                                            id={`${accordionId}-operation-type`}
                                            className="mt-2"
                                            required
                                            value={form.operation_type_id}
                                            onChange={(e) =>
                                                setForm((prev) => ({
                                                    ...prev,
                                                    operation_type_id: e.target.value,
                                                }))
                                            }
                                        >
                                            <option value="">{t('global.select')}</option>
                                            {operationTypes.map((type) => (
                                                <option key={type.id} value={type.id}>
                                                    {type.name}
                                                </option>
                                            ))}
                                        </Select>
                                    </div>
                                    <div>
                                        <Label htmlFor={`${accordionId}-anesthesia-type`}>
                                            {t('global.anesthesia_type')}
                                        </Label>
                                        <Select
                                            id={`${accordionId}-anesthesia-type`}
                                            className="mt-2"
                                            value={form.anesthesia_type}
                                            onChange={(e) =>
                                                setForm((prev) => ({
                                                    ...prev,
                                                    anesthesia_type: e.target.value,
                                                }))
                                            }
                                        >
                                            <option value="">{t('global.select')}</option>
                                            <option value="local">{t('global.local')}</option>
                                            <option value="spinal">{t('global.spinal')}</option>
                                            <option value="general">{t('global.general')}</option>
                                        </Select>
                                    </div>
                                    <div>
                                        <Label htmlFor={`${accordionId}-surgion`}>
                                            {t('global.operation_surgion')}
                                        </Label>
                                        <Select
                                            id={`${accordionId}-surgion`}
                                            className="mt-2"
                                            value={form.operation_surgion_id}
                                            onChange={(e) =>
                                                setForm((prev) => ({
                                                    ...prev,
                                                    operation_surgion_id: e.target.value,
                                                }))
                                            }
                                        >
                                            <option value="">{t('global.select')}</option>
                                            {hospitalDoctors.map((doctor) => (
                                                <option key={doctor.id} value={doctor.id}>
                                                    {doctor.name}
                                                </option>
                                            ))}
                                        </Select>
                                    </div>
                                </div>

                                {assistantOptions.length > 0 && (
                                    <div>
                                        <Label>{t('global.operation_assistants')}</Label>
                                        <div className="mt-2 flex flex-wrap gap-2">
                                            {assistantOptions.map((doctor) => {
                                                const selected = form.operation_assistants_id.includes(
                                                    doctor.value,
                                                );
                                                return (
                                                    <button
                                                        key={doctor.value}
                                                        type="button"
                                                        onClick={() => toggleAssistant(doctor.value)}
                                                        className={`rounded-full px-3 py-1 text-xs font-medium transition ${
                                                            selected
                                                                ? 'bg-violet-600 text-white'
                                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                                                        }`}
                                                    >
                                                        {doctor.label}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}

                                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                        <Label>{t('global.date')}</Label>
                                        <div className="mt-2">
                                            <PersianDateInput
                                                value={form.date}
                                                onChange={(date) => setForm((prev) => ({ ...prev, date }))}
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <Label htmlFor={`${accordionId}-time`}>{t('global.time')}</Label>
                                        <TextInput
                                            id={`${accordionId}-time`}
                                            type="time"
                                            className="mt-2"
                                            required
                                            value={form.time}
                                            onChange={(e) =>
                                                setForm((prev) => ({ ...prev, time: e.target.value }))
                                            }
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor={`${accordionId}-duration`}>
                                            {t('global.planned_duration')}
                                        </Label>
                                        <TextInput
                                            id={`${accordionId}-duration`}
                                            className="mt-2"
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
                                        <Label htmlFor={`${accordionId}-position`}>
                                            {t('global.position_on_bed')}
                                        </Label>
                                        <TextInput
                                            id={`${accordionId}-position`}
                                            className="mt-2"
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
                                    <div className="md:col-span-2 lg:col-span-4">
                                        <Label htmlFor={`${accordionId}-blood-waste`}>
                                            {t('global.estimated_blood_waste')}
                                        </Label>
                                        <TextInput
                                            id={`${accordionId}-blood-waste`}
                                            className="mt-2"
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
                            </>
                        )}
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={closeCreate}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="purple" disabled={submitting || metaLoading}>
                            {submitting && <Spinner size="sm" className="me-2" />}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </>
    );
}
