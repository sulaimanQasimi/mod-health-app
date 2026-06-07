import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import MedicineUsageTypeForm from '../../Components/MedicineUsageTypes/MedicineUsageTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function CreateMedicineUsageType({ urls }: { urls: SettingsFormUrls }) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.medicine_usage_types')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.medicine_usage_types')}
                        icon="bx-injection"
                        accent="from-purple-500 to-violet-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <MedicineUsageTypeForm mode="create" urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
