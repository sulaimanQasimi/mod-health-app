import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import RecipientForm from '../../Components/Recipients/RecipientForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function CreateRecipient({ urls }: { urls: SettingsFormUrls }) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.recipients')} />
            <div className="mx-auto max-w-3xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.recipients')}
                        icon="bx-envelope"
                        accent="from-rose-500 to-pink-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <RecipientForm mode="create" urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
