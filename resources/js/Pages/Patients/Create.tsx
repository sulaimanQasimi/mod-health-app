import { Head } from '@inertiajs/react';
import { Card, TabItem, Tabs } from 'flowbite-react';
import { useState } from 'react';
import PatientCreateForm from '../../Components/Patients/PatientCreateForm';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { PatientCreateUrls, PatientFormData, PatientType } from '../../types/patient';

interface CreatePatientProps {
    formData: PatientFormData;
    urls: PatientCreateUrls;
}

const TAB_TYPES: PatientType[] = ['0', '1', '2'];

export default function CreatePatient({ formData, urls }: CreatePatientProps) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState(0);

    return (
        <DashboardLayout>
            <Head title={t('global.create_patient')} />

            <Card>
                <h1 className="mb-6 text-xl font-semibold text-gray-900 dark:text-white">
                    {t('global.create_patient')}
                </h1>

                <Tabs
                    aria-label="Patient type tabs"
                    variant="pills"
                    onActiveTabChange={(tab) => setActiveTab(tab)}
                >
                    <TabItem active={activeTab === 0} title={t('global.mod')}>
                        <PatientCreateForm patientType={TAB_TYPES[0]} formData={formData} urls={urls} />
                    </TabItem>
                    <TabItem active={activeTab === 1} title={t('global.recipient')}>
                        <PatientCreateForm patientType={TAB_TYPES[1]} formData={formData} urls={urls} />
                    </TabItem>
                    <TabItem active={activeTab === 2} title={t('global.family')}>
                        <PatientCreateForm patientType={TAB_TYPES[2]} formData={formData} urls={urls} />
                    </TabItem>
                </Tabs>
            </Card>
        </DashboardLayout>
    );
}
