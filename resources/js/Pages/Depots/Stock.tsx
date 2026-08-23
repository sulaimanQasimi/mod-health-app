import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import DepotNavTabs from '../../Components/Depots/DepotNavTabs';
import {
    depotStockBarColor,
    depotStockLevelBadgeColor,
    depotStockLevelLabel,
    type DepotStockLevel,
} from '../../Components/Depots/depotUi';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import StatCard from '../../Components/ui/StatCard';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotNavPermissions, DepotNavUrls } from '../../types/depot';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface StockItem {
    item_type: string;
    item_id: number;
    name: string;
    available: number;
    unit: string | null;
    stock_status: DepotStockLevel;
}

interface StockStats {
    total_items: number;
    total_quantity: number;
    medicine_count: number;
    tool_count: number;
    total_low_stock: number;
    total_out_of_stock: number;
    total_healthy: number;
}

interface StockFilters {
    item_type: string;
    search: string;
    stock_status: string;
    sort_by: string;
    sort_order: string;
}

const EMPTY_FILTERS: StockFilters = {
    item_type: '',
    search: '',
    stock_status: '',
    sort_by: 'name',
    sort_order: 'asc',
};

const STOCK_STATUS_CHIPS: Array<{ value: string; labelKey: string; icon: string; activeClass: string }> = [
    { value: '', labelKey: 'global.all', icon: 'bx-grid-alt', activeClass: 'from-slate-600 to-gray-700' },
    { value: 'healthy', labelKey: 'global.in_stock', icon: 'bx-check-circle', activeClass: 'from-emerald-500 to-teal-600' },
    { value: 'low_stock', labelKey: 'global.low_stock', icon: 'bx-error', activeClass: 'from-amber-500 to-orange-600' },
    { value: 'out_of_stock', labelKey: 'global.out_of_stock', icon: 'bx-x-circle', activeClass: 'from-red-500 to-rose-600' },
];

function itemTypeIcon(itemType: string): string {
    return itemType === 'medicine' ? 'bx-capsule' : 'bx-wrench';
}

function itemTypeGradient(itemType: string): string {
    return itemType === 'medicine'
        ? 'from-sky-500 to-blue-600'
        : 'from-violet-500 to-purple-600';
}

