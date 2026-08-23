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
import { DepotCrudPermissions, DepotNavPermissions, DepotNavUrls } from '../../types/depot';
import { PaginatedResult } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ToolListItem {
    id: number;
    name: string;
    code: string;
    unit_name: string | null;
    description: string | null;
    is_active: boolean;
}

interface ToolFilters {
    search: string;
    is_active: string;
    per_page: string;
}

const EMPTY_FILTERS: ToolFilters = {
    search: '',
    is_active: '',
    per_page: '15',
};

export default function IndexTools({
    tools,
    filters: serverFilters,
    permissions,
    navUrls,
    navPermissions,
    urls,
}: {
    tools: PaginatedResult<ToolListItem>;
    filters: ToolFilters;
    permissions: DepotCrudPermissions;
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState({ ...EMPTY_FILTERS, ...serverFilters });
    const [processing, setProcessing] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    useEffect(() => setFilters({ ...EMPTY_FILTERS, ...serverFilters }), [serverFilters]);

    const applyFilters = useCallback(
        (next: ToolFilters) => {
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

    const summaryLabel = buildPaginationSummary(tools.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.depot.tools')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <DepotNavTabs active="tools" urls={navUrls} permissions={navPermissions} />
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.tools')}
                        subtitle={summaryLabel}
                        icon="bx-wrench"
                        accent="from-gray-600 to-gray-800"
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.depot.create_tool')}
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
                        <div className="xl:col-span-2">
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.status')}</Label>
                            <SearchableSelect
                                value={filters.is_active}
                                onChange={(value) => setFilters({ ...filters, is_active: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    { value: '1', label: t('global.active') },
                                    { value: '0', label: t('global.inactive') },
                                ]}
                            />
                        </div>
                        <SettingsFilterActions
                            processing={processing}
                            showClear
                            onClear={() => {
                                setFilters(EMPTY_FILTERS);
                                applyFilters(EMPTY_FILTERS);
                            }}
                        />
                    </form>

                    {tools.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.code')}</TableHeader>
                                    <TableHeader>{t('global.unit')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {tools.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(tools.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>{item.name}</TableCell>
                                        <TableCell muted>{item.code}</TableCell>
                                        <TableCell muted>{item.unit_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={item.is_active ? 'success' : 'gray'}>
                                                {item.is_active ? t('global.active') : t('global.inactive')}
                                            </Badge>
                                        </TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="edit"
                                                href={`${urls.edit}/${item.id}/edit`}
                                                permission={permissions.edit}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                disabled={deletingId === item.id}
                                                confirm={t('global.are_you_sure')}
                                                onClick={() => {
                                                    setDeletingId(item.id);
                                                    router.delete(`${urls.destroy}/${item.id}`, {
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
                        <SettingsEmptyState />
                    )}
                    <SettingsPagination links={tools.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
