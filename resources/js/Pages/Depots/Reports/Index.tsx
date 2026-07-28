import { Button, Card, Label } from 'flowbite-react';
import { useState } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import { DEPOT_CARD_CLASS } from '../../../Components/Depots/depotUi';
import { ReportAnalyticsSection, ReportKpiGrid, ReportPageShell } from '../../../Components/Reports';
import PersianDateInput from '../../../Components/ui/PersianDateInput';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotNavPermissions, DepotNavUrls } from '../../../types/depot';
import { OptionItem } from '../../../types/settings';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../../utils/settingsUi';

type ReportKey = 'transactions' | 'stock' | 'movements' | 'requests';

interface ReportFilters {
    depot_id: string;
    item_type: string;
    status: string;
    date_from: string;
    date_to: string;
}

interface DepotReportSummary {
    stock_items: number;
    stock_quantity: number;
    low_stock: number;
    pending_requests: number;
    fulfilled_requests: number;
}

interface DepotReportAnalytics {
    stock_by_type: Array<{ name: string; count: number }>;
    requests_by_status: Array<{ name: string; count: number }>;
    transactions_by_type: Array<{ name: string; count: number }>;
}

const EMPTY_FILTERS: ReportFilters = {
    depot_id: '',
    item_type: '',
    status: '',
    date_from: '',
    date_to: '',
};

const REPORTS: Array<{
    key: ReportKey;
    labelKey: string;
    showDepot: boolean;
    showItemType: boolean;
    showStatus: boolean;
    statusOptions?: string[];
}> = [
    {
        key: 'transactions',
        labelKey: 'global.depot.report_transactions',
        showDepot: true,
        showItemType: true,
        showStatus: false,
    },
    {
        key: 'stock',
        labelKey: 'global.depot.report_stock',
        showDepot: true,
        showItemType: true,
        showStatus: false,
    },
    {
        key: 'movements',
        labelKey: 'global.depot.report_movements',
        showDepot: true,
        showItemType: false,
        showStatus: false,
    },
    {
        key: 'requests',
        labelKey: 'global.depot.report_requests',
        showDepot: false,
        showItemType: false,
        showStatus: true,
    },
];

function buildExportUrl(baseUrl: string, report: ReportKey, type: 'excel' | 'pdf', filters: ReportFilters): string {
    const params = new URLSearchParams({ report, type });
    if (filters.depot_id) params.set('depot_id', filters.depot_id);
    if (filters.item_type) params.set('item_type', filters.item_type);
    if (filters.status) params.set('status', filters.status);
    if (filters.date_from) params.set('date_from', filters.date_from);
    if (filters.date_to) params.set('date_to', filters.date_to);
    return `${baseUrl}?${params.toString()}`;
}

