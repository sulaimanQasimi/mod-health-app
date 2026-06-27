import { Badge, Button } from 'flowbite-react';
import { useTranslation } from '../../../hooks/useTranslation';

interface GeneralReportTableToolbarProps {
    visibleRowCount: number;
    totalRowCount: number;
    visibleColumnCount: number;
    totalColumnCount: number;
    activeFilterCount: number;
    onOpenSettings: () => void;
}

export default function GeneralReportTableToolbar({
    visibleRowCount,
    totalRowCount,
    visibleColumnCount,
    totalColumnCount,
    activeFilterCount,
    onOpenSettings,
}: GeneralReportTableToolbarProps) {
    const { t } = useTranslation();

    return (
        <div className="general-report-no-print mb-3 flex flex-wrap items-center justify-between gap-3">
            <div className="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                <span>
                    {visibleRowCount} / {totalRowCount} {t('global.department')}
                </span>
                <span className="text-gray-300 dark:text-gray-600">|</span>
                <span>
                    {visibleColumnCount} / {totalColumnCount} columns
                </span>
                {activeFilterCount > 0 && (
                    <Badge color="indigo" size="sm">
                        {activeFilterCount} active
                    </Badge>
                )}
            </div>
            <Button type="button" size="sm" color="light" onClick={onOpenSettings}>
                <i className="bx bx-slider-alt me-2" />
                {t('global.advanced_filters')}
            </Button>
        </div>
    );
}
