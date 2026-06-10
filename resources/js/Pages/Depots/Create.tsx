import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DepotForm from '../../Components/Depots/DepotForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotFormData, DepotNavUrls } from '../../types/depot';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../utils/settingsUi';

export default function CreateDepot({
    formData,
    navUrls: _navUrls,
    urls,
}: {
    formData: DepotFormData;
    navUrls: DepotNavUrls;
    urls: { index: string; store: string };
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.depot.create')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.create')}
                        subtitle={t('global.depot.title')}
                        icon="bx-store"
                        accent="from-amber-500 to-orange-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />
                    <DepotForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
