import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import RoleForm from '../../Components/Roles/RoleForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { RoleFormData } from '../../Components/Roles/roleTypes';
import { SettingsFormUrls } from '../../types/settings';

export default function CreateRole({
    formData,
    urls,
}: {
    formData: RoleFormData;
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.roles')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.roles')}
                        icon="bx-shield-quarter"
                        accent="from-indigo-500 to-blue-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <RoleForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
