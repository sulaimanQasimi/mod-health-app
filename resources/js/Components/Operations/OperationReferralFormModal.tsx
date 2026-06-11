import { Button, Label, Modal, ModalBody, ModalFooter, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, ReactNode, useCallback, useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import PersianDateInput from '../ui/PersianDateInput';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import { SectionLoadingState } from '../Appointments/Sections/AppointmentSectionAccordion';
import { OPERATION_COMPLETE_BTN_CLASS } from './operationUi';
import {
    EMPTY_OPERATION_REFERRAL_FORM,
    OperationReferralFormValues,
    OperationReferralMeta,
} from './operationReferralFormTypes';

interface OperationReferralFormModalProps {
    show: boolean;
    onClose: () => void;
    onSuccess: () => Promise<void>;
    baseUrl: string;
    accordionId: string;
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
            <div className="flex items-center gap-2 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3 dark:border-gray-800 dark:from-amber-950/40 dark:to-orange-950/30">
                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300">
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
            <i className={`bx ${icon} text-base text-amber-500`} />
            {children}
        </Label>
    );
}

export default function OperationReferralFormModal({
    show,
    onClose,
    onSuccess,
    baseUrl,
    accordionId,
}: OperationReferralFormModalProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;

    const [submitting, setSubmitting] = useState(false);
    const [metaLoading, setMetaLoading] = useState(false);
    const [formError, setFormError] = useState<string | null>(null);
    const [meta, setMeta] = useState<OperationReferralMeta | null>(null);
    const [assistantSearch, setAssistantSearch] = useState('');
    const [form, setForm] = useState<OperationReferralFormValues>(EMPTY_OPERATION_REFERRAL_FORM);

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
                setMeta(payload.data);
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        if (show) {
            setForm(EMPTY_OPERATION_REFERRAL_FORM);
            setFormError(null);
            setAssistantSearch('');
            loadMeta();
        }
    }, [show, loadMeta]);

    const operationTypeOptions = useMemo(
        () => (meta?.operation_types ?? []).map((type) => ({ value: String(type.id), label: type.name })),
        [meta?.operation_types],
    );

    const hospitalDoctorOptions = useMemo(
        () => (meta?.hospital_doctors ?? []).map((doctor) => ({ value: String(doctor.id), label: doctor.name })),
        [meta?.hospital_doctors],
    );

    const filteredAssistants = useMemo(() => {
        const term = assistantSearch.trim().toLowerCase();
        if (!term) {
            return hospitalDoctorOptions;
        }
        return hospitalDoctorOptions.filter((doctor) => doctor.label.toLowerCase().includes(term));
    }, [hospitalDoctorOptions, assistantSearch]);

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
            onClose();
            await onSuccess();
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Modal show={show} onClose={() => !submitting && onClose()} size="4xl">
            <div className="overflow-hidden rounded-lg">
                <div className="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4 text-white">
                    <div className="flex items-center gap-3">
                        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25">
                            <i className="bx bx-cut text-2xl" />
                        </span>
                        <div>
                            <p className="text-lg font-semibold">{t('global.refere_to_operation')}</p>
                            <p className="text-sm text-amber-100">{meta?.patient_name ?? t('global.patient_name')}</p>
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

                                {meta?.will_clear_bed && (
                                    <>
                                        <div className="grid gap-3 md:grid-cols-3">
                                            <div className="rounded-lg border border-gray-100 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-900/50">
                                                <p className="text-xs font-medium uppercase text-gray-500">
                                                    {t('global.patient_name')}
                                                </p>
                                                <p className="mt-1 text-sm font-medium">{meta.patient_name ?? '—'}</p>
                                            </div>
                                            <div className="rounded-lg border border-gray-100 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-900/50">
                                                <p className="text-xs font-medium uppercase text-gray-500">
                                                    {t('global.current_room')}
                                                </p>
                                                <p className="mt-1 text-sm font-medium">{meta.current_room_name ?? '—'}</p>
                                            </div>
                                            <div className="rounded-lg border border-gray-100 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-900/50">
                                                <p className="text-xs font-medium uppercase text-gray-500">
                                                    {t('global.current_bed')}
                                                </p>
                                                <p className="mt-1 text-sm font-medium">{meta.current_bed_number ?? '—'}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-start gap-3 rounded-xl border border-amber-200/90 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
                                            <i className="bx bx-info-circle mt-0.5 text-lg text-amber-600 dark:text-amber-400" />
                                            <span>{t('global.anesthesia_clears_hospitalization_bed')}</span>
                                        </div>
                                    </>
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
                                                onChange={(e) => setForm((prev) => ({ ...prev, plan: e.target.value }))}
                                                className="resize-none"
                                            />
                                        </div>
                                        <div>
                                            <FieldLabel htmlFor={`${accordionId}-other-problems`} icon="bx-error-circle">
                                                {t('global.other_problems')}
                                            </FieldLabel>
                                            <Textarea
                                                id={`${accordionId}-other-problems`}
                                                rows={4}
                                                required
                                                value={form.other_problems}
                                                onChange={(e) =>
                                                    setForm((prev) => ({ ...prev, other_problems: e.target.value }))
                                                }
                                                className="resize-none"
                                            />
                                        </div>
                                    </div>
                                </FormSection>

                                <FormSection title={t('global.operation_team')} icon="bx-group">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <FieldLabel htmlFor={`${accordionId}-operation-type`} icon="bx-plus-medical">
                                                {t('global.operation_type')}
                                            </FieldLabel>
                                            <SearchableSelect
                                                id={`${accordionId}-operation-type`}
                                                value={form.operation_type_id}
                                                onChange={(value) =>
                                                    setForm((prev) => ({ ...prev, operation_type_id: value }))
                                                }
                                                options={operationTypeOptions}
                                                placeholder={t('global.select')}
                                                required
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
                                                    setForm((prev) => ({ ...prev, operation_surgion_id: value }))
                                                }
                                                options={hospitalDoctorOptions}
                                                placeholder={t('global.select')}
                                            />
                                        </div>
                                    </div>

                                    {hospitalDoctorOptions.length > 0 && (
                                        <div className="rounded-xl border border-dashed border-amber-200 bg-amber-50/40 p-4 dark:border-amber-800/60 dark:bg-amber-950/20">
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
                                                    const selected = form.operation_assistants_id.includes(doctor.value);
                                                    return (
                                                        <button
                                                            key={doctor.value}
                                                            type="button"
                                                            onClick={() => toggleAssistant(doctor.value)}
                                                            className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition ${
                                                                selected
                                                                    ? 'border-amber-600 bg-amber-600 text-white shadow-sm'
                                                                    : 'border-gray-200 bg-white text-gray-700 hover:border-amber-300 hover:bg-amber-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-amber-700 dark:hover:bg-amber-950/30'
                                                            }`}
                                                        >
                                                            {selected && <i className="bx bx-check text-sm" />}
                                                            {doctor.label}
                                                        </button>
                                                    );
                                                })}
                                            </div>
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
                                                onChange={(e) => setForm((prev) => ({ ...prev, time: e.target.value }))}
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
                                                    setForm((prev) => ({ ...prev, planned_duration: e.target.value }))
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
                                                    setForm((prev) => ({ ...prev, position_on_bed: e.target.value }))
                                                }
                                            />
                                        </div>
                                        <div className="sm:col-span-2 lg:col-span-4">
                                            <FieldLabel htmlFor={`${accordionId}-blood-waste`} icon="bx-droplet">
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
                        <Button type="button" color="light" onClick={onClose} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <button
                            type="submit"
                            disabled={submitting || metaLoading}
                            className={OPERATION_COMPLETE_BTN_CLASS}
                        >
                            {submitting ? <Spinner size="sm" /> : <i className="bx bx-save" />}
                            {t('global.refere_to_operation')}
                        </button>
                    </ModalFooter>
                </form>
            </div>
        </Modal>
    );
}
