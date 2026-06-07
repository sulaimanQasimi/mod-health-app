import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import VitalSignTypeForm from '../../Components/VitalSignTypes/VitalSignTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function CreateVitalSignType({ urls }: { urls: SettingsFormUrls }) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.create_vital_sign_type')} />
            <div className="mx-auto max-w-2xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create_vital_sign_type')}
                        subtitle={t('global.vital_sign_types')}
                        icon="bx-heart"
                        accent="from-red-500 to-pink-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <VitalSignTypeForm mode="create" urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
