import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import BranchForm from '../../Components/Branches/BranchForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function CreateBranch({ urls }: { urls: SettingsFormUrls }) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.branches')} />
            <div className="mx-auto max-w-3xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.branches')}
                        icon="bx-building"
                        accent="from-blue-500 to-indigo-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <BranchForm mode="create" urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
