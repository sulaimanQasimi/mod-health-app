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
import { PaginatedResult } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface IncomeListItem {
    id: number;
    medicine_name: string | null;
    amount: number | null;
    batch_number: string | null;
    supplier_name: string | null;
    purchase_price: number | null;
    purchase_date: string | null;
    income_type: string | null;
    branch_name: string | null;
    created_by_name: string | null;
    created_at: string | null;
}

interface IncomeFilters {
    search: string;
    income_type: string;
    date_from: string;
    date_to: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

const EMPTY_FILTERS: IncomeFilters = {
    search: '',
    income_type: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    per_page: '15',
};

export default function IndexIncomes({
    incomes,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    incomes: PaginatedResult<IncomeListItem>;
    filters: IncomeFilters;
    filterOptions: { incomeTypes: string[] };
    permissions: { create: boolean };
    urls: { index: string; create: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: IncomeFilters) => {
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

    const summaryLabel = buildPaginationSummary(incomes.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.income_records')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.income_records')}
                        subtitle={summaryLabel}
                        icon="bx-log-in"
                        accent="from-green-500 to-emerald-600"
                        backLabel={t('global.back')}
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
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
                            <Label>{t('global.income_type')}</Label>
                            <SearchableSelect
                                value={filters.income_type}
                                onChange={(value) => setFilters({ ...filters, income_type: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...(filterOptions?.incomeTypes ?? []).map((type) => ({
                                        value: type,
                                        label: t(`global.${type}`),
                                    })),
                                ]}
                                placeholder={t('global.all')}
                            />
                        </div>
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

                    {incomes.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.medicine')}</TableHeader>
                                    <TableHeader>{t('global.amount')}</TableHeader>
                                    <TableHeader>{t('global.batch_number')}</TableHeader>
                                    <TableHeader>{t('global.supplier_name')}</TableHeader>
                                    <TableHeader>{t('global.purchase_price')}</TableHeader>
                                    <TableHeader>{t('global.purchase_date')}</TableHeader>
                                    <TableHeader>{t('global.income_type')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {incomes.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(incomes.meta.from ?? 1) + index}</TableCell>
                                        <TableCell className="font-medium">{item.medicine_name ?? '—'}</TableCell>
                                        <TableCell>{item.amount ?? '—'}</TableCell>
                                        <TableCell muted>{item.batch_number ?? '—'}</TableCell>
                                        <TableCell muted>{item.supplier_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.purchase_price ?? '—'}</TableCell>
                                        <TableCell muted>{item.purchase_date ?? '—'}</TableCell>
                                        <TableCell>
                                            {item.income_type ? (
                                                <Badge color="info">{t(`global.${item.income_type}`)}</Badge>
                                            ) : (
                                                '—'
                                            )}
                                        </TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_income_records_found')} />
                    )}
                    <SettingsPagination links={incomes.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
