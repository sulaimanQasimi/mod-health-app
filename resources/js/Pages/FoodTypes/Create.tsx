import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import FoodTypeForm from '../../Components/FoodTypes/FoodTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function CreateFoodType({ urls }: { urls: SettingsFormUrls }) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.food_types')} />
            <div className="mx-auto max-w-2xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.food_types')}
                        icon="bx-food-menu"
                        accent="from-orange-500 to-amber-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <FoodTypeForm mode="create" urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
