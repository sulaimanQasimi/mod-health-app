import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
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

interface RoomOption extends OptionItem {
    floor_id?: number;
    branch_id?: number;
}

interface BedItem {
    id: number;
    number: string;
    room_name: string | null;
    floor_name: string | null;
    branch_name: string | null;
    is_occupied: boolean;
}

interface BedFilters {
    search: string;
    branch_id: string;
    floor_id: string;
    room_id: string;
    is_occupied: string;
    per_page: string;
}

export default function IndexBeds({
    beds,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
    nav,
}: {
    beds: PaginatedResult<BedItem>;
    filters: BedFilters;
    filterOptions: { branches: OptionItem[]; floors: FloorOption[]; rooms: RoomOption[] };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; show: string; edit: string; destroy: string };
    nav: FacilityNavProps;
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const floorsForBranch = useMemo(
        () =>
            (filterOptions?.floors ?? []).filter((floor) =>
                filters.branch_id ? String(floor.branch_id) === String(filters.branch_id) : true,
            ),
        [filterOptions?.floors, filters.branch_id],
    );

    const roomsForFloor = useMemo(
        () =>
            (filterOptions?.rooms ?? []).filter((room) => {
                if (filters.floor_id) {
                    return String(room.floor_id) === String(filters.floor_id);
                }
                if (filters.branch_id) {
                    return String(room.branch_id) === String(filters.branch_id);
                }
                return true;
            }),
        [filterOptions?.rooms, filters.branch_id, filters.floor_id],
    );

    const applyFilters = useCallback(
        (next: BedFilters) => {
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

    return (
        <DashboardLayout>
            <Head title={t('global.beds')} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={t('global.beds')}
                        subtitle={buildPaginationSummary(beds.meta, t)}
                        icon="bx-bed"
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
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-3 grid grid-cols-1 items-end gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"
                    >
                        <div>
                            <Label className="mb-0.5 !text-[11px]">{t('global.search')}</Label>
                            <TextInput
                                sizing="sm"
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                placeholder={t('global.bed_number')}
                            />
                        </div>
                        <div>
                            <Label className="mb-0.5 !text-[11px]">{t('global.branch')}</Label>
                            <SearchableSelect
                                compact
                                value={filters.branch_id}
                                onChange={(value) =>
                                    setFilters({
                                        ...filters,
                                        branch_id: value,
                                        floor_id: '',
                                        room_id: '',
                                    })
                                }
                                options={(filterOptions?.branches ?? []).map((branch) => ({
                                    value: String(branch.id),
                                    label: branch.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label className="mb-0.5 !text-[11px]">{t('global.floor')}</Label>
                            <SearchableSelect
                                compact
                                value={filters.floor_id}
                                onChange={(value) =>
                                    setFilters({ ...filters, floor_id: value, room_id: '' })
                                }
                                options={floorsForBranch.map((floor) => ({
                                    value: String(floor.id),
                                    label: floor.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label className="mb-0.5 !text-[11px]">{t('global.room')}</Label>
                            <SearchableSelect
                                compact
                                value={filters.room_id}
                                onChange={(value) => setFilters({ ...filters, room_id: value })}
                                options={roomsForFloor.map((room) => ({
                                    value: String(room.id),
                                    label: room.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label className="mb-0.5 !text-[11px]">{t('global.status')}</Label>
                            <SearchableSelect
                                compact
                                value={filters.is_occupied}
                                onChange={(value) => setFilters({ ...filters, is_occupied: value })}
                            >
                                <option value="">{t('global.all')}</option>
                                <option value="0">{t('global.available')}</option>
                                <option value="1">{t('global.occupied')}</option>
                            </SearchableSelect>
                        </div>
                        <FacilityFilterActions
                            processing={processing}
                            onClear={() => {
                                const empty = {
                                    search: '',
                                    branch_id: '',
                                    floor_id: '',
                                    room_id: '',
                                    is_occupied: '',
                                    per_page: filters.per_page,
                                };
                                setFilters(empty);
                                applyFilters(empty);
                            }}
                        />
                    </form>

                    {beds.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.bed_number')}</TableHeader>
                                    <TableHeader>{t('global.room')}</TableHeader>
                                    <TableHeader>{t('global.floor')}</TableHeader>
                                    <TableHeader>{t('global.branch')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {beds.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(beds.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>
                                            <Link
                                                href={`${urls.show}/${item.id}`}
                                                className="text-sm font-medium text-blue-600 hover:underline"
                                            >
                                                {item.number}
                                            </Link>
                                        </TableCell>
                                        <TableCell muted>{item.room_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.floor_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge
                                                color={item.is_occupied ? 'failure' : 'success'}
                                                className="!px-1.5 !py-0.5 !text-[10px]"
                                            >
                                                {item.is_occupied
                                                    ? t('global.occupied')
                                                    : t('global.available')}
                                            </Badge>
                                        </TableCell>
                                        <TableActionsCell>
                                            <TableActionButton kind="view" href={`${urls.show}/${item.id}`} />
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
                    <SettingsPagination links={beds.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
