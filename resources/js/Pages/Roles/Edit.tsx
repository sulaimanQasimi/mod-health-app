import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import RoleForm from '../../Components/Roles/RoleForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { RoleFormData, RoleRecord } from '../../Components/Roles/roleTypes';
import { SettingsFormUrls } from '../../types/settings';

export default function EditRole({
    role,
    formData,
    urls,
}: {
    role: RoleRecord;
    formData: RoleFormData;
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={role.name}
                        icon="bx-edit"
                        accent="from-indigo-500 to-blue-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <RoleForm mode="edit" formData={formData} urls={urls} role={role} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
