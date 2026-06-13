import { Head, router } from '@inertiajs/react';
import { Alert, Badge, Card } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import AppointmentPagination from '../../../Components/Appointments/AppointmentPagination';
import LaboratoryDepartmentScopeBanner from '../../../Components/Laboratory/LaboratoryDepartmentScopeBanner';
import LaboratoryPageHeader from '../../../Components/Laboratory/LaboratoryPageHeader';
import LaboratoryPatientAccordion from '../../../Components/Laboratory/LaboratoryPatientAccordion';
import LaboratoryPendingFilters from '../../../Components/Laboratory/LaboratoryPendingFilters';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    LaboratoryDepartmentScope,
    LaboratoryNavUrls,
    LaboratoryPendingFilters as Filters,
    PaginatedLaboratoryPatients,
} from '../../../types/laboratory';

interface PendingProps {
    patients: PaginatedLaboratoryPatients;
    summary: {
        patient_count: number;
        registration_count: number;
    };
    filters: Filters;
    scope: LaboratoryDepartmentScope;
    urls: LaboratoryNavUrls & { index: string };
    permissions: {
        manageResults: boolean;
    };
    flash?: {
        success?: string | null;
        error?: string | null;
    };
}

const EMPTY_FILTERS: Filters = {
    search: '',
    patient_id: '',
    priority: '',
    date_from: '',
    date_to: '',
    per_page: '50',
};

function cleanFilters(filters: Filters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function Pending({
    patients,
    summary,
    filters: serverFilters,
    scope,
    urls,
    flash,
}: PendingProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<Filters>(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: Filters) => {
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

    const updateFilter = (field: keyof Filters, value: string) => {
        setFilters((current) => ({ ...current, [field]: value }));
    };

    const handleFilterSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        const reset = { ...EMPTY_FILTERS, per_page: filters.per_page };
        setFilters(reset);
        applyFilters(reset);
    };

    return (
        <DashboardLayout>
            <Head title={t('global.pending_tests')} />

            <LaboratoryPageHeader
                title={t('global.pending_tests')}
                subtitle={t('global.test_results')}
                icon="bx-hourglass"
                accent="from-amber-500 to-orange-600"
                navUrls={urls}
                activeTab="pending"
            />

            <LaboratoryDepartmentScopeBanner scope={scope} />

            {flash?.success && (
                <Alert color="success" className="mb-4">
                    {flash.success}
                </Alert>
            )}
            {flash?.error && (
                <Alert color="failure" className="mb-4">
                    {flash.error}
                </Alert>
            )}

            <LaboratoryPendingFilters
                filters={filters}
                onChange={updateFilter}
                onSubmit={handleFilterSubmit}
                onReset={handleReset}
                processing={processing}
            />

            <Card className="mb-4 shadow-sm">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h2 className="font-semibold text-gray-900 dark:text-white">
                        {t('global.pending_tests')} — {t('global.patients')}
                    </h2>
                    <div className="flex flex-wrap gap-2">
                        <Badge color="info">
                            {summary.patient_count} {t('global.patients')}
                        </Badge>
                        <Badge color="warning">
                            {summary.registration_count} {t('global.pending')}
                        </Badge>
                    </div>
                </div>
            </Card>

            <LaboratoryPatientAccordion patients={patients.data} listMode="pending" />

            <AppointmentPagination links={patients.links} meta={patients.meta} t={t} />
        </DashboardLayout>
    );
}
