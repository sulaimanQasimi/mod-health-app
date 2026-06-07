import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import OperationTypeForm from '../../Components/OperationTypes/OperationTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function CreateOperationType({
    formData,
    urls,
}: {
    formData: { branches: OptionItem[]; departments: OptionItem[] };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.operation_types')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create')}
                        subtitle={t('global.operation_types')}
                        icon="bx-cut"
                        accent="from-rose-500 to-red-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <OperationTypeForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
