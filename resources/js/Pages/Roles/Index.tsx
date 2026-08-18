import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedResult, SettingsPermissions } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface RolePermissionPreview {
    id: number;
    name: string;
    name_dr: string | null;
}

interface RoleItem {
    id: number;
    name: string;
    name_dr: string | null;
    permissions_count: number;
    permissions: RolePermissionPreview[];
}

interface IndexRolesProps {
    roles: PaginatedResult<RoleItem>;
    filters: { search: string; per_page: string };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}

export default function IndexRoles({
    roles,
    filters: serverFilters,
    permissions,
    urls,
}: IndexRolesProps) {
    const { t, locale } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: typeof filters) => {
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

    const summaryLabel = buildPaginationSummary(roles.meta, t);
    const permissionLabel = (item: RolePermissionPreview) =>
        locale === 'en' ? item.name : item.name_dr || item.name;

    return (
        <DashboardLayout>
            <Head title={t('global.roles')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.roles')}
                        subtitle={summaryLabel}
                        icon="bx-shield-quarter"
                        accent="from-indigo-500 to-blue-600"
                        backLabel={t('global.back')}
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
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
                        className="mb-6 flex flex-wrap items-end gap-4"
                    >
                        <div className="min-w-[220px] flex-1">
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) =>
                                    setFilters({ ...filters, search: event.target.value })
                                }
                            />
                        </div>
                        <SettingsFilterActions processing={processing} />
                    </form>
                    {roles.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name_en')}</TableHeader>
                                    <TableHeader>{t('global.name_dr')}</TableHeader>
                                    <TableHeader>{t('global.permissions')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {roles.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(roles.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>{item.name}</TableCell>
                                        <TableCell muted>{item.name_dr ?? '—'}</TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap items-center gap-1">
                                                {item.permissions.map((permission) => (
                                                    <Badge key={permission.id} color="indigo">
                                                        {permissionLabel(permission)}
                                                    </Badge>
                                                ))}
                                                {item.permissions_count > item.permissions.length ? (
                                                    <Badge color="gray">
                                                        +{item.permissions_count - item.permissions.length}
                                                    </Badge>
                                                ) : null}
                                                {item.permissions_count === 0 ? '—' : null}
                                            </div>
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
                    <SettingsPagination links={roles.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
