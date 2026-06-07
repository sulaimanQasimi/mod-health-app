import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import NurseForm from '../../Components/Nurses/NurseForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../utils/settingsUi';

interface NurseUserOption {
    id: number;
    name: string;
    email: string;
}

export default function CreateNurse({
    formData,
    urls,
}: {
    formData: { branches: OptionItem[]; departments: OptionItem[]; users: NurseUserOption[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.create')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.nurses')}
                        icon="bx-user-plus"
                        accent="from-pink-500 to-rose-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <NurseForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
