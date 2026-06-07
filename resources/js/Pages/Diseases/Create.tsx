import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DiseaseForm from '../../Components/Diseases/DiseaseForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function CreateDisease({
    formData,
    urls,
}: {
    formData: { departments: OptionItem[]; diseaseCategories: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.diseases')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.diseases')}
                        icon="bx-pulse"
                        accent="from-red-500 to-rose-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <DiseaseForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
