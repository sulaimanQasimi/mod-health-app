import { Link } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { DataTable } from 'simple-datatables';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PaginatedPatients,
    PatientIndexPermissions,
    PatientIndexUrls,
} from '../../types/patient';
import LaravelPagination from './LaravelPagination';

interface PatientsDataTableProps {
    patients: PaginatedPatients;
    permissions: PatientIndexPermissions;
    urls: PatientIndexUrls;
}

function SortIcon() {
    return (
        <svg
            className="ms-1 h-4 w-4"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
        >
            <path
                stroke="currentColor"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="m8 15 4 4 4-4m0-6-4-4-4 4"
            />
        </svg>
    );
}

export default function PatientsDataTable({ patients, permissions, urls }: PatientsDataTableProps) {
    const { t } = useTranslation();
    const tableRef = useRef<HTMLTableElement>(null);
    const dataTableRef = useRef<DataTable | null>(null);

    useEffect(() => {
        const tableEl = tableRef.current;
        if (!tableEl || patients.data.length === 0) {
            return undefined;
        }

        dataTableRef.current?.destroy();

        dataTableRef.current = new DataTable(tableEl, {
            searchable: false,
            paging: false,
            perPageSelect: false,
            sortable: true,
            columns: [{ select: 10, sortable: false }],
        });

        return () => {
            dataTableRef.current?.destroy();
            dataTableRef.current = null;
        };
    }, [patients.data]);

    const summaryLabel =
        patients.meta.from && patients.meta.to
            ? `${t('global.showing')} ${patients.meta.from}-${patients.meta.to} ${t('global.of')} ${patients.meta.total} ${t('global.results')}`
            : `${patients.meta.total} ${t('global.results')}`;

    return (
        <div className="relative overflow-x-auto">
            <table
                ref={tableRef}
                id="patients-table"
                className="w-full text-left text-sm text-gray-500 dark:text-gray-400"
            >
                <thead className="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.id')} <SortIcon /></span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.id_card')} <SortIcon /></span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.name')} <SortIcon /></span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.last_name')} <SortIcon /></span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.father_name')} <SortIcon /></span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">
                                {t('global.province')} / {t('global.district')} <SortIcon />
                            </span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.age')} <SortIcon /></span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.militery_type')} <SortIcon /></span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.phone')} <SortIcon /></span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.created_by')} <SortIcon /></span>
                        </th>
                        <th scope="col" className="px-4 py-3">
                            <span className="flex items-center">{t('global.actions')}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {patients.data.length === 0 ? (
                        <tr className="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                            <td colSpan={11} className="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                {t('global.no_results_found')}
                            </td>
                        </tr>
                    ) : (
                        patients.data.map((patient) => (
                            <tr
                                key={patient.id}
                                className="border-b bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/50"
                            >
                                <td className="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {patient.id}
                                </td>
                                <td className="px-4 py-3">{patient.id_card ?? '-'}</td>
                                <td className="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {patient.name}
                                </td>
                                <td className="px-4 py-3">{patient.last_name ?? '-'}</td>
                                <td className="px-4 py-3">{patient.father_name ?? '-'}</td>
                                <td className="px-4 py-3">{patient.location}</td>
                                <td className="px-4 py-3">{patient.age ?? '-'}</td>
                                <td className="px-4 py-3">{patient.militery_type ?? '-'}</td>
                                <td className="px-4 py-3">{patient.phone ?? '-'}</td>
                                <td className="px-4 py-3">{patient.created_by ?? '-'}</td>
                                <td className="px-4 py-3">
                                    <div className="flex items-center gap-1">
                                        <Link
                                            href={`${urls.show}/${patient.id}`}
                                            className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                            title={t('global.view')}
                                        >
                                            <i className="bx bx-expand text-lg" />
                                        </Link>
                                        {permissions.edit && (
                                            <Link
                                                href={`${urls.edit}/${patient.id}`}
                                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                title={t('global.edit')}
                                            >
                                                <i className="bx bx-edit text-lg" />
                                            </Link>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        ))
                    )}
                </tbody>
            </table>

            <LaravelPagination links={patients.links} meta={patients.meta} summaryLabel={summaryLabel} />
        </div>
    );
}
