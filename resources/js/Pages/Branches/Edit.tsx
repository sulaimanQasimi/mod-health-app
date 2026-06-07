import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import BranchForm from '../../Components/Branches/BranchForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function EditBranch({
    branch,
    urls,
}: {
    branch: { id: number; name: string; address: string };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className="mx-auto max-w-3xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={branch.name}
                        icon="bx-edit"
                        accent="from-blue-500 to-indigo-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <BranchForm mode="edit" urls={urls} branch={branch} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
