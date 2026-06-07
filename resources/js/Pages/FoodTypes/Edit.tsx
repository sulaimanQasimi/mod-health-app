import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import FoodTypeForm from '../../Components/FoodTypes/FoodTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function EditFoodType({
    foodType,
    urls,
}: {
    foodType: { id: number; name: string };
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
                        subtitle={foodType.name}
                        icon="bx-edit"
                        accent="from-orange-500 to-amber-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <FoodTypeForm mode="edit" urls={urls} foodType={foodType} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
