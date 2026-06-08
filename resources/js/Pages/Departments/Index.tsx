import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
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

interface DepartmentItem {
    id: number;
    name: string;
    room_number: string | null;
    category_name: string | null;
}

export default function IndexDepartments({
    departments,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    departments: PaginatedResult<DepartmentItem>;
    filters: { search: string; category_id: string; per_page: string };
    filterOptions: { categories: OptionItem[] };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; show: string; edit: string; destroy: string };
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

    const summaryLabel = buildPaginationSummary(departments.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.departments')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.departments')}
                        subtitle={summaryLabel}
                        icon="bx-buildings"
                        accent="from-blue-500 to-indigo-600"
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
                        onSubmit={(e: FormEvent) => {
                            e.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-2"
                    >
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.category')}</Label>
                            <SearchableSelect
                                value={filters.category_id}
                                onChange={(value) => setFilters({ ...filters, category_id: value })}
                                options={(filterOptions?.categories ?? []).map((c) => ({
                                    value: String(c.id),
                                    label: c.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div className="md:col-span-2">
                            <SettingsFilterActions
                                processing={processing}
                                showClear
                                onClear={() => {
                                    const empty = { search: '', category_id: '', per_page: filters.per_page };
                                    setFilters(empty);
                                    applyFilters(empty);
                                }}
                            />
                        </div>
                    </form>
                    {departments.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.room_number')}</TableHeader>
                                    <TableHeader>{t('global.category')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {departments.data.map((item, i) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(departments.meta.from ?? 1) + i}</TableCell>
                                        <TableCell>
                                            <Link
                                                href={`${urls.show}/${item.id}`}
                                                className="font-medium text-blue-600 hover:underline"
                                            >
                                                {item.name}
                                            </Link>
                                        </TableCell>
                                        <TableCell muted>{item.room_number ?? '—'}</TableCell>
                                        <TableCell muted>{item.category_name ?? '—'}</TableCell>
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
                    <SettingsPagination links={departments.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
