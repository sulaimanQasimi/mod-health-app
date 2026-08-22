import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PermissionForm from '../../Components/Permissions/PermissionForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import { PermissionCreateEditProps } from '../../types/permission';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

export default function CreatePermission({ formData, urls }: PermissionCreateEditProps) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.create_new_permission')} />

            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.permissions')}
                        icon="bx-lock-alt"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <PermissionForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
