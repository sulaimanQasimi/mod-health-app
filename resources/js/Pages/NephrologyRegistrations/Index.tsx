import { Head, router } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import NephrologyRegistrationFilters from '../../Components/NephrologyRegistrations/NephrologyRegistrationFilters';
import NephrologyRegistrationStats from '../../Components/NephrologyRegistrations/NephrologyRegistrationStats';
import NephrologyRegistrationTable from '../../Components/NephrologyRegistrations/NephrologyRegistrationTable';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    NephrologyRegistrationFilterOptions,
    NephrologyRegistrationFilters as Filters,
    NephrologyRegistrationListPermissions,
    NephrologyRegistrationStats as Stats,
    PaginatedNephrologyRegistrations,
} from '../../types/nephrologyRegistration';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface IndexNephrologyRegistrationsProps {
    registrations: PaginatedNephrologyRegistrations;
    stats: Stats;
    filters: Filters;
    filterOptions: NephrologyRegistrationFilterOptions;
    permissions: NephrologyRegistrationListPermissions;
    urls: {
        current: string;
        show: string;
    };
}

function cleanFilters(filters: Filters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function IndexNephrologyRegistrations({
    registrations,
    stats,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: IndexNephrologyRegistrationsProps) {
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
        [urls.current],
    );

    const resetFilters = () => {
        const empty: Filters = {
            patient_id: '',
            patient_name: '',
            status: '',
            branch_id: '',
            doctor_id: '',
            visit_date_from: '',
            visit_date_to: '',
            per_page: '25',
        };
        setFilters(empty);
        applyFilters(empty);
    };

    const summaryLabel = buildPaginationSummary(registrations.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.nephrology_registrations')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.nephrology_registrations')}
                    subtitle={summaryLabel}
                    icon="bx-droplet"
                    accent="from-cyan-500 to-blue-600"
                    backLabel={t('global.back')}
                />

                <NephrologyRegistrationStats stats={stats} />

                <NephrologyRegistrationFilters
                    filters={filters}
                    filterOptions={filterOptions}
                    processing={processing}
                    onChange={setFilters}
                    onSubmit={applyFilters}
                    onReset={resetFilters}
                />

                <Card className="shadow-sm">
                    <div className="mb-4">
                        <h2 className="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-list-ul text-cyan-500" />
                            {t('global.nephrology_registrations')}
                        </h2>
                    </div>

                    <NephrologyRegistrationTable
                        items={registrations.data}
                        permissions={permissions}
                        showUrlBase={urls.show}
                        acceptUrlBase={urls.show}
                    />

                    <div className="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
                        <p className="text-sm text-gray-500">{summaryLabel}</p>
                        <SettingsPagination links={registrations.links} />
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
