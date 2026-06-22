import React, { useEffect, useMemo, useState } from 'react';
import { useHttp } from '@inertiajs/react';

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

interface MiliteryTypeColumn {
    id: string | number;
    name: string;
}

interface NumberOfPatientsBaseOnPatientMiliteryTypesProps {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const getMiliteryTypeKey = (militeryTypeId: number | null): string | number =>
    militeryTypeId ?? 'unknown';

const NumberOfPatientsBaseOnPatientMiliteryTypes: React.FC<NumberOfPatientsBaseOnPatientMiliteryTypesProps> = ({
    branch_id = '',
    date_from = '',
    date_to = '',
}) => {
    const [report, setReport] = useState<DepartmentMiliteryReport[]>([]);
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

        get('/react/api/reports/general/number-of-patients-base-on-patient-militery-types')
            .then((response) => {
                const payload = response as { data?: DepartmentMiliteryReport[] };
                setReport(payload?.data ?? []);
            })
            .catch(() => {
                setReport([]);
            });
    }, [branch_id, date_from, date_to, get, setData]);

    const militeryTypeColumns = useMemo<MiliteryTypeColumn[]>(() => {
        const columns = new Map<string | number, string>();

        report.forEach((department) => {
            department.militery_types.forEach((militeryType) => {
                const key = getMiliteryTypeKey(militeryType.militery_type_id);

                if (!columns.has(key)) {
                    columns.set(key, militeryType.militery_type_name ?? 'Unknown');
                }
            });
        });

        return Array.from(columns.entries())
            .map(([id, name]) => ({ id, name }))
            .sort((a, b) => a.name.localeCompare(b.name));
    }, [report]);

    const getCountForType = (department: DepartmentMiliteryReport, typeId: string | number): number => {
        const match = department.militery_types.find(
            (militeryType) => getMiliteryTypeKey(militeryType.militery_type_id) === typeId,
        );

        return match?.count ?? 0;
    };

    const columnCount = militeryTypeColumns.length + 2;

    return (
        <div className="overflow-x-auto">
            <table className="min-w-full border-collapse divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr className="bg-gray-100 dark:bg-gray-800">
                        <th className="sticky left-0 z-10 bg-gray-100 px-4 py-3 text-left align-bottom text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            Department
                        </th>
                        {militeryTypeColumns.map((column) => (
                            <th
                                key={String(column.id)}
                                className="h-32 w-12 min-w-12 max-w-14 border-b border-gray-200 bg-gray-100 p-0 align-bottom dark:border-gray-700 dark:bg-gray-800"
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
                        <th className="px-4 py-3 text-left align-bottom text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Total
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {processing ? (
                        <tr>
                            <td colSpan={columnCount} className="py-4 px-4 text-center text-gray-500">
                                Loading...
                            </td>
                        </tr>
                    ) : report.length > 0 ? (
                        report.map((department) => (
                            <tr
                                key={department.department_id ?? department.department_name}
                                className="border-t border-gray-200 dark:border-gray-700"
                            >
                                <td className="sticky left-0 z-10 bg-white px-4 py-2 font-medium dark:bg-gray-900">
                                    {department.department_name ?? 'Unknown'}
                                </td>
                                {militeryTypeColumns.map((column) => (
                                    <td
                                        key={String(column.id)}
                                        className="w-12 min-w-12 px-2 py-2 text-center text-sm text-gray-700 dark:text-gray-300"
                                    >
                                        {getCountForType(department, column.id)}
                                    </td>
                                ))}
                                <td className="px-4 py-2 text-center font-medium">{department.count}</td>
                            </tr>
                        ))
                    ) : (
                        <tr>
                            <td colSpan={columnCount} className="py-4 px-4 text-center text-gray-500">
                                No data available.
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
};

export default NumberOfPatientsBaseOnPatientMiliteryTypes;
