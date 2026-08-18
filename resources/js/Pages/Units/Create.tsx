import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import UnitForm from '../../Components/Units/UnitForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function CreateUnit({ urls }: { urls: SettingsFormUrls }) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.units')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.units')}
                        icon="bx-ruler"
                        accent="from-teal-500 to-cyan-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <UnitForm mode="create" urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
