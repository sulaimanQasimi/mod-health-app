import { Head, Link, router, useForm } from '@inertiajs/react';
import { Alert, Badge, Button, Card, Label, Select, Spinner, Textarea } from 'flowbite-react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../Components/ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';

interface InvoiceShowProps {
    invoice: {
        id: number;
        uuid: string;
        status: string;
        source: string | null;
        subtotal: number | string;
        total: number | string;
        paid_amount: number | string;
        issued_at: string | null;
        paid_at: string | null;
        patient_name: string;
        patient_phone: string | null;
        department_name: string | null;
        branch_name: string | null;
        items: Array<{
            id: number;
            item_type: string;
            description: string;
            unit_price: number | string;
            quantity: number;
            amount: number | string;
            payment_status: string;
        }>;
        payments: Array<{
            id: number;
            amount: number | string;
            method: string;
            paid_at: string | null;
            paid_by: string;
        }>;
    };
    receiptUrl: string | null;
    permissions: { pay: boolean; issue: boolean };
    urls: { index: string; issue: string; pay: string };
    flash?: { success?: string | null; error?: string | null };
}

function statusBadgeColor(status: string): 'gray' | 'warning' | 'success' | 'failure' {
    if (status === 'paid') return 'success';
    if (status === 'issued') return 'warning';
    if (status === 'cancelled') return 'failure';
    return 'gray';
}

