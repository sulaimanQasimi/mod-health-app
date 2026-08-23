import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import DepotNavTabs from '../../Components/Depots/DepotNavTabs';
import ToolForm, { ToolRecord } from '../../Components/Tools/ToolForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotNavPermissions, DepotNavUrls } from '../../types/depot';
import { OptionItem, SettingsFormUrls } from '../../types/settings';
import { SETTINGS_FORM_WIDTH, SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

export default function EditTool({
    tool,
    units,
    navUrls,
    navPermissions,
    urls,
}: {
    tool: ToolRecord;
    units: OptionItem[];
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.depot.edit_tool')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <DepotNavTabs active="tools" urls={navUrls} permissions={navPermissions} />
                <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                    <Card className="shadow-sm">
                        <SettingsPageHeader
                            title={t('global.depot.edit_tool')}
                            subtitle={tool.name}
                            icon="bx-edit"
                            accent="from-gray-600 to-gray-800"
                            backHref={urls.back}
                            backLabel={t('global.back')}
                        />
                        <ToolForm mode="edit" urls={urls} units={units} tool={tool} />
                    </Card>
                </div>
            </div>
        </DashboardLayout>
    );
}
