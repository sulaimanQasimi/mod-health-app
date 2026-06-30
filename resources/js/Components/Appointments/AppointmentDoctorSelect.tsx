import { router } from '@inertiajs/react';
import { Spinner } from 'flowbite-react';
import { useEffect, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { DoctorOption } from '../../types/appointment';

interface AppointmentDoctorSelectProps {
    departmentId: number | null;
    doctorId: number | null;
    canChangeDoctor: boolean;
    isCompleted: boolean;
    isProcessed: boolean;
    doctorReassigned: boolean;
    doctorsByDepartmentUrl: string;
    assignUrl: string;
}

export default function AppointmentDoctorSelect({
    departmentId,
    doctorId,
    canChangeDoctor,
    isCompleted,
    isProcessed,
    doctorReassigned,
    doctorsByDepartmentUrl,
    assignUrl,
}: AppointmentDoctorSelectProps) {
    const { t } = useTranslation();
    const [doctors, setDoctors] = useState<DoctorOption[]>([]);
    const [selectedDoctorId, setSelectedDoctorId] = useState(doctorId ? String(doctorId) : '');
    const [loadingDoctors, setLoadingDoctors] = useState(false);
    const [assigning, setAssigning] = useState(false);

    useEffect(() => {
        setSelectedDoctorId(doctorId ? String(doctorId) : '');
    }, [doctorId]);

    useEffect(() => {
        if (isCompleted || !departmentId) {
            setDoctors([]);
            return;
        }

        setLoadingDoctors(true);

        fetch(`${doctorsByDepartmentUrl}/${departmentId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => response.json())
            .then((payload) => setDoctors(payload.doctors ?? []))
            .catch(() => setDoctors([]))
            .finally(() => setLoadingDoctors(false));
    }, [departmentId, doctorsByDepartmentUrl, isCompleted]);

    if (isCompleted) {
        return null;
    }

    const disabled = !canChangeDoctor || loadingDoctors || assigning;

    const handleChange = (value: string) => {
        if (!value || disabled) {
            return;
        }

        setAssigning(true);

        router.post(
            assignUrl,
            { doctor_id: value },
            {
                preserveScroll: true,
                onError: () => setSelectedDoctorId(doctorId ? String(doctorId) : ''),
                onFinish: () => setAssigning(false),
            },
        );
    };

    return (
        <div className="w-full sm:w-auto sm:max-w-[220px]">
            <div className="relative">
                <select
                    value={selectedDoctorId}
                    disabled={disabled}
                    onChange={(event) => {
                        const value = event.target.value;
                        setSelectedDoctorId(value);
                        handleChange(value);
                    }}
                    className="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-cyan-500 focus:ring-cyan-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
                >
                    <option value="">
                        {loadingDoctors ? `${t('global.loading')}...` : t('global.select_doctor')}
                    </option>
                    {doctors.map((doctor) => (
                        <option key={doctor.id} value={doctor.id}>
                            {doctor.name}
                        </option>
                    ))}
                </select>
                {assigning && (
                    <div className="pointer-events-none absolute inset-y-0 end-8 flex items-center">
                        <Spinner size="sm" />
                    </div>
                )}
            </div>
            {doctorReassigned && (
                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {t('global.doctor_can_only_be_changed_once')}
                </p>
            )}
            {isProcessed && !doctorReassigned && canChangeDoctor && (
                <p className="mt-1 text-xs text-amber-600 dark:text-amber-400">
                    {t('global.doctor_change_once_hint')}
                </p>
            )}
        </div>
    );
}
