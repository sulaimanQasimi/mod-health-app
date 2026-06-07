import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import ProcedureTypeForm from '../../Components/ProcedureTypes/ProcedureTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function EditProcedureType({
    procedureType,
    urls,
}: {
    procedureType: { id: number; name: string };
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
                        subtitle={procedureType.name}
                        icon="bx-edit"
                        accent="from-indigo-500 to-blue-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <ProcedureTypeForm mode="edit" urls={urls} procedureType={procedureType} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
