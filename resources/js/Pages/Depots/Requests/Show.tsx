import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    Badge,
    Button,
    Card,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Textarea,
} from 'flowbite-react';
import { useState } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import { DEPOT_SUCCESS_BTN_CLASS, depotStatusBadgeColor } from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotNavUrls } from '../../../types/depot';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../../utils/settingsUi';

interface StatusLog {
    status: string;
    notes: string | null;
    user_name: string | null;
    created_at: string | null;
}

interface RequestDetail {
    id: number;
    request_number: string | null;
    status: string;
    quantity: number;
    item_name: string | null;
    requesting_depot_name: string | null;
    source_depot_name: string | null;
    requested_by_name: string | null;
    created_at: string | null;
    batch_number: string | null;
    notes: string | null;
    unit_name: string | null;
    rejection_reason: string | null;
    approved_by_name: string | null;
    fulfilled_by_name: string | null;
    approved_at: string | null;
    fulfilled_at: string | null;
    transaction_number: string | null;
    status_logs: StatusLog[];
}

interface RequestActionPermissions {
    submit: boolean;
    approve: boolean;
    reject: boolean;
    fulfill: boolean;
    cancel: boolean;
}

export default function ShowDepotRequest({
    request: depotRequest,
    permissions,
    navUrls,
    urls,
}: {
    request: RequestDetail;
    permissions: RequestActionPermissions;
    navUrls: DepotNavUrls;
    urls: {
        index: string;
        submit: string;
        approve: string;
        reject: string;
        fulfill: string;
        cancel: string;
        transaction: string | null;
    };
}) {
    const { t } = useTranslation();
    const [rejectOpen, setRejectOpen] = useState(false);
    const [processing, setProcessing] = useState<string | null>(null);

    const rejectForm = useForm({ rejection_reason: '' });

    const runAction = (key: string, url: string, method: 'post' | 'patch' = 'post', data?: Record<string, string>) => {
        setProcessing(key);
        const opts = { preserveScroll: true, onFinish: () => setProcessing(null) };
        if (method === 'patch') {
            router.patch(url, data ?? {}, opts);
        } else {
            router.post(url, data ?? {}, opts);
        }
    };

    const detailRows: Array<[string, string]> = [
        [t('global.status'), depotRequest.status],
        [t('global.depot.requesting_depot'), depotRequest.requesting_depot_name ?? '—'],
        [t('global.depot.source_depot'), depotRequest.source_depot_name ?? '—'],
        [t('global.item'), depotRequest.item_name ?? '—'],
        [t('global.quantity'), depotRequest.quantity.toLocaleString()],
        [t('global.unit'), depotRequest.unit_name ?? '—'],
        [t('global.batch_number'), depotRequest.batch_number ?? '—'],
        [t('global.requested_by'), depotRequest.requested_by_name ?? '—'],
        [t('global.created_at'), depotRequest.created_at ?? '—'],
    ];

    if (depotRequest.approved_by_name) {
        detailRows.push([t('global.approved_by'), depotRequest.approved_by_name]);
        detailRows.push([t('global.approved_at'), depotRequest.approved_at ?? '—']);
    }
    if (depotRequest.fulfilled_by_name) {
        detailRows.push([t('global.fulfilled_by'), depotRequest.fulfilled_by_name]);
        detailRows.push([t('global.fulfilled_at'), depotRequest.fulfilled_at ?? '—']);
    }
    if (depotRequest.rejection_reason) {
        detailRows.push([t('global.rejection_reason'), depotRequest.rejection_reason]);
    }
    if (depotRequest.notes) {
        detailRows.push([t('global.notes'), depotRequest.notes]);
    }

    return (
        <DashboardLayout>
            <Head title={depotRequest.request_number ?? t('global.depot.requests')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH} space-y-4`}>
                <DepotNavTabs active="requests" urls={navUrls} />
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={depotRequest.request_number ?? `#${depotRequest.id}`}
                        subtitle={t('global.depot.requests')}
                        icon="bx-git-pull-request"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />

                    <div className="mb-4 flex flex-wrap gap-2">
                        <Badge color={depotStatusBadgeColor(depotRequest.status)} className="text-sm">
                            {depotRequest.status}
                        </Badge>
                        {urls.transaction && depotRequest.transaction_number && (
                            <Button color="light" size="sm" as={Link} href={urls.transaction}>
                                {depotRequest.transaction_number}
                            </Button>
                        )}
                    </div>

                    <dl className="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {detailRows.map(([label, value]) => (
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

                    <div className="flex flex-wrap gap-2">
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
                        {t('global.workflow')}
                    </h2>
                    {depotRequest.status_logs.length > 0 ? (
                        <ul className="space-y-3">
                            {depotRequest.status_logs.map((log, index) => (
                                <li
                                    key={`${log.status}-${log.created_at}-${index}`}
                                    className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-gray-100 p-3 dark:border-gray-700"
                                >
                                    <div>
                                        <Badge color={depotStatusBadgeColor(log.status)}>{log.status}</Badge>
                                        {log.notes && (
                                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">{log.notes}</p>
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
