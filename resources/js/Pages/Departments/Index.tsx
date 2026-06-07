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

    const summaryLabel =
        departments.meta.from && departments.meta.to
            ? `${t('global.showing')} ${departments.meta.from}-${departments.meta.to} ${t('global.of')} ${departments.meta.total}`
            : `${departments.meta.total} ${t('global.results')}`;

    return (
        <DashboardLayout>
            <Head title={t('global.departments')} />
            <div className="mx-auto max-w-6xl">
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
                                onChange={(value) => {
                                    const next = { ...filters, category_id: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                                options={filterOptions.categories.map((c) => ({
                                    value: String(c.id),
                                    label: c.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div className="md:col-span-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.apply_filters')}
                            </Button>
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
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                <Link
                                                    href={`${urls.show}/${item.id}`}
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50"
                                                >
                                                    <i className="bx bx-show text-lg" />
                                                </Link>
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
                                                        onClick={() =>
                                                            window.confirm(t('global.are_you_sure')) &&
                                                            router.delete(`${urls.destroy}/${item.id}`, {
                                                                preserveScroll: true,
                                                            })
                                                        }
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
                    {departments.links.length > 0 && (
                        <ul className="mt-6 inline-flex -space-x-px text-sm">
                            {departments.links.map((link, i) => renderPaginationLink(link, i))}
                        </ul>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
