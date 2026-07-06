import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import { depotRequestStatusLabel, depotStatusBadgeColor } from '../../../Components/Depots/depotUi';
import SettingsEmptyState from '../../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import PersianDateInput from '../../../Components/ui/PersianDateInput';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../Components/ui/Table';
import TableActionButton from '../../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../../Components/ui/TableActions';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotNavPermissions, DepotNavUrls, PaginatedDepotRequests } from '../../../types/depot';
import { OptionItem } from '../../../types/settings';
import { buildPaginationSummary } from '../../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface RequestFilters {
    search: string;
    requesting_depot_id: string;
    source_depot_id: string;
    pharmacy_id: string;
    destination_type: string;
    status: string;
    medicine_id: string;
    tool_id: string;
    date_from: string;
    date_to: string;
    per_page: string;
}

interface RequestPermissions {
    view: boolean;
    create: boolean;
    approve: boolean;
    fulfill: boolean;
}

const EMPTY_FILTERS: RequestFilters = {
    search: '',
    requesting_depot_id: '',
    source_depot_id: '',
    pharmacy_id: '',
    destination_type: '',
    status: '',
    medicine_id: '',
    tool_id: '',
    date_from: '',
    date_to: '',
    per_page: '15',
};

export default function IndexDepotRequests({
    requests,
    filters: serverFilters,
    filterOptions,
    permissions,
    navUrls,
    navPermissions,
    urls,
    viewContext = 'depot',
}: {
    requests: PaginatedDepotRequests;
    filters: RequestFilters;
    filterOptions: {
        depots: OptionItem[];
        pharmacies: OptionItem[];
        medicines: OptionItem[];
        tools: OptionItem[];
        statuses: string[];
        destinationTypes: string[];
    };
    permissions: RequestPermissions;
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: { index: string; create: string; show: string };
    viewContext?: 'depot' | 'pharmacy';
}) {
    const { t } = useTranslation();
    const isPharmacyContext = viewContext === 'pharmacy';
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: RequestFilters) => {
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

    return (
        <DashboardLayout>
            <Head title={t('global.depot.requests')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                {!isPharmacyContext && <DepotNavTabs active="requests" urls={navUrls} permissions={navPermissions} />}
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.requests')}
                        subtitle={summaryLabel}
                        icon="bx-git-pull-request"
                        accent="from-violet-500 to-purple-600"
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.depot.new_request')}
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
                            <Label>{t('global.depot.destination_type')}</Label>
                            <SearchableSelect
                                value={filters.destination_type}
                                onChange={(value) => setFilters({ ...filters, destination_type: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.destinationTypes.map((type) => ({
                                        value: type,
                                        label: type === 'pharmacy' ? t('global.pharmacy') : t('global.depot.requesting_depot'),
                                    })),
                                ]}
                            />
                        </div>
                        {!isPharmacyContext && (
                            <div>
                                <Label>{t('global.depot.requesting_depot')}</Label>
                                <SearchableSelect
                                    value={filters.requesting_depot_id}
                                    onChange={(value) => setFilters({ ...filters, requesting_depot_id: value })}
                                    options={[
                                        { value: '', label: t('global.all') },
                                        ...filterOptions.depots.map((item) => ({
                                            value: String(item.id),
                                            label: item.name,
                                        })),
                                    ]}
                                />
                            </div>
                        )}
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
                        <div>
                            <Label>{t('global.depot.source_depot')}</Label>
                            <SearchableSelect
                                value={filters.source_depot_id}
                                onChange={(value) => setFilters({ ...filters, source_depot_id: value })}
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
                                    <TableHeader>{t('global.depot.destination')}</TableHeader>
                                    <TableHeader>{t('global.depot.source_depot')}</TableHeader>
                                    <TableHeader>{t('global.depot.transfer_lines')}</TableHeader>
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
                                        <TableCell muted>{item.destination_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.source_depot_name ?? '—'}</TableCell>
                                        <TableCell muted>
                                            {item.items_summary}
                                            <span className="ms-1 text-xs text-gray-400">
                                                ({item.items_count})
                                            </span>
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
