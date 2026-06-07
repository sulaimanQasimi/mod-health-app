import { Alert, Button, Label, Select, TextInput } from 'flowbite-react';
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
            <form onSubmit={handleSubmit} className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                {successMessage && (
                    <div className="col-span-full">
                        <Alert color="success">{successMessage}</Alert>
                    </div>
                )}
                {errors.general && (
                    <div className="col-span-full">
                        <Alert color="failure">{errors.general}</Alert>
                    </div>
                )}

                {patientType === '0' && (
                    <div>
                        <Label htmlFor="id_card">{t('global.id_card')}</Label>
                        <TextInput
                            id="id_card"
                            value={form.id_card}
                            onChange={(event) => updateField('id_card', event.target.value)}
                        />
                    </div>
                )}

                <div>
                    <Label htmlFor="name">{t('global.name')}</Label>
                    <TextInput
                        id="name"
                        required
                        value={form.name}
                        onChange={(event) => updateField('name', event.target.value)}
                    />
                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                </div>

                <div>
                    <Label htmlFor="last_name">{t('global.last_name')}</Label>
                    <TextInput
                        id="last_name"
                        value={form.last_name}
                        onChange={(event) => updateField('last_name', event.target.value)}
                    />
                </div>

                <div>
                    <Label htmlFor="father_name">{t('global.father_name')}</Label>
                    <TextInput
                        id="father_name"
                        value={form.father_name}
                        onChange={(event) => updateField('father_name', event.target.value)}
                    />
                </div>

                <div>
                    <Label htmlFor="nid">{t('global.nid')}</Label>
                    <TextInput
                        id="nid"
                        required
                        value={form.nid}
                        onChange={(event) => updateField('nid', event.target.value)}
                    />
                    {errors.nid && <p className="mt-1 text-sm text-red-600">{errors.nid}</p>}
                </div>

                <div>
                    <Label htmlFor="job">{jobLabel}</Label>
                    <TextInput
                        id="job"
                        value={form.job}
                        onChange={(event) => updateField('job', event.target.value)}
                    />
                </div>

                {patientType !== '2' && (
                    <div>
                        <Label htmlFor="job_category">{t('global.job_category')}</Label>
                        <Select
                            id="job_category"
                            required
                            value={form.job_category}
                            onChange={(event) => handleJobCategoryChange(event.target.value as '0' | '1')}
                        >
                            <option value="0">{t('global.military')}</option>
                            <option value="1">{t('global.civilian')}</option>
                        </Select>
                    </div>
                )}

                {showMilitaryFields && (
                    <div>
                        <Label htmlFor="militery_type_id">{t('global.militery_type')}</Label>
                        <Select
                            id="militery_type_id"
                            value={form.militery_type_id}
                            onChange={(event) => updateField('militery_type_id', event.target.value)}
                        >
                            <option value="">{t('global.select')}</option>
                            {formData.militeryTypes.map((type) => (
                                <option key={type.id} value={type.id}>
                                    {type.name}
                                </option>
                            ))}
                        </Select>
                    </div>
                )}

                {showRankField && (
                    <div>
                        <Label htmlFor="rank">{rankLabel}</Label>
                        <TextInput
                            id="rank"
                            value={form.rank}
                            onChange={(event) => updateField('rank', event.target.value)}
                        />
                    </div>
                )}

                <div>
                    <Label htmlFor="phone">{t('global.phone')}</Label>
                    <TextInput
                        id="phone"
                        value={form.phone}
                        onChange={(event) => updateField('phone', event.target.value)}
                    />
                </div>

                <div>
                    <Label>{t('global.age')}</Label>
                    <div className="grid grid-cols-3 gap-2">
                        <TextInput
                            type="number"
                            min={0}
                            max={150}
                            placeholder={t('global.year') as string}
                            value={form.age_year}
                            onChange={(event) => updateField('age_year', event.target.value)}
                        />
                        <TextInput
                            type="number"
                            min={0}
                            max={11}
                            placeholder={t('global.month') as string}
                            value={form.age_month}
                            onChange={(event) => updateField('age_month', event.target.value)}
                        />
                        <TextInput
                            type="number"
                            min={0}
                            max={31}
                            placeholder={t('global.day') as string}
                            value={form.age_day}
                            onChange={(event) => updateField('age_day', event.target.value)}
                        />
                    </div>
                    {errors.age && <p className="mt-1 text-sm text-red-600">{errors.age}</p>}
                </div>

                <div>
                    <Label htmlFor="gender">{t('global.gender')}</Label>
                    <Select
                        id="gender"
                        required
                        value={form.gender}
                        onChange={(event) => updateField('gender', event.target.value)}
                    >
                        {patientType === '2' && <option value="">{t('global.select')}</option>}
                        <option value="0">{t('global.male')}</option>
                        <option value="1">{t('global.female')}</option>
                    </Select>
                    {errors.gender && <p className="mt-1 text-sm text-red-600">{errors.gender}</p>}
                </div>

                {patientType !== '2' && (
                    <div>
                        <Label htmlFor="referred_by">{t('global.referred_by')}</Label>
                        <Select
                            id="referred_by"
                            required={patientType === '1'}
                            value={form.referred_by}
                            onChange={(event) => updateField('referred_by', event.target.value)}
                        >
                            <option value="">{t('global.select')}</option>
                            {formData.recipients.map((recipient) => (
                                <option key={recipient.id} value={recipient.id}>
                                    {recipient.name}
                                </option>
                            ))}
                        </Select>
                        {errors.referred_by && <p className="mt-1 text-sm text-red-600">{errors.referred_by}</p>}
                    </div>
                )}

                <div>
                    <Label htmlFor="province_id">{t('global.province')}</Label>
                    <Select
                        id="province_id"
                        required
                        value={form.province_id}
                        onChange={(event) => loadDistricts(event.target.value)}
                    >
                        <option value="">{t('global.select')}</option>
                        {formData.provinces.map((province) => (
                            <option key={province.id} value={province.id}>
                                {province.name_dr}
                            </option>
                        ))}
                    </Select>
                    {errors.province_id && <p className="mt-1 text-sm text-red-600">{errors.province_id}</p>}
                </div>

                <div>
                    <Label htmlFor="district_id">{t('global.district')}</Label>
                    <Select
                        id="district_id"
                        required
                        disabled={!form.province_id || loadingDistricts}
                        value={form.district_id}
                        onChange={(event) => updateField('district_id', event.target.value)}
                    >
                        <option value="">
                            {loadingDistricts ? `${t('global.loading')}...` : t('global.select')}
                        </option>
                        {districts.map((district) => (
                            <option key={district.id} value={district.id}>
                                {district.name_dr}
                            </option>
                        ))}
                    </Select>
                    {errors.district_id && <p className="mt-1 text-sm text-red-600">{errors.district_id}</p>}
                </div>

                <div>
                    <Label htmlFor="registration_date">{t('global.registration_date')}</Label>
                    <TextInput id="registration_date" readOnly value={formData.registrationDate} />
                </div>

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
                    <div className="col-span-full mt-2">
                        <h5 className="mb-3 rounded-lg bg-blue-50 px-3 py-2 text-base font-semibold text-blue-900">
                            {t('global.referred_person')}
                        </h5>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <Label htmlFor="referral_name">{t('global.name')}</Label>
                                <TextInput
                                    id="referral_name"
                                    required
                                    value={form.referral_name}
                                    onChange={(event) => updateField('referral_name', event.target.value)}
                                />
                                {errors.referral_name && (
                                    <p className="mt-1 text-sm text-red-600">{errors.referral_name}</p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="referral_last_name">{t('global.last_name')}</Label>
                                <TextInput
                                    id="referral_last_name"
                                    value={form.referral_last_name}
                                    onChange={(event) => updateField('referral_last_name', event.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="referral_father_name">{t('global.father_name')}</Label>
                                <TextInput
                                    id="referral_father_name"
                                    value={form.referral_father_name}
                                    onChange={(event) => updateField('referral_father_name', event.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="referral_nid">{t('global.nid')}</Label>
                                <TextInput
                                    id="referral_nid"
                                    required
                                    value={form.referral_nid}
                                    onChange={(event) => updateField('referral_nid', event.target.value)}
                                />
                                {errors.referral_nid && (
                                    <p className="mt-1 text-sm text-red-600">{errors.referral_nid}</p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="referral_id_card">{t('global.id_card')}</Label>
                                <TextInput
                                    id="referral_id_card"
                                    value={form.referral_id_card}
                                    onChange={(event) => updateField('referral_id_card', event.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="referral_phone">{t('global.phone')}</Label>
                                <TextInput
                                    id="referral_phone"
                                    value={form.referral_phone}
                                    onChange={(event) => updateField('referral_phone', event.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="referral_recipient">{t('global.referred_by')}</Label>
                                <Select
                                    id="referral_recipient"
                                    required
                                    value={form.referral_recipient}
                                    onChange={(event) => updateField('referral_recipient', event.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {formData.recipients.map((recipient) => (
                                        <option key={recipient.id} value={recipient.id}>
                                            {recipient.name}
                                        </option>
                                    ))}
                                </Select>
                                {errors.referral_recipient && (
                                    <p className="mt-1 text-sm text-red-600">{errors.referral_recipient}</p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="relation_id">{t('global.relation')}</Label>
                                <Select
                                    id="relation_id"
                                    required
                                    value={form.relation_id}
                                    onChange={(event) => updateField('relation_id', event.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {formData.relations.map((relation) => (
                                        <option key={relation.id} value={relation.id}>
                                            {relation.name}
                                        </option>
                                    ))}
                                </Select>
                                {errors.relation_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.relation_id}</p>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                <div className="col-span-full mt-4 flex gap-3">
                    <Button type="submit" color="blue" disabled={submitting}>
                        {submitting ? `${t('global.creating')}...` : t('global.create')}
                    </Button>
                    <Button color="gray" as="a" href={urls.back}>
                        {t('global.back')}
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
