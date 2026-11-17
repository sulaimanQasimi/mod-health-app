import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import BedForm from '../../Components/Beds/BedForm';
import FacilityNavTabs, { FacilityNavProps } from '../../Components/FacilityLayout/FacilityNavTabs';
import FacilityPageHeader from '../../Components/FacilityLayout/FacilityPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

export default function CreateBed({
    formData,
    urls,
    nav,
}: {
    formData: {
        branches: OptionItem[];
        floors: Array<OptionItem & { branch_id: number }>;
        rooms: Array<OptionItem & { floor_id: number; branch_id: number }>;
    };
    urls: SettingsFormUrls;
    nav: FacilityNavProps;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.beds')} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={t('global.create')}
                        subtitle={t('global.beds')}
                        icon="bx-bed"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <BedForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
