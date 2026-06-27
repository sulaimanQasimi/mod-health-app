import { Badge, Button } from 'flowbite-react';
import { useMemo, useState } from 'react';
import { useTranslation } from '../../../hooks/useTranslation';
import GeneralReportHeaderSettingsModal from './GeneralReportHeaderSettingsModal';
import {
    createDefaultHeaderSettings,
    REPORT_HEADER_LEFT_LOGO,
    REPORT_HEADER_RIGHT_LOGO,
    ReportHeaderSettings,
} from './generalReportHeaderSettings';

interface GeneralReportHeaderProps {
    settings?: ReportHeaderSettings;
    onSettingsChange?: (settings: ReportHeaderSettings) => void;
}

export default function GeneralReportHeader({
    settings: settingsProp,
    onSettingsChange,
}: GeneralReportHeaderProps) {
    const { t } = useTranslation();
    const [localSettings, setLocalSettings] = useState<ReportHeaderSettings>(() =>
        settingsProp ?? createDefaultHeaderSettings(),
    );
    const [settingsModalOpen, setSettingsModalOpen] = useState(false);

    const settings = settingsProp ?? localSettings;

    const activeChangeCount = useMemo(() => {
        const defaults = createDefaultHeaderSettings();
        return (Object.keys(defaults) as Array<keyof ReportHeaderSettings>).filter(
            (key) => settings[key] !== defaults[key],
        ).length;
    }, [settings]);

    const applySettings = (next: ReportHeaderSettings) => {
        if (onSettingsChange) {
            onSettingsChange(next);
        } else {
            setLocalSettings(next);
        }
    };

    const lines = [
        settings.line1,
        settings.line2,
        settings.line3,
        settings.line4,
        settings.line5,
        settings.line6,
    ];

    return (
        <>
            <div className="general-report-no-print mb-3 flex flex-wrap items-center justify-between gap-3">
                <div className="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <span>{t('global.header')}</span>
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

            <div className="general-report-header-root border-b-2 border-black pb-5">
                <div className="general-report-header-grid grid grid-cols-[100px_minmax(0,1fr)_100px] items-center gap-5">
                    <div className="flex h-[100px] w-[100px] items-center justify-center justify-self-center">
                        <img
                            src={REPORT_HEADER_LEFT_LOGO}
                            alt=""
                            className="max-h-full max-w-full object-contain"
                        />
                    </div>

                    <div className="flex min-h-[100px] flex-col items-center justify-center px-2 text-center" dir="rtl">
                        {lines.map((line, index) => (
                            <div
                                key={index}
                                className={
                                    index === 0
                                        ? 'text-base font-bold text-black dark:text-white'
                                        : 'text-sm font-bold text-black dark:text-white'
                                }
                            >
                                {line || '\u00a0'}
                            </div>
                        ))}
                    </div>

                    <div className="flex h-[100px] w-[100px] items-center justify-center justify-self-center">
                        <img
                            src={REPORT_HEADER_RIGHT_LOGO}
                            alt=""
                            className="max-h-full max-w-full object-contain"
                        />
                    </div>
                </div>
            </div>

            <GeneralReportHeaderSettingsModal
                open={settingsModalOpen}
                settings={settings}
                onClose={() => setSettingsModalOpen(false)}
                onApply={applySettings}
            />
        </>
    );
}
