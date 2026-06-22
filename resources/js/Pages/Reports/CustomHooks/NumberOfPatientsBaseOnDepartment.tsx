import React, { useCallback, useEffect, useMemo, useState } from 'react';
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
    job_categories: BreakdownItem[];
    job_types: BreakdownItem[];
    patient_types: BreakdownItem[];
    militery_types: BreakdownItem[];
    relations: BreakdownItem[];
    commanded_by: BreakdownItem[];
}

interface BreakdownColumn {
    id: string;
    name: string;
}

type BreakdownKey =
    | 'genders'
    | 'job_categories'
    | 'job_types'
    | 'patient_types'
    | 'militery_types'
    | 'relations'
    | 'commanded_by';

interface BreakdownConfig {
    key: BreakdownKey;
    titleKey: string;
}

interface NumberOfPatientsBaseOnDepartmentProps {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const BREAKDOWN_CONFIGS: BreakdownConfig[] = [
    { key: 'genders', titleKey: 'global.gender' },
    { key: 'job_categories', titleKey: 'global.job_category' },
    { key: 'job_types', titleKey: 'global.job_type' },
    { key: 'patient_types', titleKey: 'global.type' },
    { key: 'militery_types', titleKey: 'global.militery_types' },
    { key: 'relations', titleKey: 'global.relation' },
    { key: 'commanded_by', titleKey: 'global.commanded_by' },
];

const getItemKey = (item: BreakdownItem): string =>
    item.key === null || item.key === undefined || item.key === '' ? 'unknown' : String(item.key);

const isStringLabel = (label: BreakdownItem['label']): label is string =>
    typeof label === 'string' && label.trim() !== '';

function DepartmentBreakdownTable({
    title,
    departments,
    breakdownKey,
    resolveColumnLabel,
    onRemoveDepartment,
    removeLabel,
    departmentLabel,
    totalLabel,
    numberLabel,
}: {
    title: string;
    departments: DepartmentReport[];
    breakdownKey: BreakdownKey;
    resolveColumnLabel: (item: BreakdownItem) => string;
    onRemoveDepartment: (department: DepartmentReport) => void;
    removeLabel: string;
    departmentLabel: string;
    totalLabel: string;
    numberLabel: string;
}) {
    const columns = useMemo<BreakdownColumn[]>(() => {
        const map = new Map<string, string>();

        departments.forEach((department) => {
            department[breakdownKey].forEach((item) => {
                const key = getItemKey(item);
                if (!map.has(key)) {
                    map.set(key, String(resolveColumnLabel(item)));
                }
            });
        });

        return Array.from(map.entries())
            .map(([id, name]) => ({ id, name }))
            .sort((a, b) => a.name.localeCompare(b.name, undefined, { numeric: true }));
    }, [breakdownKey, departments, resolveColumnLabel]);

    const getCount = (department: DepartmentReport, columnId: string): number => {
        const match = department[breakdownKey].find((item) => getItemKey(item) === columnId);
        return match?.count ?? 0;
    };

    if (departments.length === 0 || columns.length === 0) {
        return null;
    }

    return (
        <div className="space-y-3">
            <h4 className="text-sm font-semibold text-gray-900 dark:text-white">{title}</h4>
            <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table className="min-w-full border-collapse divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr className="bg-gray-100 dark:bg-gray-800">
                            <th className="sticky left-0 z-10 bg-gray-100 px-3 py-3 text-left text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                {numberLabel}
                            </th>
                            <th className="sticky left-10 z-10 bg-gray-100 px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                {departmentLabel}
                            </th>
                            {columns.map((column) => (
                                <th
                                    key={column.id}
                                    className="h-28 w-12 min-w-12 max-w-14 border-b border-gray-200 bg-gray-100 p-0 align-bottom dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <div className="flex h-full items-end justify-center overflow-hidden pb-3">
                                        <span
                                            className="inline-block max-w-28 origin-bottom-left -rotate-90 whitespace-nowrap text-xs font-semibold leading-none text-gray-700 dark:text-gray-200"
                                            title={column.name}
                                        >
                                            {column.name}
                                        </span>
                                    </div>
                                </th>
                            ))}
                            <th className="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">
                                {totalLabel}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {departments.map((department, index) => (
                            <tr
                                key={department.department_id ?? department.department_name ?? index}
                                className="border-t border-gray-200 dark:border-gray-700"
                            >
                                <td className="sticky left-0 z-10 bg-white px-2 py-2 dark:bg-gray-900">
                                    <button
                                        type="button"
                                        onClick={() => onRemoveDepartment(department)}
                                        title={removeLabel}
                                        aria-label={`${removeLabel} ${department.department_name ?? 'Unknown'}`}
                                        className="group/row-action relative mx-auto flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium text-gray-700 transition hover:bg-red-50 dark:text-gray-200 dark:hover:bg-red-950/30"
                                    >
                                        <span className="transition-opacity group-hover/row-action:opacity-0">
                                            {index + 1}
                                        </span>
                                        <i className="bx bx-trash absolute text-lg text-red-500 opacity-0 transition-opacity group-hover/row-action:opacity-100" />
                                    </button>
                                </td>
                                <td className="sticky left-10 z-10 bg-white px-4 py-2 font-medium dark:bg-gray-900">
                                    {department.department_name ?? 'Unknown'}
                                </td>
                                {columns.map((column) => (
                                    <td
                                        key={column.id}
                                        className="w-12 min-w-12 px-2 py-2 text-center text-sm text-gray-700 dark:text-gray-300"
                                    >
                                        {getCount(department, column.id)}
                                    </td>
                                ))}
                                <td className="px-4 py-2 text-center font-medium">{department.count}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

const NumberOfPatientsBaseOnDepartment: React.FC<NumberOfPatientsBaseOnDepartmentProps> = ({
    branch_id = '',
    date_from = '',
    date_to = '',
}) => {
    const { t } = useTranslation();
    const [report, setReport] = useState<DepartmentReport[]>([]);
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

        get('/react/api/reports/general/number-of-patients-base-on-department')
            .then((response) => {
                const payload = response as { data?: DepartmentReport[] };
                setReport(payload?.data ?? []);
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

    const resolveGenderLabel = useCallback(
        (item: BreakdownItem) => {
            if (isStringLabel(item.label)) {
                return item.label;
            }
            return String(item.key) === '1' ? t('global.female') : t('global.male');
        },
        [t],
    );

    const resolveJobCategoryLabel = useCallback(
        (item: BreakdownItem) => {
            if (isStringLabel(item.label)) {
                return item.label;
            }
            return String(item.key) === '0' ? t('global.military') : t('global.civilian');
        },
        [t],
    );

    const resolveJobTypeLabel = useCallback(
        (item: BreakdownItem) => {
            if (isStringLabel(item.label)) {
                return item.label;
            }
            const map: Record<string, string> = {
                militant: t('global.militant'),
                civilian: t('global.civilian'),
                retired: t('global.retired'),
            };
            return map[String(item.key)] ?? String(item.key ?? 'Unknown');
        },
        [t],
    );

    const resolvePatientTypeLabel = useCallback(
        (item: BreakdownItem) => {
            if (isStringLabel(item.label)) {
                return item.label;
            }
            const map: Record<string, string> = {
                '0': t('global.mod'),
                '1': t('global.recipient'),
                '2': t('global.family'),
                '3': t('global.extraordinary'),
            };
            return map[String(item.key)] ?? String(item.key ?? 'Unknown');
        },
        [t],
    );

    const resolveNamedLabel = useCallback(
        (item: BreakdownItem) =>
            isStringLabel(item.label) ? item.label : String(item.key ?? 'Unknown'),
        [],
    );

    const labelResolvers: Record<BreakdownKey, (item: BreakdownItem) => string> = useMemo(
        () => ({
            genders: resolveGenderLabel,
            job_categories: resolveJobCategoryLabel,
            job_types: resolveJobTypeLabel,
            patient_types: resolvePatientTypeLabel,
            militery_types: resolveNamedLabel,
            relations: resolveNamedLabel,
            commanded_by: resolveNamedLabel,
        }),
        [
            resolveGenderLabel,
            resolveJobCategoryLabel,
            resolveJobTypeLabel,
            resolvePatientTypeLabel,
            resolveNamedLabel,
        ],
    );

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
        <div className="space-y-8">
            {BREAKDOWN_CONFIGS.map((config) => (
                <DepartmentBreakdownTable
                    key={config.key}
                    title={t(config.titleKey)}
                    departments={report}
                    breakdownKey={config.key}
                    resolveColumnLabel={labelResolvers[config.key]}
                    onRemoveDepartment={removeDepartment}
                    removeLabel={t('global.remove')}
                    departmentLabel={t('global.department')}
                    totalLabel={t('global.total')}
                    numberLabel={t('global.number')}
                />
            ))}
        </div>
    );
};

export default NumberOfPatientsBaseOnDepartment;
