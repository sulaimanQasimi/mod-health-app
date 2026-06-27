import React, { useEffect, useMemo, useState } from 'react';
import { useHttp } from '@inertiajs/react';
import { useTranslation } from '../../../hooks/useTranslation';

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

const BREAKDOWN_CONFIGS: Array<{ key: BreakdownKey; titleKey: string }> = [
    { key: 'genders', titleKey: 'global.gender' },
    { key: 'discharge_statuses', titleKey: 'global.discharge_status' },
    { key: 'job_types', titleKey: 'global.job_type' },
];

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

const NumberOfHospitalizationsBaseOnDepartment: React.FC<NumberOfHospitalizationsBaseOnDepartmentProps> = ({
    branch_id = '',
    date_from = '',
    date_to = '',
}) => {
    const { t } = useTranslation();
    const [report, setReport] = useState<DepartmentReport[]>([]);
    const [hiddenColumnIds, setHiddenColumnIds] = useState<Set<string>>(() => new Set());
    const { get, processing, setData } = useHttp({
        branch_id: '',
        date_from: '',
        date_to: '',
    });

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
                setHiddenColumnIds(new Set());
            })
            .catch(() => {
                setReport([]);
            });
    }, [branch_id, date_from, date_to, get, setData]);

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

    const removeColumn = (columnId: string) => {
        setHiddenColumnIds((current) => new Set([...current, columnId]));
    };

    const columnGroups = useMemo<ColumnGroup[]>(() => {
        return BREAKDOWN_CONFIGS.map((config) => ({
            key: config.key,
            title: t(config.titleKey),
            columns: FIXED_GROUP_COLUMNS[config.key].map(({ itemKey, labelKey }) => ({
                id: `${config.key}:${itemKey}`,
                itemKey,
                name: t(labelKey),
            })),
        }));
    }, [t]);

    const visibleColumnGroups = useMemo<ColumnGroup[]>(() => {
        return columnGroups
            .map((group) => ({
                ...group,
                columns: group.columns.filter((column) => !hiddenColumnIds.has(column.id)),
            }))
            .filter((group) => group.columns.length > 0);
    }, [columnGroups, hiddenColumnIds]);

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

    const { columnTotals, grandTotal } = useMemo(() => {
        const totals = new Map<string, number>();
        let total = 0;

        report.forEach((department) => {
            total += department.count;

            flatColumns.forEach((column) => {
                const count = getCount(department, column.breakdownKey, column.itemKey);
                totals.set(column.id, (totals.get(column.id) ?? 0) + count);
            });
        });

        return { columnTotals: totals, grandTotal: total };
    }, [flatColumns, report]);

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
                                    onRemove={() => removeColumn(column.id)}
                                />
                            )),
                        )}
                    </tr>
                </thead>
                <tbody>
                    {report.map((department, index) => (
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
                                            columnIndex === 0 ? 'border-s-2 border-s-gray-200 dark:border-s-gray-700' : ''
                                        }`}
                                    >
                                        {getCount(department, group.key, column.itemKey)}
                                    </td>
                                )),
                            )}
                            <td className="px-4 py-2 text-center font-semibold">{department.count}</td>
                        </tr>
                    ))}
                    {flatColumns.length > 0 && (
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
                                            columnIndex === 0 ? 'border-s-2 border-s-gray-300 dark:border-s-gray-600' : ''
                                        }`}
                                    >
                                        {columnTotals.get(column.id) ?? 0}
                                    </td>
                                )),
                            )}
                            <td className="px-4 py-3 text-center text-gray-900 dark:text-white">{grandTotal}</td>
                        </tr>
                    )}
                    {flatColumns.length === 0 && (
                        <tr>
                            <td colSpan={columnCount} className="py-4 px-4 text-center text-gray-500">
                                {t('global.no_data_found')}
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
};

export default NumberOfHospitalizationsBaseOnDepartment;
