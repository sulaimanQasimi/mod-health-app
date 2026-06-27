import React, { useEffect, useMemo, useState } from 'react';
import { useHttp } from '@inertiajs/react';
import { Badge, Button } from 'flowbite-react';
import { useTranslation } from '../../../hooks/useTranslation';
import HospitalizationReportTableSettingsModal from './HospitalizationReportTableSettingsModal';
import {
    BREAKDOWN_CONFIGS,
    BreakdownKey,
    createDefaultHospitalizationTableSettings,
    FIXED_GROUP_COLUMNS,
    HospitalizationTableSettings,
    TableColumnDefinition,
} from './hospitalizationReportTableSettings';

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

interface ColumnGroup {
    key: BreakdownKey;
    title: string;
    columns: Array<{ id: string; itemKey: string; name: string }>;
}

interface NumberOfHospitalizationsBaseOnDepartmentProps {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const getItemKey = (item: BreakdownItem): string =>
    item.key === null || item.key === undefined || item.key === '' ? 'unknown' : String(item.key);

function RemovableColumnHeader({
    columnName,
    onRemove,
    removeLabel,
    isFirstInGroup,
}: {
    columnName: string;
    onRemove: () => void;
    removeLabel: string;
    isFirstInGroup: boolean;
}) {
    return (
        <th
            className={`h-28 min-w-12 border-b border-gray-200 bg-gray-100 p-0 align-middle text-center dark:border-gray-700 dark:bg-gray-800 ${
                isFirstInGroup ? 'border-s-2 border-s-gray-300 dark:border-s-gray-600' : ''
            }`}
        >
            <button
                type="button"
                onClick={onRemove}
                title={removeLabel}
                aria-label={`${removeLabel} ${columnName}`}
                className="group/col-action relative flex h-full w-full items-center justify-center overflow-hidden transition hover:bg-red-50 dark:hover:bg-red-950/30"
            >
                <span
                    className="inline-block origin-center -rotate-90 whitespace-nowrap text-xs font-semibold leading-none text-gray-700 transition-opacity group-hover/col-action:opacity-0 dark:text-gray-200"
                    title={columnName}
                >
                    {columnName}
                </span>
                <i className="bx bx-trash absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-lg text-red-500 opacity-0 transition-opacity group-hover/col-action:opacity-100" />
            </button>
        </th>
    );
}

function buildAllColumns(t: (key: string) => string): TableColumnDefinition[] {
    return BREAKDOWN_CONFIGS.flatMap(({ key, titleKey }) =>
        FIXED_GROUP_COLUMNS[key].map(({ itemKey, labelKey }) => ({
            id: `${key}:${itemKey}`,
            itemKey,
            name: t(labelKey),
            groupKey: key,
            groupTitle: t(titleKey),
        })),
    );
}

function buildOrderedColumnGroups(
    settings: HospitalizationTableSettings,
    t: (key: string) => string,
): ColumnGroup[] {
    return settings.groupOrder
        .map((groupKey) => {
            const config = BREAKDOWN_CONFIGS.find((item) => item.key === groupKey);
            if (!config) {
                return null;
            }

            const columns = (settings.columnOrderByGroup[groupKey] ?? [])
                .map((columnId) => {
                    const columnMeta = FIXED_GROUP_COLUMNS[groupKey].find(
                        ({ itemKey }) => `${groupKey}:${itemKey}` === columnId,
                    );
                    if (!columnMeta || settings.hiddenColumnIds.includes(columnId)) {
                        return null;
                    }

                    return {
                        id: columnId,
                        itemKey: columnMeta.itemKey,
                        name: t(columnMeta.labelKey),
                    };
                })
                .filter((column): column is { id: string; itemKey: string; name: string } => column !== null);

            if (columns.length === 0) {
                return null;
            }

            return {
                key: groupKey,
                title: t(config.titleKey),
                columns,
            };
        })
        .filter((group): group is ColumnGroup => group !== null);
}

const NumberOfHospitalizationsBaseOnDepartment: React.FC<NumberOfHospitalizationsBaseOnDepartmentProps> = ({
    branch_id = '',
    date_from = '',
    date_to = '',
}) => {
    const { t } = useTranslation();
    const [report, setReport] = useState<DepartmentReport[]>([]);
    const [tableSettings, setTableSettings] = useState<HospitalizationTableSettings>(
        createDefaultHospitalizationTableSettings,
    );
    const [settingsModalOpen, setSettingsModalOpen] = useState(false);
    const { get, processing, setData } = useHttp({
        branch_id: '',
        date_from: '',
        date_to: '',
    });

    const allColumns = useMemo(() => buildAllColumns(t), [t]);

    const departmentOptions = useMemo(
        () =>
            report.map((row) => ({
                value: String(row.department_id ?? row.department_name ?? ''),
                label: row.department_name ?? 'Unknown',
            })),
        [report],
    );

    const minTotalOptions = useMemo(() => {
        const totals = [...new Set(report.map((row) => row.count))].sort((left, right) => left - right);

        return [
            { value: '0', label: t('global.all') },
            ...totals
                .filter((total) => total > 0)
                .map((total) => ({
                    value: String(total),
                    label: `>= ${total}`,
                })),
        ];
    }, [report, t]);

    useEffect(() => {
        setData({
            branch_id: branch_id !== '' && branch_id != null ? String(branch_id) : '',
            date_from: date_from ?? '',
            date_to: date_to ?? '',
        });

        get('/react/api/reports/general/hospitalization')
            .then((response) => {
                const payload = response as { data?: DepartmentReport[] };
                setReport(payload?.data ?? []);
                setTableSettings(createDefaultHospitalizationTableSettings());
            })
            .catch(() => {
                setReport([]);
            });
    }, [branch_id, date_from, date_to, get, setData]);

    const visibleColumnGroups = useMemo(
        () => buildOrderedColumnGroups(tableSettings, t),
        [tableSettings, t],
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

    const getCount = (department: DepartmentReport, breakdownKey: BreakdownKey, itemKey: string): number => {
        const match = department[breakdownKey].find((item) => getItemKey(item) === itemKey);
        return match?.count ?? 0;
    };

    const displayReport = useMemo(() => {
        let rows = [...report];

        if (tableSettings.departmentFilter) {
            rows = rows.filter(
                (row) =>
                    String(row.department_id ?? row.department_name ?? '') === tableSettings.departmentFilter,
            );
        }

        if (tableSettings.hideZeroRows) {
            rows = rows.filter((row) => row.count > 0);
        }

        if (tableSettings.minTotal > 0) {
            rows = rows.filter((row) => row.count >= tableSettings.minTotal);
        }

        rows.sort((left, right) => {
            let comparison = 0;

            if (tableSettings.sortBy === 'department') {
                comparison = (left.department_name ?? '').localeCompare(right.department_name ?? '');
            } else if (tableSettings.sortBy === 'total') {
                comparison = left.count - right.count;
            } else if (tableSettings.sortBy.startsWith('column:')) {
                const columnId = tableSettings.sortBy.replace('column:', '');
                const [groupKey, itemKey] = columnId.split(':') as [BreakdownKey, string];
                comparison =
                    getCount(left, groupKey, itemKey) - getCount(right, groupKey, itemKey);
            }

            return tableSettings.sortDirection === 'asc' ? comparison : -comparison;
        });

        return rows;
    }, [report, tableSettings]);

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

    const activeFilterCount = useMemo(() => {
        let count = 0;

        if (tableSettings.departmentFilter) {
            count += 1;
        }
        if (tableSettings.hideZeroRows) {
            count += 1;
        }
        if (tableSettings.minTotal > 0) {
            count += 1;
        }
        if (tableSettings.hiddenColumnIds.length > 0) {
            count += 1;
        }
        if (tableSettings.sortBy !== 'department' || tableSettings.sortDirection !== 'asc') {
            count += 1;
        }
        if (!tableSettings.showTotalsRow) {
            count += 1;
        }

        return count;
    }, [tableSettings]);

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
            <div className="general-report-no-print mb-3 flex flex-wrap items-center justify-between gap-3">
                <div className="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <span>
                        {displayReport.length} / {report.length} {t('global.department')}
                    </span>
                    <span className="text-gray-300 dark:text-gray-600">|</span>
                    <span>
                        {flatColumns.length} / {allColumns.length} columns
                    </span>
                    {activeFilterCount > 0 && (
                        <Badge color="indigo" size="sm">
                            {activeFilterCount} active
                        </Badge>
                    )}
                </div>
                <Button
                    type="button"
                    size="sm"
                    color="light"
                    onClick={() => setSettingsModalOpen(true)}
                >
                    <i className="bx bx-slider-alt me-2" />
                    {t('global.advanced_filters')}
                </Button>
            </div>

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
                                                {getCount(department, group.key, column.itemKey)}
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

            <HospitalizationReportTableSettingsModal
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
