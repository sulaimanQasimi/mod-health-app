import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import MiliteryTypeForm from '../../Components/MiliteryTypes/MiliteryTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function EditMiliteryType({
    militeryType,
    urls,
}: {
    militeryType: { id: number; name: string };
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
                        subtitle={militeryType.name}
                        icon="bx-edit"
                        accent="from-slate-500 to-gray-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <MiliteryTypeForm mode="edit" urls={urls} militeryType={militeryType} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
