import { Head } from '@inertiajs/react';
import { Alert, Card, Label, Spinner } from 'flowbite-react';
import { useCallback, useEffect, useMemo, useState, type ReactNode } from 'react';
import HorizontalBarChart from '../Components/Dashboard/HorizontalBarChart';
import LineTrendChart from '../Components/Dashboard/LineTrendChart';
import WordCloudChart from '../Components/Dashboard/WordCloudChart';
import DashboardLayout from '../Components/Layout/DashboardLayout';
import BedCard from '../Components/ui/BedCard';
import SearchableSelect from '../Components/ui/SearchableSelect';
import StatCard from '../Components/ui/StatCard';
import { useTranslation } from '../hooks/useTranslation';
import { DashboardData, DashboardVisibility } from '../types/dashboard';

interface DashboardProps {
    dashboard: DashboardData;
}

function formatValue(value?: number): number {
    return value ?? 0;
}

const ALL_VISIBLE: DashboardVisibility = {
    today_patients: true,
    emergency_today_patients: true,
    all_patients: true,
    all_appointments: true,
    consultations: true,
    hospitalizations: true,
    checkups: true,
    icu: true,
    ccu: true,
    prescriptions: true,
    operations: true,
    physiotherapy: true,
    beds: true,
    patients_trend: true,
    appointments_trend: true,
    appointments_by_user: true,
    doctors_activity: true,
    nurses_activity: true,
};

const EMPTY_SERIES = { labels: [] as string[], data: [] as number[] };

