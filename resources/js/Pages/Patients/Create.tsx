import { Head } from '@inertiajs/react';
import { useState } from 'react';
import PatientCreateForm from '../../Components/Patients/PatientCreateForm';
import PatientTypeSelector from '../../Components/Patients/ui/PatientTypeSelector';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
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
    canAccessVip?: boolean;
}

export function PatientFormPage({
    mode,
    formData,
    urls,
    patient,
    permissions,
    canAccessVip = false,
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

            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <div className="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div className="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                        <h1 className="text-lg font-semibold text-gray-900 dark:text-white">{title}</h1>
                    </div>

                    <div className="px-5 py-5">
                        {!isEdit && (
                            <PatientTypeSelector
                                value={patientType}
                                onChange={setPatientType}
                                canAccessVip={canAccessVip}
                            />
                        )}

                        <PatientCreateForm
                            key={isEdit ? `edit-${patient?.id}` : patientType}
                            mode={mode}
                            patientType={patientType}
                            formData={formData}
                            urls={urls}
                            patient={patient}
                            permissions={permissions}
                            backHref={backHref}
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
    canAccessVip?: boolean;
}

export default function CreatePatient({ formData, urls, canAccessVip = false }: CreatePatientProps) {
    return (
        <PatientFormPage
            mode="create"
            formData={formData}
            urls={urls}
            canAccessVip={canAccessVip}
        />
    );
}
