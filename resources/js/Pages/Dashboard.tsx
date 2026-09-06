import { Head } from '@inertiajs/react';
import { Alert, Card, Label, Spinner } from 'flowbite-react';
import { useCallback, useState } from 'react';
import HorizontalBarChart from '../Components/Dashboard/HorizontalBarChart';
import LineTrendChart from '../Components/Dashboard/LineTrendChart';
import WordCloudChart from '../Components/Dashboard/WordCloudChart';
import DashboardLayout from '../Components/Layout/DashboardLayout';
import BedCard from '../Components/ui/BedCard';
import SearchableSelect from '../Components/ui/SearchableSelect';
import StatCard from '../Components/ui/StatCard';
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
            const response = await fetch(`/dashboard/data${params}`, {
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

    const handleBranchChange = (branchId: string) => {
        if (branchId) {
            loadDashboardData(branchId);
        }
    };

    const statCards = [
        {
            title: t('global.today_patients'),
            value: formatValue(dashboard.todayPatients),
            subtitle: t('global.today_registered_patients'),
            iconClass: 'bx bx-user-plus',
            iconBgClass: 'bg-blue-600',
            borderClass: 'border-blue-500',
            valueClass: 'text-blue-600',
        },
        {
            title: t('global.emergency_today_patients'),
            value: formatValue(dashboard.totalEmergencyPatients),
            subtitle: `${t('global.emergency')} ${t('global.today_registered_patients')}`,
            iconClass: 'bx bx-first-aid',
            iconBgClass: 'bg-red-600',
            borderClass: 'border-red-500',
            valueClass: 'text-red-600',
        },
        {
            title: t('global.all_patients'),
            value: formatValue(dashboard.totalPatients),
            subtitle: t('global.all_registered_patients'),
            iconClass: 'bx bx-user',
            iconBgClass: 'bg-blue-600',
            borderClass: 'border-blue-500',
            valueClass: 'text-blue-600',
        },
        {
            title: t('global.all_appointments'),
            value: formatValue(dashboard.totalAppointments),
            subtitle: t('global.all_registered_appointments'),
            iconClass: 'bx bx-history',
            iconBgClass: 'bg-green-600',
            borderClass: 'border-green-500',
            valueClass: 'text-green-600',
        },
        {
            title: t('global.consultations'),
            value: formatValue(dashboard.totalConsultations),
            subtitle: t('global.all_registered_consultations'),
            iconClass: 'bx bx-chat',
            iconBgClass: 'bg-cyan-600',
            borderClass: 'border-cyan-500',
            valueClass: 'text-cyan-600',
        },
        {
            title: t('global.all_hospitalized_patients'),
            value: formatValue(dashboard.totalInPatientAdmissions),
            subtitle: t('global.all_registered_hospitalizations'),
            iconClass: 'bx bx-bed',
            iconBgClass: 'bg-yellow-500',
            borderClass: 'border-yellow-500',
            valueClass: 'text-yellow-600',
        },
        {
            title: t('global.checkups'),
            value: formatValue(dashboard.totalCheckups),
            subtitle: t('global.all_registered_checkups'),
            iconClass: 'bx bx-hard-hat',
            iconBgClass: 'bg-red-600',
            borderClass: 'border-red-500',
            valueClass: 'text-red-600',
        },
        {
            title: t('global.all_icu_patients'),
            value: formatValue(dashboard.totalIcuAdmissions),
            subtitle: t('global.all_registered_icu'),
            iconClass: 'bx bx-tv',
            iconBgClass: 'bg-gray-800',
            borderClass: 'border-gray-700',
            valueClass: 'text-gray-800 dark:text-gray-200',
        },
        {
            title: t('global.all_ccu_patients'),
            value: formatValue(dashboard.totalCcuAdmissions),
            subtitle: t('global.all_registered_ccu'),
            iconClass: 'bx bx-heart-circle',
            iconBgClass: 'bg-cyan-600',
            borderClass: 'border-cyan-500',
            valueClass: 'text-cyan-600',
        },
        {
            title: t('global.all_prescriptions'),
            value: formatValue(dashboard.totalPrescriptions),
            subtitle: t('global.all_registered_prescriptions'),
            iconClass: 'bx bx-receipt',
            iconBgClass: 'bg-purple-600',
            borderClass: 'border-purple-500',
            valueClass: 'text-purple-600',
        },
        {
            title: t('global.all_operations'),
            value: formatValue(dashboard.totalOperations),
            subtitle: t('global.all_registered_operations'),
            iconClass: 'bx bx-cut',
            iconBgClass: 'bg-pink-600',
            borderClass: 'border-pink-500',
            valueClass: 'text-pink-600',
        },
        {
            title: t('global.all_physiotherapy_procedures'),
            value: formatValue(dashboard.totalPhysiotherapyProcedures),
            subtitle: t('global.all_registered_physiotherapy_procedures'),
            iconClass: 'bx bx-spa',
            iconBgClass: 'bg-teal-500',
            borderClass: 'border-teal-500',
            valueClass: 'text-teal-600',
        },
    ];

    const bedCards = [
        {
            title: t('global.occupied_beds'),
            value: formatValue(dashboard.occupied_beds),
            iconClass: 'bx bx-bed',
            iconBgClass: 'bg-yellow-500',
            borderClass: 'border-yellow-500',
            valueClass: 'text-yellow-600',
        },
        {
            title: t('global.all_beds'),
            value: formatValue(dashboard.all_beds),
            iconClass: 'bx bx-bed',
            iconBgClass: 'bg-blue-600',
            borderClass: 'border-blue-500',
            valueClass: 'text-blue-600',
        },
        {
            title: t('global.free_beds'),
            value: formatValue(dashboard.free_beds),
            iconClass: 'bx bx-bed',
            iconBgClass: 'bg-green-600',
            borderClass: 'border-green-500',
            valueClass: 'text-green-600',
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
                    <BedCard key={card.title} {...card} />
                ))}
            </div>

            <div className="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card>
                    <div className="mb-3 flex items-center gap-2">
                        <i className="bx bx-line-chart text-blue-600" />
                        <h5 className="text-lg text-gray-900 dark:text-white">
                            {t('global.patients_comparison_graph')}
                        </h5>
                    </div>
                    <LineTrendChart data={dashboard.patientsTrendData} />
                </Card>

                <Card>
                    <div className="mb-3 flex items-center gap-2">
                        <i className="bx bx-line-chart text-blue-600" />
                        <h5 className="text-lg text-gray-900 dark:text-white">
                            {t('global.appointments_comparison_graph')}
                        </h5>
                    </div>
                    <LineTrendChart data={dashboard.appointmentsTrendData} color="#696cff" />
                </Card>
            </div>

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

            <Card className="mt-4">
                <div className="mb-3 flex items-center gap-2">
                    <i className="bx bx-line-chart text-blue-600" />
                    <h5 className="text-lg text-gray-900 dark:text-white">
                        {t('global.doctors_activity_graph')}
                    </h5>
                </div>
                <WordCloudChart data={dashboard.wordCloudData} />
            </Card>

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
        </DashboardLayout>
    );
}
