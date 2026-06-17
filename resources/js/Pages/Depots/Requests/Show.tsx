import { Head, Link, router, useForm } from '@inertiajs/react';
import { Alert, Badge, Button, Card, Modal, ModalBody, ModalFooter, ModalHeader, Textarea } from 'flowbite-react';
import { useState } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import DepotRequestWorkflowStepper from '../../../Components/Depots/DepotRequestWorkflowStepper';
import {
    DEPOT_SUCCESS_BTN_CLASS,
    depotRequestStatusLabel,
    depotStatusBadgeColor,
} from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../Components/ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotNavPermissions, DepotNavUrls, DepotRequestDetail } from '../../../types/depot';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface RequestActionPermissions {
    edit: boolean;
    submit: boolean;
    approve: boolean;
    reject: boolean;
    fulfill: boolean;
    cancel: boolean;
}

export default function ShowDepotRequest({
    request: depotRequest,
    workflowSteps,
    permissions,
    navUrls,
    navPermissions,
    urls,
    viewContext = 'depot',
}: {
    request: DepotRequestDetail;
    workflowSteps: string[];
    permissions: RequestActionPermissions;
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: {
        index: string;
        edit: string;
        print: string;
        submit: string;
        approve: string;
        reject: string;
        fulfill: string;
        cancel: string;
        transactions: string;
        transactionShow: string;
    };
    viewContext?: 'depot' | 'pharmacy';
}) {
    const { t } = useTranslation();
    const isPharmacyContext = viewContext === 'pharmacy';
    const [rejectOpen, setRejectOpen] = useState(false);
    const [processing, setProcessing] = useState<string | null>(null);

    const rejectForm = useForm({ rejection_reason: '' });

    const runAction = (key: string, url: string, data?: Record<string, string>) => {
        setProcessing(key);
        router.post(url, data ?? {}, { preserveScroll: true, onFinish: () => setProcessing(null) });
    };

    const metaRows: Array<[string, string]> = [
        [t('global.depot.branch'), depotRequest.branch_name ?? '—'],
        [t('global.depot.requesting_department'), depotRequest.department_name ?? '—'],
        [t('global.depot.request_user'), depotRequest.request_user_name ?? depotRequest.requested_by_name ?? '—'],
        [t('global.depot.pharmacy_depot'), depotRequest.pharmacy_depot_label ?? depotRequest.destination_name ?? '—'],
        [
            depotRequest.destination_type === 'pharmacy'
                ? t('global.pharmacy')
                : t('global.depot.requesting_depot'),
            depotRequest.destination_name ?? '—',
        ],
        [t('global.depot.source_depot'), depotRequest.source_depot_name ?? '—'],
        [t('global.depot.transfer_lines'), depotRequest.items_count.toLocaleString()],
        [t('global.quantity'), depotRequest.total_quantity.toLocaleString()],
        [t('global.requested_by'), depotRequest.requested_by_name ?? '—'],
        [t('global.created_at'), depotRequest.created_at ?? '—'],
    ];

    if (depotRequest.approved_by_name) {
        metaRows.push([t('global.approved_by'), depotRequest.approved_by_name]);
        metaRows.push([t('global.approved_at'), depotRequest.approved_at ?? '—']);
    }
    if (depotRequest.fulfilled_by_name) {
        metaRows.push([t('global.fulfilled_by'), depotRequest.fulfilled_by_name]);
        metaRows.push([t('global.fulfilled_at'), depotRequest.fulfilled_at ?? '—']);
    }
    if (depotRequest.rejection_reason) {
        metaRows.push([t('global.rejection_reason'), depotRequest.rejection_reason]);
    }
    if (depotRequest.notes) {
        metaRows.push([t('global.notes'), depotRequest.notes]);
    }

    const awaitingSourceProcessing = ['pending', 'approved'].includes(depotRequest.status);
    const canProcessRequest = permissions.approve || permissions.reject || permissions.fulfill;

    return (
        <DashboardLayout>
            <Head title={depotRequest.request_number ?? t('global.depot.requests')} />
            <div className={`mx-auto w-full min-w-0 ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                {!isPharmacyContext && <DepotNavTabs active="requests" urls={navUrls} permissions={navPermissions} />}

                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={depotRequest.request_number ?? `#${depotRequest.id}`}
                        subtitle={t('global.depot.requests')}
                        icon="bx-git-pull-request"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />

                    <div className="mb-4 flex flex-wrap items-center gap-2">
                        <Badge color={depotStatusBadgeColor(depotRequest.status)} className="text-sm">
                            {depotRequestStatusLabel(depotRequest.status, t)}
                        </Badge>
                        <span className="text-sm text-gray-500">{depotRequest.items_summary}</span>
                    </div>

                    <div className="mb-6 rounded-xl border border-violet-100 bg-violet-50/50 p-4 dark:border-violet-900/40 dark:bg-violet-950/20">
                        <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-300">
                            {t('global.workflow')}
                        </p>
                        <DepotRequestWorkflowStepper
                            steps={workflowSteps}
                            currentRank={depotRequest.workflow_rank}
                            status={depotRequest.status}
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

                    {awaitingSourceProcessing && !canProcessRequest && depotRequest.source_depot_name && (
                        <Alert color="info" className="mb-6">
                            <span className="text-sm">
                                {t('global.depot.request_processing_by_source').replace(
                                    ':depot',
                                    depotRequest.source_depot_name,
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
                        {permissions.approve && (
                            <button
                                type="button"
                                className={DEPOT_SUCCESS_BTN_CLASS}
                                disabled={processing === 'approve'}
                                onClick={() => runAction('approve', urls.approve)}
                            >
                                {t('global.approve')}
                            </button>
                        )}
                        {permissions.reject && (
                            <Button color="failure" onClick={() => setRejectOpen(true)}>
                                {t('global.reject')}
                            </Button>
                        )}
                        {permissions.fulfill && (
                            <Button
                                color="blue"
                                disabled={processing === 'fulfill'}
                                onClick={() => {
                                    if (window.confirm(t('global.are_you_sure'))) {
                                        runAction('fulfill', urls.fulfill);
                                    }
                                }}
                            >
                                {t('global.fulfill_branch_transfer')}
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
                        {t('global.depot.transfer_lines')}
                    </h2>
                    <Table>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.type')}</TableHeader>
                                <TableHeader>{t('global.name')}</TableHeader>
                                <TableHeader align="center">{t('global.quantity')}</TableHeader>
                                <TableHeader>{t('global.unit')}</TableHeader>
                                <TableHeader>{t('global.batch_number')}</TableHeader>
                                <TableHeader>{t('global.depot.transfers')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {depotRequest.items.map((line, index) => (
                                <TableRow key={line.id}>
                                    <TableCell>{index + 1}</TableCell>
                                    <TableCell>
                                        <Badge color="gray">
                                            {line.item_type === 'tool'
                                                ? t('global.depot.tool')
                                                : t('global.medicine')}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="font-medium">{line.item_name}</TableCell>
                                    <TableCell align="center">{line.quantity.toLocaleString()}</TableCell>
                                    <TableCell muted>{line.unit_name ?? '—'}</TableCell>
                                    <TableCell muted>{line.batch_number ?? '—'}</TableCell>
                                    <TableCell>
                                        {line.transaction_id ? (
                                            <Link
                                                href={`${urls.transactionShow}/${line.transaction_id}`}
                                                className="text-sm font-medium text-violet-600 hover:underline"
                                            >
                                                {line.transaction_number ?? `#${line.transaction_id}`}
                                            </Link>
                                        ) : (
                                            <span className="text-sm text-gray-400">—</span>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Card>

                {depotRequest.transfers.length > 0 && (
                    <Card className="shadow-sm">
                        <div className="mb-4 flex items-center justify-between gap-2">
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                {t('global.depot.transfers')}
                            </h2>
                            <Button color="light" size="sm" as={Link} href={urls.transactions}>
                                {t('global.depot.transactions')}
                            </Button>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {depotRequest.transfers.map((transfer) => (
                                <Button
                                    key={transfer.id}
                                    color="light"
                                    size="sm"
                                    as={Link}
                                    href={`${urls.transactionShow}/${transfer.id}`}
                                >
                                    {transfer.transaction_number ?? `#${transfer.id}`}
                                </Button>
                            ))}
                        </div>
                    </Card>
                )}

                <Card className="shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.depot.status_history')}
                    </h2>
                    {depotRequest.status_logs.length > 0 ? (
                        <ul className="space-y-3">
                            {depotRequest.status_logs.map((log, index) => (
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

            <Modal show={rejectOpen} onClose={() => setRejectOpen(false)}>
                <ModalHeader>{t('global.reject')}</ModalHeader>
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        rejectForm.post(urls.reject, {
                            preserveScroll: true,
                            onSuccess: () => {
                                setRejectOpen(false);
                                rejectForm.reset();
                            },
                        });
                    }}
                >
                    <ModalBody>
                        <Textarea
                            required
                            rows={4}
                            value={rejectForm.data.rejection_reason}
                            onChange={(event) => rejectForm.setData('rejection_reason', event.target.value)}
                            placeholder={t('global.rejection_reason')}
                        />
                        {rejectForm.errors.rejection_reason && (
                            <p className="mt-1 text-sm text-red-600">{rejectForm.errors.rejection_reason}</p>
                        )}
                    </ModalBody>
                    <ModalFooter>
                        <Button color="failure" type="submit" disabled={rejectForm.processing}>
                            {t('global.reject')}
                        </Button>
                        <Button color="light" onClick={() => setRejectOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </DashboardLayout>
    );
}
