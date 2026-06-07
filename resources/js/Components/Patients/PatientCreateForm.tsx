import { Alert, Button, Spinner } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    NamedOption,
    PatientCreateUrls,
    PatientFormData,
    PatientType,
    StorePatientResponse,
} from '../../types/patient';
import { buildAgeValue } from '../../utils/patientAge';
import AppointmentSection from './AppointmentSection';
import TokenModal from './TokenModal';
import AgeInput from './ui/AgeInput';
import FormSection from './ui/FormSection';
import { FormField, IconSelect, IconTextInput } from './ui/FormField';
import SegmentedControl from './ui/SegmentedControl';

interface PatientCreateFormProps {
    patientType: PatientType;
    formData: PatientFormData;
    urls: PatientCreateUrls;
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
    province_id: string;
    district_id: string;
    referral_name: string;
    referral_last_name: string;
    referral_father_name: string;
    referral_nid: string;
    referral_id_card: string;
    referral_phone: string;
    referral_recipient: string;
    relation_id: string;
    appointment_clinic_type: string;
    appointment_department_id: string;
    appointment_doctor_id: string;
}

function createInitialState(patientType: PatientType): FormState {
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
        province_id: '',
        district_id: '',
        referral_name: '',
        referral_last_name: '',
        referral_father_name: '',
        referral_nid: '',
        referral_id_card: '',
        referral_phone: '',
        referral_recipient: '',
        relation_id: '',
        appointment_clinic_type: '',
        appointment_department_id: '',
        appointment_doctor_id: '',
    };
}

