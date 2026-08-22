import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PermissionForm from '../../Components/Permissions/PermissionForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import { PermissionCreateEditProps } from '../../types/permission';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

export default function EditPermission({ permission, formData, urls }: PermissionCreateEditProps) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.edit_permission')} />

            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={permission?.name_dr ?? permission?.name ?? t('global.permissions')}
                        icon="bx-edit"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    {permission && <PermissionForm mode="edit" formData={formData} urls={urls} permission={permission} />}
                </Card>
            </div>
        </DashboardLayout>
    );
}
