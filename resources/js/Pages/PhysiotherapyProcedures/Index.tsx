import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PhysiotherapyProcedureFilters from '../../Components/PhysiotherapyProcedures/PhysiotherapyProcedureFilters';
import PhysiotherapyProcedureStats from '../../Components/PhysiotherapyProcedures/PhysiotherapyProcedureStats';
import PhysiotherapyProcedureTable from '../../Components/PhysiotherapyProcedures/PhysiotherapyProcedureTable';
import UpdateProgressModal from '../../Components/PhysiotherapyProcedures/UpdateProgressModal';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PaginatedPhysiotherapyProcedures,
    PhysiotherapyProcedureFilterOptions,
    PhysiotherapyProcedureFilters as ProcedureFilters,
    PhysiotherapyProcedureListItem,
    PhysiotherapyProcedureListPermissions,
    PhysiotherapyProcedureStats as Stats,
} from '../../types/physiotherapyProcedure';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface IndexPhysiotherapyProceduresProps {
    mode: 'all' | 'own';
    procedures: PaginatedPhysiotherapyProcedures;
    stats: Stats;
    filters: ProcedureFilters;
    filterOptions: PhysiotherapyProcedureFilterOptions;
    permissions: PhysiotherapyProcedureListPermissions;
    urls: {
        current: string;
        index: string;
        myProcedures: string;
        reports: string;
        show: string;
    };
}

function cleanFilters(filters: ProcedureFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function IndexPhysiotherapyProcedures({
    mode,
    procedures,
    stats,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: IndexPhysiotherapyProceduresProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [progressProcedure, setProgressProcedure] = useState<PhysiotherapyProcedureListItem | null>(null);
    const [progressSubmitting, setProgressSubmitting] = useState(false);

    const isOwn = mode === 'own';

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: ProcedureFilters) => {
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
        const empty: ProcedureFilters = {
            search: '',
            status: '',
            physiotherapy_type_id: '',
            doctor_id: '',
            start_date: '',
            end_date: '',
            sort_by: 'created_at',
            sort_order: 'desc',
            per_page: '15',
        };
        setFilters(empty);
        applyFilters(empty);
    };

    const submitProgress = (counter: number) => {
        if (!progressProcedure) return;

        setProgressSubmitting(true);
        router.post(
            `${urls.show}/${progressProcedure.id}/update-counter`,
            { counter },
            {
                preserveScroll: true,
                onSuccess: () => setProgressProcedure(null),
                onFinish: () => setProgressSubmitting(false),
            },
        );
    };

    const summaryLabel = buildPaginationSummary(procedures.meta, t);

    return (
        <DashboardLayout>
            <Head title={isOwn ? t('global.my_physiotherapy_procedures') : t('global.physiotherapy_procedures')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={isOwn ? t('global.my_physiotherapy_procedures') : t('global.physiotherapy_procedures')}
                    subtitle={summaryLabel}
                    icon="bx-health"
                    accent="from-cyan-500 to-teal-600"
                    action={
                        <div className="flex flex-wrap gap-2">
                            {!isOwn && permissions.viewMyProcedures && (
                                <Button as={Link} href={urls.myProcedures} color="light" size="sm">
                                    <i className="bx bx-user me-2" />
                                    {t('global.my_procedures')}
                                </Button>
                            )}
                            {isOwn && permissions.viewAllProcedures && (
                                <Button as={Link} href={urls.index} color="light" size="sm">
                                    <i className="bx bx-list-ul me-2" />
                                    {t('global.all_procedures')}
                                </Button>
                            )}
                            {permissions.viewReports && (
                                <Button as={Link} href={urls.reports} color="light" size="sm">
                                    <i className="bx bx-chart me-2" />
                                    {t('global.reports')}
                                </Button>
                            )}
                        </div>
                    }
                    backLabel={t('global.back')}
                />

                {!isOwn && <PhysiotherapyProcedureStats stats={stats} />}

                <PhysiotherapyProcedureFilters
                    filters={filters}
                    filterOptions={filterOptions}
                    processing={processing}
                    showPhysiotherapistFilter={!isOwn}
                    onChange={setFilters}
                    onSubmit={applyFilters}
                    onReset={resetFilters}
                />

                <Card className="shadow-sm">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <h2 className="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-list-ul text-cyan-500" />
                            {t('global.procedures_list')}
                        </h2>
                    </div>

                    <PhysiotherapyProcedureTable
                        items={procedures.data}
                        permissions={permissions}
                        showUrlBase={urls.show}
                        showPhysiotherapistColumn={!isOwn}
                        showFatherNameColumn={isOwn}
                        onUpdateProgress={permissions.updateProgress ? setProgressProcedure : undefined}
                    />

                    <div className="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
                        <p className="text-sm text-gray-500">{summaryLabel}</p>
                        <SettingsPagination links={procedures.links} />
                    </div>
                </Card>
            </div>

            <UpdateProgressModal
                open={progressProcedure !== null}
                procedure={progressProcedure}
                submitting={progressSubmitting}
                onClose={() => setProgressProcedure(null)}
                onSubmit={submitProgress}
            />
        </DashboardLayout>
    );
}
