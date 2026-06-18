import { Alert, Button, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    NamedOption,
    PatientFormData,
    PatientFormMode,
    PatientFormPermissions,
    PatientFormUrls,
    PatientFormValues,
    PatientType,
    StorePatientResponse,
    UpdatePatientResponse,
} from '../../types/patient';
import { buildAgeValue } from '../../utils/patientAge';
import AppointmentSection from './AppointmentSection';
import TokenModal from './TokenModal';
import AgeInput from './ui/AgeInput';
import { FormField, GridDivider } from './ui/FormField';
import SearchableSelect from '../ui/SearchableSelect';

const FORM_GRID_CLASS = 'grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 lg:grid-cols-4';

interface PatientCreateFormProps {
    mode?: PatientFormMode;
    patientType: PatientType;
    formData: PatientFormData;
    urls: PatientFormUrls;
    patient?: PatientFormValues;
    permissions?: PatientFormPermissions;
    backHref?: string;
}

interface FormState {
    id_card: string;
    name: string;
    last_name: string;
    father_name: string;
    nid: string;
    job: string;
    job_category: '0' | '1';
    militery_type_id: string;
    rank: string;
    phone: string;
    age_year: string;
    age_month: string;
    age_day: string;
    gender: string;
    referred_by: string;
    recipient_part_id: string;
    province_id: string;
    district_id: string;
    referral_name: string;
    referral_last_name: string;
    referral_father_name: string;
    referral_nid: string;
    referral_id_card: string;
    referral_phone: string;
    referral_recipient: string;
    referral_recipient_part_id: string;
    relation_id: string;
    appointment_clinic_type: string;
    appointment_department_id: string;
    appointment_doctor_id: string;
}

function createInitialState(patientType: PatientType, patient?: PatientFormValues): FormState {
    if (patient) {
        return {
            id_card: patient.id_card,
            name: patient.name,
            last_name: patient.last_name,
            father_name: patient.father_name,
            nid: patient.nid,
            job: patient.job,
            job_category: patient.job_category,
            militery_type_id: patient.militery_type_id,
            rank: patient.rank,
            phone: patient.phone,
            age_year: patient.age_year,
            age_month: patient.age_month,
            age_day: patient.age_day,
            gender: patient.gender || (patientType === '2' ? '' : '0'),
            referred_by: patient.referred_by,
            recipient_part_id: patient.recipient_part_id,
            province_id: patient.province_id,
            district_id: patient.district_id,
            referral_name: patient.referral_name,
            referral_last_name: patient.referral_last_name,
            referral_father_name: patient.referral_father_name,
            referral_nid: patient.referral_nid,
            referral_id_card: patient.referral_id_card,
            referral_phone: patient.referral_phone,
            referral_recipient: patient.referral_recipient,
            referral_recipient_part_id: patient.referral_recipient_part_id,
            relation_id: patient.relation_id,
            appointment_clinic_type: '',
            appointment_department_id: '',
            appointment_doctor_id: '',
        };
    }

    return {
        id_card: '',
        name: '',
        last_name: '',
        father_name: '',
        nid: '',
        job: '',
        job_category: '0',
        militery_type_id: '',
        rank: '',
        phone: '',
        age_year: '',
        age_month: '',
        age_day: '',
        gender: patientType === '2' ? '' : '0',
        referred_by: '',
        recipient_part_id: '',
        province_id: '',
        district_id: '',
        referral_name: '',
        referral_last_name: '',
        referral_father_name: '',
        referral_nid: '',
        referral_id_card: '',
        referral_phone: '',
        referral_recipient: '',
        referral_recipient_part_id: '',
        relation_id: '',
        appointment_clinic_type: '',
        appointment_department_id: '',
        appointment_doctor_id: '',
    };
}

