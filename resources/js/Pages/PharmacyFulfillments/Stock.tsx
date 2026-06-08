import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import StatCard from '../../Components/ui/StatCard';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface StockItem {
    medicine_id: number;
    medicine_name: string;
    pharmacy_id: number;
    pharmacy_name: string;
    income: number;
    outcome: number;
    stock: number;
}

interface StockStats {
    total_items: number;
    total_stock: number;
    total_income: number;
    total_outcome: number;
    total_low_stock: number;
    total_out_of_stock: number;
}

interface StockFilters {
    search: string;
    medicine_id: string;
    pharmacy_id: string;
    stock_status: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

const EMPTY_FILTERS: StockFilters = {
    search: '',
    medicine_id: '',
    pharmacy_id: '',
    stock_status: '',
    sort_by: 'medicine',
    sort_order: 'asc',
    per_page: '15',
};

export default function StockPharmacyFulfillments({
    stockItems,
    stockStats,
    filters: serverFilters,
    filterOptions,
    userPharmacies,
    urls,
}: {
    stockItems: PaginatedResult<StockItem>;
    stockStats: StockStats;
    filters: StockFilters;
    filterOptions: { pharmacies: OptionItem[]; medicines: OptionItem[]; stockStatuses: string[] };
    userPharmacies?: OptionItem[];
    urls: { index: string; fulfillments: string; outcomes: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: StockFilters) => {
            setProcessing(true);
            router.get(
                urls.index,
                Object.fromEntries(Object.entries(next).filter(([, value]) => value !== '')),
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                    onFinish: () => setProcessing(false),
                },
            );
        },
        [urls.index],
    );

    const summaryLabel = buildPaginationSummary(stockItems.meta, t);
    const pharmacySubtitle = useMemo(() => {
        if (!userPharmacies?.length) return summaryLabel;
        return userPharmacies.map((pharmacy) => pharmacy.name).join(', ');
    }, [userPharmacies, summaryLabel]);

    const statCards = [
        {
            title: t('global.total_stock'),
            value: stockStats.total_stock,
            subtitle: t('global.stock'),
            iconClass: 'bx bx-box',
            iconBgClass: 'bg-emerald-500',
            borderClass: 'border-emerald-200 dark:border-emerald-800',
            valueClass: 'text-emerald-700 dark:text-emerald-300',
        },
        {
            title: t('global.income'),
            value: stockStats.total_income,
            subtitle: t('global.pharmacy_income'),
            iconClass: 'bx bx-log-in',
            iconBgClass: 'bg-green-500',
            borderClass: 'border-green-200 dark:border-green-800',
            valueClass: 'text-green-700 dark:text-green-300',
        },
        {
            title: t('global.outcome'),
            value: stockStats.total_outcome,
            subtitle: t('global.pharmacy_outcome'),
            iconClass: 'bx bx-log-out',
            iconBgClass: 'bg-orange-500',
            borderClass: 'border-orange-200 dark:border-orange-800',
            valueClass: 'text-orange-700 dark:text-orange-300',
        },
        {
            title: t('global.low_stock'),
            value: stockStats.total_low_stock,
            subtitle: t('global.total_low_stock'),
            iconClass: 'bx bx-error',
            iconBgClass: 'bg-amber-500',
            borderClass: 'border-amber-200 dark:border-amber-800',
            valueClass: 'text-amber-700 dark:text-amber-300',
        },
        {
            title: t('global.out_of_stock'),
            value: stockStats.total_out_of_stock,
            subtitle: t('global.total_out_of_stock'),
            iconClass: 'bx bx-x-circle',
            iconBgClass: 'bg-red-500',
            borderClass: 'border-red-200 dark:border-red-800',
            valueClass: 'text-red-700 dark:text-red-300',
        },
    ];

    return (
        <DashboardLayout>
            <Head title={t('global.pharmacy_stock')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.pharmacy_stock')}
                        subtitle={pharmacySubtitle}
                        icon="bx-box"
                        accent="from-emerald-500 to-green-600"
                        backHref={urls.fulfillments}
                        backLabel={t('global.back')}
                        action={
                            <Button color="light" as={Link} href={urls.outcomes}>
                                <i className="bx bx-log-out me-2 text-lg" />
                                {t('global.outcome_records')}
                            </Button>
                        }
                    />

                    <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                        {statCards.map((card) => (
                            <StatCard key={card.title} {...card} />
                        ))}
                    </div>

                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
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
                            <Label>{t('global.medicine')}</Label>
                            <SearchableSelect
                                value={filters.medicine_id}
                                onChange={(value) => setFilters({ ...filters, medicine_id: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...(filterOptions?.medicines ?? []).map((medicine) => ({
                                        value: String(medicine.id),
                                        label: medicine.name,
                                    })),
                                ]}
                                placeholder={t('global.all')}
                            />
                        </div>
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
                        <div>
                            <Label>{t('global.stock_status')}</Label>
                            <SearchableSelect
                                value={filters.stock_status}
                                onChange={(value) => setFilters({ ...filters, stock_status: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...(filterOptions?.stockStatuses ?? []).map((status) => ({
                                        value: status,
                                        label: t(`global.${status}`),
                                    })),
                                ]}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div className="xl:col-span-4">
                            <SettingsFilterActions
                                processing={processing}
                                showClear
                                onClear={() => {
                                    const next = { ...EMPTY_FILTERS, per_page: filters.per_page };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                            />
                        </div>
                    </form>

                    {stockItems.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.medicine')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy')}</TableHeader>
                                    <TableHeader>{t('global.income')}</TableHeader>
                                    <TableHeader>{t('global.outcome')}</TableHeader>
                                    <TableHeader>{t('global.stock')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {stockItems.data.map((item, index) => (
                                    <TableRow key={`${item.medicine_id}-${item.pharmacy_id}`}>
                                        <TableCell>{(stockItems.meta.from ?? 1) + index}</TableCell>
                                        <TableCell className="font-medium">{item.medicine_name}</TableCell>
                                        <TableCell muted>{item.pharmacy_name}</TableCell>
                                        <TableCell>{item.income}</TableCell>
                                        <TableCell>{item.outcome}</TableCell>
                                        <TableCell className="font-semibold">{item.stock}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_pharmacy_stock_found')} />
                    )}
                    <SettingsPagination links={stockItems.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
