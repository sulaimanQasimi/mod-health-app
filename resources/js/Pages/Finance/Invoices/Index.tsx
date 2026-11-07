import { Head, Link, router } from '@inertiajs/react';
import { Alert, Button, Card, Label, TextInput } from 'flowbite-react';
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
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

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
    const { t } = useTranslation();
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
        if (status === 'paid') return t('global.invoice_status_paid');
        if (status === 'cancelled') return t('global.invoice_status_cancelled');
        return status;
    };

    return (
        <DashboardLayout>
            <Head title={t('global.finance_invoices')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
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

                    <div className="mb-4 grid gap-3 sm:grid-cols-3">
                        <div className="rounded-xl border border-gray-100 bg-gray-50 p-3 text-sm dark:border-gray-700 dark:bg-gray-800/40">
                            {t('global.invoice_status_draft')}: <strong>{stats.draft}</strong>
                        </div>
                        <div className="rounded-xl border border-amber-100 bg-amber-50 p-3 text-sm dark:border-amber-900 dark:bg-amber-950/30">
                            {t('global.invoice_status_issued')}: <strong>{stats.issued}</strong>
                        </div>
                        <div className="rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-sm dark:border-emerald-900 dark:bg-emerald-950/30">
                            {t('global.invoice_status_paid')}: <strong>{stats.paid}</strong>
                        </div>
                    </div>

                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-2"
                    >
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                                placeholder={t('global.patient_name') + ' / UUID'}
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
                                <option value="draft">{t('global.invoice_status_draft')}</option>
                                <option value="issued">{t('global.invoice_status_issued')}</option>
                                <option value="paid">{t('global.invoice_status_paid')}</option>
                            </SearchableSelect>
                        </div>
                        <div className="md:col-span-2">
                            <SettingsFilterActions processing={processing} />
                        </div>
                    </form>

                    {invoices.data.length > 0 ? (
                        <>
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>#</TableHeader>
                                        <TableHeader>UUID</TableHeader>
                                        <TableHeader>{t('global.patient_name')}</TableHeader>
                                        <TableHeader>{t('global.invoice_source')}</TableHeader>
                                        <TableHeader>{t('global.department')}</TableHeader>
                                        <TableHeader>{t('global.status')}</TableHeader>
                                        <TableHeader>{t('global.price')}</TableHeader>
                                        <TableHeader align="center">{t('global.actions')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {invoices.data.map((item, index) => (
                                        <TableRow key={item.id}>
                                            <TableCell>{(invoices.meta.from ?? 1) + index}</TableCell>
                                            <TableCell className="max-w-[160px] truncate font-mono text-xs">
                                                <Link href={item.show_url} className="text-emerald-700 hover:underline">
                                                    {item.uuid}
                                                </Link>
                                            </TableCell>
                                            <TableCell>{item.patient_name || '—'}</TableCell>
                                            <TableCell muted>{item.source ?? '—'}</TableCell>
                                            <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                            <TableCell>{statusLabel(item.status)}</TableCell>
                                            <TableCell>{item.total}</TableCell>
                                            <TableActionsCell>
                                                <TableActionButton kind="view" href={item.show_url} />
                                            </TableActionsCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
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
