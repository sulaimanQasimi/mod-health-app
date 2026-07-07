import React, { useEffect, useMemo, useState } from 'react';
import { useHttp } from '@inertiajs/react';
import { useTranslation } from '../../../hooks/useTranslation';
import GeneralReportTableSettingsModal from './GeneralReportTableSettingsModal';
import GeneralReportTableToolbar from './GeneralReportTableToolbar';
import {
    applyTableSettings,
    buildDepartmentOptions,
    buildMinTotalOptions,
    buildOrderedColumnGroups,
    countActiveTableFilters,
    createDefaultTableSettings,
    GeneralReportTableSettings,
    TableColumnDefinition,
} from './generalReportTableSettings';
import RemovableColumnHeader from './RemovableColumnHeader';
import { buildGeneralReportExportData } from './generalReportExport';

interface BreakdownItem {
    key: string | number | null;
    label: string | null;
    count: number;
}

interface DepartmentReport {
    department_id: number | null;
    department_name: string | null;
    count: number;
    genders: BreakdownItem[];
    discharge_statuses: BreakdownItem[];
    job_types: BreakdownItem[];
}

type BreakdownKey = 'genders' | 'discharge_statuses' | 'job_types';

interface NumberOfHospitalizationsBaseOnDepartmentProps {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const GROUP_CONFIGS: Array<{ key: BreakdownKey; titleKey: string }> = [
    { key: 'genders', titleKey: 'global.gender' },
    { key: 'discharge_statuses', titleKey: 'global.discharge_status' },
    { key: 'job_types', titleKey: 'global.job_type' },
];

const FIXED_GROUP_COLUMNS: Record<
    BreakdownKey,
    Array<{ itemKey: string; labelKey: string }>
> = {
    genders: [
        { itemKey: '0', labelKey: 'global.male' },
        { itemKey: '1', labelKey: 'global.female' },
    ],
    discharge_statuses: [
        { itemKey: 'recovered', labelKey: 'global.recovered' },
        { itemKey: 'died', labelKey: 'global.died' },
        { itemKey: 'moved', labelKey: 'global.moved' },
    ],
    job_types: [
        { itemKey: 'militant', labelKey: 'global.militant' },
        { itemKey: 'civilian', labelKey: 'global.civilian' },
        { itemKey: 'retired', labelKey: 'global.retired' },
    ],
};

const getItemKey = (item: BreakdownItem): string =>
    item.key === null || item.key === undefined || item.key === '' ? 'unknown' : String(item.key);

function buildAllColumns(t: (key: string) => string): TableColumnDefinition[] {
    return GROUP_CONFIGS.flatMap(({ key, titleKey }) =>
        FIXED_GROUP_COLUMNS[key].map(({ itemKey, labelKey }) => ({
            id: `${key}:${itemKey}`,
            itemKey,
            name: t(labelKey),
            groupKey: key,
            groupTitle: t(titleKey),
        })),
    );
}

const NumberOfHospitalizationsBaseOnDepartment: React.FC<NumberOfHospitalizationsBaseOnDepartmentProps> = ({
    branch_id = '',
    date_from = '',
    date_to = '',
}) => {
    const { t } = useTranslation();
    const [report, setReport] = useState<DepartmentReport[]>([]);
    const [tableSettings, setTableSettings] = useState<GeneralReportTableSettings>(() =>
        createDefaultTableSettings([]),
    );
    const [settingsModalOpen, setSettingsModalOpen] = useState(false);
    const { get, processing } = useHttp();

    const allColumns = useMemo(() => buildAllColumns(t), [t]);
    const departmentOptions = useMemo(() => buildDepartmentOptions(report), [report]);
    const minTotalOptions = useMemo(() => buildMinTotalOptions(report, t('global.all')), [report, t]);

    useEffect(() => {
        const params = new URLSearchParams({
            branch_id: branch_id !== '' && branch_id != null ? String(branch_id) : '',
            date_from: date_from ?? '',
            date_to: date_to ?? '',
        });

        get(`/react/api/reports/general/hospitalization?${params.toString()}`)
            .then((response) => {
                const payload = response as { data?: DepartmentReport[] };
                setReport(payload?.data ?? []);
                setTableSettings(createDefaultTableSettings(buildAllColumns(t)));
            })
            .catch(() => {
                setReport([]);
            });
    }, [branch_id, date_from, date_to]);

    const visibleColumnGroups = useMemo(
        () => buildOrderedColumnGroups(tableSettings, allColumns),
        [tableSettings, allColumns],
    );

    const flatColumns = useMemo(
        () =>
            visibleColumnGroups.flatMap((group) =>
                group.columns.map((column) => ({
                    ...column,
                    breakdownKey: group.key as BreakdownKey,
                })),
            ),
        [visibleColumnGroups],
    );

    const getCount = (department: DepartmentReport, breakdownKey: BreakdownKey, itemKey: string): number => {
        const match = department[breakdownKey].find((item) => getItemKey(item) === itemKey);
        return match?.count ?? 0;
    };

    const getColumnCount = (department: DepartmentReport, columnId: string): number => {
        const [groupKey, itemKey] = columnId.split(':') as [BreakdownKey, string];
        return getCount(department, groupKey, itemKey);
    };

    const displayReport = useMemo(
        () => applyTableSettings(report, tableSettings, getColumnCount),
        [report, tableSettings],
    );

    const { columnTotals, grandTotal } = useMemo(() => {
        const totals = new Map<string, number>();
        let total = 0;

        displayReport.forEach((department) => {
            total += department.count;

            flatColumns.forEach((column) => {
                const count = getCount(department, column.breakdownKey, column.itemKey);
                totals.set(column.id, (totals.get(column.id) ?? 0) + count);
            });
        });

        return { columnTotals: totals, grandTotal: total };
    }, [displayReport, flatColumns]);

    const activeFilterCount = useMemo(() => countActiveTableFilters(tableSettings), [tableSettings]);

    const exportData = useMemo(
        () =>
            buildGeneralReportExportData({
                fileName: 'hospitalizations-by-department',
                indexLabel: '#',
                rowLabelColumn: t('global.department'),
                totalLabel: t('global.total'),
                visibleColumnGroups,
                displayReport,
                getRowLabel: (department) => department.department_name ?? 'Unknown',
                getCellValue: (department, columnId) => getColumnCount(department, columnId),
                getRowTotal: (department) => department.count,
                showTotalsRow: tableSettings.showTotalsRow,
                totalsLabel: t('global.total'),
                columnTotals,
                grandTotal,
            }),
        [columnTotals, displayReport, flatColumns.length, grandTotal, t, tableSettings.showTotalsRow, visibleColumnGroups],
    );

    const hideColumn = (columnId: string) => {
        setTableSettings((current) => ({
            ...current,
            hiddenColumnIds: current.hiddenColumnIds.includes(columnId)
                ? current.hiddenColumnIds
                : [...current.hiddenColumnIds, columnId],
        }));
    };

    const removeDepartment = (department: DepartmentReport) => {
        setReport((current) =>
            current.filter((row) => {
                if (department.department_id != null && row.department_id != null) {
                    return row.department_id !== department.department_id;
                }

                return row.department_name !== department.department_name;
            }),
        );
    };

    const columnCount = flatColumns.length + 3;

    if (processing) {
        return (
            <div className="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                {t('global.loading')}...
            </div>
        );
    }

    if (report.length === 0) {
        return (
            <div className="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                {t('global.no_data_found')}
            </div>
        );
    }

    return (
        <>
            <GeneralReportTableToolbar
                visibleRowCount={displayReport.length}
                totalRowCount={report.length}
                visibleColumnCount={flatColumns.length}
                totalColumnCount={allColumns.length}
                activeFilterCount={activeFilterCount}
                onOpenSettings={() => setSettingsModalOpen(true)}
                exportData={exportData}
            />

            <div className="general-report-table-root overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table className="general-report-data-table w-full table-auto border-collapse divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr className="bg-gray-100 dark:bg-gray-800">
                            <th
                                rowSpan={2}
                                className="sticky left-0 z-20 min-w-12 bg-gray-100 px-3 py-3 text-left text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            >
                                #
                            </th>
                            <th
                                rowSpan={2}
                                className="sticky left-12 z-20 min-w-40 bg-gray-100 px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            >
                                {t('global.department')}
                            </th>
                            {visibleColumnGroups.map((group) => (
                                <th
                                    key={group.key}
                                    colSpan={group.columns.length}
                                    className="whitespace-nowrap border-b border-s-2 border-gray-300 bg-gray-100 px-2 py-2 text-center text-xs font-bold uppercase tracking-wide text-indigo-700 dark:border-gray-600 dark:bg-gray-800 dark:text-indigo-300"
                                >
                                    {group.title}
                                </th>
                            ))}
                            <th
                                rowSpan={2}
                                className="bg-gray-100 px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            >
                                {t('global.total')}
                            </th>
                        </tr>
                        <tr className="bg-gray-100 dark:bg-gray-800">
                            {visibleColumnGroups.map((group) =>
                                group.columns.map((column, columnIndex) => (
                                    <RemovableColumnHeader
                                        key={column.id}
                                        columnName={column.name}
                                        removeLabel={t('global.remove')}
                                        isFirstInGroup={columnIndex === 0}
                                        onRemove={() => hideColumn(column.id)}
                                    />
                                )),
                            )}
                        </tr>
                    </thead>
                    <tbody>
                        {displayReport.length > 0 ? (
                            displayReport.map((department, index) => (
                                <tr
                                    key={department.department_id ?? department.department_name ?? index}
                                    className="border-t border-gray-200 dark:border-gray-700"
                                >
                                    <td className="sticky left-0 z-10 bg-white px-2 py-2 dark:bg-gray-900">
                                        <button
                                            type="button"
                                            onClick={() => removeDepartment(department)}
                                            title={t('global.remove')}
                                            aria-label={`${t('global.remove')} ${department.department_name ?? 'Unknown'}`}
                                            className="group/row-action relative mx-auto flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium text-gray-700 transition hover:bg-red-50 dark:text-gray-200 dark:hover:bg-red-950/30"
                                        >
                                            <span className="transition-opacity group-hover/row-action:opacity-0">
                                                {index + 1}
                                            </span>
                                            <i className="bx bx-trash absolute text-lg text-red-500 opacity-0 transition-opacity group-hover/row-action:opacity-100" />
                                        </button>
                                    </td>
                                    <td className="sticky left-12 z-10 bg-white px-4 py-2 font-medium dark:bg-gray-900">
                                        {department.department_name ?? 'Unknown'}
                                    </td>
                                    {visibleColumnGroups.map((group) =>
                                        group.columns.map((column, columnIndex) => (
                                            <td
                                                key={column.id}
                                                className={`min-w-12 px-2 py-2 text-center text-sm text-gray-700 dark:text-gray-300 ${
                                                    columnIndex === 0
                                                        ? 'border-s-2 border-s-gray-200 dark:border-s-gray-700'
                                                        : ''
                                                }`}
                                            >
                                                {getCount(
                                                    department,
                                                    group.key as BreakdownKey,
                                                    column.itemKey,
                                                )}
                                            </td>
                                        )),
                                    )}
                                    <td className="px-4 py-2 text-center font-semibold">{department.count}</td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={columnCount} className="py-8 px-4 text-center text-gray-500">
                                    {t('global.no_data_found')}
                                </td>
                            </tr>
                        )}

                        {tableSettings.showTotalsRow && flatColumns.length > 0 && displayReport.length > 0 && (
                            <tr className="border-t-2 border-gray-300 bg-gray-50 font-semibold dark:border-gray-600 dark:bg-gray-800/80">
                                <td className="sticky left-0 z-10 bg-gray-50 px-2 py-3 dark:bg-gray-800/80" />
                                <td className="sticky left-12 z-10 bg-gray-50 px-4 py-3 text-right text-gray-900 dark:bg-gray-800/80 dark:text-white">
                                    {t('global.total')}
                                </td>
                                {visibleColumnGroups.map((group) =>
                                    group.columns.map((column, columnIndex) => (
                                        <td
                                            key={column.id}
                                            className={`min-w-12 px-2 py-3 text-center text-sm text-gray-900 dark:text-white ${
                                                columnIndex === 0
                                                    ? 'border-s-2 border-s-gray-300 dark:border-s-gray-600'
                                                    : ''
                                            }`}
                                        >
                                            {columnTotals.get(column.id) ?? 0}
                                        </td>
                                    )),
                                )}
                                <td className="px-4 py-3 text-center text-gray-900 dark:text-white">{grandTotal}</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <GeneralReportTableSettingsModal
                open={settingsModalOpen}
                settings={tableSettings}
                allColumns={allColumns}
                departmentOptions={departmentOptions}
                minTotalOptions={minTotalOptions}
                onClose={() => setSettingsModalOpen(false)}
                onApply={(nextSettings) => {
                    setTableSettings(nextSettings);
                    setSettingsModalOpen(false);
                }}
            />
        </>
    );
};

export default NumberOfHospitalizationsBaseOnDepartment;
