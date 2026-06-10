import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import { depotStatusBadgeColor, depotTypeLabel } from '../../../Components/Depots/depotUi';
import SettingsEmptyState from '../../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../Components/ui/Table';
import TableActionButton from '../../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../../Components/ui/TableActions';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotNavUrls, PaginatedDepotTransactions } from '../../../types/depot';
import { OptionItem } from '../../../types/settings';
import { buildPaginationSummary } from '../../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface TransactionFilters {
    search: string;
    depot_id: string;
    pharmacy_id: string;
    medicine_id: string;
    tool_id: string;
    item_type: string;
    type: string;
    status: string;
    date_from: string;
    date_to: string;
    per_page: string;
}

interface TransactionPermissions {
    view: boolean;
    create: boolean;
    cancel: boolean;
}

const EMPTY_FILTERS: TransactionFilters = {
    search: '',
    depot_id: '',
    pharmacy_id: '',
    medicine_id: '',
    tool_id: '',
    item_type: '',
    type: '',
    status: '',
    date_from: '',
    date_to: '',
    per_page: '15',
};

export default function IndexDepotTransactions({
    transactions,
    filters: serverFilters,
    filterOptions,
    permissions,
    navUrls,
    urls,
}: {
    transactions: PaginatedDepotTransactions;
    filters: TransactionFilters;
    filterOptions: {
        depots: OptionItem[];
        pharmacies: OptionItem[];
        medicines: OptionItem[];
        tools: OptionItem[];
        types: string[];
        statuses: string[];
    };
    permissions: TransactionPermissions;
    navUrls: DepotNavUrls;
    urls: { index: string; create: string; show: string; cancel: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [cancellingId, setCancellingId] = useState<number | null>(null);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: TransactionFilters) => {
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

    const summaryLabel = buildPaginationSummary(transactions.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.depot.depot_transactions')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <DepotNavTabs active="transactions" urls={navUrls} />
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.depot_transactions')}
                        subtitle={summaryLabel}
                        icon="bx-transfer"
                        accent="from-sky-500 to-blue-600"
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.depot.new')}
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
                            />
                        </div>
                        <div>
                            <Label>{t('global.depot.name')}</Label>
                            <SearchableSelect
                                value={filters.depot_id}
                                onChange={(value) => setFilters({ ...filters, depot_id: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.depots.map((item) => ({
                                        value: String(item.id),
                                        label: item.name,
                                    })),
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.depot.pharmacy')}</Label>
                            <SearchableSelect
                                value={filters.pharmacy_id}
                                onChange={(value) => setFilters({ ...filters, pharmacy_id: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.pharmacies.map((item) => ({
                                        value: String(item.id),
                                        label: item.name,
                                    })),
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.type')}</Label>
                            <SearchableSelect
                                value={filters.item_type}
                                onChange={(value) => setFilters({ ...filters, item_type: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    { value: 'medicine', label: t('global.medicine') },
                                    { value: 'tool', label: t('global.depot.tool') },
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.medicine')}</Label>
                            <SearchableSelect
                                value={filters.medicine_id}
                                onChange={(value) => setFilters({ ...filters, medicine_id: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.medicines.map((item) => ({
                                        value: String(item.id),
                                        label: item.name,
                                    })),
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.depot.tool')}</Label>
                            <SearchableSelect
                                value={filters.tool_id}
                                onChange={(value) => setFilters({ ...filters, tool_id: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.tools.map((item) => ({
                                        value: String(item.id),
                                        label: item.name,
                                    })),
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.type')}</Label>
                            <SearchableSelect
                                value={filters.type}
                                onChange={(value) => setFilters({ ...filters, type: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.types.map((type) => ({
                                        value: type,
                                        label: depotTypeLabel(type, t),
                                    })),
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.status')}</Label>
                            <SearchableSelect
                                value={filters.status}
                                onChange={(value) => setFilters({ ...filters, status: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.statuses.map((status) => ({
                                        value: status,
                                        label: status,
                                    })),
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <TextInput
                                type="date"
                                value={filters.date_from}
                                onChange={(event) => setFilters({ ...filters, date_from: event.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <TextInput
                                type="date"
                                value={filters.date_to}
                                onChange={(event) => setFilters({ ...filters, date_to: event.target.value })}
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

                    {transactions.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.transaction_number')}</TableHeader>
                                    <TableHeader>{t('global.type')}</TableHeader>
                                    <TableHeader>{t('global.item')}</TableHeader>
                                    <TableHeader align="center">{t('global.quantity')}</TableHeader>
                                    <TableHeader>{t('global.depot.source_depot')}</TableHeader>
                                    <TableHeader>{t('global.depot.destination_depot')}</TableHeader>
                                    <TableHeader>{t('global.date')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {transactions.data.map((tx, index) => (
                                    <TableRow key={tx.id}>
                                        <TableCell>{(transactions.meta.from ?? 1) + index}</TableCell>
                                        <TableCell className="font-medium">{tx.transaction_number ?? '—'}</TableCell>
                                        <TableCell>{depotTypeLabel(tx.type, t)}</TableCell>
                                        <TableCell muted>{tx.item_name ?? '—'}</TableCell>
                                        <TableCell align="center">{tx.quantity.toLocaleString()}</TableCell>
                                        <TableCell muted>{tx.source_name ?? '—'}</TableCell>
                                        <TableCell muted>{tx.destination_name ?? '—'}</TableCell>
                                        <TableCell muted>{tx.transaction_date ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={depotStatusBadgeColor(tx.status)}>{tx.status}</Badge>
                                        </TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${tx.id}`}
                                                permission={permissions.view}
                                            />
                                            {permissions.cancel && tx.status !== 'cancelled' && (
                                                <TableActionButton
                                                    kind="delete"
                                                    permission
                                                    disabled={cancellingId === tx.id}
                                                    confirm={t('global.are_you_sure')}
                                                    onClick={() => {
                                                        setCancellingId(tx.id);
                                                        router.patch(`${urls.cancel}/${tx.id}/cancel`, {}, {
                                                            preserveScroll: true,
                                                            onFinish: () => setCancellingId(null),
                                                        });
                                                    }}
                                                />
                                            )}
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_data_found')} />
                    )}
                    <SettingsPagination links={transactions.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
