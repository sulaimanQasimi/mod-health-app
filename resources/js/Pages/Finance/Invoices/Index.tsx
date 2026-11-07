import { Head, Link, router } from '@inertiajs/react';
import { Alert, Badge, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsEmptyState from '../../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../Components/ui/Table';
import TableActionButton from '../../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../../Components/ui/TableActions';
import { useTranslation } from '../../../hooks/useTranslation';
import { PaginatedResult } from '../../../types/settings';
import { buildPaginationSummary } from '../../../utils/pagination';

interface InvoiceRow {
    id: number;
    uuid: string;
    patient_name: string;
    source: string | null;
    department_name: string | null;
    status: string;
    total: number | string;
    paid_amount: number | string;
    issued_at: string | null;
    paid_at: string | null;
    show_url: string;
}

type StatusFilter = '' | 'draft' | 'issued' | 'paid' | 'unpaid';

function statusBadgeColor(status: string): 'gray' | 'warning' | 'success' | 'failure' {
    if (status === 'paid') return 'success';
    if (status === 'issued') return 'warning';
    if (status === 'cancelled') return 'failure';
    return 'gray';
}

export default function FinanceInvoicesIndex({
    invoices,
    filters: serverFilters,
    stats,
    urls,
    flash,
}: {
    invoices: PaginatedResult<InvoiceRow>;
    filters: { search: string; status: string; per_page: string };
    stats: { draft: number; issued: number; paid: number };
    permissions: { pay: boolean; issue: boolean };
    urls: { index: string };
    flash?: { success?: string | null; error?: string | null };
}) {
    const { t, direction } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: typeof filters) => {
            setProcessing(true);
            router.get(urls.index, Object.fromEntries(Object.entries(next).filter(([, v]) => v !== '')), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.index],
    );

    const statusLabel = (status: string) => {
        if (status === 'draft') return t('global.invoice_status_draft');
        if (status === 'issued') return t('global.invoice_status_issued');
        if (status === 'paid') return t('global.paid');
        if (status === 'cancelled') return t('global.invoice_status_cancelled');
        return status;
    };

    const setStatusFilter = (status: StatusFilter) => {
        const next = { ...filters, status };
        setFilters(next);
        applyFilters(next);
    };

    const unpaidCount = stats.draft + stats.issued;

    return (
        <DashboardLayout>
            <Head title={t('global.finance_invoices')} />
            <div className="mx-auto w-full max-w-[1600px]" dir={direction}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.finance_invoices')}
                        subtitle={buildPaginationSummary(invoices.meta, t)}
                        icon="bx-money"
                        accent="from-emerald-500 to-teal-600"
                        backLabel={t('global.back')}
                    />

                    {flash?.success && (
                        <Alert color="success" className="mb-4">
                            {flash.success}
                        </Alert>
                    )}
                    {flash?.error && (
                        <Alert color="failure" className="mb-4">
                            {flash.error}
                        </Alert>
                    )}

                    <div className="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <button
                            type="button"
                            onClick={() => setStatusFilter('')}
                            className={`rounded-xl border p-4 text-start transition ${
                                filters.status === ''
                                    ? 'border-emerald-400 bg-emerald-50 ring-2 ring-emerald-200 dark:border-emerald-700 dark:bg-emerald-950/40'
                                    : 'border-gray-100 bg-gray-50 hover:border-gray-200 dark:border-gray-700 dark:bg-gray-800/40'
                            }`}
                        >
                            <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {t('global.all')}
                            </div>
                            <div className="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                {stats.draft + stats.issued + stats.paid}
                            </div>
                        </button>
                        <button
                            type="button"
                            onClick={() => setStatusFilter('unpaid')}
                            className={`rounded-xl border p-4 text-start transition ${
                                filters.status === 'unpaid'
                                    ? 'border-amber-400 bg-amber-50 ring-2 ring-amber-200 dark:border-amber-800 dark:bg-amber-950/40'
                                    : 'border-amber-100 bg-amber-50/70 hover:border-amber-200 dark:border-amber-900 dark:bg-amber-950/20'
                            }`}
                        >
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                                    {t('global.awaiting_payment')}
                                </span>
                                <Badge color="warning">{t('global.unpaid')}</Badge>
                            </div>
                            <div className="mt-1 text-2xl font-semibold text-amber-900 dark:text-amber-100">
                                {unpaidCount}
                            </div>
                            <p className="mt-1 text-xs text-amber-700/80 dark:text-amber-300/80">
                                {t('global.invoice_status_draft')}: {stats.draft} · {t('global.invoice_status_issued')}:{' '}
                                {stats.issued}
                            </p>
                        </button>
                        <button
                            type="button"
                            onClick={() => setStatusFilter('draft')}
                            className={`rounded-xl border p-4 text-start transition ${
                                filters.status === 'draft'
                                    ? 'border-gray-400 bg-white ring-2 ring-gray-200 dark:border-gray-500 dark:bg-gray-800'
                                    : 'border-gray-100 bg-white hover:border-gray-200 dark:border-gray-700 dark:bg-gray-800/40'
                            }`}
                        >
                            <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {t('global.invoice_status_draft')}
                            </div>
                            <div className="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{stats.draft}</div>
                        </button>
                        <button
                            type="button"
                            onClick={() => setStatusFilter('paid')}
                            className={`rounded-xl border p-4 text-start transition ${
                                filters.status === 'paid'
                                    ? 'border-emerald-400 bg-emerald-50 ring-2 ring-emerald-200 dark:border-emerald-700 dark:bg-emerald-950/40'
                                    : 'border-emerald-100 bg-emerald-50/70 hover:border-emerald-200 dark:border-emerald-900 dark:bg-emerald-950/20'
                            }`}
                        >
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                                    {t('global.paid')}
                                </span>
                                <Badge color="success">{t('global.paid')}</Badge>
                            </div>
                            <div className="mt-1 text-2xl font-semibold text-emerald-900 dark:text-emerald-100">
                                {stats.paid}
                            </div>
                        </button>
                    </div>

                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-3"
                    >
                        <div className="md:col-span-2">
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                                placeholder={`${t('global.patient_name')} / UUID`}
                            />
                        </div>
                        <div>
                            <Label>{t('global.status')}</Label>
                            <SearchableSelect
                                value={filters.status}
                                onChange={(value) => setFilters({ ...filters, status: value })}
                                placeholder={t('global.all')}
                            >
                                <option value="">{t('global.all')}</option>
                                <option value="unpaid">{t('global.awaiting_payment')}</option>
                                <option value="draft">{t('global.invoice_status_draft')}</option>
                                <option value="issued">{t('global.invoice_status_issued')}</option>
                                <option value="paid">{t('global.paid')}</option>
                            </SearchableSelect>
                        </div>
                        <div className="md:col-span-3">
                            <SettingsFilterActions processing={processing} />
                        </div>
                    </form>

                    {invoices.data.length > 0 ? (
                        <>
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>#</TableHeader>
                                            <TableHeader>{t('global.patient_name')}</TableHeader>
                                            <TableHeader>{t('global.department')}</TableHeader>
                                            <TableHeader>{t('global.invoice_source')}</TableHeader>
                                            <TableHeader>{t('global.status')}</TableHeader>
                                            <TableHeader>{t('global.price')}</TableHeader>
                                            <TableHeader>UUID</TableHeader>
                                            <TableHeader align="center">{t('global.actions')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {invoices.data.map((item, index) => {
                                            const isPaid = item.status === 'paid';
                                            return (
                                                <TableRow key={item.id}>
                                                    <TableCell>{(invoices.meta.from ?? 1) + index}</TableCell>
                                                    <TableCell>
                                                        <Link
                                                            href={item.show_url}
                                                            className="font-medium text-emerald-700 hover:underline dark:text-emerald-400"
                                                        >
                                                            {item.patient_name || '—'}
                                                        </Link>
                                                    </TableCell>
                                                    <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.source ?? '—'}</TableCell>
                                                    <TableCell>
                                                        <Badge color={statusBadgeColor(item.status)}>
                                                            {isPaid ? t('global.paid') : statusLabel(item.status)}
                                                        </Badge>
                                                        {!isPaid && (
                                                            <Badge color="failure" className="ms-2">
                                                                {t('global.unpaid')}
                                                            </Badge>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="font-semibold tabular-nums">
                                                        {item.total}
                                                    </TableCell>
                                                    <TableCell className="max-w-[140px] truncate font-mono text-xs text-gray-500">
                                                        {item.uuid}
                                                    </TableCell>
                                                    <TableActionsCell>
                                                        <TableActionButton kind="view" href={item.show_url} />
                                                    </TableActionsCell>
                                                </TableRow>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            </div>
                            <SettingsPagination links={invoices.links ?? []} />
                        </>
                    ) : (
                        <SettingsEmptyState title={t('global.no_results_found')} />
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
