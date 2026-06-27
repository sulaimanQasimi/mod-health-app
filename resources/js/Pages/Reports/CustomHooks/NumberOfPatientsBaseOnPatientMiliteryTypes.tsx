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

interface MiliteryTypeReport {
    militery_type_id: number | null;
    militery_type_name: string | null;
    count: number;
}

interface DepartmentMiliteryReport {
    department_id: number | null;
    department_name: string | null;
    count: number;
    militery_types: MiliteryTypeReport[];
}

interface NumberOfPatientsBaseOnPatientMiliteryTypesProps {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const GROUP_KEY = 'militery_types';

const getMiliteryTypeKey = (militeryTypeId: number | null): string =>
    String(militeryTypeId ?? 'unknown');

function buildAllColumns(report: DepartmentMiliteryReport[], groupTitle: string): TableColumnDefinition[] {
    const columns = new Map<string, string>();

    report.forEach((department) => {
        department.militery_types.forEach((militeryType) => {
            const key = getMiliteryTypeKey(militeryType.militery_type_id);
            if (!columns.has(key)) {
                columns.set(key, militeryType.militery_type_name ?? 'Unknown');
            }
        });
    });

    return Array.from(columns.entries())
        .sort((left, right) => left[1].localeCompare(right[1]))
        .map(([itemKey, name]) => ({
            id: `${GROUP_KEY}:${itemKey}`,
            itemKey,
            name,
            groupKey: GROUP_KEY,
            groupTitle,
        }));
}

const NumberOfPatientsBaseOnPatientMiliteryTypes: React.FC<NumberOfPatientsBaseOnPatientMiliteryTypesProps> = ({
    branch_id = '',
    date_from = '',
    date_to = '',
}) => {
    const { t } = useTranslation();
    const [report, setReport] = useState<DepartmentMiliteryReport[]>([]);
    const [tableSettings, setTableSettings] = useState<GeneralReportTableSettings>(() =>
        createDefaultTableSettings([]),
    );
    const [settingsModalOpen, setSettingsModalOpen] = useState(false);
    const { get, processing } = useHttp();

    const groupTitle = t('global.militery_types');
    const allColumns = useMemo(() => buildAllColumns(report, groupTitle), [groupTitle, report]);
    const departmentOptions = useMemo(() => buildDepartmentOptions(report), [report]);
    const minTotalOptions = useMemo(() => buildMinTotalOptions(report, t('global.all')), [report, t]);

    useEffect(() => {
        const params = new URLSearchParams({
            branch_id: branch_id !== '' && branch_id != null ? String(branch_id) : '',
            date_from: date_from ?? '',
            date_to: date_to ?? '',
        });

        get(`/react/api/reports/general/number-of-patients-base-on-patient-militery-types?${params.toString()}`)
            .then((response) => {
                const payload = response as { data?: DepartmentMiliteryReport[] };
                const data = payload?.data ?? [];
                setReport(data);
                setTableSettings(createDefaultTableSettings(buildAllColumns(data, groupTitle)));
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
                    breakdownKey: group.key,
                })),
            ),
        [visibleColumnGroups],
    );

    const getCountForType = (department: DepartmentMiliteryReport, itemKey: string): number => {
        const match = department.militery_types.find(
            (militeryType) => getMiliteryTypeKey(militeryType.militery_type_id) === itemKey,
        );

        return match?.count ?? 0;
    };

    const getColumnCount = (department: DepartmentMiliteryReport, columnId: string): number => {
        const itemKey = columnId.replace(`${GROUP_KEY}:`, '');
        return getCountForType(department, itemKey);
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
                const count = getCountForType(department, column.itemKey);
                totals.set(column.id, (totals.get(column.id) ?? 0) + count);
            });
        });

        return { columnTotals: totals, grandTotal: total };
    }, [displayReport, flatColumns]);

    const activeFilterCount = useMemo(() => countActiveTableFilters(tableSettings), [tableSettings]);

    const hideColumn = (columnId: string) => {
        setTableSettings((current) => ({
            ...current,
            hiddenColumnIds: current.hiddenColumnIds.includes(columnId)
                ? current.hiddenColumnIds
                : [...current.hiddenColumnIds, columnId],
        }));
    };

    const removeDepartment = (department: DepartmentMiliteryReport) => {
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
                {t('global.no_data_available')}
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
                                                {getCountForType(department, column.itemKey)}
                                            </td>
                                        )),
                                    )}
                                    <td className="px-4 py-2 text-center font-semibold">{department.count}</td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={columnCount} className="py-8 px-4 text-center text-gray-500">
                                    {t('global.no_data_available')}
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

export default NumberOfPatientsBaseOnPatientMiliteryTypes;
