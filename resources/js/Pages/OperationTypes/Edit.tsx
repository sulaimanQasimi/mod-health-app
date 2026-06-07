import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import OperationTypeForm from '../../Components/OperationTypes/OperationTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export default function EditOperationType({
    operationType,
    formData,
    urls,
}: {
    operationType: {
        id: number;
        name: string;
        branch_id: string;
        department_id: string;
    };
    formData: { branches: OptionItem[]; departments: OptionItem[] };
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
                        subtitle={operationType.name}
                        icon="bx-edit"
                        accent="from-rose-500 to-red-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <OperationTypeForm
                        mode="edit"
                        formData={formData}
                        urls={urls}
                        operationType={operationType}
                    />
                </Card>
            </div>
        </DashboardLayout>
    );
}
