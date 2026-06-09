import { Head, Link, router } from '@inertiajs/react';
import { Button } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import HospitalizationFilters, {
    EMPTY_HOSPITALIZATION_FILTERS,
} from '../../Components/Hospitalizations/HospitalizationFilters';
import HospitalizationPanel from '../../Components/Hospitalizations/HospitalizationPanel';
import HospitalizationStatsCards from '../../Components/Hospitalizations/HospitalizationStatsCards';
import HospitalizationTable from '../../Components/Hospitalizations/HospitalizationTable';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HospitalizationActiveFilters,
    HospitalizationDashboardStats,
    HospitalizationOption,
    PaginatedHospitalizations,
} from '../../types/hospitalization';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface IndexProps {
    hospitalizations: PaginatedHospitalizations;
    stats: HospitalizationDashboardStats;
    filters: HospitalizationActiveFilters;
    filterOptions: { rooms: HospitalizationOption[] };
    urls: {
        current: string;
        discharged: string;
        report: string;
        room_management: string | null;
    };
}

function cleanFilters(filters: HospitalizationActiveFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function HospitalizationsIndex({
    hospitalizations,
    stats,
    filters: serverFilters,
    filterOptions,
    urls,
}: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: HospitalizationActiveFilters) => {
            setProcessing(true);
            router.get(urls.current, cleanFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current]
    );

    return (
        <DashboardLayout>
            <Head title={t('global.hospitalized_patients')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.hospitalized_patients')}
                    subtitle={t('global.patients_list')}
                    icon="bx-bed"
                    accent="from-emerald-600 to-teal-700"
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            {urls.room_management && (
                                <Button as={Link} href={urls.room_management} color="light" size="sm">
                                    <i className="bx bx-building-house me-2" />
                                    {t('global.room_management')}
                                </Button>
                            )}
                            <Button as={Link} href={urls.report} color="light" size="sm">
                                <i className="bx bx-bar-chart-alt-2 me-2" />
                                {t('global.reports')}
                            </Button>
                            <Button as={Link} href={urls.discharged} color="light" size="sm">
                                <i className="bx bx-exit me-2" />
                                {t('global.discharged_hospitalizations')}
                            </Button>
                        </div>
                    }
                />

                <HospitalizationStatsCards stats={stats} />

                <HospitalizationPanel
                    variant="filter"
                    title={t('global.search')}
                    icon="bx-filter-alt"
                    description={t('global.search_by_patient_room_bed')}
                >
                    <HospitalizationFilters
                        filters={filters}
                        rooms={filterOptions.rooms}
                        processing={processing}
                        onChange={setFilters}
                        onApply={applyFilters}
                        onReset={() => applyFilters(EMPTY_HOSPITALIZATION_FILTERS)}
                    />
                </HospitalizationPanel>

                <HospitalizationPanel
                    variant="table"
                    title={t('global.hospitalized_patients')}
                    icon="bx-list-ul"
                    action={
                        <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                            {buildPaginationSummary(hospitalizations.meta, t)}
                        </span>
                    }
                    footer={<SettingsPagination links={hospitalizations.links} />}
                >
                    <HospitalizationTable items={hospitalizations.data} embedded />
                </HospitalizationPanel>
            </div>
        </DashboardLayout>
    );
}
