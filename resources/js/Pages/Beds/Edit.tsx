import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import BedForm from '../../Components/Beds/BedForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function EditBed({
    bed,
    formData,
    urls,
}: {
    bed: { id: number; number: string; room_id: string; is_occupied: boolean };
    formData: { rooms: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={bed.number}
                        icon="bx-edit"
                        accent="from-cyan-500 to-blue-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <BedForm mode="edit" formData={formData} urls={urls} bed={bed} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
