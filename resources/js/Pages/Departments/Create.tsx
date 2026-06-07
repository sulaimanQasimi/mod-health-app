import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DepartmentForm from '../../Components/Departments/DepartmentForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function CreateDepartment({
    formData,
    urls,
}: {
    formData: { categories: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.departments')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.departments')}
                        icon="bx-buildings"
                        accent="from-blue-500 to-indigo-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <DepartmentForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
