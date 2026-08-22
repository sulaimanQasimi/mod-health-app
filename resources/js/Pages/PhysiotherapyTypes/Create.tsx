import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PhysiotherapyTypeForm from '../../Components/PhysiotherapyTypes/PhysiotherapyTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

export default function CreatePhysiotherapyType({ urls }: { urls: SettingsFormUrls }) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.create_physiotherapy_type')} />

            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create_physiotherapy_type')}
                        subtitle={t('global.physiotherapy_types')}
                        icon="bx-dumbbell"
                        accent="from-teal-500 to-cyan-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <PhysiotherapyTypeForm mode="create" urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
