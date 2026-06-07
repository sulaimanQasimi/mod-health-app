import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PatientFilters from '../../Components/Patients/PatientFilters';
import PatientsDataTable from '../../Components/Patients/PatientsDataTable';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PaginatedPatients,
    PatientIndexFilterOptions,
    PatientIndexFilters,
    PatientIndexPermissions,
    PatientIndexUrls,
} from '../../types/patient';

interface IndexPatientProps {
    patients: PaginatedPatients;
    filters: PatientIndexFilters;
    filterOptions: PatientIndexFilterOptions;
    permissions: PatientIndexPermissions;
    urls: PatientIndexUrls;
}

const EMPTY_FILTERS: PatientIndexFilters = {
    name: '',
    father_name: '',
    last_name: '',
    phone: '',
    card_search: '',
    militery_type_id: '',
    province_id: '',
    gender: '',
    job_category: '',
};

function cleanFilters(filters: PatientIndexFilters): Record<string, string> {
    return Object.fromEntries(
        Object.entries(filters).filter(([, value]) => value !== ''),
    );
}

export default function IndexPatient({
    patients,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: IndexPatientProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<PatientIndexFilters>(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: PatientIndexFilters) => {
            setProcessing(true);
            router.get(urls.index, cleanFilters(nextFilters), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.index],
    );

    const updateFilter = (field: keyof PatientIndexFilters, value: string) => {
        setFilters((current) => ({
            ...current,
            [field]: value,
        }));
    };

    const handleSelectChange = (field: keyof PatientIndexFilters, value: string) => {
        const nextFilters = {
            ...filters,
            [field]: value,
        };
        setFilters(nextFilters);
        applyFilters(nextFilters);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        applyFilters(EMPTY_FILTERS);
    };

    return (
        <DashboardLayout>
            <Head title={t('global.patients_list')} />

            <div className="mx-auto max-w-[1600px]">
                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md">
                                <i className="bx bx-group text-xl" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {t('global.patients_list')}
                                </h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    {patients.meta.total} {t('global.patients')}
                                </p>
                            </div>
                        </div>
                        {permissions.create && (
                            <Button color="blue" as={Link} href={urls.create} className="w-fit">
                                <i className="bx bx-plus me-2 text-lg" />
                                {t('global.create')}
                            </Button>
                        )}
                    </div>

                    <div className="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                        <h2 className="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-filter-alt text-blue-500" />
                            {t('global.filters')}
                        </h2>
                        <PatientFilters
                            filters={filters}
                            filterOptions={filterOptions}
                            processing={processing}
                            onChange={updateFilter}
                            onSubmit={() => applyFilters(filters)}
                            onReset={handleReset}
                            onSelectChange={handleSelectChange}
                        />
                    </div>

                    <PatientsDataTable patients={patients} permissions={permissions} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
