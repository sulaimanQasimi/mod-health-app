import { Head, Link } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useState } from 'react';
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
    const icon = isEdit ? 'bx-edit' : 'bx-user-plus';
    const backHref = isEdit && urls.show ? urls.show : urls.back;

    return (
        <DashboardLayout>
            <Head title={title} />

            <div className="mx-auto max-w-7xl">
                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md">
                                <i className={`bx ${icon} text-xl`} />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">{title}</h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    {t('global.registration_date')}:{' '}
                                    <span className="font-medium text-gray-700 dark:text-gray-300">
                                        {formData.registrationDate}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <Button color="light" as={Link} href={backHref} className="w-fit">
                            <i className="bx bx-arrow-back me-2 text-lg" />
                            {t('global.back')}
                        </Button>
                    </div>

                    {!isEdit && (
                        <div className="mb-6">
                            <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
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
                </Card>
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
