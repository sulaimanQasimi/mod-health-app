import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import FacilityNavTabs, { FacilityNavProps } from '../../Components/FacilityLayout/FacilityNavTabs';
import FacilityPageHeader from '../../Components/FacilityLayout/FacilityPageHeader';
import FloorForm from '../../Components/Floors/FloorForm';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

export default function EditFloor({
    floor,
    formData,
    urls,
    nav,
}: {
    floor: { id: number; name: string; branch_id: string };
    formData: { branches: OptionItem[] };
    urls: SettingsFormUrls;
    nav: FacilityNavProps;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={t('global.edit')}
                        subtitle={floor.name}
                        icon="bx-edit"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <FloorForm mode="edit" formData={formData} urls={urls} floor={floor} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
