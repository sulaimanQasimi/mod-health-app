import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import DepotNavTabs from '../../Components/Depots/DepotNavTabs';
import { DEPOT_CARD_CLASS, depotStatusBadgeColor, depotTypeLabel } from '../../Components/Depots/depotUi';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotCrudPermissions, DepotDetail, DepotNavPermissions, DepotNavUrls } from '../../types/depot';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface StockPreviewItem {
    item_type: string;
    name: string;
    available: number;
}

interface TransactionPreview {
    id: number;
    transaction_number: string | null;
    type: string;
    status: string;
    quantity: number;
    item_name: string | null;
    transaction_date: string | null;
    show_url: string;
}

interface RequestPreview {
    id: number;
    request_number: string | null;
    status: string;
    quantity: number;
    item_name: string | null;
    counterparty_name: string | null;
    show_url: string;
}

interface DepotShowPermissions extends DepotCrudPermissions {
    transaction_create: boolean;
    request_create: boolean;
    movement_pharmacy: boolean;
}

export default function ShowDepot({
    depot,
    metrics,
    stockPreview,
    recentTransactions,
    pendingOutgoingRequests,
    pendingIncomingRequests,
    permissions,
    navUrls,
    navPermissions,
    urls,
}: {
    depot: DepotDetail;
    metrics: {
        stock_items: number;
        total_quantity: number;
        recent_transactions: number;
        pending_requests: number;
    };
    stockPreview: StockPreviewItem[];
    recentTransactions: TransactionPreview[];
    pendingOutgoingRequests: RequestPreview[];
    pendingIncomingRequests: RequestPreview[];
    permissions: DepotShowPermissions;
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: {
        index: string;
        edit: string;
        destroy: string;
        stock: string;
        transactionCreate: string;
        requestCreate: string;
        depotToPharmacy: string;
    };
}) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    const metricCards = [
        { label: t('global.depot.stock_summary'), value: metrics.stock_items, icon: 'bx-capsule' },
        { label: t('global.quantity'), value: metrics.total_quantity, icon: 'bx-layer' },
        { label: t('global.depot.recent_transactions'), value: metrics.recent_transactions, icon: 'bx-transfer' },
        { label: t('global.depot.requests'), value: metrics.pending_requests, icon: 'bx-time-five' },
    ];

    return (
        <DashboardLayout>
            <Head title={depot.name} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <DepotNavTabs active="index" urls={navUrls} permissions={navPermissions} />
                <Card className="overflow-hidden shadow-sm">
                    <SettingsPageHeader
                        title={depot.name}
                        subtitle={depot.address || t('global.depot.address')}
                        icon="bx-store"
                        accent="from-amber-500 to-orange-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                        action={
                            <div className="flex flex-wrap gap-2">
                                {permissions.request_create && (
                                    <Button color="blue" as={Link} href={urls.requestCreate}>
                                        <i className="bx bx-plus-circle me-2" />
                                        {t('global.depot.new_request')}
                                    </Button>
                                )}
                                {permissions.transaction_create && (
                                    <Button color="light" as={Link} href={urls.transactionCreate}>
                                        <i className="bx bx-package me-2" />
                                        {t('global.depot.new')}
                                    </Button>
                                )}
                                {permissions.movement_pharmacy && (
                                    <Button color="light" as={Link} href={urls.depotToPharmacy}>
                                        <i className="bx bx-clinic me-2" />
                                        {t('global.depot.depot_to_pharmacy')}
                                    </Button>
                                )}
                                <Button color="light" as={Link} href={urls.stock}>
                                    <i className="bx bx-list-check me-2" />
                                    {t('global.depot.full_stock')}
                                </Button>
                                {permissions.edit && (
                                    <Button color="warning" as={Link} href={urls.edit}>
                                        <i className="bx bx-edit me-2" />
                                        {t('global.edit')}
                                    </Button>
                                )}
                                {permissions.delete && (
                                    <Button
                                        color="failure"
                                        disabled={deleting}
                                        onClick={() => {
                                            if (window.confirm(t('global.are_you_sure'))) {
                                                setDeleting(true);
                                                router.delete(urls.destroy, {
                                                    onFinish: () => setDeleting(false),
                                                });
                                            }
                                        }}
                                    >
                                        <i className="bx bx-trash me-2" />
                                        {t('global.delete')}
                                    </Button>
                                )}
                            </div>
                        }
                    />
                    <div className="mt-4 flex flex-wrap gap-2">
                        <Badge color={depot.is_active ? 'success' : 'gray'}>
                            {depot.is_active ? t('global.active') : t('global.inactive')}
                        </Badge>
                        <Badge color={depot.is_base ? 'info' : 'gray'}>
                            {depot.is_base ? t('global.depot.base') : t('global.depot.child')}
                        </Badge>
                        {depot.branch_name && (
                            <Badge color="gray">
                                <i className="bx bx-buildings me-1" />
                                {depot.branch_name}
                            </Badge>
                        )}
                    </div>
                </Card>

                <dl className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {metricCards.map((metric) => (
                        <div key={metric.label} className={`${DEPOT_CARD_CLASS} flex items-center gap-3 p-4`}>
                            <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white">
                                <i className={`bx ${metric.icon} text-xl`} />
                            </span>
                            <div>
                                <dt className="text-xs font-semibold uppercase text-gray-500">{metric.label}</dt>
                                <dd className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {metric.value.toLocaleString()}
                                </dd>
                            </div>
                        </div>
                    ))}
                </dl>

                <Card className="shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.details')}
                    </h2>
                    <dl className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {[
                            [t('global.depot.branch'), depot.branch_name ?? '—'],
                            [t('global.depot.department'), depot.department_name ?? '—'],
                            [t('global.depot.pharmacy'), depot.pharmacy_name ?? '—'],
                            [t('global.depot.parent_depot'), depot.parent_depot_name ?? '—'],
                            [t('global.depot.is_active'), depot.is_active ? t('global.active') : t('global.inactive')],
                            [t('global.depot.is_base'), depot.is_base ? t('global.depot.base') : t('global.depot.child')],
                        ].map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40"
                            >
                                <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {label}
                                </dt>
                                <dd className="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{value}</dd>
                            </div>
                        ))}
                    </dl>
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card className="shadow-sm">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                {t('global.depot.stock_summary')}
                            </h2>
                            <Link href={urls.stock} className="text-sm font-semibold text-amber-600 hover:underline">
                                {t('global.view')}
                            </Link>
                        </div>
                        {stockPreview.length > 0 ? (
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.type')}</TableHeader>
                                        <TableHeader>{t('global.item')}</TableHeader>
                                        <TableHeader align="center">{t('global.quantity')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {stockPreview.map((item, index) => (
                                        <TableRow key={`${item.item_type}-${item.name}-${index}`}>
                                            <TableCell>
                                                <Badge color="info">{item.item_type}</Badge>
                                            </TableCell>
                                            <TableCell className="font-medium">{item.name}</TableCell>
                                            <TableCell align="center">{item.available.toLocaleString()}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <p className="text-sm text-gray-500">{t('global.no_data_found')}</p>
                        )}
                    </Card>

                    <Card className="shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.depot.recent_transactions')}
                        </h2>
                        {recentTransactions.length > 0 ? (
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.type')}</TableHeader>
                                        <TableHeader>{t('global.item')}</TableHeader>
                                        <TableHeader align="center">{t('global.quantity')}</TableHeader>
                                        <TableHeader>{t('global.date')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {recentTransactions.map((tx) => (
                                        <TableRow key={tx.id}>
                                            <TableCell>
                                                <Link href={tx.show_url} className="font-medium text-amber-600 hover:underline">
                                                    {depotTypeLabel(tx.type, t)}
                                                </Link>
                                            </TableCell>
                                            <TableCell muted>{tx.item_name ?? '—'}</TableCell>
                                            <TableCell align="center">{tx.quantity.toLocaleString()}</TableCell>
                                            <TableCell muted>{tx.transaction_date ?? '—'}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <p className="text-sm text-gray-500">{t('global.no_data_found')}</p>
                        )}
                    </Card>

                    <Card className="shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.depot.my_requests')}
                        </h2>
                        {pendingOutgoingRequests.length > 0 ? (
                            <div className="space-y-3">
                                {pendingOutgoingRequests.map((req) => (
                                    <div
                                        key={req.id}
                                        className="flex items-center justify-between gap-3 rounded-xl border border-gray-100 p-3 dark:border-gray-700"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900 dark:text-white">
                                                {req.item_name ?? '—'}
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                {t('global.quantity')}: {req.quantity.toLocaleString()}
                                            </p>
                                        </div>
                                        <Link href={req.show_url}>
                                            <Badge color={depotStatusBadgeColor(req.status)}>{req.status}</Badge>
                                        </Link>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-gray-500">{t('global.no_data_found')}</p>
                        )}
                    </Card>

                    <Card className="shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.depot.incoming_requests')}
                        </h2>
                        {pendingIncomingRequests.length > 0 ? (
                            <div className="space-y-3">
                                {pendingIncomingRequests.map((req) => (
                                    <div
                                        key={req.id}
                                        className="flex items-center justify-between gap-3 rounded-xl border border-gray-100 p-3 dark:border-gray-700"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900 dark:text-white">
                                                {req.counterparty_name ?? '—'}
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                {req.item_name ?? '—'} — {t('global.quantity')}:{' '}
                                                {req.quantity.toLocaleString()}
                                            </p>
                                        </div>
                                        <Link href={req.show_url}>
                                            <Badge color={depotStatusBadgeColor(req.status)}>{req.status}</Badge>
                                        </Link>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-gray-500">{t('global.no_data_found')}</p>
                        )}
                    </Card>
                </div>

                {depot.users.length > 0 && (
                    <Card className="shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.users')}
                        </h2>
                        <div className="grid gap-3 md:grid-cols-2">
                            {depot.users.map((user) => (
                                <div
                                    key={user.id}
                                    className="flex items-start gap-3 rounded-xl border border-gray-100 p-3 dark:border-gray-700"
                                >
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        {user.full_name.charAt(0).toUpperCase()}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="font-medium text-gray-900 dark:text-white">{user.full_name}</p>
                                        <p className="text-sm text-gray-500">{user.email}</p>
                                        <Badge color="info" className="mt-2">
                                            {user.role}
                                        </Badge>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </Card>
                )}
            </div>
        </DashboardLayout>
    );
}
