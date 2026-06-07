import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult, SettingsPermissions } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

interface OperationTypeItem {
    id: number;
    name: string;
    branch_name: string | null;
    department_name: string | null;
}

interface OperationTypeFilters {
    search: string;
    branch_id: string;
    department_id: string;
    per_page: string;
}

export default function IndexOperationTypes({
    operationTypes,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    operationTypes: PaginatedResult<OperationTypeItem>;
    filters: OperationTypeFilters;
    filterOptions: { branches: OptionItem[]; departments: OptionItem[] };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: OperationTypeFilters) => {
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

    const summaryLabel =
        operationTypes.meta.from && operationTypes.meta.to
            ? `${t('global.showing')} ${operationTypes.meta.from}-${operationTypes.meta.to} ${t('global.of')} ${operationTypes.meta.total}`
            : `${operationTypes.meta.total} ${t('global.results')}`;

    return (
        <DashboardLayout>
            <Head title={t('global.operation_types')} />
            <div className="mx-auto max-w-7xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.operation_types')}
                        subtitle={summaryLabel}
                        icon="bx-cut"
                        accent="from-rose-500 to-red-600"
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
                                onChange={(value) => {
                                    const next = { ...filters, branch_id: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                                options={filterOptions.branches.map((branch) => ({
                                    value: String(branch.id),
                                    label: branch.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.department')}</Label>
                            <SearchableSelect
                                value={filters.department_id}
                                onChange={(value) => {
                                    const next = { ...filters, department_id: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                                options={filterOptions.departments.map((department) => ({
                                    value: String(department.id),
                                    label: department.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div className="flex items-end">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.search')}
                            </Button>
                        </div>
                    </form>
                    {operationTypes.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.branch')}</TableHeader>
                                    <TableHeader>{t('global.department')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {operationTypes.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(operationTypes.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>{item.name}</TableCell>
                                        <TableCell muted>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                {permissions.edit && (
                                                    <Link
                                                        href={`${urls.edit}/${item.id}/edit`}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50"
                                                    >
                                                        <i className="bx bx-edit text-lg" />
                                                    </Link>
                                                )}
                                                {permissions.delete && (
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            if (window.confirm(t('global.are_you_sure'))) {
                                                                router.delete(`${urls.destroy}/${item.id}`, {
                                                                    preserveScroll: true,
                                                                });
                                                            }
                                                        }}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50"
                                                    >
                                                        <i className="bx bx-trash text-lg" />
                                                    </button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <p className="py-12 text-center text-sm text-gray-500">
                            {t('global.no_results_found')}
                        </p>
                    )}
                    {operationTypes.links.length > 0 && (
                        <ul className="mt-6 inline-flex -space-x-px text-sm">
                            {operationTypes.links.map((link, index) => renderPaginationLink(link, index))}
                        </ul>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
