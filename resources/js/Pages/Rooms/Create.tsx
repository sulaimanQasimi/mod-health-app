import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import FacilityNavTabs, { FacilityNavProps } from '../../Components/FacilityLayout/FacilityNavTabs';
import FacilityPageHeader from '../../Components/FacilityLayout/FacilityPageHeader';
import RoomForm from '../../Components/Rooms/RoomForm';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

export default function CreateRoom({
    formData,
    urls,
    nav,
}: {
    formData: {
        branches: OptionItem[];
        floors: Array<OptionItem & { branch_id: number }>;
        departments: OptionItem[];
    };
    urls: SettingsFormUrls;
    nav: FacilityNavProps;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.rooms')} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={t('global.create')}
                        subtitle={t('global.rooms')}
                        icon="bx-door-open"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <RoomForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
