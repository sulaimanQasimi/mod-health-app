import { Head, Link, router } from '@inertiajs/react';
import { Alert, Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import DepotRequestWorkflowStepper from '../../Components/Depots/DepotRequestWorkflowStepper';
import { depotRequestStatusLabel, depotStatusBadgeColor } from '../../Components/Depots/depotUi';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface TransferRequestItem {
    id: number;
    medicine_id: number | null;
    item_name: string;
    quantity: number;
    unit_name: string | null;
    batch_number: string | null;
}

interface TransferRequestDetail {
    id: number;
    request_number: string | null;
    status: string;
    items_count: number;
    total_quantity: number;
    items_summary: string;
    pharmacy_name: string | null;
    source_depot_name: string | null;
    requested_by_name: string | null;
    created_at: string | null;
    notes: string | null;
    workflow_rank: number;
    rejection_reason: string | null;
    approved_by_name: string | null;
    fulfilled_by_name: string | null;
    approved_at: string | null;
    fulfilled_at: string | null;
    items: TransferRequestItem[];
    status_logs: Array<{
        from_status: string | null;
        to_status: string;
        notes: string | null;
        user_name: string | null;
        created_at: string | null;
    }>;
}

interface RequestPermissions {
    edit: boolean;
    submit: boolean;
    cancel: boolean;
}

export default function ShowPharmacyTransferRequest({
    request: transferRequest,
    workflowSteps,
    permissions,
    urls,
}: {
    request: TransferRequestDetail;
    workflowSteps: string[];
    permissions: RequestPermissions;
    urls: {
        index: string;
        edit: string;
        print: string;
        submit: string;
        cancel: string;
    };
}) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState<string | null>(null);

    const runAction = (key: string, url: string) => {
        setProcessing(key);
        router.post(url, {}, { preserveScroll: true, onFinish: () => setProcessing(null) });
    };

    const awaitingDepot = ['pending', 'approved'].includes(transferRequest.status);

    const metaRows: Array<[string, string]> = [
        [t('global.pharmacy'), transferRequest.pharmacy_name ?? '—'],
        [t('global.depot.source_depot'), transferRequest.source_depot_name ?? '—'],
        [t('global.requested_by'), transferRequest.requested_by_name ?? '—'],
        [t('global.created_at'), transferRequest.created_at ?? '—'],
    ];

    if (transferRequest.approved_by_name) {
        metaRows.push([t('global.approved_by'), transferRequest.approved_by_name]);
        metaRows.push([t('global.approved_at'), transferRequest.approved_at ?? '—']);
    }
    if (transferRequest.fulfilled_by_name) {
        metaRows.push([t('global.fulfilled_by'), transferRequest.fulfilled_by_name]);
        metaRows.push([t('global.fulfilled_at'), transferRequest.fulfilled_at ?? '—']);
    }
    if (transferRequest.rejection_reason) {
        metaRows.push([t('global.rejection_reason'), transferRequest.rejection_reason]);
    }
    if (transferRequest.notes) {
        metaRows.push([t('global.notes'), transferRequest.notes]);
    }

    return (
        <DashboardLayout>
            <Head title={transferRequest.request_number ?? t('global.pharmacy_transfer_requests')} />
            <div className={`mx-auto w-full min-w-0 ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={transferRequest.request_number ?? `#${transferRequest.id}`}
                        subtitle={t('global.pharmacy_transfer_requests')}
                        icon="bx-package"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />

                    <div className="mb-4 flex flex-wrap items-center gap-2">
                        <Badge color={depotStatusBadgeColor(transferRequest.status)} className="text-sm">
                            {depotRequestStatusLabel(transferRequest.status, t)}
                        </Badge>
                        <span className="text-sm text-gray-500">{transferRequest.items_summary}</span>
                    </div>

                    <div className="mb-6 rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                        <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                            {t('global.workflow')}
                        </p>
                        <DepotRequestWorkflowStepper
                            steps={workflowSteps}
                            currentRank={transferRequest.workflow_rank}
                            status={transferRequest.status}
                        />
                    </div>

                    <dl className="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {metaRows.map(([label, value]) => (
                            <div
                                key={label}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40"
                            >
                                <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {label}
                                </dt>
                                <dd className="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{value}</dd>
                            </div>
                        ))}
                    </dl>

                    {awaitingDepot && transferRequest.source_depot_name && (
                        <Alert color="info" className="mb-6">
                            <span className="text-sm">
                                {t('global.depot.request_processing_by_source').replace(
                                    ':depot',
                                    transferRequest.source_depot_name,
                                )}
                            </span>
                        </Alert>
                    )}

                    <div className="flex flex-wrap gap-2">
                        <Button color="light" as="a" href={urls.print} target="_blank" rel="noreferrer">
                            <i className="bx bx-printer me-1" />
                            {t('global.depot.print_form_14')}
                        </Button>
                        {permissions.edit && (
                            <Button color="light" as={Link} href={urls.edit}>
                                <i className="bx bx-edit me-1" />
                                {t('global.edit')}
                            </Button>
                        )}
                        {permissions.submit && (
                            <Button
                                color="blue"
                                disabled={processing === 'submit'}
                                onClick={() => runAction('submit', urls.submit)}
                            >
                                {t('global.submit')}
                            </Button>
                        )}
                        {permissions.cancel && (
                            <Button
                                color="light"
                                disabled={processing === 'cancel'}
                                onClick={() => {
                                    if (window.confirm(t('global.are_you_sure'))) {
                                        runAction('cancel', urls.cancel);
                                    }
                                }}
                            >
                                {t('global.cancel')}
                            </Button>
                        )}
                    </div>
                </Card>

                <Card className="shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.medicines')}
                    </h2>
                    <Table>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.medicine')}</TableHeader>
                                <TableHeader align="center">{t('global.quantity')}</TableHeader>
                                <TableHeader>{t('global.unit')}</TableHeader>
                                <TableHeader>{t('global.batch_number')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {transferRequest.items.map((line, index) => (
                                <TableRow key={line.id}>
                                    <TableCell>{index + 1}</TableCell>
                                    <TableCell className="font-medium">{line.item_name}</TableCell>
                                    <TableCell align="center">{line.quantity.toLocaleString()}</TableCell>
                                    <TableCell muted>{line.unit_name ?? '—'}</TableCell>
                                    <TableCell muted>{line.batch_number ?? '—'}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Card>

                <Card className="shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.depot.status_history')}
                    </h2>
                    {transferRequest.status_logs.length > 0 ? (
                        <ul className="space-y-3">
                            {transferRequest.status_logs.map((log, index) => (
                                <li
                                    key={`${log.to_status}-${log.created_at}-${index}`}
                                    className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-gray-100 p-3 dark:border-gray-700"
                                >
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            {log.from_status && (
                                                <Badge color="gray">{log.from_status}</Badge>
                                            )}
                                            <i className="bx bx-right-arrow-alt text-gray-400" />
                                            <Badge color={depotStatusBadgeColor(log.to_status)}>
                                                {depotRequestStatusLabel(log.to_status, t)}
                                            </Badge>
                                        </div>
                                        {log.notes && (
                                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                {log.notes}
                                            </p>
                                        )}
                                    </div>
                                    <span className="text-sm text-gray-500">
                                        {log.user_name ?? '—'} · {log.created_at ?? '—'}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-sm text-gray-500">{t('global.no_data_found')}</p>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