export default function Dashboard({ dashboard: initialDashboard }: DashboardProps) {
    const { t } = useTranslation();
    const [dashboard, setDashboard] = useState(initialDashboard);
    const [statsLoading, setStatsLoading] = useState(!initialDashboard.statsLoaded);
    const [chartsLoading, setChartsLoading] = useState(false);
    const [branchLoading, setBranchLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const visible = dashboard.visible ?? ALL_VISIBLE;

    const needsCharts =
        visible.patients_trend ||
        visible.appointments_trend ||
        visible.appointments_by_user ||
        visible.doctors_activity ||
        visible.nurses_activity;

    const fetchDashboardSection = useCallback(
        async (section: string, chartBranchId?: string) => {
            const params = new URLSearchParams({ section });
            if (chartBranchId) {
                params.set('chart_branch_id', chartBranchId);
            }

            const response = await fetch(`/dashboard/data?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message ?? 'Failed to load dashboard data');
            }

            return payload.data as DashboardData;
        },
        [],
    );

    const loadStats = useCallback(async () => {
        setStatsLoading(true);
        setError(null);

        try {
            const data = await fetchDashboardSection('summary');
            setDashboard((current) => ({
                ...current,
                ...data,
                statsLoaded: true,
                chartsLoaded: current.chartsLoaded,
            }));
        } catch (loadError) {
            setError(loadError instanceof Error ? loadError.message : t('global.error'));
        } finally {
            setStatsLoading(false);
        }
    }, [fetchDashboardSection, t]);

    const loadCharts = useCallback(
        async (chartBranchId?: string) => {
            if (!needsCharts) {
                return;
            }

            setChartsLoading(true);
            setError(null);

            try {
                const data = await fetchDashboardSection('charts', chartBranchId);
                setDashboard((current) => ({
                    ...current,
                    patientsTrendData: data.patientsTrendData ?? current.patientsTrendData,
                    appointmentsTrendData: data.appointmentsTrendData ?? current.appointmentsTrendData,
                    appointmentsByUserData: data.appointmentsByUserData ?? current.appointmentsByUserData,
                    nurseActivityData: data.nurseActivityData ?? current.nurseActivityData,
                    wordCloudData: data.wordCloudData ?? current.wordCloudData,
                    branches: data.branches ?? current.branches,
                    chartBranchId: data.chartBranchId ?? current.chartBranchId,
                    chartsLoaded: true,
                }));
            } catch (loadError) {
                setError(loadError instanceof Error ? loadError.message : t('global.error'));
            } finally {
                setChartsLoading(false);
            }
        },
        [fetchDashboardSection, needsCharts, t],
    );

    useEffect(() => {
        let cancelled = false;

        const bootstrap = async () => {
            if (!dashboard.statsLoaded) {
                await loadStats();
            }

            if (cancelled) {
                return;
            }

            if (!dashboard.chartsLoaded && needsCharts) {
                await loadCharts(String(dashboard.chartBranchId ?? ''));
            }
        };

        void bootstrap();

        return () => {
            cancelled = true;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const handleBranchChange = async (branchId: string) => {
        if (!branchId) {
            return;
        }

        setBranchLoading(true);
        setError(null);

        try {
            const data = await fetchDashboardSection('appointments_by_user', branchId);
            setDashboard((current) => ({
                ...current,
                appointmentsByUserData: data.appointmentsByUserData ?? EMPTY_SERIES,
                branches: data.branches ?? current.branches,
                chartBranchId: Number(data.chartBranchId ?? branchId),
            }));
        } catch (loadError) {
            setError(loadError instanceof Error ? loadError.message : t('global.error'));
        } finally {
            setBranchLoading(false);
        }
    };

    const displayValue = useCallback(
        (value?: number): number | ReactNode => {
            if (statsLoading || !dashboard.statsLoaded) {
                return <Spinner size="sm" />;
            }

            return formatValue(value);
        },
        [dashboard.statsLoaded, statsLoading],
    );

    const statCards = useMemo(
        () =>
            [
                {
                    key: 'today_patients' as const,
                    title: t('global.today_patients'),
                    value: displayValue(dashboard.todayPatients),
                    subtitle: t('global.today_registered_patients'),
                    iconClass: 'bx bx-user-plus',
                    iconBgClass: 'bg-blue-600',
                    borderClass: 'border-blue-500',
                    valueClass: 'text-blue-600',
                },
                {
                    key: 'emergency_today_patients' as const,
                    title: t('global.emergency_today_patients'),
                    value: displayValue(dashboard.totalEmergencyPatients),
                    subtitle: `${t('global.emergency')} ${t('global.today_registered_patients')}`,
                    iconClass: 'bx bx-first-aid',
                    iconBgClass: 'bg-red-600',
                    borderClass: 'border-red-500',
                    valueClass: 'text-red-600',
                },
                {
                    key: 'all_patients' as const,
                    title: t('global.all_patients'),
                    value: displayValue(dashboard.totalPatients),
                    subtitle: t('global.all_registered_patients'),
                    iconClass: 'bx bx-user',
                    iconBgClass: 'bg-blue-600',
                    borderClass: 'border-blue-500',
                    valueClass: 'text-blue-600',
                },
                {
                    key: 'all_appointments' as const,
                    title: t('global.all_appointments'),
                    value: displayValue(dashboard.totalAppointments),
                    subtitle: t('global.all_registered_appointments'),
                    iconClass: 'bx bx-history',
                    iconBgClass: 'bg-green-600',
                    borderClass: 'border-green-500',
                    valueClass: 'text-green-600',
                },
                {
                    key: 'consultations' as const,
                    title: t('global.consultations'),
                    value: displayValue(dashboard.totalConsultations),
                    subtitle: t('global.all_registered_consultations'),
                    iconClass: 'bx bx-chat',
                    iconBgClass: 'bg-cyan-600',
                    borderClass: 'border-cyan-500',
                    valueClass: 'text-cyan-600',
                },
                {
                    key: 'hospitalizations' as const,
                    title: t('global.all_hospitalized_patients'),
                    value: displayValue(dashboard.totalInPatientAdmissions),
                    subtitle: t('global.all_registered_hospitalizations'),
                    iconClass: 'bx bx-bed',
                    iconBgClass: 'bg-yellow-500',
                    borderClass: 'border-yellow-500',
                    valueClass: 'text-yellow-600',
                },
                {
                    key: 'checkups' as const,
                    title: t('global.checkups'),
                    value: displayValue(dashboard.totalCheckups),
                    subtitle: t('global.all_registered_checkups'),
                    iconClass: 'bx bx-hard-hat',
                    iconBgClass: 'bg-red-600',
                    borderClass: 'border-red-500',
                    valueClass: 'text-red-600',
                },
                {
                    key: 'icu' as const,
                    title: t('global.all_icu_patients'),
                    value: displayValue(dashboard.totalIcuAdmissions),
                    subtitle: t('global.all_registered_icu'),
                    iconClass: 'bx bx-tv',
                    iconBgClass: 'bg-gray-800',
                    borderClass: 'border-gray-700',
                    valueClass: 'text-gray-800 dark:text-gray-200',
                },
                {
                    key: 'ccu' as const,
                    title: t('global.all_ccu_patients'),
                    value: displayValue(dashboard.totalCcuAdmissions),
                    subtitle: t('global.all_registered_ccu'),
                    iconClass: 'bx bx-heart-circle',
                    iconBgClass: 'bg-cyan-600',
                    borderClass: 'border-cyan-500',
                    valueClass: 'text-cyan-600',
                },
                {
                    key: 'prescriptions' as const,
                    title: t('global.all_prescriptions'),
                    value: displayValue(dashboard.totalPrescriptions),
                    subtitle: t('global.all_registered_prescriptions'),
                    iconClass: 'bx bx-receipt',
                    iconBgClass: 'bg-purple-600',
                    borderClass: 'border-purple-500',
                    valueClass: 'text-purple-600',
                },
                {
                    key: 'operations' as const,
                    title: t('global.all_operations'),
                    value: displayValue(dashboard.totalOperations),
                    subtitle: t('global.all_registered_operations'),
                    iconClass: 'bx bx-cut',
                    iconBgClass: 'bg-pink-600',
                    borderClass: 'border-pink-500',
                    valueClass: 'text-pink-600',
                },
                {
                    key: 'physiotherapy' as const,
                    title: t('global.all_physiotherapy_procedures'),
                    value: displayValue(dashboard.totalPhysiotherapyProcedures),
                    subtitle: t('global.all_registered_physiotherapy_procedures'),
                    iconClass: 'bx bx-spa',
                    iconBgClass: 'bg-teal-500',
                    borderClass: 'border-teal-500',
                    valueClass: 'text-teal-600',
                },
            ].filter((card) => visible[card.key]),
        [dashboard, displayValue, t, visible],
    );

    const bedCards = useMemo(
        () =>
            visible.beds
                ? [
                      {
                          title: t('global.occupied_beds'),
                          value: displayValue(dashboard.occupied_beds),
                          iconClass: 'bx bx-bed',
                          iconBgClass: 'bg-yellow-500',
                          borderClass: 'border-yellow-500',
                          valueClass: 'text-yellow-600',
                      },
                      {
                          title: t('global.all_beds'),
                          value: displayValue(dashboard.all_beds),
                          iconClass: 'bx bx-bed',
                          iconBgClass: 'bg-blue-600',
                          borderClass: 'border-blue-500',
                          valueClass: 'text-blue-600',
                      },
                      {
                          title: t('global.free_beds'),
                          value: displayValue(dashboard.free_beds),
                          iconClass: 'bx bx-bed',
                          iconBgClass: 'bg-green-600',
                          borderClass: 'border-green-500',
                          valueClass: 'text-green-600',
                      },
                  ]
                : [],
        [dashboard, displayValue, t, visible.beds],
    );

    const hasAnyContent =
        statCards.length > 0 ||
        bedCards.length > 0 ||
        visible.patients_trend ||
        visible.appointments_trend ||
        visible.appointments_by_user ||
        visible.doctors_activity ||
        visible.nurses_activity;

    return (
        <DashboardLayout>
            <Head title={t('global.dashboard')} />

            {(statsLoading || chartsLoading || branchLoading) && (
                <div className="mb-4 flex items-center gap-2 text-sm text-gray-500">
                    <Spinner size="sm" />
                    <span>{t('global.loading_dashboard')}</span>
                </div>
            )}

            {error && (
                <Alert color="failure" className="mb-4">
                    <i className="bx bx-error-circle me-2" />
                    {error}
                </Alert>
            )}

            {!hasAnyContent && !statsLoading && !chartsLoading && (
                <Card className="shadow-sm">
                    <div className="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        {t('global.no_item_is_found')}
                    </div>
                </Card>
            )}

            {statCards.length > 0 && (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {statCards.map(({ key, ...card }) => (
                        <StatCard key={key} {...card} />
                    ))}
                </div>
            )}

            {bedCards.length > 0 && (
                <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    {bedCards.map((card) => (
                        <BedCard key={card.title} {...card} />
                    ))}
                </div>
            )}

            {(visible.patients_trend || visible.appointments_trend) && (
                <div className="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    {visible.patients_trend && (
                        <Card>
                            <div className="mb-3 flex items-center gap-2">
                                <i className="bx bx-line-chart text-blue-600" />
                                <h5 className="text-lg text-gray-900 dark:text-white">
                                    {t('global.patients_comparison_graph')}
                                </h5>
                            </div>
                            <LineTrendChart data={dashboard.patientsTrendData} />
                        </Card>
                    )}

                    {visible.appointments_trend && (
                        <Card>
                            <div className="mb-3 flex items-center gap-2">
                                <i className="bx bx-line-chart text-blue-600" />
                                <h5 className="text-lg text-gray-900 dark:text-white">
                                    {t('global.appointments_comparison_graph')}
                                </h5>
                            </div>
                            <LineTrendChart data={dashboard.appointmentsTrendData} color="#696cff" />
                        </Card>
                    )}
                </div>
            )}

            {visible.appointments_by_user && (
                <Card className="mt-4">
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h5 className="flex items-center gap-2 text-lg text-gray-900 dark:text-white">
                            <i className="bx bx-user-check text-blue-600" />
                            {t('global.appointments_processed_by_user')}
                        </h5>
                        <div className="flex items-center gap-2">
                            <Label htmlFor="chart-branch-select" className="mb-0 whitespace-nowrap">
                                {t('global.filter_by_branch')}:
                            </Label>
                            <SearchableSelect
                                id="chart-branch-select"
                                compact
                                value={String(dashboard.chartBranchId ?? '')}
                                onChange={handleBranchChange}
                                className="min-w-[180px]"
                                options={dashboard.branches?.map((branch) => ({
                                    value: String(branch.id),
                                    label: branch.name,
                                }))}
                            />
                        </div>
                    </div>
                    <HorizontalBarChart
                        data={dashboard.appointmentsByUserData}
                        label={t('global.appointments_processed')}
                        color="rgba(13, 202, 240, 0.85)"
                    />
                    <p className="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                        {t('global.appointments_processed_by_user_hint')}
                    </p>
                </Card>
            )}

            {visible.doctors_activity && (
                <Card className="mt-4">
                    <div className="mb-3 flex items-center gap-2">
                        <i className="bx bx-line-chart text-blue-600" />
                        <h5 className="text-lg text-gray-900 dark:text-white">
                            {t('global.doctors_activity_graph')}
                        </h5>
                    </div>
                    <WordCloudChart data={dashboard.wordCloudData} />
                </Card>
            )}

            {visible.nurses_activity && (
                <Card className="mt-4">
                    <div className="mb-3 flex items-center gap-2">
                        <i className="bx bx-line-chart text-blue-600" />
                        <h5 className="text-lg text-gray-900 dark:text-white">
                            {t('global.nurses_activity_graph')}
                        </h5>
                    </div>
                    <HorizontalBarChart
                        data={dashboard.nurseActivityData}
                        label={t('global.nurse_activity_count')}
                        color="rgba(32, 201, 151, 0.85)"
                    />
                </Card>
            )}
        </DashboardLayout>
    );
}
