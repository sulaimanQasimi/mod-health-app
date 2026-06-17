import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import RecipientPartForm from '../../Components/RecipientParts/RecipientPartForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function EditRecipientPart({
    recipientPart,
    formData,
    urls,
}: {
    recipientPart: { id: number; name: string; code: string; recipient_id: string };
    formData: { recipients: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.recipient_parts')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={t('global.recipient_parts')}
                        icon="bx-sitemap"
                        accent="from-indigo-500 to-blue-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <RecipientPartForm mode="edit" recipientPart={recipientPart} formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
