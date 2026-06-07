import { Label, Select } from 'flowbite-react';
import { useEffect, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { DoctorOption, NamedOption, PatientCreateUrls } from '../../types/patient';

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
        <div className="col-span-full mt-4">
            <h5 className="mb-3 rounded-lg bg-cyan-50 px-3 py-2 text-base font-semibold text-cyan-900">
                {t('global.create_appointment')}
            </h5>
            <div className="grid gap-4 md:grid-cols-2">
                {clinicType === 'both' && (
                    <div>
                        <Label htmlFor="appointment_clinic_type">{t('global.clinic_type')}</Label>
                        <Select
                            id="appointment_clinic_type"
                            value={values.appointment_clinic_type}
                            onChange={(event) => onChange('appointment_clinic_type', event.target.value)}
                        >
                            <option value="">{t('global.select')}...</option>
                            <option value="hospital">{t('global.hospital')}</option>
                            <option value="clinic">{t('global.clinic')}</option>
                        </Select>
                        {errors.appointment_clinic_type && (
                            <p className="mt-1 text-sm text-red-600">{errors.appointment_clinic_type}</p>
                        )}
                    </div>
                )}
                <div>
                    <Label htmlFor="appointment_department_id">{t('global.department')}</Label>
                    <Select
                        id="appointment_department_id"
                        value={values.appointment_department_id}
                        onChange={(event) => onChange('appointment_department_id', event.target.value)}
                    >
                        <option value="">{t('global.select_department')}</option>
                        {departments.map((department) => (
                            <option key={department.id} value={department.id}>
                                {department.name}
                            </option>
                        ))}
                    </Select>
                    {errors.appointment_department_id && (
                        <p className="mt-1 text-sm text-red-600">{errors.appointment_department_id}</p>
                    )}
                </div>
                <div>
                    <Label htmlFor="appointment_doctor_id">{t('global.doctor')}</Label>
                    <Select
                        id="appointment_doctor_id"
                        value={values.appointment_doctor_id}
                        disabled={!values.appointment_department_id || loadingDoctors}
                        onChange={(event) => onChange('appointment_doctor_id', event.target.value)}
                    >
                        <option value="">
                            {loadingDoctors ? `${t('global.loading')}...` : t('global.select_doctor')}
                        </option>
                        {doctors.map((doctor) => (
                            <option key={doctor.id} value={doctor.id}>
                                {doctor.name}
                            </option>
                        ))}
                    </Select>
                    {errors.appointment_doctor_id && (
                        <p className="mt-1 text-sm text-red-600">{errors.appointment_doctor_id}</p>
                    )}
                </div>
            </div>
        </div>
    );
}