export default function StockDepot({
    depot,
    stockItems,
    stockStats,
    maxQuantity,
    filters: serverFilters,
    filterOptions,
    navUrls,
    navPermissions,
    urls,
    permissions,
}: {
    depot: { id: number; name: string; is_active: boolean };
    stockItems: StockItem[];
    stockStats: StockStats;
    maxQuantity: number;
    filters: StockFilters;
    filterOptions: { stockStatuses: string[]; sortFields: string[] };
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: {
        show: string;
        stock: string;
        requestCreate: string;
        transactionCreate: string;
    };
    permissions: { request_create: boolean; transaction_create: boolean };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: StockFilters) => {
            setProcessing(true);
            router.get(
                urls.stock,
                Object.fromEntries(Object.entries(next).filter(([, value]) => value !== '')),
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                    onFinish: () => setProcessing(false),
                },
            );
        },
        [urls.stock],
    );

    const applyChip = (stockStatus: string) => {
        const next = { ...filters, stock_status: stockStatus };
        setFilters(next);
        applyFilters(next);
    };

    const statCards = useMemo(
        () => [
            {
                title: t('global.total_stock'),
                value: stockStats.total_quantity.toLocaleString(),
                subtitle: `${stockStats.total_items} ${t('global.items')}`,
                iconClass: 'bx bx-box',
                iconBgClass: 'bg-gradient-to-br from-amber-500 to-orange-600',
                borderClass: 'border-amber-200 dark:border-amber-800',
                valueClass: 'text-amber-700 dark:text-amber-300',
            },
            {
                title: t('global.medicine'),
                value: stockStats.medicine_count.toLocaleString(),
                subtitle: t('global.depot.stock_summary'),
                iconClass: 'bx bx-capsule',
                iconBgClass: 'bg-gradient-to-br from-sky-500 to-blue-600',
                borderClass: 'border-sky-200 dark:border-sky-800',
                valueClass: 'text-sky-700 dark:text-sky-300',
            },
            {
                title: t('global.depot.tool'),
                value: stockStats.tool_count.toLocaleString(),
                subtitle: t('global.depot.stock_summary'),
                iconClass: 'bx bx-wrench',
                iconBgClass: 'bg-gradient-to-br from-violet-500 to-purple-600',
                borderClass: 'border-violet-200 dark:border-violet-800',
                valueClass: 'text-violet-700 dark:text-violet-300',
            },
            {
                title: t('global.in_stock'),
                value: stockStats.total_healthy.toLocaleString(),
                subtitle: t('global.stock_status'),
                iconClass: 'bx bx-check-circle',
                iconBgClass: 'bg-gradient-to-br from-emerald-500 to-teal-600',
                borderClass: 'border-emerald-200 dark:border-emerald-800',
                valueClass: 'text-emerald-700 dark:text-emerald-300',
            },
            {
                title: t('global.low_stock'),
                value: stockStats.total_low_stock.toLocaleString(),
                subtitle: t('global.total_low_stock'),
                iconClass: 'bx bx-error',
                iconBgClass: 'bg-gradient-to-br from-amber-500 to-orange-600',
                borderClass: 'border-amber-200 dark:border-amber-800',
                valueClass: 'text-amber-700 dark:text-amber-300',
            },
            {
                title: t('global.out_of_stock'),
                value: stockStats.total_out_of_stock.toLocaleString(),
                subtitle: t('global.total_out_of_stock'),
                iconClass: 'bx bx-x-circle',
                iconBgClass: 'bg-gradient-to-br from-red-500 to-rose-600',
                borderClass: 'border-red-200 dark:border-red-800',
                valueClass: 'text-red-700 dark:text-red-300',
            },
        ],
        [stockStats, t],
    );

    const composition = useMemo(() => {
        const total = stockStats.medicine_count + stockStats.tool_count;
        if (total === 0) {
            return { medicinePct: 0, toolPct: 0 };
        }

        return {
            medicinePct: Math.round((stockStats.medicine_count / total) * 100),
            toolPct: Math.round((stockStats.tool_count / total) * 100),
        };
    }, [stockStats]);

    return (
        <DashboardLayout>
            <Head title={`${depot.name} — ${t('global.depot.full_stock')}`} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <DepotNavTabs active="index" urls={navUrls} permissions={navPermissions} />

                <Card className="overflow-hidden shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.full_stock')}
                        subtitle={depot.name}
                        icon="bx-box"
                        accent="from-amber-500 to-orange-600"
                        backHref={urls.show}
                        backLabel={t('global.back')}
                        action={
                            <div className="flex flex-wrap gap-2">
                                {permissions.request_create && (
                                    <Button color="blue" as={Link} href={urls.requestCreate}>
                                        <i className="bx bx-git-pull-request me-2 text-lg" />
                                        {t('global.depot.new_request')}
                                    </Button>
                                )}
                                {permissions.transaction_create && (
                                    <Button color="light" as={Link} href={urls.transactionCreate}>
                                        <i className="bx bx-slider-alt me-2 text-lg" />
                                        {t('global.depot.stock_adjustment')}
                                    </Button>
                                )}
                            </div>
                        }
                    />

                    <div className="mt-4 flex flex-wrap items-center gap-2">
                        <Badge color={depot.is_active ? 'success' : 'gray'}>
                            {depot.is_active ? t('global.active') : t('global.inactive')}
                        </Badge>
                        <Badge color="info">
                            <i className="bx bx-package me-1" />
                            {stockItems.length.toLocaleString()} {t('global.items')}
                        </Badge>
                    </div>

                    <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                        {statCards.map((card) => (
                            <StatCard key={card.title} {...card} />
                        ))}
                    </div>

                    {stockStats.total_items > 0 && (
                        <div className="mt-6 rounded-2xl border border-gray-100 bg-gradient-to-r from-amber-50/80 via-white to-orange-50/80 p-4 dark:border-gray-700 dark:from-amber-950/20 dark:via-gray-900 dark:to-orange-950/20">
                            <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <p className="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    {t('global.depot.stock_summary')}
                                </p>
                                <span className="text-xs text-gray-500">
                                    {composition.medicinePct}% {t('global.medicine')} · {composition.toolPct}% {t('global.depot.tool')}
                                </span>
                            </div>
                            <div className="flex h-3 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    className="bg-gradient-to-r from-sky-500 to-blue-600 transition-all"
                                    style={{ width: `${composition.medicinePct}%` }}
                                />
                                <div
                                    className="bg-gradient-to-r from-violet-500 to-purple-600 transition-all"
                                    style={{ width: `${composition.toolPct}%` }}
                                />
                            </div>
                        </div>
                    )}

                    <div className="mt-6 flex flex-wrap gap-2">
                        {STOCK_STATUS_CHIPS.map((chip) => {
                            const isActive = filters.stock_status === chip.value;

                            return (
                                <button
                                    key={chip.value || 'all'}
                                    type="button"
                                    onClick={() => applyChip(chip.value)}
                                    className={`inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition-all ${
                                        isActive
                                            ? `bg-gradient-to-r ${chip.activeClass} text-white shadow-md`
                                            : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                                    }`}
                                >
                                    <i className={`bx ${chip.icon} text-lg`} />
                                    {t(chip.labelKey)}
                                </button>
                            );
                        })}
                    </div>

                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5"
                    >
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                placeholder={t('global.search')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.type')}</Label>
                            <SearchableSelect
                                value={filters.item_type}
                                onChange={(value) => setFilters({ ...filters, item_type: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    { value: 'medicine', label: t('global.medicine') },
                                    { value: 'tool', label: t('global.depot.tool') },
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.sort_by')}</Label>
                            <SearchableSelect
                                value={filters.sort_by}
                                onChange={(value) => setFilters({ ...filters, sort_by: value })}
                                options={[
                                    { value: 'name', label: t('global.name') },
                                    { value: 'quantity', label: t('global.quantity') },
                                    { value: 'item_type', label: t('global.type') },
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.sort_order')}</Label>
                            <SearchableSelect
                                value={filters.sort_order}
                                onChange={(value) => setFilters({ ...filters, sort_order: value })}
                                options={[
                                    { value: 'asc', label: t('global.ascending') },
                                    { value: 'desc', label: t('global.descending') },
                                ]}
                            />
                        </div>
                        <div className="flex items-end">
                            <SettingsFilterActions
                                processing={processing}
                                showClear
                                onClear={() => {
                                    setFilters(EMPTY_FILTERS);
                                    applyFilters(EMPTY_FILTERS);
                                }}
                            />
                        </div>
                    </form>

                    {stockItems.length > 0 ? (
                        <div className="mt-2 overflow-x-auto">
                            <Table className="min-w-[920px]">
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader className="w-12">#</TableHeader>
                                        <TableHeader>{t('global.item')}</TableHeader>
                                        <TableHeader>{t('global.type')}</TableHeader>
                                        <TableHeader align="center">{t('global.quantity')}</TableHeader>
                                        <TableHeader>{t('global.stock_status')}</TableHeader>
                                        <TableHeader className="min-w-[10rem]">{t('global.current_stock')}</TableHeader>
                                        <TableHeader>{t('global.unit')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {stockItems.map((item, index) => {
                                        const barWidth = Math.max(
                                            4,
                                            Math.round((item.available / Math.max(maxQuantity, 1)) * 100),
                                        );

                                        return (
                                            <TableRow key={`${item.item_type}-${item.item_id}`}>
                                                <TableCell muted>{index + 1}</TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-3">
                                                        <span
                                                            className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${itemTypeGradient(item.item_type)} text-white shadow-sm`}
                                                        >
                                                            <i className={`bx ${itemTypeIcon(item.item_type)} text-lg`} />
                                                        </span>
                                                        <span className="font-medium text-gray-900 dark:text-white">
                                                            {item.name}
                                                        </span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge color={item.item_type === 'medicine' ? 'info' : 'purple'}>
                                                        {item.item_type === 'medicine'
                                                            ? t('global.medicine')
                                                            : t('global.depot.tool')}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell align="center">
                                                    <span className="text-lg font-bold tabular-nums text-gray-900 dark:text-white">
                                                        {item.available.toLocaleString()}
                                                    </span>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge color={depotStockLevelBadgeColor(item.stock_status)}>
                                                        {depotStockLevelLabel(item.stock_status, t)}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-1">
                                                        <div className="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                                            <div
                                                                className={`h-full rounded-full transition-all ${depotStockBarColor(item.stock_status)}`}
                                                                style={{ width: `${barWidth}%` }}
                                                            />
                                                        </div>
                                                        <p className="text-xs text-gray-500 tabular-nums">
                                                            {item.available.toLocaleString()} / {maxQuantity.toLocaleString()}
                                                        </p>
                                                    </div>
                                                </TableCell>
                                                <TableCell muted>{item.unit ?? '—'}</TableCell>
                                            </TableRow>
                                        );
                                    })}
                                </TableBody>
                            </Table>
                        </div>
                    ) : (
                        <SettingsEmptyState message={t('global.no_stock_found')} />
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
