import { router, usePage } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import {
    ReportAnalyticsSection,
    ReportExportButtons,
    ReportFilterPanel,
    ReportKpiGrid,
    ReportPageShell,
    ReportResultsCard,
} from '../../Components/Reports';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import { OptionItem, PaginatedResult } from '../../types/settings';

interface OutcomeReportItem {
    id: number;
    name: string;
    usage_count: number;
}

interface OutcomeReportFilters {
    search: string;
    pharmacy_id: string;
    date_from: string;
    date_to: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

interface OutcomeReportSummary {
    total_items: number;
    total_usage: number;
    top_usage: number;
}

interface OutcomeReportAnalytics {
    top_items: Array<{ name: string; count: number }>;
}

const EMPTY_FILTERS: OutcomeReportFilters = {
    search: '',
    pharmacy_id: '',
    date_from: '',
    date_to: '',
    sort_by: 'usage_count',
    sort_order: 'desc',
    per_page: '15',
};

export default function ReportOutcomes({
    outcomes,
    summary,
    analytics,
    filters: serverFilters,
    filterOptions,
    urls,
}: {
    outcomes: PaginatedResult<OutcomeReportItem>;
    summary: OutcomeReportSummary;
    analytics: OutcomeReportAnalytics;
    filters: OutcomeReportFilters;
    filterOptions: { pharmacies: OptionItem[] };
    urls: { index: string; report: string; export?: string };
}) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: OutcomeReportFilters) => {
            setProcessing(true);
            router.get(
                urls.report,
                Object.fromEntries(Object.entries(next).filter(([, value]) => value !== '')),
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                    onFinish: () => setProcessing(false),
                },
            );
        },
        [urls.report],
    );

    const showPharmacyFilter = (filterOptions?.pharmacies ?? []).length > 0;
    const canExport = Boolean(urls.export && outcomes.meta.total > 0);
    const exportFields = Object.fromEntries(
        Object.entries(filters).filter(([, value]) => value !== ''),
    ) as Record<string, string>;

    const kpiStats = [
        {
            key: 'items',
            label: t('global.total'),
            value: summary.total_items,
            icon: 'bx-capsule',
            accent: 'from-cyan-500 to-blue-600',
        },
        {
            key: 'usage',
            label: t('global.usage_count'),
            value: summary.total_usage,
            icon: 'bx-bar-chart-alt-2',
            accent: 'from-emerald-500 to-teal-600',
        },
        {
            key: 'top-usage',
            label: t('global.top_usage') !== 'global.top_usage' ? t('global.top_usage') : t('global.usage_count'),
            value: summary.top_usage,
            icon: 'bx-trophy',
            accent: 'from-amber-500 to-orange-600',
        },
    ];

    return (
        <ReportPageShell
            title={t('global.outcome_report')}
            subtitle={t('global.usage_count')}
            icon="bx-log-out"
            accent="from-orange-500 to-amber-600"
            backHref={urls.index}
            backLabel={t('global.back')}
            action={
                canExport && urls.export ? (
                    <ReportExportButtons
                        action={urls.export}
                        csrfToken={csrfToken}
                        fields={exportFields}
                    />
                ) : undefined
            }
        >
            <ReportKpiGrid stats={kpiStats} columns="sm:grid-cols-2 lg:grid-cols-3" />
            <ReportAnalyticsSection
                title={t('global.usage_count')}
                charts={[
                    {
                        key: 'top-items',
                        title: t('global.usage_count'),
                        type: 'bar',
                        labels: analytics.top_items.map((item) => item.name),
                        values: analytics.top_items.map((item) => item.count),
                        color: '#f97316',
                    },
                ]}
            />

            <ReportFilterPanel
                title={t('global.advanced_filters')}
                onSubmit={(event: FormEvent) => {
                    event.preventDefault();
                    applyFilters(filters);
                }}
                actions={
                    <>
                        <Button type="submit" color="blue" disabled={processing}>
                            {processing ? <Spinner size="sm" className="me-2" /> : <i className="bx bx-search me-2" />}
                            {processing ? t('global.loading') : t('global.search')}
                        </Button>
                        <Button
                            type="button"
                            color="light"
                            disabled={processing}
                            onClick={() => {
                                const next = { ...EMPTY_FILTERS, per_page: filters.per_page };
                                setFilters(next);
                                applyFilters(next);
                            }}
                        >
                            <i className="bx bx-refresh me-2" />
                            {t('global.reset')}
                        </Button>
                    </>
                }
            >
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                placeholder={t('global.search')}
                            />
                        </div>
                        {showPharmacyFilter && (
                            <div>
                                <Label>{t('global.pharmacy')}</Label>
                                <SearchableSelect
                                    value={filters.pharmacy_id}
                                    onChange={(value) => setFilters({ ...filters, pharmacy_id: value })}
                                    options={[
                                        { value: '', label: t('global.all') },
                                        ...(filterOptions?.pharmacies ?? []).map((pharmacy) => ({
                                            value: String(pharmacy.id),
                                            label: pharmacy.name,
                                        })),
                                    ]}
                                    placeholder={t('global.all')}
                                />
                            </div>
                        )}
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <TextInput
                                value={filters.date_from}
                                onChange={(event) => setFilters({ ...filters, date_from: event.target.value })}
                                placeholder={t('global.date_from')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <TextInput
                                value={filters.date_to}
                                onChange={(event) => setFilters({ ...filters, date_to: event.target.value })}
                                placeholder={t('global.date_to')}
                            />
                        </div>
                </div>
            </ReportFilterPanel>

            <ReportResultsCard
                title={t('global.outcome_report')}
                hasSearch
                resultCount={outcomes.meta.total}
                resultsLabel={t('global.results')}
                emptyMessage={t('global.no_medicine_usage_found')}
            >
                <div className="overflow-x-auto">
                    <Table embedded>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.usage_count')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {outcomes.data.length === 0 ? (
                                    <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                        <TableCell colSpan={3} align="center" muted className="py-10">
                                            {t('global.no_medicine_usage_found')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    outcomes.data.map((item, index) => (
                                        <TableRow key={item.id}>
                                            <TableCell>{(outcomes.meta.from ?? 1) + index}</TableCell>
                                            <TableCell className="font-medium">{item.name}</TableCell>
                                            <TableCell className="font-semibold">{item.usage_count}</TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                    </Table>
                </div>
                <SettingsPagination links={outcomes.links} />
            </ReportResultsCard>
        </ReportPageShell>
    );
}
