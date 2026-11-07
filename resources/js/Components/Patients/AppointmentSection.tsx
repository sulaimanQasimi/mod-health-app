import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { DoctorOption, NamedOption, PatientCreateUrls } from '../../types/patient';
import { FormField, GridDivider } from './ui/FormField';
import SearchableSelect from '../ui/SearchableSelect';

interface AppointmentSectionProps {
    clinicType: string | null;
    departments: NamedOption[];
    urls: PatientCreateUrls;
    values: {
        appointment_clinic_type: string;
        appointment_department_id: string;
        appointment_doctor_id: string;
    };
    onChange: (field: string, value: string) => void;
    errors: Record<string, string>;
}

export default function AppointmentSection({
    clinicType,
    departments,
    urls,
    values,
    onChange,
    errors,
}: AppointmentSectionProps) {
    const { t } = useTranslation();
    const [doctors, setDoctors] = useState<DoctorOption[]>([]);
    const [loadingDoctors, setLoadingDoctors] = useState(false);

    const selectedDepartment = useMemo(
        () => departments.find((department) => String(department.id) === String(values.appointment_department_id)),
        [departments, values.appointment_department_id],
    );

    useEffect(() => {
        const departmentId = values.appointment_department_id;

        if (!departmentId) {
            setDoctors([]);
            return;
        }

        const params = new URLSearchParams();
        if (clinicType === 'both' && values.appointment_clinic_type) {
            params.set('clinic_type', values.appointment_clinic_type);
        }

        const query = params.toString();
        const url = `${urls.doctorsByDepartment}/${departmentId}${query ? `?${query}` : ''}`;

        setLoadingDoctors(true);
        fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.json())
            .then((payload) => {
                setDoctors(payload.doctors ?? []);
            })
            .catch(() => {
                setDoctors([]);
            })
            .finally(() => {
                setLoadingDoctors(false);
            });
    }, [clinicType, urls.doctorsByDepartment, values.appointment_clinic_type, values.appointment_department_id]);

    return (
        <>
            <GridDivider title={t('global.create_appointment')} />
            {clinicType === 'both' && (
                <FormField label={t('global.clinic_type')} error={errors.appointment_clinic_type} compact className="lg:col-span-2">
                    <SearchableSelect
                        id="appointment_clinic_type"
                        compact
                        value={values.appointment_clinic_type}
                        onChange={(value) => onChange('appointment_clinic_type', value)}
                        placeholder={`${t('global.select')}...`}
                    >
                        <option value="">{t('global.select')}...</option>
                        <option value="hospital">{t('global.hospital')}</option>
                        <option value="clinic">{t('global.clinic')}</option>
                    </SearchableSelect>
                </FormField>
            )}
            <FormField
                label={t('global.department')}
                error={errors.appointment_department_id}
                compact
                className="lg:col-span-2"
            >
                <SearchableSelect
                    id="appointment_department_id"
                    compact
                    value={values.appointment_department_id}
                    onChange={(value) => onChange('appointment_department_id', value)}
                    placeholder={t('global.select_department')}
                >
                    <option value="">{t('global.select_department')}</option>
                    {departments.map((department) => (
                        <option key={department.id} value={department.id}>
                            {department.name}
                            {department.price !== null && department.price !== undefined && department.price !== ''
                                ? ` (${t('global.price')}: ${department.price})`
                                : ''}
                        </option>
                    ))}
                </SearchableSelect>
            </FormField>
            <FormField label={t('global.price')} compact className="lg:col-span-2">
                <input
                    type="text"
                    readOnly
                    className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    value={
                        selectedDepartment
                            ? String(selectedDepartment.price ?? 0)
                            : ''
                    }
                    placeholder="—"
                />
            </FormField>
            <FormField label={t('global.doctor')} error={errors.appointment_doctor_id} compact className="lg:col-span-2">
                <SearchableSelect
                    id="appointment_doctor_id"
                    compact
                    value={values.appointment_doctor_id}
                    disabled={!values.appointment_department_id || loadingDoctors}
                    onChange={(value) => onChange('appointment_doctor_id', value)}
                    placeholder={loadingDoctors ? `${t('global.loading')}...` : t('global.select_doctor')}
                >
                    <option value="">
                        {loadingDoctors ? `${t('global.loading')}...` : t('global.select_doctor')}
                    </option>
                    {doctors.map((doctor) => (
                        <option key={doctor.id} value={doctor.id}>
                            {doctor.name}
                        </option>
                    ))}
                </SearchableSelect>
            </FormField>
        </>
    );
}
