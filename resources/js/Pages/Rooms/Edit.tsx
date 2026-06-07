import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import RoomForm from '../../Components/Rooms/RoomForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function EditRoom({
    room,
    formData,
    urls,
}: {
    room: { id: number; name: string; floor_id: string; department_id: string };
    formData: { floors: OptionItem[]; departments: OptionItem[] };
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
                        subtitle={room.name}
                        icon="bx-edit"
                        accent="from-emerald-500 to-green-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <RoomForm mode="edit" formData={formData} urls={urls} room={room} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
