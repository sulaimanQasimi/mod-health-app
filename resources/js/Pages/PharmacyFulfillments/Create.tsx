import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import PharmacyFulfillmentForm from '../../Components/PharmacyFulfillments/PharmacyFulfillmentForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem } from '../../types/settings';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

export default function CreatePharmacyFulfillment({
    formData,
    userPharmacy,
    urls,
}: {
    formData: { medicines: OptionItem[] };
    userPharmacy?: { id: number; name: string } | null;
    urls: { index: string; store: string; update: string; back: string };
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.create_pharmacy_fulfillment')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create_pharmacy_fulfillment')}
                        subtitle={t('global.pharmacy_fulfillments')}
                        icon="bx-list-check"
                        accent="from-teal-500 to-cyan-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <PharmacyFulfillmentForm
                        mode="create"
                        formData={formData}
                        userPharmacy={userPharmacy}
                        urls={urls}
                    />
                </Card>
            </div>
        </DashboardLayout>
    );
}
