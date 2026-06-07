import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import FloorForm from '../../Components/Floors/FloorForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function CreateFloor({
    formData,
    urls,
}: {
    formData: { branches: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.floors')} />
            <div className="mx-auto max-w-2xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.floors')}
                        icon="bx-layer"
                        accent="from-amber-500 to-orange-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <FloorForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
