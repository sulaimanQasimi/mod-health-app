import { Link, router } from '@inertiajs/react';
import { Button, Label, Spinner } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import {
    ReportAnalyticsSection,
    ReportFilterPanel,
    ReportKpiGrid,
    ReportPageShell,
    ReportResultsCard,
} from '../../../Components/Reports';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import PersianDateInput from '../../../Components/ui/PersianDateInput';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../../Components/ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import { buildPaginationSummary } from '../../../utils/pagination';

interface ReportCase {
    id: number;
    case_number: string;
    status: string;
    created_at: string | null;
    patient?: { name: string; last_name?: string };
    deliveries?: Array<{ delivered_at: string | null }>;
}

interface IndexProps {
    cases: {
        data: ReportCase[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;
        };
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    statusCounts: Record<string, number>;
    hasSearch: boolean;
    summary: {
        avg_days: number | null;
        delivered_count: number;
        total_cases: number;
    };
    filters: { status: string; from: string; to: string };
    statusOptions: string[];
    urls: { current: string; dashboard: string; caseShow: string };
}

const EMPTY_FILTERS = { status: '', from: '', to: '' };

export default function ProstheticsReportsIndex({
    cases,
    statusCounts,
    hasSearch,
    summary,
    filters: serverFilters,
    statusOptions,
    urls,
}: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: typeof filters) => {
            setProcessing(true);
            const params: Record<string, string> = { search: '1' };
            Object.entries(next).forEach(([key, value]) => {
                if (value !== '') {
                    params[key] = value;
                }
            });
            router.get(urls.current, params, {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current]
    );

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };
    const kpis = [
        { key: 'turnaround', label: t('global.prosthetics_avg_turnaround'), value: summary.avg_days ?? '—', icon: 'bx-time-five', accent: 'from-violet-500 to-purple-600' },
        { key: 'delivered', label: t('global.prosthetics_delivered_cases'), value: summary.delivered_count, icon: 'bx-package', accent: 'from-emerald-500 to-teal-600' },
        { key: 'total', label: t('global.total'), value: summary.total_cases, icon: 'bx-folder', accent: 'from-blue-500 to-cyan-600' },
    ];
    const charts = [{
        key: 'status', title: t('global.status'), type: 'donut' as const,
        labels: Object.keys(statusCounts).map((status) => t(`global.prosthetics_case_status_${status}`)),
        values: Object.values(statusCounts),
        colors: ['#8b5cf6', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'],
    }];

    return (
        <ReportPageShell title={t('global.reports')} subtitle={t('global.prosthetics')} accent="from-violet-500 to-purple-600" backHref={urls.dashboard} backLabel={t('global.back')}>
                {hasSearch ? <ReportKpiGrid stats={kpis} columns="sm:grid-cols-3" /> : null}
                {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={charts} /> : null}
                <ReportFilterPanel
                    title={t('global.advanced_filters')}
                    onSubmit={handleSubmit}
                    accentIconClass="text-violet-500"
                    actions={<>
                        <Button type="submit" color="blue" disabled={processing}>
                            {processing ? <><Spinner size="sm" className="me-2" />{t('global.loading')}</> : <><i className="bx bx-search me-2" />{t('global.filter')}</>}
                        </Button>
                        <Button type="button" color="light" onClick={handleReset} disabled={processing}><i className="bx bx-refresh me-2" />{t('global.reset')}</Button>
                    </>}
                >
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <Label>{t('global.status')}</Label>
                                    <SearchableSelect
                                        value={filters.status}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, status: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...statusOptions.map((status) => ({
                                                value: status,
                                                label: t(`global.prosthetics_case_status_${status}`),
                                            })),
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.from')}</Label>
                                    <PersianDateInput
                                        value={filters.from}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, from: value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.to')}</Label>
                                    <PersianDateInput
                                        value={filters.to}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, to: value }))}
                                    />
                                </div>
                            </div>
                </ReportFilterPanel>
                <ReportResultsCard title={t('global.reports')} hasSearch={hasSearch} resultCount={cases.meta.total} resultsLabel={t('global.results')} emptyMessage={t('global.search_and_filters')}>
                            <div className="mb-3 text-sm text-gray-500">{buildPaginationSummary(cases.meta, t)}</div>
                            <div className="overflow-x-auto">
                                <Table embedded>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>{t('global.prosthetics_case_number')}</TableHeader>
                                            <TableHeader>{t('global.patient_name')}</TableHeader>
                                            <TableHeader>{t('global.status')}</TableHeader>
                                            <TableHeader>{t('global.created_at')}</TableHeader>
                                            <TableHeader>{t('global.prosthetics_delivered')}</TableHeader>
                                            <TableHeader className="text-end">{t('global.actions')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {cases.data.length === 0 ? (
                                            <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                                <TableCell colSpan={6} align="center" muted className="py-12">
                                                    {t('global.no_records_found')}
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            cases.data.map((item) => (
                                                <TableRow key={item.id}>
                                                    <TableCell className="font-mono">{item.case_number}</TableCell>
                                                    <TableCell>
                                                        {item.patient
                                                            ? `${item.patient.name} ${item.patient.last_name ?? ''}`.trim()
                                                            : '—'}
                                                    </TableCell>
                                                    <TableCell muted>
                                                        {t(`global.prosthetics_case_status_${item.status}`)}
                                                    </TableCell>
                                                    <TableCell muted dir="ltr">{item.created_at ?? '—'}</TableCell>
                                                    <TableCell muted dir="ltr">
                                                        {item.deliveries?.[0]?.delivered_at ?? '—'}
                                                    </TableCell>
                                                    <TableCell align="right">
                                                        <Link
                                                            href={`${urls.caseShow}/${item.id}`}
                                                            className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-violet-600 hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-950/30"
                                                        >
                                                            <i className="bx bx-expand" />
                                                        </Link>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                            <SettingsPagination links={cases.links} className="mt-4" />
                </ReportResultsCard>
        </ReportPageShell>
    );
}
