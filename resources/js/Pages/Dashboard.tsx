import { Head } from '@inertiajs/react';
import { Alert, Card, Label, Select, Spinner } from 'flowbite-react';
import { useCallback, useState } from 'react';
import HorizontalBarChart from '../Components/Dashboard/HorizontalBarChart';
import LineTrendChart from '../Components/Dashboard/LineTrendChart';
import StatCard from '../Components/Dashboard/StatCard';
import WordCloudChart from '../Components/Dashboard/WordCloudChart';
import DashboardLayout from '../Components/Layout/DashboardLayout';
import { useTranslation } from '../hooks/useTranslation';
import { DashboardData } from '../types/dashboard';

interface DashboardProps {
    dashboard: DashboardData;
}

function formatValue(value?: number): number {
    return value ?? 0;
}

export default function Dashboard({ dashboard: initialDashboard }: DashboardProps) {
    const { t } = useTranslation();
    const [dashboard, setDashboard] = useState(initialDashboard);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const loadDashboardData = useCallback(async (chartBranchId?: string) => {
        setLoading(true);
        setError(null);

        try {
            const params = chartBranchId ? `?chart_branch_id=${chartBranchId}` : '';
            const response = await fetch(`/react/dashboard/data${params}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message ?? 'Failed to load dashboard data');
            }

            setDashboard(payload.data);
        } catch (loadError) {
            setError(loadError instanceof Error ? loadError.message : t('global.error'));
        } finally {
            setLoading(false);
        }
    }, [t]);

    const handleBranchChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
        const branchId = event.target.value;
        if (branchId) {
            loadDashboardData(branchId);
        }
    };

    const statCards = [
        {
            title: t('global.today_patients'),
            value: formatValue(dashboard.todayPatients),
            subtitle: t('global.today_registered_patients'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white"><i className="bx bx-user-plus text-2xl" /></span>,
            borderClass: 'border-blue-500',
            valueClass: 'text-blue-600',
        },
        {
            title: t('global.emergency_today_patients'),
            value: formatValue(dashboard.totalEmergencyPatients),
            subtitle: `${t('global.emergency')} ${t('global.today_registered_patients')}`,
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-red-600 text-white"><i className="bx bx-first-aid text-2xl" /></span>,
            borderClass: 'border-red-500',
            valueClass: 'text-red-600',
        },
        {
            title: t('global.all_patients'),
            value: formatValue(dashboard.totalPatients),
            subtitle: t('global.all_registered_patients'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white"><i className="bx bx-user text-2xl" /></span>,
        },
        {
            title: t('global.all_appointments'),
            value: formatValue(dashboard.totalAppointments),
            subtitle: t('global.all_registered_appointments'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-green-600 text-white"><i className="bx bx-history text-2xl" /></span>,
        },
        {
            title: t('global.consultations'),
            value: formatValue(dashboard.totalConsultations),
            subtitle: t('global.all_registered_consultations'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-cyan-600 text-white"><i className="bx bx-chat text-2xl" /></span>,
        },
        {
            title: t('global.all_hospitalized_patients'),
            value: formatValue(dashboard.totalInPatientAdmissions),
            subtitle: t('global.all_registered_hospitalizations'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-500 text-white"><i className="bx bx-bed text-2xl" /></span>,
        },
        {
            title: t('global.checkups'),
            value: formatValue(dashboard.totalCheckups),
            subtitle: t('global.all_registered_checkups'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-red-600 text-white"><i className="bx bx-hard-hat text-2xl" /></span>,
        },
        {
            title: t('global.all_icu_patients'),
            value: formatValue(dashboard.totalIcuAdmissions),
            subtitle: t('global.all_registered_icu'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-gray-800 text-white"><i className="bx bx-tv text-2xl" /></span>,
        },
        {
            title: t('global.all_ccu_patients'),
            value: formatValue(dashboard.totalCcuAdmissions),
            subtitle: t('global.all_registered_ccu'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-cyan-600 text-white"><i className="bx bx-heart-circle text-2xl" /></span>,
            borderClass: 'border-cyan-500',
            valueClass: 'text-cyan-600',
        },
        {
            title: t('global.all_prescriptions'),
            value: formatValue(dashboard.totalPrescriptions),
            subtitle: t('global.all_registered_prescriptions'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-purple-600 text-white"><i className="bx bx-receipt text-2xl" /></span>,
        },
        {
            title: t('global.all_operations'),
            value: formatValue(dashboard.totalOperations),
            subtitle: t('global.all_registered_operations'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-pink-600 text-white"><i className="bx bx-cut text-2xl" /></span>,
        },
        {
            title: t('global.all_physiotherapy_procedures'),
            value: formatValue(dashboard.totalPhysiotherapyProcedures),
            subtitle: t('global.all_registered_physiotherapy_procedures'),
            icon: <span className="flex h-12 w-12 items-center justify-center rounded-full bg-teal-500 text-white"><i className="bx bx-spa text-2xl" /></span>,
        },
    ];

    const bedCards = [
        {
            title: t('global.occupied_beds'),
            value: formatValue(dashboard.occupied_beds),
            className: 'border-yellow-300 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20',
            badgeClass: 'bg-yellow-500',
        },
        {
            title: t('global.all_beds'),
            value: formatValue(dashboard.all_beds),
            className: 'border-blue-300 bg-blue-50 dark:border-blue-700 dark:bg-blue-900/20',
            badgeClass: 'bg-blue-600',
        },
        {
            title: t('global.free_beds'),
            value: formatValue(dashboard.free_beds),
            className: 'border-green-300 bg-green-50 dark:border-green-700 dark:bg-green-900/20',
            badgeClass: 'bg-green-600',
        },
    ];

    return (
        <DashboardLayout>
            <Head title={t('global.dashboard')} />

            {loading && (
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

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {statCards.map((card) => (
                    <StatCard key={card.title} {...card} />
                ))}
            </div>

            <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                {bedCards.map((card) => (
                    <Card key={card.title} className={card.className}>
                        <div className="flex items-start justify-between">
                            <div>
                                <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300">{card.title}</h4>
                                <p className="mt-3 text-4xl font-bold text-gray-900 dark:text-white">{card.value}</p>
                            </div>
                            <span className={`rounded-lg p-2 ${card.badgeClass}`}>
                                <i className="bx bx-bed text-2xl text-white" />
                            </span>
                        </div>
                    </Card>
                ))}
            </div>

            <div className="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card>
                    <div className="mb-3 flex items-center gap-2">
                        <i className="bx bx-line-chart text-blue-600" />
                        <h5 className="text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.patients_comparison_graph')}
                        </h5>
                    </div>
                    <LineTrendChart data={dashboard.patientsTrendData} />
                </Card>

                <Card>
                    <div className="mb-3 flex items-center gap-2">
                        <i className="bx bx-line-chart text-blue-600" />
                        <h5 className="text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.appointments_comparison_graph')}
                        </h5>
                    </div>
                    <LineTrendChart data={dashboard.appointmentsTrendData} color="#696cff" />
                </Card>
            </div>

            <Card className="mt-4">
                <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h5 className="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                        <i className="bx bx-user-check text-blue-600" />
                        {t('global.appointments_processed_by_user')}
                    </h5>
                    <div className="flex items-center gap-2">
                        <Label htmlFor="chart-branch-select" className="mb-0 whitespace-nowrap">
                            {t('global.filter_by_branch')}:
                        </Label>
                        <Select
                            id="chart-branch-select"
                            sizing="sm"
                            value={String(dashboard.chartBranchId ?? '')}
                            onChange={handleBranchChange}
                            className="min-w-[180px]"
                        >
                            {dashboard.branches?.map((branch) => (
                                <option key={branch.id} value={branch.id}>
                                    {branch.name}
                                </option>
                            ))}
                        </Select>
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

            <Card className="mt-4">
                <div className="mb-3 flex items-center gap-2">
                    <i className="bx bx-line-chart text-blue-600" />
                    <h5 className="text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.doctors_activity_graph')}
                    </h5>
                </div>
                <WordCloudChart data={dashboard.wordCloudData} />
            </Card>

            <Card className="mt-4">
                <div className="mb-3 flex items-center gap-2">
                    <i className="bx bx-line-chart text-blue-600" />
                    <h5 className="text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.nurses_activity_graph')}
                    </h5>
                </div>
                <HorizontalBarChart
                    data={dashboard.nurseActivityData}
                    label={t('global.nurse_activity_count')}
                    color="rgba(32, 201, 151, 0.85)"
                />
            </Card>
        </DashboardLayout>
    );
}
