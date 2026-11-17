import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import FacilityFilterActions from '../../Components/FacilityLayout/FacilityFilterActions';
import FacilityNavTabs, { FacilityNavProps } from '../../Components/FacilityLayout/FacilityNavTabs';
import FacilityPageHeader from '../../Components/FacilityLayout/FacilityPageHeader';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult, SettingsPermissions } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface FloorOption extends OptionItem {
    branch_id?: number;
}

interface RoomItem {
    id: number;
    name: string;
    floor_name: string | null;
    branch_name: string | null;
    department_name: string | null;
    beds_count: number;
}

export default function IndexRooms({
    rooms,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
    nav,
}: {
    rooms: PaginatedResult<RoomItem>;
    filters: {
        search: string;
        branch_id: string;
        floor_id: string;
        department_id: string;
        per_page: string;
    };
    filterOptions: { branches: OptionItem[]; floors: FloorOption[]; departments: OptionItem[] };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; show: string; edit: string; destroy: string };
    nav: FacilityNavProps;
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    const floorsForBranch = useMemo(
        () =>
            (filterOptions?.floors ?? []).filter((floor) =>
                filters.branch_id ? String(floor.branch_id) === String(filters.branch_id) : true,
            ),
        [filterOptions?.floors, filters.branch_id],
    );

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: typeof filters) => {
            setProcessing(true);
            router.get(urls.index, Object.fromEntries(Object.entries(next).filter(([, v]) => v !== '')), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.index],
    );

    return (
        <DashboardLayout>
            <Head title={t('global.rooms')} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={t('global.rooms')}
                        subtitle={buildPaginationSummary(rooms.meta, t)}
                        icon="bx-door-open"
                        backHref={nav.urls.dashboard}
                        backLabel={t('global.dashboard')}
                        action={
                            permissions.create ? (
                                <Button color="blue" size="xs" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-0.5 text-xs" />
                                    {t('global.create')}
                                </Button>
                            ) : undefined
                        }
                    />

                    <form
                        onSubmit={(e: FormEvent) => {
                            e.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-3 grid grid-cols-1 items-end gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"
                    >
                        <div>
                            <Label className="mb-0.5 !text-[11px]">{t('global.search')}</Label>
                            <TextInput
                                sizing="sm"
                                value={filters.search}
                                onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label className="mb-0.5 !text-[11px]">{t('global.branch')}</Label>
                            <SearchableSelect
                                compact
                                value={filters.branch_id}
                                onChange={(value) =>
                                    setFilters({ ...filters, branch_id: value, floor_id: '' })
                                }
                                options={(filterOptions?.branches ?? []).map((b) => ({
                                    value: String(b.id),
                                    label: b.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label className="mb-0.5 !text-[11px]">{t('global.floor')}</Label>
                            <SearchableSelect
                                compact
                                value={filters.floor_id}
                                onChange={(value) => setFilters({ ...filters, floor_id: value })}
                                options={floorsForBranch.map((f) => ({
                                    value: String(f.id),
                                    label: f.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label className="mb-0.5 !text-[11px]">{t('global.department')}</Label>
                            <SearchableSelect
                                compact
                                value={filters.department_id}
                                onChange={(value) => setFilters({ ...filters, department_id: value })}
                                options={(filterOptions?.departments ?? []).map((d) => ({
                                    value: String(d.id),
                                    label: d.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div className="xl:col-span-2">
                            <FacilityFilterActions
                                processing={processing}
                                onClear={() => {
                                    const empty = {
                                        search: '',
                                        branch_id: '',
                                        floor_id: '',
                                        department_id: '',
                                        per_page: filters.per_page,
                                    };
                                    setFilters(empty);
                                    applyFilters(empty);
                                }}
                            />
                        </div>
                    </form>

                    {rooms.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.floor')}</TableHeader>
                                    <TableHeader>{t('global.department')}</TableHeader>
                                    <TableHeader>{t('global.branch')}</TableHeader>
                                    <TableHeader>{t('global.beds')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {rooms.data.map((item, i) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(rooms.meta.from ?? 1) + i}</TableCell>
                                        <TableCell>
                                            {permissions.view ? (
                                                <Link
                                                    href={`${urls.show}/${item.id}`}
                                                    className="text-sm font-medium text-blue-600 hover:underline"
                                                >
                                                    {item.name}
                                                </Link>
                                            ) : (
                                                item.name
                                            )}
                                        </TableCell>
                                        <TableCell muted>{item.floor_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.beds_count}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${item.id}`}
                                                permission={permissions.view}
                                            />
                                            <TableActionButton
                                                kind="edit"
                                                href={`${urls.edit}/${item.id}/edit`}
                                                permission={permissions.edit}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                confirm={t('global.are_you_sure')}
                                                onClick={() =>
                                                    router.delete(`${urls.destroy}/${item.id}`, {
                                                        preserveScroll: true,
                                                    })
                                                }
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState />
                    )}
                    <SettingsPagination links={rooms.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