export default function PatientCreateForm({ patientType, formData, urls }: PatientCreateFormProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [form, setForm] = useState<FormState>(() => createInitialState(patientType));
    const [districts, setDistricts] = useState<NamedOption[]>([]);
    const [loadingDistricts, setLoadingDistricts] = useState(false);
    const [submitting, setSubmitting] = useState(false);
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

    const resetForm = () => {
        setForm(createInitialState(patientType));
        setDistricts([]);
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
        }

        if (patientType === '1') {
            payload.append('job_category', form.job_category);
            payload.append('rank', form.rank);
            payload.append('referred_by', form.referred_by);
        }

        if (patientType === '2') {
            payload.append('referral_name', form.referral_name);
            payload.append('referral_last_name', form.referral_last_name);
            payload.append('referral_father_name', form.referral_father_name);
            payload.append('referral_nid', form.referral_nid);
            payload.append('referral_id_card', form.referral_id_card);
            payload.append('referral_phone', form.referral_phone);
            payload.append('referral_recipient', form.referral_recipient);
            payload.append('relation_id', form.relation_id);
        }

        if (form.appointment_department_id) {
            payload.append('appointment_department_id', form.appointment_department_id);
        }
        if (form.appointment_doctor_id) {
            payload.append('appointment_doctor_id', form.appointment_doctor_id);
        }
        if (formData.clinicType === 'both' && form.appointment_clinic_type) {
            payload.append('appointment_clinic_type', form.appointment_clinic_type);
        }

        try {
            const response = await fetch(urls.store, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload,
            });

            const result: StorePatientResponse = await response.json();

            if (!response.ok) {
                const fieldErrors: Record<string, string> = {};
                Object.entries(result.errors ?? {}).forEach(([key, messages]) => {
                    fieldErrors[key] = messages[0];
                });
                setErrors(fieldErrors);
                return;
            }

            setSuccessMessage(result.message);
            setCreatedPatient(result.patient ?? null);
            resetForm();

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

                <FormSection
                    icon="bx-user-circle"
                    title={t('global.personal_information')}
                    accent="blue"
                    isFirst
                >
                    {patientType === '0' && (
                        <FormField label={t('global.id_card')} icon="bx-id-card" error={errors.id_card}>
                            <IconTextInput
                                id="id_card"
                                icon="bx-id-card"
                                value={form.id_card}
                                onChange={(value) => updateField('id_card', value)}
                            />
                        </FormField>
                    )}

                    <FormField label={t('global.name')} icon="bx-user" required error={errors.name}>
                        <IconTextInput
                            id="name"
                            icon="bx-user"
                            required
                            value={form.name}
                            onChange={(value) => updateField('name', value)}
                        />
                    </FormField>

                    <FormField label={t('global.last_name')} icon="bx-user" error={errors.last_name}>
                        <IconTextInput
                            id="last_name"
                            icon="bx-user"
                            value={form.last_name}
                            onChange={(value) => updateField('last_name', value)}
                        />
                    </FormField>

                    <FormField label={t('global.father_name')} icon="bx-male" error={errors.father_name}>
                        <IconTextInput
                            id="father_name"
                            icon="bx-male"
                            value={form.father_name}
                            onChange={(value) => updateField('father_name', value)}
                        />
                    </FormField>

                    <FormField label={t('global.nid')} icon="bx-fingerprint" required error={errors.nid}>
                        <IconTextInput
                            id="nid"
                            icon="bx-fingerprint"
                            required
                            value={form.nid}
                            onChange={(value) => updateField('nid', value)}
                        />
                    </FormField>

                    {patientType === '2' && (
                        <FormField label={jobLabel} icon="bx-briefcase" error={errors.job}>
                            <IconTextInput
                                id="job"
                                icon="bx-briefcase"
                                value={form.job}
                                onChange={(value) => updateField('job', value)}
                            />
                        </FormField>
                    )}
                </FormSection>

                {patientType !== '2' && (
                    <FormSection
                        icon="bx-briefcase"
                        title={t('global.employment_information')}
                        accent="violet"
                    >
                        <FormField label={jobLabel} icon="bx-briefcase" error={errors.job}>
                            <IconTextInput
                                id="job"
                                icon="bx-briefcase"
                                value={form.job}
                                onChange={(value) => updateField('job', value)}
                            />
                        </FormField>

                        <FormField label={t('global.job_category')} icon="bx-category" required>
                            <SegmentedControl
                                value={form.job_category}
                                onChange={(value) => handleJobCategoryChange(value as '0' | '1')}
                                options={[
                                    { value: '0', label: t('global.military'), icon: 'bx-shield' },
                                    { value: '1', label: t('global.civilian'), icon: 'bx-buildings' },
                                ]}
                            />
                        </FormField>

                        {showMilitaryFields && (
                            <FormField
                                label={t('global.militery_type')}
                                icon="bx-shield-quarter"
                                error={errors.militery_type_id}
                            >
                                <IconSelect
                                    id="militery_type_id"
                                    icon="bx-shield-quarter"
                                    value={form.militery_type_id}
                                    onChange={(value) => updateField('militery_type_id', value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {formData.militeryTypes.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.name}
                                        </option>
                                    ))}
                                </IconSelect>
                            </FormField>
                        )}

                        {showRankField && (
                            <FormField label={rankLabel} icon="bx-medal" error={errors.rank}>
                                <IconTextInput
                                    id="rank"
                                    icon="bx-medal"
                                    value={form.rank}
                                    onChange={(value) => updateField('rank', value)}
                                />
                            </FormField>
                        )}
                    </FormSection>
                )}

                <FormSection
                    icon="bx-map-pin"
                    title={t('global.contact_information')}
                    description={t('global.location_details')}
                    accent="amber"
                >
                    <FormField label={t('global.phone')} icon="bx-phone" error={errors.phone}>
                        <IconTextInput
                            id="phone"
                            icon="bx-phone"
                            value={form.phone}
                            onChange={(value) => updateField('phone', value)}
                        />
                    </FormField>

                    <FormField label={t('global.age')} icon="bx-calendar" error={errors.age}>
                        <AgeInput
                            year={form.age_year}
                            month={form.age_month}
                            day={form.age_day}
                            onChange={updateField}
                            preview={ageValue || undefined}
                        />
                    </FormField>

                    <FormField label={t('global.gender')} icon="bx-male-female" required error={errors.gender}>
                        {patientType === '2' ? (
                            <IconSelect
                                id="gender"
                                icon="bx-male-female"
                                required
                                value={form.gender}
                                onChange={(value) => updateField('gender', value)}
                            >
                                <option value="">{t('global.select')}</option>
                                <option value="0">{t('global.male')}</option>
                                <option value="1">{t('global.female')}</option>
                            </IconSelect>
                        ) : (
                            <SegmentedControl
                                value={form.gender}
                                onChange={(value) => updateField('gender', value)}
                                options={[
                                    { value: '0', label: t('global.male'), icon: 'bx-male' },
                                    { value: '1', label: t('global.female'), icon: 'bx-female' },
                                ]}
                            />
                        )}
                    </FormField>

                    {patientType !== '2' && (
                        <FormField
                            label={t('global.referred_by')}
                            icon="bx-user-check"
                            required={patientType === '1'}
                            error={errors.referred_by}
                        >
                            <IconSelect
                                id="referred_by"
                                icon="bx-user-check"
                                required={patientType === '1'}
                                value={form.referred_by}
                                onChange={(value) => updateField('referred_by', value)}
                            >
                                <option value="">{t('global.select')}</option>
                                {formData.recipients.map((recipient) => (
                                    <option key={recipient.id} value={recipient.id}>
                                        {recipient.name}
                                    </option>
                                ))}
                            </IconSelect>
                        </FormField>
                    )}

                    <FormField label={t('global.province')} icon="bx-map" required error={errors.province_id}>
                        <IconSelect
                            id="province_id"
                            icon="bx-map"
                            required
                            value={form.province_id}
                            onChange={loadDistricts}
                        >
                            <option value="">{t('global.select')}</option>
                            {formData.provinces.map((province) => (
                                <option key={province.id} value={province.id}>
                                    {province.name_dr}
                                </option>
                            ))}
                        </IconSelect>
                    </FormField>

                    <FormField label={t('global.district')} icon="bx-map-alt" required error={errors.district_id}>
                        <IconSelect
                            id="district_id"
                            icon="bx-map-alt"
                            required
                            disabled={!form.province_id || loadingDistricts}
                            value={form.district_id}
                            onChange={(value) => updateField('district_id', value)}
                        >
                            <option value="">
                                {loadingDistricts ? `${t('global.loading')}...` : t('global.select')}
                            </option>
                            {districts.map((district) => (
                                <option key={district.id} value={district.id}>
                                    {district.name_dr}
                                </option>
                            ))}
                        </IconSelect>
                    </FormField>
                </FormSection>

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

                {patientType === '2' && (
                    <FormSection
                        icon="bx-group"
                        title={t('global.referred_person')}
                        accent="emerald"
                    >
                        <FormField label={t('global.name')} icon="bx-user" required error={errors.referral_name}>
                            <IconTextInput
                                id="referral_name"
                                icon="bx-user"
                                required
                                value={form.referral_name}
                                onChange={(value) => updateField('referral_name', value)}
                            />
                        </FormField>

                        <FormField label={t('global.last_name')} icon="bx-user" error={errors.referral_last_name}>
                            <IconTextInput
                                id="referral_last_name"
                                icon="bx-user"
                                value={form.referral_last_name}
                                onChange={(value) => updateField('referral_last_name', value)}
                            />
                        </FormField>

                        <FormField
                            label={t('global.father_name')}
                            icon="bx-male"
                            error={errors.referral_father_name}
                        >
                            <IconTextInput
                                id="referral_father_name"
                                icon="bx-male"
                                value={form.referral_father_name}
                                onChange={(value) => updateField('referral_father_name', value)}
                            />
                        </FormField>

                        <FormField label={t('global.nid')} icon="bx-fingerprint" required error={errors.referral_nid}>
                            <IconTextInput
                                id="referral_nid"
                                icon="bx-fingerprint"
                                required
                                value={form.referral_nid}
                                onChange={(value) => updateField('referral_nid', value)}
                            />
                        </FormField>

                        <FormField label={t('global.id_card')} icon="bx-id-card" error={errors.referral_id_card}>
                            <IconTextInput
                                id="referral_id_card"
                                icon="bx-id-card"
                                value={form.referral_id_card}
                                onChange={(value) => updateField('referral_id_card', value)}
                            />
                        </FormField>

                        <FormField label={t('global.phone')} icon="bx-phone" error={errors.referral_phone}>
                            <IconTextInput
                                id="referral_phone"
                                icon="bx-phone"
                                value={form.referral_phone}
                                onChange={(value) => updateField('referral_phone', value)}
                            />
                        </FormField>

                        <FormField
                            label={t('global.referred_by')}
                            icon="bx-user-check"
                            required
                            error={errors.referral_recipient}
                        >
                            <IconSelect
                                id="referral_recipient"
                                icon="bx-user-check"
                                required
                                value={form.referral_recipient}
                                onChange={(value) => updateField('referral_recipient', value)}
                            >
                                <option value="">{t('global.select')}</option>
                                {formData.recipients.map((recipient) => (
                                    <option key={recipient.id} value={recipient.id}>
                                        {recipient.name}
                                    </option>
                                ))}
                            </IconSelect>
                        </FormField>

                        <FormField label={t('global.relation')} icon="bx-link" required error={errors.relation_id}>
                            <IconSelect
                                id="relation_id"
                                icon="bx-link"
                                required
                                value={form.relation_id}
                                onChange={(value) => updateField('relation_id', value)}
                            >
                                <option value="">{t('global.select')}</option>
                                {formData.relations.map((relation) => (
                                    <option key={relation.id} value={relation.id}>
                                        {relation.name}
                                    </option>
                                ))}
                            </IconSelect>
                        </FormField>
                    </FormSection>
                )}

                <div className="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <Button type="submit" color="blue" disabled={submitting} className="min-w-[140px]">
                        {submitting ? (
                            <>
                                <Spinner size="sm" className="me-2" />
                                {t('global.creating')}...
                            </>
                        ) : (
                            <>
                                <i className="bx bx-plus-circle me-2 text-lg" />
                                {t('global.create')}
                            </>
                        )}
                    </Button>
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
