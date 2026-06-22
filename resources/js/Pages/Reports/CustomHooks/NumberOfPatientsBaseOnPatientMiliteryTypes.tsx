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
            <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr className="bg-gray-100">
                            <th className="py-2 px-4 text-left">Department</th>
                            {militeryTypeColumns.map((column) => (
                                <th key={String(column.id)} className="py-2 px-4 text-left">
                                    {column.name}
                                </th>
                            ))}
                            <th className="py-2 px-4 text-left">Total</th>
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
                                <tr key={department.department_id ?? department.department_name} className="border-t">
                                    <td className="py-2 px-4 font-medium">{department.department_name ?? 'Unknown'}</td>
                                    {militeryTypeColumns.map((column) => (
                                        <td key={String(column.id)} className="py-2 px-4">
                                            {getCountForType(department, column.id)}
                                        </td>
                                    ))}
                                    <td className="py-2 px-4 font-medium">{department.count}</td>
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