export default function PatientCreateForm({
    mode = 'create',
    patientType,
    formData,
    urls,
    patient,
    permissions,
    backHref,
}: PatientCreateFormProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const isEdit = mode === 'edit';
    const [form, setForm] = useState<FormState>(() => createInitialState(patientType, patient));
    const [districts, setDistricts] = useState<NamedOption[]>(formData.districts ?? []);
    const [recipientParts, setRecipientParts] = useState<NamedOption[]>(formData.recipientParts ?? []);
    const [referralRecipientParts, setReferralRecipientParts] = useState<NamedOption[]>(
        formData.referralRecipientParts ?? [],
    );
    const [loadingDistricts, setLoadingDistricts] = useState(false);
    const [loadingRecipientParts, setLoadingRecipientParts] = useState(false);
    const [loadingReferralRecipientParts, setLoadingReferralRecipientParts] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [successMessage, setSuccessMessage] = useState<string | null>(null);
    const [tokenModal, setTokenModal] = useState<StorePatientResponse['appointment'] | null>(null);
    const [createdPatient, setCreatedPatient] = useState<StorePatientResponse['patient'] | null>(null);

    const ageValue = useMemo(
        () => buildAgeValue(form.age_year, form.age_month, form.age_day),
        [form.age_day, form.age_month, form.age_year],
    );

    const showMilitaryFields = patientType !== '2' && form.job_category === '0';
    const showRankField = patientType === '1' || (patientType === '0' && form.job_category === '1');
    const rankLabel = patientType === '1' && form.job_category === '1' ? t('global.bast') : t('global.rank');
    const jobLabel = patientType === '2' ? t('global.job2') : t('global.job');

    const updateField = (field: keyof FormState | string, value: string) => {
        setForm((current) => ({
            ...current,
            [field]: value,
        }));
    };

    const updateAppointmentField = (field: string, value: string) => {
        if (field === 'appointment_department_id' || field === 'appointment_clinic_type') {
            setForm((current) => ({
                ...current,
                [field]: value,
                appointment_doctor_id: '',
            }));
            return;
        }

        updateField(field, value);
    };

    const loadDistricts = async (provinceId: string) => {
        updateField('province_id', provinceId);
        updateField('district_id', '');

        if (!provinceId) {
            setDistricts([]);
            return;
        }

        setLoadingDistricts(true);
        try {
            const response = await fetch(`${urls.districts}/${provinceId}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json();
            setDistricts(payload.districts ?? []);
        } catch {
            setDistricts([]);
        } finally {
            setLoadingDistricts(false);
        }
    };

    const loadRecipientParts = async (recipientId: string, field: 'referred_by' | 'referral_recipient') => {
        if (field === 'referred_by') {
            updateField('referred_by', recipientId);
            updateField('recipient_part_id', '');
        } else {
            updateField('referral_recipient', recipientId);
            updateField('referral_recipient_part_id', '');
        }

        if (!recipientId) {
            if (field === 'referred_by') {
                setRecipientParts([]);
            } else {
                setReferralRecipientParts([]);
            }
            return;
        }

        const setLoading = field === 'referred_by' ? setLoadingRecipientParts : setLoadingReferralRecipientParts;
        const setParts = field === 'referred_by' ? setRecipientParts : setReferralRecipientParts;

        setLoading(true);
        try {
            const response = await fetch(`${urls.recipientParts}/${recipientId}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json();
            setParts(payload.recipientParts ?? []);
        } catch {
            setParts([]);
        } finally {
            setLoading(false);
        }
    };

    const resetForm = () => {
        setForm(createInitialState(patientType));
        setDistricts([]);
        setRecipientParts([]);
        setReferralRecipientParts([]);
        setErrors({});
    };

    const handleJobCategoryChange = (value: '0' | '1') => {
        setForm((current) => ({
            ...current,
            job_category: value,
            militery_type_id: value === '1' ? '' : current.militery_type_id,
            rank: value === '0' ? '' : current.rank,
        }));
    };

    const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setSubmitting(true);
        setErrors({});
        setSuccessMessage(null);

        const payload = new FormData();
        payload.append('_token', csrfToken);
        payload.append('type', patientType);
        payload.append('branch_id', String(formData.branchId));
        payload.append('registration_date', formData.registrationDate);
        payload.append('name', form.name);
        payload.append('last_name', form.last_name);
        payload.append('father_name', form.father_name);
        payload.append('nid', form.nid);
        payload.append('job', form.job);
        payload.append('phone', form.phone);
        payload.append('gender', patientType === '2' ? form.gender : form.gender || '0');
        payload.append('province_id', form.province_id);
        payload.append('district_id', form.district_id);
        payload.append('age', ageValue);
        payload.append('age_year', form.age_year);
        payload.append('age_month', form.age_month);
        payload.append('age_day', form.age_day);

        if (patientType === '0') {
            payload.append('id_card', form.id_card);
            payload.append('job_category', form.job_category);
            if (form.job_category === '0') {
                payload.append('militery_type_id', form.militery_type_id);
            } else {
                payload.append('rank', form.rank);
            }
            if (form.referred_by) {
                payload.append('referred_by', form.referred_by);
            }
            if (form.recipient_part_id) {
                payload.append('recipient_part_id', form.recipient_part_id);
            }
        }

        if (patientType === '1') {
            payload.append('job_category', form.job_category);
            payload.append('rank', form.rank);
            payload.append('referred_by', form.referred_by);
            if (form.recipient_part_id) {
                payload.append('recipient_part_id', form.recipient_part_id);
            }
        }

        if (patientType === '2') {
            payload.append('referral_name', form.referral_name);
            payload.append('referral_last_name', form.referral_last_name);
            payload.append('referral_father_name', form.referral_father_name);
            payload.append('referral_nid', form.referral_nid);
            payload.append('referral_id_card', form.referral_id_card);
            payload.append('referral_phone', form.referral_phone);
            payload.append('referral_recipient', form.referral_recipient);
            if (form.referral_recipient_part_id) {
                payload.append('referral_recipient_part_id', form.referral_recipient_part_id);
            }
            payload.append('relation_id', form.relation_id);
        }

        if (!isEdit) {
            if (form.appointment_department_id) {
                payload.append('appointment_department_id', form.appointment_department_id);
            }
            if (form.appointment_doctor_id) {
                payload.append('appointment_doctor_id', form.appointment_doctor_id);
            }
            if (formData.clinicType === 'both' && form.appointment_clinic_type) {
                payload.append('appointment_clinic_type', form.appointment_clinic_type);
            }
        }

        const submitUrl = isEdit ? urls.update : urls.store;

        if (!submitUrl) {
            setErrors({ general: t('global.error_occurred') });
            setSubmitting(false);
            return;
        }

        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload,
            });

            const result: StorePatientResponse | UpdatePatientResponse = await response.json();

            if (!response.ok) {
                const fieldErrors: Record<string, string> = {};
                Object.entries(result.errors ?? {}).forEach(([key, messages]) => {
                    fieldErrors[key] = messages[0];
                });
                setErrors(fieldErrors);
                return;
            }

            if (isEdit) {
                router.visit(urls.show ?? urls.back);
                return;
            }

            setSuccessMessage(result.message);
            setCreatedPatient(result.patient ?? null);
            resetForm();

            if ('appointment' in result && result.appointment) {
                setTokenModal(result.appointment);
            }
        } catch {
            setErrors({ general: t('global.error_occurred') });
        } finally {
            setSubmitting(false);
        }
    };

    const handleDelete = () => {
        if (!urls.destroy || !window.confirm(t('global.confirm_delete'))) {
            return;
        }

        setDeleting(true);
        router.delete(urls.destroy, {
            onFinish: () => setDeleting(false),
        });
    };

    return (
        <>
            <form onSubmit={handleSubmit} className="space-y-0">
                {successMessage && (
                    <Alert color="success" className="mb-4 border border-green-200 dark:border-green-800">
                        <i className="bx bx-check-circle me-2 text-lg" />
                        {successMessage}
                    </Alert>
                )}
                {errors.general && (
                    <Alert color="failure" className="mb-4 border border-red-200 dark:border-red-800">
                        <i className="bx bx-error-circle me-2 text-lg" />
                        {errors.general}
                    </Alert>
                )}

                <div className={FORM_GRID_CLASS}>
                    {patientType === '0' && (
                        <FormField label={t('global.id_card')} error={errors.id_card} compact>
                            <TextInput
                                id="id_card"
                                sizing="sm"
                                value={form.id_card}
                                onChange={(e) => updateField('id_card', e.target.value)}
                            />
                        </FormField>
                    )}

                    <FormField label={t('global.name')} required error={errors.name} compact>
                        <TextInput
                            id="name"
                            sizing="sm"
                            required
                            value={form.name}
                            onChange={(e) => updateField('name', e.target.value)}
                        />
                    </FormField>

                    <FormField label={t('global.last_name')} error={errors.last_name} compact>
                        <TextInput
                            id="last_name"
                            sizing="sm"
                            value={form.last_name}
                            onChange={(e) => updateField('last_name', e.target.value)}
                        />
                    </FormField>

                    <FormField label={t('global.father_name')} error={errors.father_name} compact>
                        <TextInput
                            id="father_name"
                            sizing="sm"
                            value={form.father_name}
                            onChange={(e) => updateField('father_name', e.target.value)}
                        />
                    </FormField>

                    <FormField label={t('global.nid')} required error={errors.nid} compact>
                        <TextInput
                            id="nid"
                            sizing="sm"
                            required
                            value={form.nid}
                            onChange={(e) => updateField('nid', e.target.value)}
                        />
                    </FormField>

                    <FormField label={jobLabel} error={errors.job} compact>
                        <TextInput
                            id="job"
                            sizing="sm"
                            value={form.job}
                            onChange={(e) => updateField('job', e.target.value)}
                        />
                    </FormField>

                    {patientType !== '2' && (
                        <>
                            <FormField label={t('global.job_category')} required error={errors.job_category} compact>
                                <SearchableSelect
                                    id="job_category"
                                    compact
                                    value={form.job_category}
                                    onChange={(value) => handleJobCategoryChange(value as '0' | '1')}
                                >
                                    <option value="0">{t('global.military')}</option>
                                    <option value="1">{t('global.civilian')}</option>
                                </SearchableSelect>
                            </FormField>

                            {showMilitaryFields && (
                                <FormField label={t('global.militery_type')} error={errors.militery_type_id} compact>
                                    <SearchableSelect
                                        id="militery_type_id"
                                        compact
                                        value={form.militery_type_id}
                                        onChange={(value) => updateField('militery_type_id', value)}
                                        placeholder={t('global.select')}
                                    >
                                        <option value="">{t('global.select')}</option>
                                        {formData.militeryTypes.map((type) => (
                                            <option key={type.id} value={type.id}>
                                                {type.name}
                                            </option>
                                        ))}
                                    </SearchableSelect>
                                </FormField>
                            )}

                            {showRankField && (
                                <FormField label={rankLabel} error={errors.rank} compact>
                                    <TextInput
                                        id="rank"
                                        sizing="sm"
                                        value={form.rank}
                                        onChange={(e) => updateField('rank', e.target.value)}
                                    />
                                </FormField>
                            )}
                        </>
                    )}

                    <FormField label={t('global.phone')} error={errors.phone} compact>
                        <TextInput
                            id="phone"
                            sizing="sm"
                            value={form.phone}
                            onChange={(e) => updateField('phone', e.target.value)}
                        />
                    </FormField>

                    <FormField label={t('global.age')} error={errors.age} compact>
                        <AgeInput
                            year={form.age_year}
                            month={form.age_month}
                            day={form.age_day}
                            onChange={updateField}
                            preview={ageValue || undefined}
                            compact
                        />
                    </FormField>

                    <FormField label={t('global.gender')} required error={errors.gender} compact>
                        <SearchableSelect
                            id="gender"
                            compact
                            required
                            value={form.gender}
                            onChange={(value) => updateField('gender', value)}
                            placeholder={t('global.select')}
                        >
                            {patientType === '2' && <option value="">{t('global.select')}</option>}
                            <option value="0">{t('global.male')}</option>
                            <option value="1">{t('global.female')}</option>
                        </SearchableSelect>
                    </FormField>

                    {patientType !== '2' && (
                        <>
                            <FormField
                                label={t('global.recipient')}
                                required={patientType === '1'}
                                error={errors.referred_by}
                                compact
                            >
                                <SearchableSelect
                                    id="referred_by"
                                    compact
                                    required={patientType === '1'}
                                    value={form.referred_by}
                                    onChange={(value) => loadRecipientParts(value, 'referred_by')}
                                    placeholder={t('global.select')}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {formData.recipients.map((recipient) => (
                                        <option key={recipient.id} value={recipient.id}>
                                            {recipient.name}
                                        </option>
                                    ))}
                                </SearchableSelect>
                            </FormField>
                            <FormField
                                label={t('global.recipient_parts')}
                                error={errors.recipient_part_id}
                                compact
                            >
                                <SearchableSelect
                                    id="recipient_part_id"
                                    compact
                                    disabled={!form.referred_by || loadingRecipientParts}
                                    value={form.recipient_part_id}
                                    onChange={(value) => updateField('recipient_part_id', value)}
                                    placeholder={
                                        loadingRecipientParts ? `${t('global.loading')}...` : t('global.select')
                                    }
                                >
                                    <option value="">
                                        {loadingRecipientParts ? `${t('global.loading')}...` : t('global.select')}
                                    </option>
                                    {recipientParts.map((part) => (
                                        <option key={part.id} value={part.id}>
                                            {part.name} ({part.code})
                                        </option>
                                    ))}
                                </SearchableSelect>
                            </FormField>
                        </>
                    )}

                    <FormField label={t('global.province')} required error={errors.province_id} compact>
                        <SearchableSelect
                            id="province_id"
                            compact
                            required
                            value={form.province_id}
                            onChange={loadDistricts}
                            placeholder={t('global.select')}
                        >
                            <option value="">{t('global.select')}</option>
                            {formData.provinces.map((province) => (
                                <option key={province.id} value={province.id}>
                                    {province.name_dr}
                                </option>
                            ))}
                        </SearchableSelect>
                    </FormField>

                    <FormField label={t('global.district')} required error={errors.district_id} compact>
                        <SearchableSelect
                            id="district_id"
                            compact
                            required
                            disabled={!form.province_id || loadingDistricts}
                            value={form.district_id}
                            onChange={(value) => updateField('district_id', value)}
                            placeholder={loadingDistricts ? `${t('global.loading')}...` : t('global.select')}
                        >
                            <option value="">
                                {loadingDistricts ? `${t('global.loading')}...` : t('global.select')}
                            </option>
                            {districts.map((district) => (
                                <option key={district.id} value={district.id}>
                                    {district.name_dr}
                                </option>
                            ))}
                        </SearchableSelect>
                    </FormField>

                    <FormField label={t('global.registration_date')} compact>
                        <TextInput
                            id="registration_date"
                            sizing="sm"
                            readOnly
                            value={formData.registrationDate}
                        />
                    </FormField>

                    {!isEdit && (
                        <AppointmentSection
                            clinicType={formData.clinicType}
                            departments={formData.departments}
                            urls={urls}
                            values={{
                                appointment_clinic_type: form.appointment_clinic_type,
                                appointment_department_id: form.appointment_department_id,
                                appointment_doctor_id: form.appointment_doctor_id,
                            }}
                            onChange={updateAppointmentField}
                            errors={errors}
                        />
                    )}

                    {patientType === '2' && (
                        <>
                            <GridDivider title={t('global.referred_person')} variant="primary" />
                            <FormField label={t('global.name')} required error={errors.referral_name} compact>
                                <TextInput
                                    id="referral_name"
                                    sizing="sm"
                                    required
                                    value={form.referral_name}
                                    onChange={(e) => updateField('referral_name', e.target.value)}
                                />
                            </FormField>
                            <FormField label={t('global.last_name')} error={errors.referral_last_name} compact>
                                <TextInput
                                    id="referral_last_name"
                                    sizing="sm"
                                    value={form.referral_last_name}
                                    onChange={(e) => updateField('referral_last_name', e.target.value)}
                                />
                            </FormField>
                            <FormField label={t('global.father_name')} error={errors.referral_father_name} compact>
                                <TextInput
                                    id="referral_father_name"
                                    sizing="sm"
                                    value={form.referral_father_name}
                                    onChange={(e) => updateField('referral_father_name', e.target.value)}
                                />
                            </FormField>
                            <FormField label={t('global.nid')} required error={errors.referral_nid} compact>
                                <TextInput
                                    id="referral_nid"
                                    sizing="sm"
                                    required
                                    value={form.referral_nid}
                                    onChange={(e) => updateField('referral_nid', e.target.value)}
                                />
                            </FormField>
                            <FormField label={t('global.id_card')} error={errors.referral_id_card} compact>
                                <TextInput
                                    id="referral_id_card"
                                    sizing="sm"
                                    value={form.referral_id_card}
                                    onChange={(e) => updateField('referral_id_card', e.target.value)}
                                />
                            </FormField>
                            <FormField label={t('global.phone')} error={errors.referral_phone} compact>
                                <TextInput
                                    id="referral_phone"
                                    sizing="sm"
                                    value={form.referral_phone}
                                    onChange={(e) => updateField('referral_phone', e.target.value)}
                                />
                            </FormField>
                            <FormField label={t('global.recipient')} required error={errors.referral_recipient} compact>
                                <SearchableSelect
                                    id="referral_recipient"
                                    compact
                                    required
                                    value={form.referral_recipient}
                                    onChange={(value) => loadRecipientParts(value, 'referral_recipient')}
                                    placeholder={t('global.select')}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {formData.recipients.map((recipient) => (
                                        <option key={recipient.id} value={recipient.id}>
                                            {recipient.name}
                                        </option>
                                    ))}
                                </SearchableSelect>
                            </FormField>
                            <FormField
                                label={t('global.recipient_parts')}
                                error={errors.referral_recipient_part_id}
                                compact
                            >
                                <SearchableSelect
                                    id="referral_recipient_part_id"
                                    compact
                                    disabled={!form.referral_recipient || loadingReferralRecipientParts}
                                    value={form.referral_recipient_part_id}
                                    onChange={(value) => updateField('referral_recipient_part_id', value)}
                                    placeholder={
                                        loadingReferralRecipientParts
                                            ? `${t('global.loading')}...`
                                            : t('global.select')
                                    }
                                >
                                    <option value="">
                                        {loadingReferralRecipientParts
                                            ? `${t('global.loading')}...`
                                            : t('global.select')}
                                    </option>
                                    {referralRecipientParts.map((part) => (
                                        <option key={part.id} value={part.id}>
                                            {part.name} ({part.code})
                                        </option>
                                    ))}
                                </SearchableSelect>
                            </FormField>
                            <FormField label={t('global.relation')} required error={errors.relation_id} compact>
                                <SearchableSelect
                                    id="relation_id"
                                    compact
                                    required
                                    value={form.relation_id}
                                    onChange={(value) => updateField('relation_id', value)}
                                    placeholder={t('global.select')}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {formData.relations.map((relation) => (
                                        <option key={relation.id} value={relation.id}>
                                            {relation.name}
                                        </option>
                                    ))}
                                </SearchableSelect>
                            </FormField>
                        </>
                    )}
                </div>

                <div className="mt-6 flex flex-wrap items-center gap-2">
                    <Button type="submit" color="blue" disabled={submitting || deleting}>
                        {submitting ? (
                            <>
                                <Spinner size="sm" className="me-2" />
                                {isEdit ? `${t('global.updating')}...` : `${t('global.creating')}...`}
                            </>
                        ) : (
                            isEdit ? t('global.update') : t('global.create')
                        )}
                    </Button>
                    {backHref && (
                        <Link href={backHref}>
                            <Button type="button" color="gray">
                                {t('global.back')}
                            </Button>
                        </Link>
                    )}
                    {isEdit && permissions?.delete && (
                        <Button
                            type="button"
                            color="failure"
                            outline
                            disabled={deleting || submitting}
                            onClick={handleDelete}
                            className="ms-auto"
                        >
                            {deleting ? (
                                <>
                                    <Spinner size="sm" className="me-2" />
                                    {t('global.deleting')}...
                                </>
                            ) : (
                                t('global.delete')
                            )}
                        </Button>
                    )}
                </div>
            </form>

            {tokenModal && createdPatient && (
                <TokenModal
                    open
                    onClose={() => setTokenModal(null)}
                    patientName={createdPatient.name}
                    patientLastName={createdPatient.last_name ?? ''}
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
