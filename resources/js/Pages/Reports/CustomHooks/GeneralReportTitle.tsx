import { Badge, Button } from 'flowbite-react';
import { useMemo, useState } from 'react';
import { useTranslation } from '../../../hooks/useTranslation';
import GeneralReportTitleSettingsModal from './GeneralReportTitleSettingsModal';
import {
    buildTitleStyle,
    countTitleSettingChanges,
    createDefaultTitleSettings,
    ReportTitleSettings,
} from './generalReportTitleSettings';

interface GeneralReportTitleProps {
    settings?: ReportTitleSettings;
    onSettingsChange?: (settings: ReportTitleSettings) => void;
}

export default function GeneralReportTitle({
    settings: settingsProp,
    onSettingsChange,
}: GeneralReportTitleProps) {
    const { t } = useTranslation();
    const [localSettings, setLocalSettings] = useState<ReportTitleSettings>(() =>
        settingsProp ?? createDefaultTitleSettings(),
    );
    const [settingsModalOpen, setSettingsModalOpen] = useState(false);

    const settings = settingsProp ?? localSettings;

    const activeChangeCount = useMemo(() => countTitleSettingChanges(settings), [settings]);
    const titleStyle = useMemo(() => buildTitleStyle(settings), [settings]);

    const applySettings = (next: ReportTitleSettings) => {
        if (onSettingsChange) {
            onSettingsChange(next);
        } else {
            setLocalSettings(next);
        }
    };

    return (
        <>
            <div className="general-report-no-print mb-3 flex flex-wrap items-center justify-between gap-3">
                <div className="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <span>{t('global.title')}</span>
                    {activeChangeCount > 0 && (
                        <Badge color="indigo" size="sm">
                            {activeChangeCount} customized
                        </Badge>
                    )}
                </div>
                <Button type="button" size="sm" color="light" onClick={() => setSettingsModalOpen(true)}>
                    <i className="bx bx-slider-alt me-2" />
                    {t('global.advanced_filters')}
                </Button>
            </div>

            <div className="general-report-title-root w-full" dir="rtl">
                <div className="general-report-title-text" style={titleStyle}>
                    {settings.text || '\u00a0'}
                </div>
            </div>

            <GeneralReportTitleSettingsModal
                open={settingsModalOpen}
                settings={settings}
                onClose={() => setSettingsModalOpen(false)}
                onApply={applySettings}
            />
        </>
    );
}