export default function FinanceInvoiceShow({
    invoice,
    receiptUrl,
    permissions,
    urls,
    flash,
}: InvoiceShowProps) {
    const { t, direction } = useTranslation();
    const payForm = useForm({
        method: 'cash',
        notes: '',
    });

    const isPaid = invoice.status === 'paid';

    const statusLabel = (status: string) => {
        if (status === 'draft') return t('global.invoice_status_draft');
        if (status === 'issued') return t('global.invoice_status_issued');
        if (status === 'paid') return t('global.paid');
        if (status === 'cancelled') return t('global.invoice_status_cancelled');
        return status;
    };

    return (
        <DashboardLayout>
            <Head title={`${t('global.invoice')} ${invoice.uuid}`} />
            <div className="mx-auto w-full max-w-[1600px]" dir={direction}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.invoice')}
                        subtitle={invoice.patient_name || invoice.uuid}
                        icon="bx-receipt"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                        action={
                            <div className="flex flex-wrap items-center gap-2">
                                <Badge color={statusBadgeColor(invoice.status)}>
                                    {statusLabel(invoice.status)}
                                </Badge>
                                {!isPaid && <Badge color="failure">{t('global.unpaid')}</Badge>}
                                {isPaid && <Badge color="success">{t('global.paid')}</Badge>}
                                {permissions.issue && (
                                    <Button
                                        color="warning"
                                        onClick={() => router.post(urls.issue, {}, { preserveScroll: true })}
                                    >
                                        {t('global.issue_invoice')}
                                    </Button>
                                )}
                                {receiptUrl && (
                                    <Button color="light" as={Link} href={receiptUrl} target="_blank">
                                        <i className="bx bx-printer" />
                                        <span className="ms-1">{t('global.print_receipt')}</span>
                                    </Button>
                                )}
                            </div>
                        }
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

                    <div className="mb-6 grid gap-4 lg:grid-cols-3">
                        <div className="space-y-4 lg:col-span-2">
                            <dl className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                {[
                                    [t('global.patient_name'), invoice.patient_name],
                                    [t('global.phone'), invoice.patient_phone ?? '—'],
                                    [t('global.invoice_source'), invoice.source ?? '—'],
                                    [t('global.department'), invoice.department_name ?? '—'],
                                    [t('global.branch'), invoice.branch_name ?? '—'],
                                    [t('global.paid_at'), invoice.paid_at ?? '—'],
                                ].map(([label, value]) => (
                                    <div
                                        key={String(label)}
                                        className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700 dark:bg-gray-800/40"
                                    >
                                        <dt className="text-xs font-semibold uppercase text-gray-500">{label}</dt>
                                        <dd className="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                            {value}
                                        </dd>
                                    </div>
                                ))}
                            </dl>

                            <div>
                                <h3 className="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {t('global.invoice_items')}
                                </h3>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.description')}</TableHeader>
                                                <TableHeader>{t('global.price')}</TableHeader>
                                                <TableHeader>{t('global.quantity')}</TableHeader>
                                                <TableHeader>{t('global.amount')}</TableHeader>
                                                <TableHeader>{t('global.status')}</TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {invoice.items.map((item) => {
                                                const itemPaid = item.payment_status === 'paid';
                                                return (
                                                    <TableRow key={item.id}>
                                                        <TableCell>{item.description}</TableCell>
                                                        <TableCell className="tabular-nums">{item.unit_price}</TableCell>
                                                        <TableCell className="tabular-nums">{item.quantity}</TableCell>
                                                        <TableCell className="font-semibold tabular-nums">
                                                            {item.amount}
                                                        </TableCell>
                                                        <TableCell>
                                                            <Badge color={itemPaid ? 'success' : 'failure'}>
                                                                {itemPaid ? t('global.paid') : t('global.unpaid')}
                                                            </Badge>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })}
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>

                            {invoice.payments.length > 0 && (
                                <div>
                                    <h3 className="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                        {t('global.payment_receipt')}
                                    </h3>
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHead>
                                                <TableRow variant="header">
                                                    <TableHeader>{t('global.amount')}</TableHeader>
                                                    <TableHeader>{t('global.payment_method')}</TableHeader>
                                                    <TableHeader>{t('global.paid_at')}</TableHeader>
                                                    <TableHeader>{t('global.staff')}</TableHeader>
                                                </TableRow>
                                            </TableHead>
                                            <TableBody>
                                                {invoice.payments.map((payment) => (
                                                    <TableRow key={payment.id}>
                                                        <TableCell className="font-semibold tabular-nums">
                                                            {payment.amount}
                                                        </TableCell>
                                                        <TableCell>{payment.method}</TableCell>
                                                        <TableCell>{payment.paid_at ?? '—'}</TableCell>
                                                        <TableCell>{payment.paid_by || '—'}</TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </div>
                                </div>
                            )}
                        </div>

                        <aside className="space-y-4">
                            <div
                                className={`rounded-2xl border p-5 ${
                                    isPaid
                                        ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/30'
                                        : 'border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/30'
                                }`}
                            >
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <span className="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        {t('global.price')}
                                    </span>
                                    <Badge color={isPaid ? 'success' : 'failure'}>
                                        {isPaid ? t('global.paid') : t('global.unpaid')}
                                    </Badge>
                                </div>
                                <div className="mt-3 text-3xl font-bold tabular-nums text-gray-900 dark:text-white">
                                    {invoice.total}
                                </div>
                                <dl className="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                    <div className="flex items-center justify-between gap-3">
                                        <dt>{t('global.paid_amount')}</dt>
                                        <dd className="font-medium tabular-nums">{invoice.paid_amount}</dd>
                                    </div>
                                    <div className="flex items-center justify-between gap-3">
                                        <dt>UUID</dt>
                                        <dd className="max-w-[60%] truncate font-mono text-xs" title={invoice.uuid}>
                                            {invoice.uuid}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {permissions.pay && (
                                <form
                                    className="space-y-3 rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm dark:border-emerald-900 dark:bg-gray-900"
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        payForm.post(urls.pay, { preserveScroll: true });
                                    }}
                                >
                                    <h3 className="text-base font-semibold text-emerald-900 dark:text-emerald-100">
                                        {t('global.record_payment')}
                                    </h3>
                                    <div>
                                        <Label>{t('global.payment_method')}</Label>
                                        <Select
                                            value={payForm.data.method}
                                            onChange={(e) => payForm.setData('method', e.target.value)}
                                        >
                                            <option value="cash">{t('global.cash')}</option>
                                            <option value="card">{t('global.card')}</option>
                                            <option value="other">{t('global.other')}</option>
                                        </Select>
                                    </div>
                                    <div>
                                        <Label>{t('global.notes')}</Label>
                                        <Textarea
                                            rows={3}
                                            value={payForm.data.notes}
                                            onChange={(e) => payForm.setData('notes', e.target.value)}
                                        />
                                    </div>
                                    <Button
                                        type="submit"
                                        color="success"
                                        className="w-full"
                                        disabled={payForm.processing}
                                    >
                                        {payForm.processing ? (
                                            <Spinner size="sm" />
                                        ) : (
                                            <span className="inline-flex items-center gap-2">
                                                <i className="bx bx-check-circle text-lg" />
                                                {t('global.mark_as_paid')}
                                            </span>
                                        )}
                                    </Button>
                                </form>
                            )}
                        </aside>
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
