import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import PharmacyForm from '../../Components/Pharmacies/PharmacyForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../utils/settingsUi';

export default function CreatePharmacy({
    formData,
    urls,
}: {
    formData: { users: { id: number; full_name: string; email: string }[]; roles: string[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.pharmacies')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.pharmacies')}
                        icon="bx-clinic"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <PharmacyForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
