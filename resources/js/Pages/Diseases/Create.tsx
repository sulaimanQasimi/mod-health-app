import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DiseaseForm from '../../Components/Diseases/DiseaseForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
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
            <Head title={t('global.create_disease')} />
            <div className="mx-auto max-w-3xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create_disease')}
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
