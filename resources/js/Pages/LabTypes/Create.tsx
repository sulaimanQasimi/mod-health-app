import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import LabTypeForm from '../../Components/LabTypes/LabTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function CreateLabType({
    formData,
    urls,
}: {
    formData: { categories: OptionItem[]; departments: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.create_lab_type')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create_lab_type')}
                        subtitle={t('global.lab_types')}
                        icon="bx-test-tube"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <LabTypeForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
