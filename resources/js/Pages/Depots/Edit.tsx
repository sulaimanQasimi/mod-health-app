import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DepotForm from '../../Components/Depots/DepotForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotDetail, DepotFormData, DepotNavUrls } from '../../types/depot';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../utils/settingsUi';

export default function EditDepot({
    depot,
    formData,
    navUrls: _navUrls,
    urls,
}: {
    depot: DepotDetail;
    formData: DepotFormData;
    navUrls: DepotNavUrls;
    urls: { index: string; show: string; update: string };
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.depot.edit')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.edit')}
                        subtitle={depot.name}
                        icon="bx-store"
                        accent="from-amber-500 to-orange-600"
                        backHref={urls.show}
                        backLabel={t('global.back')}
                    />
                    <DepotForm mode="edit" formData={formData} depot={depot} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
