import React, { useEffect, useState } from 'react';
import { useHttp } from '@inertiajs/react';
import { useTranslation } from '../../../hooks/useTranslation';

interface DepartmentReport {
    department_id: number | null;
    department_name: string | null;
    count: number;
}

interface NumberOfPatientsBaseOnDepartmentProps {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
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

    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr className="bg-gray-100">
                            <th className="py-2 px-4 text-right">{t('global.number')}</th>
                            <th className="py-2 px-4 text-right">{t('global.department')}</th>
                            <th className="py-2 px-4 text-left">{t('global.count')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {processing ? (
                            <tr>
                                <td colSpan={2} className="py-4 px-4 text-center text-gray-500">
                                    Loading...
                                </td>
                            </tr>
                        ) : report.length > 0 ? (
                            report.map((item, index) => (
                                <tr key={item.department_id ?? item.department_name} className="border-t">
                                    <td className="py-2 px-4 text-right">{index + 1}</td>
                                    <td className="py-2 px-4">{item.department_name}</td>
                                    <td className="py-2 px-4">{item.count}</td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={3} className="py-4 px-4 text-center text-gray-500">
                                    <div className="flex items-center justify-center w-full">
                                        <span className="h-5 w-48 bg-gray-200 dark:bg-gray-700 rounded animate-pulse inline-block" />
                                    </div>
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
        </div>
    );
};

export default NumberOfPatientsBaseOnDepartment;
