import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import MedicineForm from '../../Components/Medicines/MedicineForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function EditMedicine({
    medicine,
    urls,
}: {
    medicine: { id: number; name: string };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={medicine.name}
                        icon="bx-edit"
                        accent="from-teal-500 to-cyan-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <MedicineForm mode="edit" urls={urls} medicine={medicine} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