export default function IndexDepotReports({
    filterOptions,
    summary,
    analytics,
    navUrls,
    navPermissions,
    urls,
}: {
    filterOptions: {
        depots: OptionItem[];
        pharmacies: OptionItem[];
        medicines: OptionItem[];
        tools: OptionItem[];
        transactionTypes: string[];
        transactionStatuses: string[];
        requestStatuses: string[];
    };
    summary: DepotReportSummary;
    analytics: DepotReportAnalytics;
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: { export: string };
}) {
    const { t } = useTranslation();
    const [filtersByReport, setFiltersByReport] = useState<Record<ReportKey, ReportFilters>>({
        transactions: { ...EMPTY_FILTERS },
        stock: { ...EMPTY_FILTERS },
        movements: { ...EMPTY_FILTERS },
        requests: { ...EMPTY_FILTERS },
    });

    const updateFilter = (report: ReportKey, field: keyof ReportFilters, value: string) => {
        setFiltersByReport((prev) => ({
            ...prev,
            [report]: { ...prev[report], [field]: value },
        }));
    };

    return (
        <ReportPageShell
            title={t('global.depot.reports')}
            subtitle={t('global.depot.title')}
            icon="bx-bar-chart-alt-2"
            accent="from-indigo-500 to-blue-700"
            backLabel={t('global.back')}
        >
            <div className={`${SETTINGS_WIDE_FORM_WIDTH} space-y-5`}>
                <DepotNavTabs active="reports" urls={navUrls} permissions={navPermissions} />
                <ReportKpiGrid
                    columns="sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5"
                    stats={[
                        {
                            key: 'stock-items',
                            label: t('global.depot.report_stock'),
                            value: summary.stock_items,
                            icon: 'bx-package',
                            accent: 'from-cyan-500 to-blue-600',
                        },
                        {
                            key: 'stock-quantity',
                            label: t('global.quantity'),
                            value: summary.stock_quantity,
                            icon: 'bx-box',
                            accent: 'from-emerald-500 to-teal-600',
                        },
                        {
                            key: 'low-stock',
                            label: t('global.low_stock') !== 'global.low_stock' ? t('global.low_stock') : 'Low stock',
                            value: summary.low_stock,
                            icon: 'bx-error-circle',
                            accent: 'from-amber-500 to-orange-600',
                        },
                        {
                            key: 'pending-requests',
                            label: t('global.pending'),
                            value: summary.pending_requests,
                            icon: 'bx-time-five',
                            accent: 'from-violet-500 to-purple-600',
                        },
                        {
                            key: 'fulfilled-requests',
                            label: t('global.fulfilled'),
                            value: summary.fulfilled_requests,
                            icon: 'bx-check-circle',
                            accent: 'from-pink-500 to-rose-600',
                        },
                    ]}
                />
                <ReportAnalyticsSection
                    title={t('global.depot.reports')}
                    charts={[
                        {
                            key: 'stock-by-type',
                            title: t('global.depot.report_stock'),
                            type: 'donut',
                            labels: analytics.stock_by_type.map((item) =>
                                item.name === 'medicine' ? t('global.medicine') : t('global.depot.tool'),
                            ),
                            values: analytics.stock_by_type.map((item) => item.count),
                            colors: ['#06b6d4', '#6366f1'],
                        },
                        {
                            key: 'requests-by-status',
                            title: t('global.depot.report_requests'),
                            type: 'bar',
                            labels: analytics.requests_by_status.map((item) => item.name),
                            values: analytics.requests_by_status.map((item) => item.count),
                            color: '#8b5cf6',
                        },
                        {
                            key: 'transactions-by-type',
                            title: t('global.depot.report_transactions'),
                            type: 'bar',
                            labels: analytics.transactions_by_type.map((item) => item.name),
                            values: analytics.transactions_by_type.map((item) => item.count),
                            color: '#2563eb',
                        },
                    ]}
                />
                <Card className="!shadow-sm">
                    <div className="space-y-4">
                        {REPORTS.map((report) => {
                            const filters = filtersByReport[report.key];
                            const statusOptions =
                                report.key === 'requests'
                                    ? filterOptions.requestStatuses
                                    : filterOptions.transactionStatuses;

                            return (
                                <div key={report.key} className={`${DEPOT_CARD_CLASS} p-4`}>
                                    <h3 className="mb-4 text-base font-semibold text-gray-900 dark:text-white">
                                        {t(report.labelKey)}
                                    </h3>
                                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                        {report.showDepot && (
                                            <div>
                                                <Label>{t('global.depot.name')}</Label>
                                                <SearchableSelect
                                                    value={filters.depot_id}
                                                    onChange={(value) => updateFilter(report.key, 'depot_id', value)}
                                                    options={[
                                                        { value: '', label: t('global.all') },
                                                        ...filterOptions.depots.map((depot) => ({
                                                            value: String(depot.id),
                                                            label: depot.name,
                                                        })),
                                                    ]}
                                                />
                                            </div>
                                        )}
                                        {report.showItemType && (
                                            <div>
                                                <Label>{t('global.type')}</Label>
                                                <SearchableSelect
                                                    value={filters.item_type}
                                                    onChange={(value) => updateFilter(report.key, 'item_type', value)}
                                                    options={[
                                                        { value: '', label: t('global.all') },
                                                        { value: 'medicine', label: t('global.medicine') },
                                                        { value: 'tool', label: t('global.depot.tool') },
                                                    ]}
                                                />
                                            </div>
                                        )}
                                        {report.showStatus && (
                                            <div>
                                                <Label>{t('global.status')}</Label>
                                                <SearchableSelect
                                                    value={filters.status}
                                                    onChange={(value) => updateFilter(report.key, 'status', value)}
                                                    options={[
                                                        { value: '', label: t('global.all') },
                                                        ...(statusOptions ?? []).map((status) => ({
                                                            value: status,
                                                            label: status,
                                                        })),
                                                    ]}
                                                />
                                            </div>
                                        )}
                                        <div>
                                            <Label>{t('global.date_from')}</Label>
                                            <PersianDateInput
                                                value={filters.date_from}
                                                onChange={(value) => updateFilter(report.key, 'date_from', value)}
                                            />
                                        </div>
                                        <div>
                                            <Label>{t('global.date_to')}</Label>
                                            <PersianDateInput
                                                value={filters.date_to}
                                                onChange={(value) => updateFilter(report.key, 'date_to', value)}
                                            />
                                        </div>
                                    </div>
                                    <div className="mt-4 flex flex-wrap gap-2">
                                        <Button
                                            color="success"
                                            as="a"
                                            href={buildExportUrl(urls.export, report.key, 'excel', filters)}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Excel
                                        </Button>
                                        <Button
                                            color="failure"
                                            as="a"
                                            href={buildExportUrl(urls.export, report.key, 'pdf', filters)}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            PDF
                                        </Button>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </Card>
            </div>
        </ReportPageShell>
    );
}
