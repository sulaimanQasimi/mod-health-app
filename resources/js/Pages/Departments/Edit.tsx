import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DepartmentForm from '../../Components/Departments/DepartmentForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function EditDepartment({
    department,
    formData,
    urls,
}: {
    department: { id: number; name: string; room_number: string; category_id: string };
    formData: { categories: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className="mx-auto max-w-2xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={department.name}
                        icon="bx-edit"
                        accent="from-blue-500 to-indigo-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <DepartmentForm mode="edit" formData={formData} urls={urls} department={department} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
