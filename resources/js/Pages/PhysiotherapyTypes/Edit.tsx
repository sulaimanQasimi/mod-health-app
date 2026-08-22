import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PhysiotherapyTypeForm from '../../Components/PhysiotherapyTypes/PhysiotherapyTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

export default function EditPhysiotherapyType({
    physiotherapyType,
    urls,
}: {
    physiotherapyType: { id: number; name: string; description: string };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.edit_physiotherapy_type')} />

            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit_physiotherapy_type')}
                        subtitle={physiotherapyType.name}
                        icon="bx-edit"
                        accent="from-teal-500 to-cyan-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <PhysiotherapyTypeForm mode="edit" urls={urls} physiotherapyType={physiotherapyType} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
