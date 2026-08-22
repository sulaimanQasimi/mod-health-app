import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import { depotRequestStatusLabel, depotStatusBadgeColor } from '../../Components/Depots/depotUi';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface TransferRequestListItem {
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
}

interface TransferFilters {
    search: string;
    pharmacy_id: string;
    status: string;
    medicine_id: string;
    date_from: string;
    date_to: string;
    per_page: string;
}

const EMPTY_FILTERS: TransferFilters = {
    search: '',
    pharmacy_id: '',
    status: '',
    medicine_id: '',
    date_from: '',
    date_to: '',
    per_page: '15',
};

export default function IndexPharmacyTransferRequests({
    requests,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    requests: PaginatedResult<TransferRequestListItem>;
    filters: TransferFilters;
    filterOptions: {
        pharmacies: OptionItem[];
        medicines: OptionItem[];
        statuses: string[];
    };
    permissions: { view: boolean; create: boolean };
    urls: { index: string; create: string; show: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: TransferFilters) => {
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

    const summaryLabel = buildPaginationSummary(requests.meta, t);
    const showPharmacyFilter = filterOptions.pharmacies.length > 1;

    return (
        <DashboardLayout>
            <Head title={t('global.pharmacy_transfer_requests')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.pharmacy_transfer_requests')}
                        subtitle={summaryLabel}
                        icon="bx-package"
                        accent="from-emerald-500 to-teal-600"
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.pharmacy_new_transfer_request')}
                                </Button>
                            ) : undefined
                        }
                    />

                    <p className="mb-6 text-sm text-gray-600 dark:text-gray-400">
                        {t('global.pharmacy_transfer_requests_hint')}
                    </p>

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
                                placeholder={t('global.medicine')}
                            />
                        </div>
                        {showPharmacyFilter && (
                            <div>
                                <Label>{t('global.pharmacy')}</Label>
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
                        )}
                        <div>
                            <Label>{t('global.status')}</Label>
                            <SearchableSelect
                                value={filters.status}
                                onChange={(value) => setFilters({ ...filters, status: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.statuses.map((status) => ({
                                        value: status,
                                        label: depotRequestStatusLabel(status, t),
                                    })),
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
                            <Label>{t('global.date_from')}</Label>
                            <PersianDateInput
                                value={filters.date_from}
                                onChange={(value) => setFilters({ ...filters, date_from: value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <PersianDateInput
                                value={filters.date_to}
                                onChange={(value) => setFilters({ ...filters, date_to: value })}
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

                    {requests.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.request_number')}</TableHeader>
                                    {showPharmacyFilter && <TableHeader>{t('global.pharmacy')}</TableHeader>}
                                    <TableHeader>{t('global.depot.source_depot')}</TableHeader>
                                    <TableHeader>{t('global.medicines')}</TableHeader>
                                    <TableHeader align="center">{t('global.quantity')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {requests.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(requests.meta.from ?? 1) + index}</TableCell>
                                        <TableCell className="font-medium">{item.request_number ?? '—'}</TableCell>
                                        {showPharmacyFilter && (
                                            <TableCell muted>{item.pharmacy_name ?? '—'}</TableCell>
                                        )}
                                        <TableCell muted>{item.source_depot_name ?? '—'}</TableCell>
                                        <TableCell muted>
                                            {item.items_summary}
                                            <span className="ms-1 text-xs text-gray-400">({item.items_count})</span>
                                        </TableCell>
                                        <TableCell align="center">{item.total_quantity.toLocaleString()}</TableCell>
                                        <TableCell>
                                            <Badge color={depotStatusBadgeColor(item.status)}>
                                                {depotRequestStatusLabel(item.status, t)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${item.id}`}
                                                permission={permissions.view}
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_data_found')} />
                    )}
                    <SettingsPagination links={requests.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
