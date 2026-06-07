import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import SectionForm from '../../Components/Sections/SectionForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function EditSection({
    section,
    formData,
    urls,
}: {
    section: { id: number; name: string; department_id: string };
    formData: { departments: OptionItem[] };
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
                        subtitle={section.name}
                        icon="bx-edit"
                        accent="from-cyan-500 to-teal-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <SectionForm mode="edit" formData={formData} urls={urls} section={section} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
