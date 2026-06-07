import { router } from '@inertiajs/react';
import { Alert, Button, Label, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import { FormField, IconSelect } from '../Patients/ui/FormField';
import { useTranslation } from '../../hooks/useTranslation';
import {
    AppointmentEditFormData,
    AppointmentEditPermissions,
    AppointmentEditUrls,
    AppointmentFormValues,
    DoctorOption,
} from '../../types/appointment';

interface AppointmentEditFormProps {
    appointment: AppointmentFormValues;
    formData: AppointmentEditFormData;
    permissions: AppointmentEditPermissions;
    urls: AppointmentEditUrls;
}

interface FormState {
    doctor_id: string;
    clinic_type: string;
    date: string;
    time: string;
    refferal_remarks: string;
}

export default function AppointmentEditForm({
    appointment,
    formData,
    permissions,
    urls,
}: AppointmentEditFormProps) {
    const { t } = useTranslation();
    const [form, setForm] = useState<FormState>({
        doctor_id: appointment.doctor_id,
        clinic_type: appointment.clinic_type,
        date: appointment.date,
        time: appointment.time,
        refferal_remarks: appointment.refferal_remarks,
    });
    const [doctors, setDoctors] = useState<DoctorOption[]>([]);
    const [loadingDoctors, setLoadingDoctors] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    useEffect(() => {
        const departmentId = appointment.department_id;

        if (!departmentId) {
            setDoctors([]);
            return;
        }

        if (formData.clinicType === 'both' && !form.clinic_type) {
            setDoctors([]);
            return;
        }

        const params = new URLSearchParams();
        if (formData.clinicType === 'both' && form.clinic_type) {
            params.set('clinic_type', form.clinic_type);
        }

        const query = params.toString();
        const url = `${formData.doctorsByDepartment}/${departmentId}${query ? `?${query}` : ''}`;

        setLoadingDoctors(true);
        fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => response.json())
            .then((payload) => setDoctors(payload.doctors ?? []))
            .catch(() => setDoctors([]))
            .finally(() => setLoadingDoctors(false));
    }, [appointment.department_id, form.clinic_type, formData.clinicType, formData.doctorsByDepartment]);

    const updateField = (field: keyof FormState, value: string) => {
        if (field === 'clinic_type') {
            setForm((current) => ({ ...current, [field]: value, doctor_id: '' }));
            return;
        }
        setForm((current) => ({ ...current, [field]: value }));
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setSubmitting(true);
        setErrors({});

        router.post(
            urls.update,
            {
                patient_id: appointment.patient_id,
                branch_id: appointment.branch_id,
                doctor_id: form.doctor_id || null,
                date: form.date,
                time: form.time,
                refferal_remarks: form.refferal_remarks,
                ...(formData.clinicType === 'both' ? { clinic_type: form.clinic_type } : {}),
            },
            {
                preserveScroll: true,
                onError: (fieldErrors) => {
                    const mapped: Record<string, string> = {};
                    Object.entries(fieldErrors).forEach(([key, message]) => {
                        mapped[key] = Array.isArray(message) ? message[0] : String(message);
                    });
                    setErrors(mapped);
                },
                onFinish: () => setSubmitting(false),
            },
        );
    };

    const handleDelete = () => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        setDeleting(true);
        router.delete(urls.destroy, {
            onFinish: () => setDeleting(false),
        });
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            {errors.general && (
                <Alert color="failure" className="border border-red-200 dark:border-red-800">
                    {errors.general}
                </Alert>
            )}

            {appointment.processed_by && (
                <Alert color="warning" className="border border-amber-200 dark:border-amber-800">
                    <i className="bx bx-info-circle me-2 text-lg" />
                    {t('global.appointment_already_accepted')}
                </Alert>
            )}

            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-700/60 dark:bg-gray-800/40">
                    <Label className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {t('global.patient_name')}
                    </Label>
                    <p className="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">
                        {appointment.patient_name ?? '—'}
                    </p>
                    {appointment.id_card && (
                        <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {t('global.card_number')}: {appointment.id_card}
                        </p>
                    )}
                </div>

                <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-700/60 dark:bg-gray-800/40">
                    <Label className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {t('global.department')}
                    </Label>
                    <p className="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">
                        {appointment.department_name ?? '—'}
                    </p>
                </div>

                <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-700/60 dark:bg-gray-800/40">
                    <Label className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {t('global.status')}
                    </Label>
                    <p className="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">
                        {appointment.is_completed ? t('global.completed') : t('global.pending')}
                    </p>
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {formData.clinicType === 'both' && (
                    <FormField
                        label={t('global.clinic_type')}
                        icon="bx-buildings"
                        required
                        error={errors.clinic_type}
                    >
                        <IconSelect
                            id="clinic_type"
                            icon="bx-buildings"
                            required
                            value={form.clinic_type}
                            onChange={(value) => updateField('clinic_type', value)}
                        >
                            <option value="">{t('global.select')}...</option>
                            <option value="hospital">{t('global.hospital')}</option>
                            <option value="clinic">{t('global.clinic')}</option>
                        </IconSelect>
                    </FormField>
                )}

                <FormField
                    label={t('global.doctor_name')}
                    icon="bx-user-pin"
                    error={errors.doctor_id}
                >
                    <IconSelect
                        id="doctor_id"
                        icon="bx-user-pin"
                        value={form.doctor_id}
                        disabled={!appointment.can_change_doctor || loadingDoctors}
                        onChange={(value) => updateField('doctor_id', value)}
                    >
                        <option value="">
                            {loadingDoctors
                                ? `${t('global.loading')}...`
                                : t('global.select_doctor')}
                        </option>
                        {doctors.map((doctor) => (
                            <option key={doctor.id} value={doctor.id}>
                                {doctor.name}
                            </option>
                        ))}
                    </IconSelect>
                </FormField>

                <FormField label={t('global.date')} icon="bx-calendar" error={errors.date}>
                    <TextInput
                        id="date"
                        value={form.date}
                        placeholder="1400-01-01"
                        required
                        onChange={(event) => updateField('date', event.target.value)}
                    />
                </FormField>

                <FormField label={t('global.time')} icon="bx-time" error={errors.time}>
                    <TextInput
                        id="time"
                        type="time"
                        value={form.time}
                        required
                        onChange={(event) => updateField('time', event.target.value)}
                    />
                </FormField>
            </div>

            <FormField label={t('global.referral_remarks')} icon="bx-note" error={errors.refferal_remarks}>
                <Textarea
                    id="refferal_remarks"
                    rows={3}
                    value={form.refferal_remarks}
                    onChange={(event) => updateField('refferal_remarks', event.target.value)}
                />
            </FormField>

            <div className="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                <div className="flex flex-wrap gap-2">
                    <Button type="submit" color="blue" disabled={submitting || deleting}>
                        {submitting ? (
                            <>
                                <Spinner size="sm" className="me-2" />
                                {t('global.saving')}
                            </>
                        ) : (
                            <>
                                <i className="bx bx-save me-2 text-lg" />
                                {t('global.save')}
                            </>
                        )}
                    </Button>
                    <Button color="light" href={urls.index} as="a">
                        <i className="bx bx-x me-2 text-lg" />
                        {t('global.cancel')}
                    </Button>
                </div>

                <div className="flex flex-wrap gap-2">
                    <Button color="light" href={urls.show} as="a">
                        <i className="bx bx-expand me-2 text-lg" />
                        {t('global.view')}
                    </Button>
                    {permissions.delete && (
                        <Button type="button" color="failure" outline disabled={submitting || deleting} onClick={handleDelete}>
                            {deleting ? (
                                <>
                                    <Spinner size="sm" className="me-2" />
                                    {t('global.deleting')}
                                </>
                            ) : (
                                <>
                                    <i className="bx bx-trash me-2 text-lg" />
                                    {t('global.delete')}
                                </>
                            )}
                        </Button>
                    )}
                </div>
            </div>
        </form>
    );
}
