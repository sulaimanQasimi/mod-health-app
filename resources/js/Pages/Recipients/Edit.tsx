import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import RecipientForm from '../../Components/Recipients/RecipientForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

interface EditRecipientProps {
    recipient: { id: number; name: string; description: string };
    urls: SettingsFormUrls;
}

export default function EditRecipient({ recipient, urls }: EditRecipientProps) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={recipient.name}
                        icon="bx-edit"
                        accent="from-rose-500 to-pink-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <RecipientForm mode="edit" urls={urls} recipient={recipient} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
