import { Head, Link, router } from '@inertiajs/react';
import { Button } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import HospitalizationDischargedFilters, {
    EMPTY_DISCHARGED_FILTERS,
} from '../../Components/Hospitalizations/HospitalizationDischargedFilters';
import HospitalizationPanel from '../../Components/Hospitalizations/HospitalizationPanel';
import HospitalizationStatsCards from '../../Components/Hospitalizations/HospitalizationStatsCards';
import HospitalizationTable from '../../Components/Hospitalizations/HospitalizationTable';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HospitalizationDashboardStats,
    HospitalizationDischargedFilters as Filters,
    HospitalizationOption,
    PaginatedHospitalizations,
} from '../../types/hospitalization';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface DischargedProps {
    hospitalizations: PaginatedHospitalizations;
    stats: HospitalizationDashboardStats;
    filters: Filters;
    filterOptions: { rooms: HospitalizationOption[]; doctors: HospitalizationOption[] };
    urls: { current: string; index: string };
}

function cleanFilters(filters: Filters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function HospitalizationsDischarged({
    hospitalizations,
    stats,
    filters: serverFilters,
    filterOptions,
    urls,
}: DischargedProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: Filters) => {
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
            <Head title={t('global.discharged_hospitalizations')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.discharged_hospitalizations')}
                    subtitle={t('global.patients_list')}
                    icon="bx-exit"
                    accent="from-slate-600 to-gray-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        <Button as={Link} href={urls.index} color="success" size="sm">
                            <i className="bx bx-bed me-2" />
                            {t('global.hospitalized_patients')}
                        </Button>
                    }
                />

                <HospitalizationStatsCards stats={stats} variant="discharged" />

                <HospitalizationPanel
                    variant="filter"
                    title={t('global.search')}
                    icon="bx-filter-alt"
                    iconClassName="text-slate-600 dark:text-slate-400"
                >
                    <HospitalizationDischargedFilters
                        filters={filters}
                        rooms={filterOptions.rooms}
                        doctors={filterOptions.doctors}
                        processing={processing}
                        onChange={setFilters}
                        onApply={applyFilters}
                        onReset={() => applyFilters(EMPTY_DISCHARGED_FILTERS)}
                    />
                </HospitalizationPanel>

                <HospitalizationPanel
                    title={t('global.discharged_hospitalizations')}
                    icon="bx-exit"
                    iconClassName="text-slate-600 dark:text-slate-400"
                    action={
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {buildPaginationSummary(hospitalizations.meta, t)}
                        </span>
                    }
                >
                    <HospitalizationTable items={hospitalizations.data} variant="discharged" />
                    <div className="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <SettingsPagination links={hospitalizations.links} />
                    </div>
                </HospitalizationPanel>
            </div>
        </DashboardLayout>
    );
}
