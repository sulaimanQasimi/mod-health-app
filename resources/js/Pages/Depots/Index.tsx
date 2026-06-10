import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DepotNavTabs from '../../Components/Depots/DepotNavTabs';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { useTranslation } from '../../hooks/useTranslation';
import {
    DepotCrudPermissions,
    DepotListFilters,
    DepotNavUrls,
    PaginatedDepots,
} from '../../types/depot';
import { OptionItem } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

const EMPTY_FILTERS: DepotListFilters = {
    search: '',
    branch_id: '',
    department_id: '',
    pharmacy_id: '',
    parent_depot_id: '',
    is_active: '',
    is_base: '',
    per_page: '15',
};

export default function IndexDepots({
    depots,
    filters: serverFilters,
    filterOptions,
    permissions,
    navUrls,
    urls,
}: {
    depots: PaginatedDepots;
    filters: DepotListFilters;
    filterOptions: {
        branches: OptionItem[];
        departments: OptionItem[];
        pharmacies: OptionItem[];
        parentDepots: OptionItem[];
    };
    permissions: DepotCrudPermissions;
    navUrls: DepotNavUrls;
    urls: {
        index: string;
        create: string;
        show: string;
        edit: string;
        destroy: string;
        stock: string;
    };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: DepotListFilters) => {
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

    const summaryLabel = buildPaginationSummary(depots.meta, t);
    const yesNoOptions = [
        { value: '', label: t('global.all') },
        { value: '1', label: t('global.yes') },
        { value: '0', label: t('global.no') },
    ];

    return (
        <DashboardLayout>
            <Head title={t('global.depot.list')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <DepotNavTabs active="index" urls={navUrls} />
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.list')}
                        subtitle={summaryLabel}
                        icon="bx-store"
                        accent="from-amber-500 to-orange-600"
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.depot.create')}
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
                                placeholder={t('global.depot.name')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.depot.branch')}</Label>
                            <SearchableSelect
                                value={filters.branch_id}
                                onChange={(value) => setFilters({ ...filters, branch_id: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.branches.map((item) => ({
                                        value: String(item.id),
                                        label: item.name,
                                    })),
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.depot.department')}</Label>
                            <SearchableSelect
                                value={filters.department_id}
                                onChange={(value) => setFilters({ ...filters, department_id: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.departments.map((item) => ({
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
                            <Label>{t('global.depot.parent_depot')}</Label>
                            <SearchableSelect
                                value={filters.parent_depot_id}
                                onChange={(value) => setFilters({ ...filters, parent_depot_id: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.parentDepots.map((item) => ({
                                        value: String(item.id),
                                        label: item.name,
                                    })),
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.depot.is_active')}</Label>
                            <SearchableSelect
                                value={filters.is_active}
                                onChange={(value) => setFilters({ ...filters, is_active: value })}
                                options={yesNoOptions}
                            />
                        </div>
                        <div>
                            <Label>{t('global.depot.is_base')}</Label>
                            <SearchableSelect
                                value={filters.is_base}
                                onChange={(value) => setFilters({ ...filters, is_base: value })}
                                options={yesNoOptions}
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

                    {depots.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.depot.name')}</TableHeader>
                                    <TableHeader>{t('global.depot.branch')}</TableHeader>
                                    <TableHeader>{t('global.depot.department')}</TableHeader>
                                    <TableHeader>{t('global.depot.pharmacy')}</TableHeader>
                                    <TableHeader>{t('global.depot.parent_depot')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.users')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {depots.data.map((depot, index) => (
                                    <TableRow key={depot.id}>
                                        <TableCell>{(depots.meta.from ?? 1) + index}</TableCell>
                                        <TableCell className="font-medium">{depot.name}</TableCell>
                                        <TableCell muted>{depot.branch_name ?? '—'}</TableCell>
                                        <TableCell muted>{depot.department_name ?? '—'}</TableCell>
                                        <TableCell muted>{depot.pharmacy_name ?? '—'}</TableCell>
                                        <TableCell muted>{depot.parent_depot_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                <Badge color={depot.is_active ? 'success' : 'gray'}>
                                                    {depot.is_active ? t('global.active') : t('global.inactive')}
                                                </Badge>
                                                {depot.is_base && (
                                                    <Badge color="info">{t('global.depot.base')}</Badge>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>{depot.users_count}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${depot.id}`}
                                                permission={permissions.view}
                                            />
                                            <TableActionButton
                                                kind="edit"
                                                href={`${urls.edit}/${depot.id}/edit`}
                                                permission={permissions.edit}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                disabled={deletingId === depot.id}
                                                confirm={t('global.are_you_sure')}
                                                onClick={() => {
                                                    setDeletingId(depot.id);
                                                    router.delete(`${urls.destroy}/${depot.id}`, {
                                                        preserveScroll: true,
                                                        onFinish: () => setDeletingId(null),
                                                    });
                                                }}
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_data_found')} />
                    )}
                    <SettingsPagination links={depots.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
