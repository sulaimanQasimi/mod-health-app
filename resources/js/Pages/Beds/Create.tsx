import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import BedForm from '../../Components/Beds/BedForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function CreateBed({
    formData,
    urls,
}: {
    formData: { rooms: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.beds')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.beds')}
                        icon="bx-bed"
                        accent="from-cyan-500 to-blue-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <BedForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
