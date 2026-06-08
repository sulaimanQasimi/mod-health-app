import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import HemodialysisSessionFilters from '../../Components/HemodialysisSessions/HemodialysisSessionFilters';
import HemodialysisSessionStats from '../../Components/HemodialysisSessions/HemodialysisSessionStats';
import HemodialysisSessionTable from '../../Components/HemodialysisSessions/HemodialysisSessionTable';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HemodialysisSessionFilterOptions,
    HemodialysisSessionFilters as Filters,
    HemodialysisSessionListPermissions,
    HemodialysisSessionStats as Stats,
    PaginatedHemodialysisSessions,
} from '../../types/hemodialysisSession';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface IndexHemodialysisSessionsProps {
    sessions: PaginatedHemodialysisSessions;
    stats: Stats;
    filters: Filters;
    filterOptions: HemodialysisSessionFilterOptions;
    permissions: HemodialysisSessionListPermissions;
    urls: {
        current: string;
        create: string;
        show: string;
        edit: string;
    };
}

function cleanFilters(filters: Filters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function IndexHemodialysisSessions({
    sessions,
    stats,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: IndexHemodialysisSessionsProps) {
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
            session_date: '',
            date_from: '',
            date_to: '',
            doctor_id: '',
            status: '',
            per_page: '25',
        };
        setFilters(empty);
        applyFilters(empty);
    };

    const summaryLabel = buildPaginationSummary(sessions.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.hemodialysis')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.hemodialysis')}
                    subtitle={summaryLabel}
                    icon="bx-water"
                    accent="from-sky-500 to-blue-600"
                    backLabel={t('global.back')}
                    action={
                        <Button as={Link} href={urls.create} color="blue">
                            <i className="bx bx-plus me-2" />
                            {t('global.add_hemodialysis_session')}
                        </Button>
                    }
                />

                <HemodialysisSessionStats stats={stats} />

                <HemodialysisSessionFilters
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
                            <i className="bx bx-list-ul text-sky-500" />
                            {t('global.hemodialysis_sessions')}
                        </h2>
                    </div>

                    <HemodialysisSessionTable
                        items={sessions.data}
                        permissions={permissions}
                        showUrlBase={urls.show}
                        editUrlBase={urls.edit}
                    />

                    <div className="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
                        <p className="text-sm text-gray-500">{summaryLabel}</p>
                        <SettingsPagination links={sessions.links} />
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
