import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import FloorForm from '../../Components/Floors/FloorForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function EditFloor({
    floor,
    formData,
    urls,
}: {
    floor: { id: number; name: string; branch_id: string };
    formData: { branches: OptionItem[] };
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
                        subtitle={floor.name}
                        icon="bx-edit"
                        accent="from-amber-500 to-orange-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <FloorForm mode="edit" formData={formData} urls={urls} floor={floor} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
