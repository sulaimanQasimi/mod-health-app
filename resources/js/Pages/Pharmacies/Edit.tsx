import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import PharmacyForm from '../../Components/Pharmacies/PharmacyForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../utils/settingsUi';

export default function EditPharmacy({
    pharmacy,
    formData,
    urls,
}: {
    pharmacy: {
        id: number;
        name: string;
        phone: string;
        address: string;
        assignments: { user_id: string; role: string }[];
    };
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
                        title={t('global.edit')}
                        subtitle={pharmacy.name}
                        icon="bx-edit"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <PharmacyForm mode="edit" pharmacy={pharmacy} formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
