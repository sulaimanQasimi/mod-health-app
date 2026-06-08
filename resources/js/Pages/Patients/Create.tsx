import { Head } from '@inertiajs/react';
import { useState } from 'react';
import BackLink from '../../Components/ui/BackLink';
import PatientCreateForm from '../../Components/Patients/PatientCreateForm';
import PatientTypeSelector from '../../Components/Patients/ui/PatientTypeSelector';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PatientFormData,
    PatientFormMode,
    PatientFormPermissions,
    PatientFormUrls,
    PatientFormValues,
    PatientType,
} from '../../types/patient';

export interface PatientFormPageProps {
    mode: PatientFormMode;
    formData: PatientFormData;
    urls: PatientFormUrls;
    patient?: PatientFormValues;
    permissions?: PatientFormPermissions;
}

export function PatientFormPage({
    mode,
    formData,
    urls,
    patient,
    permissions,
}: PatientFormPageProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const [patientType, setPatientType] = useState<PatientType>(
        patient?.type ?? '0',
    );

    const title = isEdit ? t('global.edit_patient') : t('global.create_patient');
    const backHref = isEdit && urls.show ? urls.show : urls.back;

    return (
        <DashboardLayout>
            <Head title={title} />

            <div className="mx-auto max-w-6xl">
                <div className="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div className="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-xl font-semibold text-gray-900 dark:text-white">{title}</h1>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {t('global.registration_date')}:{' '}
                                <span className="font-medium text-gray-700 dark:text-gray-300">
                                    {formData.registrationDate}
                                </span>
                            </p>
                        </div>
                        <BackLink href={backHref}>{t('global.back')}</BackLink>
                    </div>

                    <div className="px-6 py-6">
                        {!isEdit && (
                            <div className="mb-8">
                                <p className="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {t('global.type')}
                                </p>
                                <PatientTypeSelector value={patientType} onChange={setPatientType} />
                            </div>
                        )}

                        <PatientCreateForm
                            key={isEdit ? `edit-${patient?.id}` : patientType}
                            mode={mode}
                            patientType={patientType}
                            formData={formData}
                            urls={urls}
                            patient={patient}
                            permissions={permissions}
                        />
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}

interface CreatePatientProps {
    mode?: 'create';
    formData: PatientFormData;
    urls: PatientFormUrls;
}

export default function CreatePatient({ formData, urls }: CreatePatientProps) {
    return <PatientFormPage mode="create" formData={formData} urls={urls} />;
}
