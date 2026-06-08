import { Head, router } from '@inertiajs/react';
import { Alert, Badge, Card } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import AppointmentPagination from '../../../Components/Appointments/AppointmentPagination';
import LaboratoryPageHeader from '../../../Components/Laboratory/LaboratoryPageHeader';
import LaboratoryPatientAccordion from '../../../Components/Laboratory/LaboratoryPatientAccordion';
import LaboratoryResultsFilters from '../../../Components/Laboratory/LaboratoryResultsFilters';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    LaboratoryListMode,
    LaboratoryNavUrls,
    LaboratoryResultsFilters as Filters,
    PaginatedLaboratoryPatients,
} from '../../../types/laboratory';

interface IndexProps {
    listMode: LaboratoryListMode;
    page: {
        titleKey: string;
        subtitleKey: string;
        icon: string;
        accent: string;
    };
    patients: PaginatedLaboratoryPatients;
    summary: {
        patient_count: number;
        registration_count: number;
    };
    filters: Filters;
    urls: LaboratoryNavUrls;
    flash?: {
        success?: string | null;
        error?: string | null;
    };
}

const EMPTY_FILTERS: Filters = {
    search: '',
    patient_id: '',
    status: '',
    priority: '',
    date_from: '',
    date_to: '',
    per_page: '50',
};

function cleanFilters(filters: Filters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function Index({
    listMode,
    page,
    patients,
    summary,
    filters: serverFilters,
    urls,
    flash,
}: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<Filters>(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const activeTab =
        listMode === 'pending'
            ? 'pending'
            : listMode === 'in_progress'
              ? 'inProgress'
              : 'completed';

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
            <Head title={t(page.titleKey)} />

            <LaboratoryPageHeader
                title={t(page.titleKey)}
                subtitle={t(page.subtitleKey)}
                icon={page.icon}
                accent={page.accent}
                navUrls={urls}
                activeTab={activeTab}
            />

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

            <LaboratoryResultsFilters
                filters={filters}
                onChange={updateFilter}
                onSubmit={handleFilterSubmit}
                onReset={handleReset}
                processing={processing}
                showStatusFilter={listMode === 'pending'}
            />

            <Card className="mb-4 shadow-sm">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h2 className="font-semibold text-gray-900 dark:text-white">
                        {t('global.test_results')} — {t('global.patients')}
                    </h2>
                    <div className="flex flex-wrap gap-2">
                        <Badge color="info">
                            {summary.patient_count} {t('global.patients')}
                        </Badge>
                        <Badge color="purple">
                            {summary.registration_count}{' '}
                            {t('global.registrations') || 'registrations'}
                        </Badge>
                    </div>
                </div>
            </Card>

            <LaboratoryPatientAccordion patients={patients.data} listMode={listMode} />

            <AppointmentPagination links={patients.links} meta={patients.meta} t={t} />
        </DashboardLayout>
    );
}
