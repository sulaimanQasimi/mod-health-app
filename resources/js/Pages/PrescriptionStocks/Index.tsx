import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface PrescriptionStockItem {
    medicine_id: number;
    medicine_name: string;
    pharmacy_id: number | null;
    pharmacy_name: string | null;
    pharmacy_stock: number;
    total_stock: number;
    pharmacy_income: number;
    pharmacy_outcome: number;
    minimum_stock: number;
    maximum_stock: number;
    stock_status: string;
}

interface PrescriptionStockFilters {
    search: string;
    pharmacy_id: string;
    stock_status: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

const EMPTY_FILTERS: PrescriptionStockFilters = {
    search: '',
    pharmacy_id: '',
    stock_status: '',
    sort_by: 'medicine_name',
    sort_order: 'asc',
    per_page: '15',
};

export default function IndexPrescriptionStocks({
    prescriptionStocks,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    prescriptionStocks: PaginatedResult<PrescriptionStockItem>;
    filters: PrescriptionStockFilters;
    filterOptions: { pharmacies: OptionItem[]; stockStatuses: string[] };
    permissions: { create: boolean };
    urls: { index: string; createIncome: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: PrescriptionStockFilters) => {
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

    const summaryLabel = buildPaginationSummary(prescriptionStocks.meta, t);

    const stockStatusBadge = (status: string) => {
        const colorMap: Record<string, 'success' | 'warning' | 'failure' | 'info' | 'gray'> = {
            normal: 'success',
            low_stock: 'warning',
            out_of_stock: 'failure',
            overstocked: 'info',
        };
        const labelKey = status in colorMap ? status : status;
        return (
            <Badge color={colorMap[status] ?? 'gray'}>
                {t(`global.${labelKey}`)}
            </Badge>
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.prescription_stocks')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.prescription_stocks')}
                        subtitle={summaryLabel}
                        icon="bx-package"
                        accent="from-blue-500 to-indigo-600"
                        backLabel={t('global.back')}
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.createIncome}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.create')}
                                </Button>
                            ) : undefined
                        }
                    />

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

                    {prescriptionStocks.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.medicine')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy_stock')}</TableHeader>
                                    <TableHeader>{t('global.total_stock')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy_income')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy_outcome')}</TableHeader>
                                    <TableHeader>{t('global.minimum_stock')}</TableHeader>
                                    <TableHeader>{t('global.maximum_stock')}</TableHeader>
                                    <TableHeader>{t('global.stock_status')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {prescriptionStocks.data.map((item, index) => (
                                    <TableRow key={`${item.medicine_id}-${item.pharmacy_id ?? 'all'}`}>
                                        <TableCell>{(prescriptionStocks.meta.from ?? 1) + index}</TableCell>
                                        <TableCell className="font-medium">{item.medicine_name}</TableCell>
                                        <TableCell muted>{item.pharmacy_name ?? '—'}</TableCell>
                                        <TableCell>{item.pharmacy_stock}</TableCell>
                                        <TableCell>{item.total_stock}</TableCell>
                                        <TableCell>{item.pharmacy_income}</TableCell>
                                        <TableCell>{item.pharmacy_outcome}</TableCell>
                                        <TableCell muted>{item.minimum_stock}</TableCell>
                                        <TableCell muted>{item.maximum_stock}</TableCell>
                                        <TableCell>{stockStatusBadge(item.stock_status)}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_stock_found')} />
                    )}
                    <SettingsPagination links={prescriptionStocks.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
