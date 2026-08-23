import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import { depotStatusBadgeColor, depotTypeLabel } from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotNavPermissions, DepotNavUrls } from '../../../types/depot';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface TransactionDetail {
    id: number;
    transaction_number: string | null;
    type: string;
    status: string;
    quantity: number;
    item_name: string | null;
    item_type: string | null;
    source_name: string | null;
    destination_name: string | null;
    transaction_date: string | null;
    created_by_name: string | null;
    batch_number: string | null;
    unit_name: string | null;
    issued_date: string | null;
    expiry_date: string | null;
    notes: string | null;
    depot_name: string | null;
    from_depot_name: string | null;
    to_depot_name: string | null;
    pharmacy_name: string | null;
    request_number: string | null;
    created_at: string | null;
    updated_by_name: string | null;
}

export default function ShowDepotTransaction({
    transaction,
    permissions,
    navUrls,
    navPermissions,
    urls,
}: {
    transaction: TransactionDetail;
    permissions: { view: boolean; create: boolean; cancel: boolean };
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: { index: string; cancel: string };
}) {
    const { t } = useTranslation();
    const [cancelling, setCancelling] = useState(false);

    const detailRows: Array<[string, string]> = [
        [t('global.transaction_number'), transaction.transaction_number ?? '—'],
        [t('global.type'), depotTypeLabel(transaction.type, t)],
        [t('global.status'), transaction.status],
        [t('global.item'), transaction.item_name ?? '—'],
        [t('global.quantity'), transaction.quantity.toLocaleString()],
        [t('global.unit'), transaction.unit_name ?? '—'],
        [t('global.batch_number'), transaction.batch_number ?? '—'],
        [t('global.depot.name'), transaction.depot_name ?? '—'],
        [t('global.depot.source_depot'), transaction.from_depot_name ?? '—'],
        [t('global.depot.destination_depot'), transaction.to_depot_name ?? '—'],
        [t('global.depot.pharmacy'), transaction.pharmacy_name ?? '—'],
        [t('global.transaction_date'), transaction.transaction_date ?? '—'],
        [t('global.expiry_date'), transaction.expiry_date ?? '—'],
        [t('global.created_by'), transaction.created_by_name ?? '—'],
        [t('global.created_at'), transaction.created_at ?? '—'],
    ];

    if (transaction.notes) {
        detailRows.push([t('global.notes'), transaction.notes]);
    }

    return (
        <DashboardLayout>
            <Head title={transaction.transaction_number ?? t('global.depot.transactions')} />
            <div className={`mx-auto w-full min-w-0 ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <DepotNavTabs active="index" urls={navUrls} permissions={navPermissions} />
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={transaction.transaction_number ?? `#${transaction.id}`}
                        subtitle={depotTypeLabel(transaction.type, t)}
                        icon="bx-transfer"
                        accent="from-sky-500 to-blue-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                        action={
                            permissions.cancel && transaction.status !== 'cancelled' ? (
                                <Button
                                    color="failure"
                                    disabled={cancelling}
                                    onClick={() => {
                                        if (window.confirm(t('global.are_you_sure'))) {
                                            setCancelling(true);
                                            router.patch(urls.cancel, {}, {
                                                onFinish: () => setCancelling(false),
                                            });
                                        }
                                    }}
                                >
                                    <i className="bx bx-x-circle me-2" />
                                    {t('global.cancel')}
                                </Button>
                            ) : undefined
                        }
                    />

                    <div className="mb-4">
                        <Badge color={depotStatusBadgeColor(transaction.status)} className="text-sm">
                            {transaction.status}
                        </Badge>
                    </div>

                    <dl className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
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
                </Card>
            </div>
        </DashboardLayout>
    );
}
