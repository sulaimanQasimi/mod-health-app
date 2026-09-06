import { router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import {
    ReportAnalyticsSection,
    ReportFilterPanel,
    ReportKpiGrid,
    ReportPageShell,
    ReportResultsCard,
} from '../../Components/Reports';
import { ReportChartCard, ReportKpiStat } from '../../Components/Reports/reportTypes';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';

interface PerformanceResult {
    id: number;
    rank: number;
    user: string | null;
    specialization: string | null;
    department_name: string | null;
    appointments: number;
    prescriptions: number;
    lab_tests: number;
    anesthesias: number;
    total: number;
    percentage: number;
}

interface DoctorOption {
    id: number;
    name: string;
    specialization: string | null;
    department_id: number | null;
    department_name: string | null;
}

interface DepartmentOption {
    id: number;
    name: string;
}

interface ReportProps {
    results: PerformanceResult[];
    summary: {
        appointments: number;
        prescriptions: number;
        lab_tests: number;
        anesthesias: number;
        total: number;
        doctor_count: number;
        avg_per_doctor: number;
    };
    analytics: {
        by_activity: Array<{ name: string; count: number }>;
        by_doctor: Array<{ name: string; count: number }>;
        by_department: Array<{ name: string; count: number }>;
    };
    hasSearch: boolean;
    error: string | null;
    filters: {
        startDate: string;
        endDate: string;
        doctorId: string;
        department_id: string;
    };
    filterOptions: {
        doctors: DoctorOption[];
        departments: DepartmentOption[];
    };
    urls: {
        current: string;
        index: string;
    };
}

const EMPTY_FILTERS = {
    startDate: '',
    endDate: '',
    doctorId: '',
    department_id: '',
};

const PARTIAL_KEYS = [
    'results',
    'summary',
    'analytics',
    'hasSearch',
    'error',
    'filters',
    'filterOptions',
    'urls',
] as const;

function MetricCell({
    value,
    color,
}: {
    value: number;
    color: 'info' | 'success' | 'purple' | 'warning';
}) {
    return (
        <TableCell align="center">
            <Badge color={color} className="min-w-[2.75rem] justify-center tabular-nums">
                {value.toLocaleString()}
            </Badge>
        </TableCell>
    );
}

function ShareBar({ percentage }: { percentage: number }) {
    const width = Math.min(100, Math.max(0, percentage));

    return (
        <div className="min-w-[7rem]">
            <div className="mb-1 flex items-center justify-between gap-2 text-xs tabular-nums text-gray-500 dark:text-gray-400">
                <span>{percentage.toFixed(1)}%</span>
            </div>
            <div className="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                <div
                    className="h-full rounded-full bg-gradient-to-r from-rose-500 to-pink-500 transition-[width] duration-500"
                    style={{ width: `${width}%` }}
                />
            </div>
        </div>
    );
}

export default function DoctorPerformanceReport({
    results,
    summary,
    analytics,
    hasSearch,
    error,
    filters: serverFilters,
    filterOptions,
    urls,
}: ReportProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const filteredDoctors = useMemo(() => {
        if (!filters.department_id) {
            return filterOptions.doctors;
        }

        return filterOptions.doctors.filter(
            (doctor) => String(doctor.department_id ?? '') === filters.department_id,
        );
    }, [filterOptions.doctors, filters.department_id]);

    const visit = (params: Record<string, string>, replace = false) => {
        setProcessing(true);
        router.get(urls.current, params, {
            only: [...PARTIAL_KEYS],
            preserveScroll: true,
            preserveState: true,
            replace,
            onFinish: () => setProcessing(false),
        });
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!filters.startDate || !filters.endDate) {
            return;
        }

        const params: Record<string, string> = {
            search: '1',
            startDate: filters.startDate,
            endDate: filters.endDate,
        };
        if (filters.doctorId) {
            params.doctorId = filters.doctorId;
        }
        if (filters.department_id) {
            params.department_id = filters.department_id;
        }
        visit(params);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        visit({}, true);
    };

    const activityLabel = (name: string) => {
        const map: Record<string, string> = {
            appointments: t('global.appointments'),
            prescriptions: t('global.prescriptions'),
            lab_tests: t('global.lab_tests'),
            anesthesias: t('global.anesthesias'),
        };
        return map[name] ?? name;
    };

    const kpiStats: ReportKpiStat[] = hasSearch
        ? [
              {
                  key: 'appointments',
                  label: t('global.appointments'),
                  value: summary.appointments.toLocaleString(),
                  icon: 'bx-calendar-check',
                  accent: 'from-blue-500 to-cyan-600',
              },
              {
                  key: 'prescriptions',
                  label: t('global.prescriptions'),
                  value: summary.prescriptions.toLocaleString(),
                  icon: 'bx-receipt',
                  accent: 'from-emerald-500 to-teal-600',
              },
              {
                  key: 'lab',
                  label: t('global.lab_tests'),
                  value: summary.lab_tests.toLocaleString(),
                  icon: 'bx-test-tube',
                  accent: 'from-violet-500 to-purple-600',
              },
              {
                  key: 'anesthesia',
                  label: t('global.anesthesias'),
                  value: summary.anesthesias.toLocaleString(),
                  icon: 'bx-plus-medical',
                  accent: 'from-amber-500 to-orange-600',
              },
              {
                  key: 'total',
                  label: t('global.total'),
                  value: summary.total.toLocaleString(),
                  icon: 'bx-bar-chart-alt-2',
                  accent: 'from-rose-500 to-pink-600',
                  subtitle: `${summary.doctor_count} ${t('global.doctors')} · ${t('global.avg_per_doctor')}: ${summary.avg_per_doctor}`,
              },
          ]
        : [];

    const charts: ReportChartCard[] = hasSearch
        ? [
              {
                  key: 'activity',
                  title: t('global.activity_breakdown'),
                  type: 'donut',
                  labels: (analytics.by_activity ?? []).map((item) => activityLabel(item.name)),
                  values: (analytics.by_activity ?? []).map((item) => item.count),
                  colors: ['#06b6d4', '#10b981', '#8b5cf6', '#f59e0b'],
              },
              {
                  key: 'doctors',
                  title: t('global.doctors_activity_graph'),
                  type: 'bar',
                  labels: (analytics.by_doctor ?? []).map((item) => item.name),
                  values: (analytics.by_doctor ?? []).map((item) => item.count),
                  color: '#f43f5e',
              },
              {
                  key: 'departments',
                  title: t('global.department'),
                  type: 'bar',
                  labels: (analytics.by_department ?? []).map((item) => item.name),
                  values: (analytics.by_department ?? []).map((item) => item.count),
                  color: '#6366f1',
              },
          ]
        : [];

    return (
        <ReportPageShell
            title={t('global.user_performance_report')}
            subtitle={t('global.reports')}
            icon="bx-line-chart"
            accent="from-rose-600 to-pink-700"
            backHref={urls.index}
            backLabel={t('global.back')}
        >
            <ReportFilterPanel
                title={t('global.advanced_filters')}
                accentIconClass="text-rose-500"
                onSubmit={handleSubmit}
                actions={
                    <>
                        <Button
                            type="submit"
                            color="blue"
                            disabled={processing || !filters.startDate || !filters.endDate}
                        >
                            {processing ? (
                                <>
                                    <Spinner size="sm" className="me-2" />
                                    {t('global.loading')}
                                </>
                            ) : (
                                <>
                                    <i className="bx bx-search me-2" />
                                    {t('global.search')}
                                </>
                            )}
                        </Button>
                        <Button type="button" color="light" onClick={handleReset} disabled={processing}>
                            <i className="bx bx-refresh me-2" />
                            {t('global.reset')}
                        </Button>
                    </>
                }
            >
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <Label>{t('global.from')}</Label>
                        <PersianDateInput
                            value={filters.startDate}
                            onChange={(value) => setFilters((prev) => ({ ...prev, startDate: value }))}
                        />
                    </div>
                    <div>
                        <Label>{t('global.to')}</Label>
                        <PersianDateInput
                            value={filters.endDate}
                            onChange={(value) => setFilters((prev) => ({ ...prev, endDate: value }))}
                        />
                    </div>
                    <div>
                        <Label>{t('global.department')}</Label>
                        <SearchableSelect
                            value={filters.department_id}
                            onChange={(value) =>
                                setFilters((prev) => ({
                                    ...prev,
                                    department_id: value,
                                    doctorId: '',
                                }))
                            }
                            options={[
                                { value: '', label: t('global.all_departments') },
                                ...filterOptions.departments.map((department) => ({
                                    value: String(department.id),
                                    label: department.name,
                                })),
                            ]}
                        />
                    </div>
                    <div>
                        <Label>{t('global.doctor')}</Label>
                        <SearchableSelect
                            value={filters.doctorId}
                            onChange={(value) => setFilters((prev) => ({ ...prev, doctorId: value }))}
                            options={[
                                { value: '', label: t('global.all') },
                                ...filteredDoctors.map((doctor) => ({
                                    value: String(doctor.id),
                                    label: doctor.department_name
                                        ? `${doctor.name} — ${doctor.department_name}`
                                        : doctor.name,
                                })),
                            ]}
                        />
                    </div>
                </div>
            </ReportFilterPanel>

            {error ? (
                <Card className="border-rose-200 bg-rose-50 !shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
                    <div className="flex items-start gap-3">
                        <i className="bx bx-error-circle mt-0.5 text-xl text-rose-600 dark:text-rose-300" />
                        <p className="text-sm text-rose-700 dark:text-rose-300">{t(error)}</p>
                    </div>
                </Card>
            ) : null}

            {hasSearch ? (
                <ReportKpiGrid stats={kpiStats} columns="sm:grid-cols-2 xl:grid-cols-5" />
            ) : null}

            {hasSearch ? (
                <ReportAnalyticsSection title={t('global.activity_breakdown')} charts={charts} />
            ) : null}

            <ReportResultsCard
                title={t('global.user_performance_report')}
                hasSearch={hasSearch}
                resultCount={results.length}
                resultsLabel={t('global.results')}
                emptyMessage={t('global.search_and_filters')}
            >
                <div className="overflow-x-auto">
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader className="w-14">#</TableHeader>
                                <TableHeader>{t('global.doctor')}</TableHeader>
                                <TableHeader>{t('global.department')}</TableHeader>
                                <TableHeader className="text-center">{t('global.appointments')}</TableHeader>
                                <TableHeader className="text-center">{t('global.prescriptions')}</TableHeader>
                                <TableHeader className="text-center">{t('global.lab_tests')}</TableHeader>
                                <TableHeader className="text-center">{t('global.anesthesias')}</TableHeader>
                                <TableHeader className="text-center">{t('global.total')}</TableHeader>
                                <TableHeader>{t('global.percentage')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {results.length === 0 ? (
                                <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                    <TableCell colSpan={9} align="center" muted className="py-12">
                                        {t('global.no_records_found')}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                results.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="font-mono text-xs text-gray-500">
                                            {row.rank}
                                        </TableCell>
                                        <TableCell>
                                            <div className="min-w-0">
                                                <div className="font-medium text-gray-900 dark:text-white">
                                                    {row.user ?? '—'}
                                                </div>
                                                {row.specialization ? (
                                                    <div className="truncate text-xs text-gray-500 dark:text-gray-400">
                                                        {row.specialization}
                                                    </div>
                                                ) : null}
                                            </div>
                                        </TableCell>
                                        <TableCell muted>{row.department_name ?? '—'}</TableCell>
                                        <MetricCell value={row.appointments} color="info" />
                                        <MetricCell value={row.prescriptions} color="success" />
                                        <MetricCell value={row.lab_tests} color="purple" />
                                        <MetricCell value={row.anesthesias} color="warning" />
                                        <TableCell align="center" className="font-semibold tabular-nums">
                                            {row.total.toLocaleString()}
                                        </TableCell>
                                        <TableCell>
                                            <ShareBar percentage={row.percentage} />
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {hasSearch && results.length > 0 ? (
                    <div className="mt-4 grid gap-3 rounded-xl border border-gray-100 bg-gray-50/80 p-4 text-sm dark:border-gray-700 dark:bg-gray-900/40 sm:grid-cols-3">
                        <div>
                            <p className="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {t('global.doctors')}
                            </p>
                            <p className="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white">
                                {summary.doctor_count.toLocaleString()}
                            </p>
                        </div>
                        <div>
                            <p className="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {t('global.avg_per_doctor')}
                            </p>
                            <p className="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white">
                                {summary.avg_per_doctor.toLocaleString()}
                            </p>
                        </div>
                        <div>
                            <p className="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {t('global.total')}
                            </p>
                            <p className="mt-1 text-lg font-semibold tabular-nums text-rose-600 dark:text-rose-400">
                                {summary.total.toLocaleString()}
                            </p>
                        </div>
                    </div>
                ) : null}
            </ReportResultsCard>
        </ReportPageShell>
    );
}
