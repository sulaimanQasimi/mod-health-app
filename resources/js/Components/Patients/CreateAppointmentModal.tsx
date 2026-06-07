import { Alert, Button, Modal, ModalBody, ModalFooter, ModalHeader, Spinner } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    CreateAppointmentResponse,
    DoctorOption,
    NamedOption,
    PatientDetail,
    PatientShowAppointmentForm,
    PatientShowUrls,
} from '../../types/patient';
import TokenModal from './TokenModal';
import { FormField, IconSelect } from './ui/FormField';

interface CreateAppointmentModalProps {
    open: boolean;
    onClose: () => void;
    patient: PatientDetail;
    formData: PatientShowAppointmentForm;
    urls: Pick<PatientShowUrls, 'appointmentStore' | 'doctorsByDepartment'>;
    onCreated?: () => void;
}

interface AppointmentFormState {
    clinic_type: string;
    department_id: string;
    doctor_id: string;
}

export default function CreateAppointmentModal({
    open,
    onClose,
    patient,
    formData,
    urls,
    onCreated,
}: CreateAppointmentModalProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [form, setForm] = useState<AppointmentFormState>({
        clinic_type: '',
        department_id: '',
        doctor_id: '',
    });
    const [doctors, setDoctors] = useState<DoctorOption[]>([]);
    const [loadingDoctors, setLoadingDoctors] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [tokenModal, setTokenModal] = useState<CreateAppointmentResponse['appointment'] | null>(null);

    useEffect(() => {
        if (!open) {
            setForm({ clinic_type: '', department_id: '', doctor_id: '' });
            setDoctors([]);
            setErrors({});
        }
    }, [open]);

    useEffect(() => {
        const departmentId = form.department_id;

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
        const url = `${urls.doctorsByDepartment}/${departmentId}${query ? `?${query}` : ''}`;

        setLoadingDoctors(true);
        fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => response.json())
            .then((payload) => setDoctors(payload.doctors ?? []))
            .catch(() => setDoctors([]))
            .finally(() => setLoadingDoctors(false));
    }, [form.clinic_type, form.department_id, formData.clinicType, urls.doctorsByDepartment]);

    const updateField = (field: keyof AppointmentFormState, value: string) => {
        if (field === 'department_id' || field === 'clinic_type') {
            setForm((current) => ({ ...current, [field]: value, doctor_id: '' }));
            return;
        }
        setForm((current) => ({ ...current, [field]: value }));
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        setSubmitting(true);
        setErrors({});

        const payload = new FormData();
        payload.append('_token', csrfToken);
        payload.append('patient_id', String(patient.id));
        payload.append('branch_id', String(formData.branchId));
        payload.append('department_id', form.department_id);
        payload.append('is_completed', '0');
        if (form.doctor_id) {
            payload.append('doctor_id', form.doctor_id);
        }
        if (formData.clinicType === 'both' && form.clinic_type) {
            payload.append('clinic_type', form.clinic_type);
        }

        try {
            const response = await fetch(urls.appointmentStore, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload,
            });

            const result: CreateAppointmentResponse = await response.json();

            if (!response.ok) {
                const fieldErrors: Record<string, string> = {};
                Object.entries(result.errors ?? {}).forEach(([key, messages]) => {
                    fieldErrors[key] = messages[0];
                });
                setErrors(fieldErrors);
                return;
            }

            onClose();
            onCreated?.();

            if (result.appointment) {
                setTokenModal(result.appointment);
            }
        } catch {
            setErrors({ general: t('global.error_occurred') });
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <>
            <Modal show={open} onClose={onClose} size="lg">
                <ModalHeader>
                    <span className="flex items-center gap-2">
                        <i className="bx bx-calendar-plus text-xl text-cyan-500" />
                        {t('global.create_appointment')}
                    </span>
                </ModalHeader>
                <form onSubmit={handleSubmit}>
                    <ModalBody>
                        <div className="space-y-4">
                        {errors.general && (
                            <Alert color="failure" className="mb-4">
                                {errors.general}
                            </Alert>
                        )}

                        {formData.clinicType === 'both' && (
                            <FormField
                                label={t('global.clinic_type')}
                                icon="bx-clinic"
                                required
                                error={errors.clinic_type}
                            >
                                <IconSelect
                                    id="modal_clinic_type"
                                    icon="bx-clinic"
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
                            label={t('global.department')}
                            icon="bx-building"
                            required
                            error={errors.department_id}
                        >
                            <IconSelect
                                id="modal_department_id"
                                icon="bx-building"
                                required
                                value={form.department_id}
                                onChange={(value) => updateField('department_id', value)}
                            >
                                <option value="">{t('global.select_department')}</option>
                                {formData.departments.map((department: NamedOption) => (
                                    <option key={department.id} value={department.id}>
                                        {department.name}
                                    </option>
                                ))}
                            </IconSelect>
                        </FormField>

                        <FormField
                            label={`${t('global.doctor')} (${t('global.optional')})`}
                            icon="bx-user-voice"
                            error={errors.doctor_id}
                        >
                            <IconSelect
                                id="modal_doctor_id"
                                icon="bx-user-voice"
                                value={form.doctor_id}
                                disabled={!form.department_id || loadingDoctors}
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
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={onClose} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={submitting}>
                            {submitting ? (
                                <>
                                    <Spinner size="sm" className="me-2" />
                                    {t('global.creating')}...
                                </>
                            ) : (
                                <>
                                    <i className="bx bx-plus me-1 text-lg" />
                                    {t('global.create')}
                                </>
                            )}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            {tokenModal && (
                <TokenModal
                    open
                    onClose={() => setTokenModal(null)}
                    patientName={patient.name}
                    patientLastName={patient.last_name ?? ''}
                    department={tokenModal.department}
                    doctor={tokenModal.doctor}
                    date={tokenModal.date}
                    time={tokenModal.time}
                    tokenUrl={tokenModal.token_url}
                />
            )}
        </>
    );
}
