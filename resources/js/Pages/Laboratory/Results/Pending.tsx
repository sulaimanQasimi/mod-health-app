import { Head } from '@inertiajs/react';
import AppointmentPagination from '../../../Components/Appointments/AppointmentPagination';
import LaboratoryDepartmentScopeBanner from '../../../Components/Laboratory/LaboratoryDepartmentScopeBanner';
import LaboratoryFlashAlerts from '../../../Components/Laboratory/LaboratoryFlashAlerts';
import LaboratoryPageHeader from '../../../Components/Laboratory/LaboratoryPageHeader';
import LaboratoryPatientAccordion from '../../../Components/Laboratory/LaboratoryPatientAccordion';
import LaboratoryPendingFilters from '../../../Components/Laboratory/LaboratoryPendingFilters';
import LaboratoryResultsSummary from '../../../Components/Laboratory/LaboratoryResultsSummary';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import { useLaboratoryListFilters } from '../../../hooks/useLaboratoryListFilters';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    LaboratoryDepartmentScope,
    LaboratoryNavUrls,
    LaboratoryPendingFilters as PendingFilters,
    PaginatedLaboratoryPatients,
} from '../../../types/laboratory';

interface PendingProps {
    patients: PaginatedLaboratoryPatients;
    summary: {
        patient_count: number;
        registration_count: number;
    };
    filters: PendingFilters;
    scope: LaboratoryDepartmentScope;
    permissions: {
        manageResults: boolean;
    };
    urls: LaboratoryNavUrls;
    flash?: {
        success?: string | null;
        error?: string | null;
    };
}

const EMPTY_FILTERS: PendingFilters = {
    search: '',
    patient_id: '',
    priority: '',
    date_from: '',
    date_to: '',
    per_page: '50',
};

export default function Pending({
    patients,
    summary,
    filters: serverFilters,
    scope,
    urls,
    flash,
}: PendingProps) {
    const { t } = useTranslation();
    const { filters, processing, updateFilter, handleSubmit, handleReset } = useLaboratoryListFilters(
        serverFilters,
        urls.index,
        EMPTY_FILTERS,
    );

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

            <LaboratoryFlashAlerts flash={flash} />

            <LaboratoryDepartmentScopeBanner scope={scope} />

            <LaboratoryPendingFilters
                filters={filters}
                onChange={updateFilter}
                onSubmit={handleSubmit}
                onReset={handleReset}
                processing={processing}
            />

            <LaboratoryResultsSummary
                patientCount={summary.patient_count}
                registrationCount={summary.registration_count}
            />

            <LaboratoryPatientAccordion patients={patients.data} listMode="pending" />

            <AppointmentPagination links={patients.links} meta={patients.meta} t={t} />
        </DashboardLayout>
    );
}
