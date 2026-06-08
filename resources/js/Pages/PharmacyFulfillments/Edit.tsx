import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import PharmacyFulfillmentForm from '../../Components/PharmacyFulfillments/PharmacyFulfillmentForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem } from '../../types/settings';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

export default function EditPharmacyFulfillment({
    fulfillment,
    formData,
    urls,
}: {
    fulfillment: {
        id: number;
        medicine_id: string;
        unit_type: string;
        amount: string | null;
        form_no: string | null;
        date: string | null;
        form_path?: string | null;
        pharmacy_name?: string | null;
    };
    formData: { medicines: OptionItem[] };
    urls: { index: string; store: string; update: string; back: string };
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={t('global.pharmacy_fulfillments')}
                        icon="bx-list-check"
                        accent="from-teal-500 to-cyan-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <PharmacyFulfillmentForm
                        mode="edit"
                        formData={formData}
                        fulfillment={fulfillment}
                        urls={urls}
                    />
                </Card>
            </div>
        </DashboardLayout>
    );
}
