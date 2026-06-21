import { Head, router } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DentistRegistrationFilters from '../../Components/DentistRegistrations/DentistRegistrationFilters';
import DentistRegistrationStats from '../../Components/DentistRegistrations/DentistRegistrationStats';
import DentistRegistrationTable from '../../Components/DentistRegistrations/DentistRegistrationTable';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    DentistRegistrationFilterOptions,
    DentistRegistrationFilters as Filters,
    DentistRegistrationListPermissions,
    DentistRegistrationStats as Stats,
    PaginatedDentistRegistrations,
} from '../../types/dentistRegistration';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
interface IndexDentistRegistrationsProps {
    registrations: PaginatedDentistRegistrations;
    stats: Stats;
    filters: Filters;
    filterOptions: DentistRegistrationFilterOptions;
    permissions: DentistRegistrationListPermissions;
    urls: {
        current: string;
        show: string;
    };
}

function cleanFilters(filters: Filters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function IndexDentistRegistrations({
    registrations,
    stats,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: IndexDentistRegistrationsProps) {
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
            search: '',
            status: '',
            branch_id: '',
            dentist_id: '',
            sort_by: 'created_at',
            sort_order: 'desc',
            per_page: '25',
        };
        setFilters(empty);
        applyFilters(empty);
    };

    const summaryLabel = buildPaginationSummary(registrations.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.dentist_registrations')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.dentist_registrations')}
                    subtitle={summaryLabel}
                    icon="bx-plus-medical"
                    accent="from-blue-500 to-indigo-600"
                    backLabel={t('global.back')}
                />

                <DentistRegistrationStats stats={stats} />

                <DentistRegistrationFilters
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
                            <i className="bx bx-list-ul text-blue-500" />
                            {t('global.dentist_registrations_list')}
                        </h2>
                    </div>

                    <DentistRegistrationTable
                        items={registrations.data}
                        permissions={permissions}
                        showUrlBase={urls.show}
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
