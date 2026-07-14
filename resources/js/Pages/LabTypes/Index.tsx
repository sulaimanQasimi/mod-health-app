import { Head, Link, router } from '@inertiajs/react';
import { Alert, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import LabCategoriesModal, { LabCategoryItem } from '../../Components/LabTypes/LabCategoriesModal';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
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

interface LabTypeItem {
    id: number;
    name: string;
    branch_name: string | null;
    category_name: string | null;
    department_name: string | null;
    parameters_count: number;
}

export default function IndexLabTypes({
    labTypes,
    filters: serverFilters,
    filterOptions,
    categories,
    permissions,
    urls,
    categoryUrls,
    flash,
}: {
    labTypes: PaginatedResult<LabTypeItem>;
    filters: { search: string; branch_id: string; category_id: string; department_id: string; per_page: string };
    filterOptions: { branches: OptionItem[]; categories: OptionItem[]; departments: OptionItem[] };
    categories: LabCategoryItem[];
    permissions: SettingsPermissions & { view?: boolean };
    urls: { index: string; create: string; show: string; edit: string; destroy: string };
    categoryUrls: { store: string; update: string; destroy: string };
    flash?: { success?: string | null; error?: string | null };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [categoriesOpen, setCategoriesOpen] = useState(false);

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

    const summaryLabel = buildPaginationSummary(labTypes.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.lab_types')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.lab_types')}
                        subtitle={summaryLabel}
                        icon="bx-test-tube"
                        accent="from-violet-500 to-purple-600"
                        backLabel={t('global.back')}
                        action={
                            <SettingsPageActions>
                                {permissions.create && (
                                    <Button color="light" onClick={() => setCategoriesOpen(true)}>
                                        <i className="bx bx-category me-2 text-lg" />
                                        {t('global.categories')}
                                    </Button>
                                )}
                                {permissions.create && (
                                    <Button color="blue" as={Link} href={urls.create}>
                                        <i className="bx bx-plus me-2 text-lg" />
                                        {t('global.add_lab_type')}
                                    </Button>
                                )}
                            </SettingsPageActions>
                        }
                    />

                    {flash?.success && (
                        <Alert color="success" className="mb-4">
                            {flash.success}
                        </Alert>
                    )}
                    {flash?.error && (
                        <Alert color="failure" className="mb-4">
                            {flash.error}
                        </Alert>
                    )}

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
                            <Label>{t('global.branch')}</Label>
                            <SearchableSelect
                                value={filters.branch_id}
                                onChange={(value) =>
                                    setFilters({ ...filters, branch_id: value, department_id: '' })
                                }
                                options={filterOptions.branches.map((branch) => ({
                                    value: String(branch.id),
                                    label: branch.name,
                                }))}
                                placeholder={t('global.all_branches') || t('global.all')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.category')}</Label>
                            <SearchableSelect
                                value={filters.category_id}
                                onChange={(value) => setFilters({ ...filters, category_id: value })}
                                options={filterOptions.categories.map((c) => ({
                                    value: String(c.id),
                                    label: c.name,
                                }))}
                                placeholder={t('global.all_categories')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.department')}</Label>
                            <SearchableSelect
                                value={filters.department_id}
                                onChange={(value) => setFilters({ ...filters, department_id: value })}
                                options={filterOptions.departments.map((d) => ({
                                    value: String(d.id),
                                    label: d.name,
                                }))}
                                placeholder={t('global.all_departments')}
                            />
                        </div>
                        <div className="md:col-span-2 xl:col-span-4">
                            <SettingsFilterActions processing={processing} />
                        </div>
                    </form>
                    {labTypes.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.branch')}</TableHeader>
                                    <TableHeader>{t('global.category')}</TableHeader>
                                    <TableHeader>{t('global.department')}</TableHeader>
                                    <TableHeader>{t('global.parameters_count')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {labTypes.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(labTypes.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>
                                            {permissions.view ? (
                                                <Link
                                                    href={`${urls.show}/${item.id}`}
                                                    className="font-medium text-violet-700 hover:underline dark:text-violet-300"
                                                >
                                                    {item.name}
                                                </Link>
                                            ) : (
                                                item.name
                                            )}
                                        </TableCell>
                                        <TableCell muted>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.category_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                        <TableCell>{item.parameters_count}</TableCell>
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
                        <SettingsEmptyState message={t('global.no_item_is_found')} />
                    )}
                    <SettingsPagination links={labTypes.links} />
                </Card>
            </div>

            <LabCategoriesModal
                show={categoriesOpen}
                onClose={() => setCategoriesOpen(false)}
                categories={categories}
                canManage={Boolean(permissions.create)}
                urls={categoryUrls}
            />
        </DashboardLayout>
    );
}
