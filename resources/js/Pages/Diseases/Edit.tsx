import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DiseaseForm from '../../Components/Diseases/DiseaseForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function EditDisease({
    disease,
    formData,
    urls,
}: {
    disease: {
        id: number;
        name: string;
        description: string;
        department_id: string;
        disease_category_id: string;
    };
    formData: { departments: OptionItem[]; diseaseCategories: OptionItem[] };
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
                        subtitle={disease.name}
                        icon="bx-edit"
                        accent="from-red-500 to-rose-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <DiseaseForm mode="edit" formData={formData} urls={urls} disease={disease} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
