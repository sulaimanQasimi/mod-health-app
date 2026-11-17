import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
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

interface FloorItem {
    id: number;
    name: string;
    branch_name: string | null;
    rooms_count: number;
}

export default function IndexFloors({
    floors,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
    nav,
}: {
    floors: PaginatedResult<FloorItem>;
    filters: { search: string; branch_id: string; per_page: string };
    filterOptions: { branches: OptionItem[] };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; show: string; edit: string; destroy: string };
    nav: FacilityNavProps;
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

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
            <Head title={t('global.floors')} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={t('global.floors')}
                        subtitle={buildPaginationSummary(floors.meta, t)}
                        icon="bx-layer"
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
                        className="mb-3 grid grid-cols-1 items-end gap-2 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto]"
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
                                onChange={(value) => setFilters({ ...filters, branch_id: value })}
                                options={(filterOptions?.branches ?? []).map((b) => ({
                                    value: String(b.id),
                                    label: b.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <FacilityFilterActions
                            processing={processing}
                            onClear={() => {
                                const empty = { search: '', branch_id: '', per_page: filters.per_page };
                                setFilters(empty);
                                applyFilters(empty);
                            }}
                        />
                    </form>

                    {floors.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.floor')}</TableHeader>
                                    <TableHeader>{t('global.branch')}</TableHeader>
                                    <TableHeader>{t('global.rooms')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {floors.data.map((item, i) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(floors.meta.from ?? 1) + i}</TableCell>
                                        <TableCell>
                                            <Link
                                                href={`${urls.show}/${item.id}`}
                                                className="text-sm font-medium text-blue-600 hover:underline"
                                            >
                                                {item.name}
                                            </Link>
                                        </TableCell>
                                        <TableCell muted>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.rooms_count}</TableCell>
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
                    <SettingsPagination links={floors.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
