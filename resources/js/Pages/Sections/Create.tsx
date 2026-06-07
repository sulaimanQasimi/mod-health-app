import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import SectionForm from '../../Components/Sections/SectionForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function CreateSection({
    formData,
    urls,
}: {
    formData: { departments: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.sections')} />
            <div className="mx-auto max-w-2xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.sections')}
                        icon="bx-grid-alt"
                        accent="from-cyan-500 to-teal-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <SectionForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
