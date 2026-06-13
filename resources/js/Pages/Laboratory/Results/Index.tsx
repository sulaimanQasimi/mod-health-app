import { Head } from '@inertiajs/react';
import AppointmentPagination from '../../../Components/Appointments/AppointmentPagination';
import LaboratoryFlashAlerts from '../../../Components/Laboratory/LaboratoryFlashAlerts';
import LaboratoryPageHeader from '../../../Components/Laboratory/LaboratoryPageHeader';
import LaboratoryPatientAccordion from '../../../Components/Laboratory/LaboratoryPatientAccordion';
import LaboratoryResultsFilters from '../../../Components/Laboratory/LaboratoryResultsFilters';
import LaboratoryResultsSummary from '../../../Components/Laboratory/LaboratoryResultsSummary';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import { useLaboratoryListFilters } from '../../../hooks/useLaboratoryListFilters';
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
    const { filters, processing, updateFilter, handleSubmit, handleReset } = useLaboratoryListFilters(
        serverFilters,
        urls.index,
        EMPTY_FILTERS,
    );

    const activeTab =
        listMode === 'pending'
            ? 'pending'
            : listMode === 'in_progress'
              ? 'inProgress'
              : 'completed';

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

            <LaboratoryFlashAlerts flash={flash} />

            <LaboratoryResultsFilters
                filters={filters}
                onChange={updateFilter}
                onSubmit={handleSubmit}
                onReset={handleReset}
                processing={processing}
                showStatusFilter={listMode === 'pending'}
            />

            <LaboratoryResultsSummary
                patientCount={summary.patient_count}
                registrationCount={summary.registration_count}
            />

            <LaboratoryPatientAccordion patients={patients.data} listMode={listMode} />

            <AppointmentPagination links={patients.links} meta={patients.meta} t={t} />
        </DashboardLayout>
    );
}
