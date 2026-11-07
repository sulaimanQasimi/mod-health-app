import { Head, Link, router, useForm } from '@inertiajs/react';
import { Alert, Button, Card, Label, Select, Spinner, Textarea } from 'flowbite-react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../Components/ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../../utils/settingsUi';

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

export default function FinanceInvoiceShow({
    invoice,
    receiptUrl,
    permissions,
    urls,
    flash,
}: InvoiceShowProps) {
    const { t } = useTranslation();
    const payForm = useForm({
        method: 'cash',
        notes: '',
    });

    const statusLabel = (status: string) => {
        if (status === 'draft') return t('global.invoice_status_draft');
        if (status === 'issued') return t('global.invoice_status_issued');
        if (status === 'paid') return t('global.invoice_status_paid');
        if (status === 'cancelled') return t('global.invoice_status_cancelled');
        return status;
    };

    return (
        <DashboardLayout>
            <Head title={`${t('global.invoice')} ${invoice.uuid}`} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.invoice')}
                        subtitle={invoice.uuid}
                        icon="bx-receipt"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                        action={
                            <div className="flex flex-wrap gap-2">
                                {permissions.issue && (
                                    <Button
                                        color="warning"
                                        onClick={() =>
                                            router.post(urls.issue, {}, { preserveScroll: true })
                                        }
                                    >
                                        {t('global.issue_invoice')}
                                    </Button>
                                )}
                                {receiptUrl && (
                                    <Button color="light" as={Link} href={receiptUrl} target="_blank">
                                        <i className="bx bx-printer me-1" />
                                        {t('global.print_receipt')}
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

                    <dl className="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {[
                            [t('global.patient_name'), invoice.patient_name],
                            [t('global.phone'), invoice.patient_phone ?? '—'],
                            [t('global.invoice_source'), invoice.source ?? '—'],
                            [t('global.department'), invoice.department_name ?? '—'],
                            [t('global.branch'), invoice.branch_name ?? '—'],
                            [t('global.status'), statusLabel(invoice.status)],
                            [t('global.price'), String(invoice.total)],
                        ].map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700 dark:bg-gray-800/40"
                            >
                                <dt className="text-xs font-semibold uppercase text-gray-500">{label}</dt>
                                <dd className="mt-1 text-sm font-medium text-gray-900 dark:text-white">{value}</dd>
                            </div>
                        ))}
                    </dl>

                    <h3 className="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                        {t('global.invoice_items')}
                    </h3>
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
                            {invoice.items.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell>{item.description}</TableCell>
                                    <TableCell>{item.unit_price}</TableCell>
                                    <TableCell>{item.quantity}</TableCell>
                                    <TableCell>{item.amount}</TableCell>
                                    <TableCell>
                                        {item.payment_status === 'paid'
                                            ? t('global.invoice_status_paid')
                                            : t('global.unpaid')}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>

                    {permissions.pay && (
                        <form
                            className="mt-6 space-y-3 rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-900 dark:bg-emerald-950/20"
                            onSubmit={(event) => {
                                event.preventDefault();
                                payForm.post(urls.pay, { preserveScroll: true });
                            }}
                        >
                            <h3 className="text-sm font-semibold text-emerald-900 dark:text-emerald-100">
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
                                    rows={2}
                                    value={payForm.data.notes}
                                    onChange={(e) => payForm.setData('notes', e.target.value)}
                                />
                            </div>
                            <Button type="submit" color="success" disabled={payForm.processing}>
                                {payForm.processing ? <Spinner size="sm" /> : t('global.mark_as_paid')}
                            </Button>
                        </form>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
