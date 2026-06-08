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

interface PharmacyUserPreview {
    id: number;
    full_name: string;
    email: string;
    role: string;
}

interface PharmacyListItem {
    id: number;
    name: string;
    phone: string;
    address: string;
    users_count: number;
    users: PharmacyUserPreview[];
}

export default function IndexPharmacies({
    pharmacies,
    filters: serverFilters,
    permissions,
    urls,
}: {
    pharmacies: PaginatedResult<PharmacyListItem>;
    filters: { search: string; per_page: string };
    permissions: SettingsPermissions;
    urls: {
        index: string;
        create: string;
        show: string;
        edit: string;
        manageUsers: string;
        destroy: string;
    };
}) {
    const { t } = useTranslation();
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

    const summaryLabel = buildPaginationSummary(pharmacies.meta, t);

    const roleLabel = (role: string) => {
        if (role === 'manager') return t('global.manager');
        if (role === 'staff') return t('global.staff');
        if (role === 'procurement') return t('global.procurement');
        if (role === 'viewer') return t('global.viewer');
        return role;
    };

    return (
        <DashboardLayout>
            <Head title={t('global.pharmacies')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.pharmacies')}
                        subtitle={summaryLabel}
                        icon="bx-clinic"
                        accent="from-emerald-500 to-teal-600"
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
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                placeholder={t('global.search_by_pharmacy_name')}
                            />
                        </div>
                        <SettingsFilterActions processing={processing} />
                    </form>

                    {pharmacies.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.pharmacy_name')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy_phone')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy_address')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy_users')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {pharmacies.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(pharmacies.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>
                                            {permissions.view ? (
                                                <Link
                                                    href={`${urls.show}/${item.id}`}
                                                    className="font-medium text-blue-600 hover:underline"
                                                >
                                                    {item.name}
                                                </Link>
                                            ) : (
                                                item.name
                                            )}
                                        </TableCell>
                                        <TableCell muted>{item.phone}</TableCell>
                                        <TableCell muted>
                                            {item.address.length > 60
                                                ? `${item.address.slice(0, 60)}…`
                                                : item.address}
                                        </TableCell>
                                        <TableCell>
                                            {item.users_count > 0 ? (
                                                <div className="space-y-1">
                                                    {item.users.slice(0, 2).map((user) => (
                                                        <div key={user.id} className="text-sm">
                                                            <span className="font-medium">{user.full_name}</span>
                                                            <Badge color="gray" className="ms-2">
                                                                {roleLabel(user.role)}
                                                            </Badge>
                                                        </div>
                                                    ))}
                                                    {item.users_count > 2 && (
                                                        <span className="text-xs text-gray-500">
                                                            + {item.users_count - 2}
                                                        </span>
                                                    )}
                                                </div>
                                            ) : (
                                                <span className="text-sm text-gray-500">
                                                    {t('global.no_users_assigned')}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${item.id}`}
                                                permission={permissions.view}
                                            />
                                            <TableActionButton
                                                kind="custom"
                                                href={`${urls.manageUsers}/${item.id}/manage-users`}
                                                permission={permissions.manage_users}
                                                icon="bx-user-plus"
                                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-cyan-600 hover:bg-cyan-50 dark:hover:bg-cyan-900/30"
                                            />
                                            <TableActionButton
                                                kind="edit"
                                                href={`${urls.edit}/${item.id}/edit`}
                                                permission={permissions.edit}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                disabled={deletingId === item.id}
                                                confirm={t('global.are_you_sure_delete_pharmacy')}
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
                        <SettingsEmptyState message={t('global.no_pharmacies_found')} />
                    )}
                    <SettingsPagination links={pharmacies.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
