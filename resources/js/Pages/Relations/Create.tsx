import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import RelationForm from '../../Components/Relations/RelationForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function CreateRelation({ urls }: { urls: SettingsFormUrls }) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.relations')} />
            <div className="mx-auto max-w-2xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.relations')}
                        icon="bx-group"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <RelationForm mode="create" urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
