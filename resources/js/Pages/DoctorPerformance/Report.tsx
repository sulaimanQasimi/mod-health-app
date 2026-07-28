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
    user: string | null;
    appointments: number;
    prescriptions: number;
    lab_tests: number;
    anesthesias: number;
    total: number;
}

interface DoctorOption {
    id: number;
    name: string;
    specialization: string | null;
    department_name: string | null;
}

interface ReportProps {
    results: PerformanceResult[];
    summary: {
        appointments: number;
        prescriptions: number;
        lab_tests: number;
        anesthesias: number;
        total: number;
    };
    hasSearch: boolean;
    error: string | null;
    filters: {
        startDate: string;
        endDate: string;
        doctorId: string;
    };
    filterOptions: {
        doctors: DoctorOption[];
    };
    urls: {
        current: string;
    };
}

const EMPTY_FILTERS = {
    startDate: '',
    endDate: '',
    doctorId: '',
};

export default function DoctorPerformanceReport({
    results,
    summary,
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

    const grandTotal = summary.total;

    const resultsWithPercentage = useMemo(
        () =>
            results.map((row) => ({
                ...row,
                percentage: grandTotal > 0 ? (row.total / grandTotal) * 100 : 0,
            })),
        [results, grandTotal]
    );
    const kpiStats: ReportKpiStat[] = hasSearch
        ? [
              { key: 'appointments', label: t('global.appointments'), value: summary.appointments, icon: 'bx-calendar', accent: 'from-blue-500 to-cyan-600' },
              { key: 'prescriptions', label: t('global.prescriptions'), value: summary.prescriptions, icon: 'bx-receipt', accent: 'from-emerald-500 to-teal-600' },
              { key: 'lab', label: t('global.lab_tests'), value: summary.lab_tests, icon: 'bx-test-tube', accent: 'from-violet-500 to-purple-600' },
              { key: 'anesthesia', label: t('global.anesthesias'), value: summary.anesthesias, icon: 'bx-first-aid', accent: 'from-amber-500 to-orange-600' },
              { key: 'total', label: t('global.total'), value: summary.total, icon: 'bx-calculator', accent: 'from-rose-500 to-pink-600' },
          ]
        : [];
    const charts: ReportChartCard[] = hasSearch
        ? [
              { key: 'appointments', title: t('global.appointments'), type: 'bar', labels: results.map((row) => row.user ?? '—'), values: results.map((row) => row.appointments), color: '#06b6d4' },
              { key: 'prescriptions', title: t('global.prescriptions'), type: 'bar', labels: results.map((row) => row.user ?? '—'), values: results.map((row) => row.prescriptions), color: '#10b981' },
              { key: 'lab', title: t('global.lab_tests'), type: 'bar', labels: results.map((row) => row.user ?? '—'), values: results.map((row) => row.lab_tests), color: '#8b5cf6' },
          ]
        : [];

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!filters.startDate || !filters.endDate) {
            return;
        }
        setProcessing(true);
        const params: Record<string, string> = {
            search: '1',
            startDate: filters.startDate,
            endDate: filters.endDate,
        };
        if (filters.doctorId) {
            params.doctorId = filters.doctorId;
        }
        router.get(urls.current, params, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <ReportPageShell title={t('global.user_performance_report')} subtitle={t('global.reports')} icon="bx-line-chart" accent="from-rose-600 to-pink-700" backLabel={t('global.back')}>
                {error ? (
                    <Card className="border-rose-200 bg-rose-50 !shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
                        <p className="text-sm text-rose-700 dark:text-rose-300">{t(error)}</p>
                    </Card>
                ) : null}
                {hasSearch ? <ReportKpiGrid stats={kpiStats} /> : null}
                {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={charts} /> : null}
                <ReportFilterPanel title={t('global.advanced_filters')} accentIconClass="text-rose-500" onSubmit={handleSubmit} actions={<><Button type="submit" color="blue" disabled={processing || !filters.startDate || !filters.endDate}>{processing ? <><Spinner size="sm" className="me-2" />{t('global.loading')}</> : <><i className="bx bx-search me-2" />{t('global.search')}</>}</Button><Button type="button" color="light" onClick={handleReset} disabled={processing}><i className="bx bx-refresh me-2" />{t('global.reset')}</Button></>}>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
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
                                    <Label>{t('global.doctor')}</Label>
                                    <SearchableSelect
                                        value={filters.doctorId}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, doctorId: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.doctors.map((doctor) => ({
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
                <ReportResultsCard title={t('global.user_performance_report')} hasSearch={hasSearch} resultCount={results.length} resultsLabel={t('global.results')} emptyMessage={t('global.search_and_filters')}>
                        <div className="overflow-x-auto">
                            <Table embedded>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.user')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.appointments')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.prescriptions')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.lab_tests')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.anesthesias')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.total')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.percentage')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {resultsWithPercentage.length === 0 ? (
                                        <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                            <TableCell colSpan={7} align="center" muted className="py-12">
                                                {t('global.no_records_found')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        resultsWithPercentage.map((row, index) => (
                                            <TableRow key={`${row.user}-${index}`}>
                                                <TableCell className="font-medium">{row.user ?? '—'}</TableCell>
                                                <TableCell align="center">
                                                    <Badge color="info">{row.appointments.toLocaleString()}</Badge>
                                                </TableCell>
                                                <TableCell align="center">
                                                    <Badge color="success">{row.prescriptions.toLocaleString()}</Badge>
                                                </TableCell>
                                                <TableCell align="center">
                                                    <Badge color="purple">{row.lab_tests.toLocaleString()}</Badge>
                                                </TableCell>
                                                <TableCell align="center">
                                                    <Badge color="warning">{row.anesthesias.toLocaleString()}</Badge>
                                                </TableCell>
                                                <TableCell align="center" className="font-semibold">
                                                    {row.total.toLocaleString()}
                                                </TableCell>
                                                <TableCell align="center" muted>
                                                    {row.percentage.toFixed(1)}%
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                </ReportResultsCard>
        </ReportPageShell>
    );
}
