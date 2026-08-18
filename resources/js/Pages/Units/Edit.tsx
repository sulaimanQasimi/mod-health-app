import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import UnitForm from '../../Components/Units/UnitForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

interface UnitRecord {
    id: number;
    name: string;
    symbol: string | null;
    is_active: boolean;
}

export default function EditUnit({
    unit,
    urls,
}: {
    unit: UnitRecord;
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
                        subtitle={unit.name}
                        icon="bx-edit"
                        accent="from-teal-500 to-cyan-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <UnitForm mode="edit" urls={urls} unit={unit} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
