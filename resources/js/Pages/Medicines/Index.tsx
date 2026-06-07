import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedResult, SettingsPermissions } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

interface MedicineItem {
    id: number;
    name: string;
}

interface MedicineFilters {
    search: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

export default function IndexMedicines({
    medicines,
    filters: serverFilters,
    permissions,
    urls,
}: {
    medicines: PaginatedResult<MedicineItem>;
    filters: MedicineFilters;
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: MedicineFilters) => {
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
        medicines.meta.from && medicines.meta.to
            ? `${t('global.showing')} ${medicines.meta.from}-${medicines.meta.to} ${t('global.of')} ${medicines.meta.total}`
            : `${medicines.meta.total} ${t('global.results')}`;

    return (
        <DashboardLayout>
            <Head title={t('global.medicines')} />
            <div className="mx-auto max-w-6xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.medicines')}
                        subtitle={summaryLabel}
                        icon="bx-plus-medical"
                        accent="from-teal-500 to-cyan-600"
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
                        className="mb-6 space-y-4"
                    >
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <Label>{t('global.search')}</Label>
                                <TextInput
                                    value={filters.search}
                                    onChange={(event) =>
                                        setFilters({ ...filters, search: event.target.value })
                                    }
                                    placeholder={t('global.search_by_name')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.per_page')}</Label>
                                <SearchableSelect
                                    value={filters.per_page || '15'}
                                    onChange={(value) => {
                                        const next = { ...filters, per_page: value };
                                        setFilters(next);
                                        applyFilters(next);
                                    }}
                                >
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.sort_by')}</Label>
                                <SearchableSelect
                                    value={filters.sort_by || 'id'}
                                    onChange={(value) => {
                                        const next = { ...filters, sort_by: value };
                                        setFilters(next);
                                        applyFilters(next);
                                    }}
                                >
                                    <option value="id">{t('global.id')}</option>
                                    <option value="name">{t('global.name')}</option>
                                    <option value="created_at">{t('global.created_at')}</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.sort_order') || t('global.order')}</Label>
                                <SearchableSelect
                                    value={filters.sort_order || 'desc'}
                                    onChange={(value) => {
                                        const next = { ...filters, sort_order: value };
                                        setFilters(next);
                                        applyFilters(next);
                                    }}
                                >
                                    <option value="desc">{t('global.descending')}</option>
                                    <option value="asc">{t('global.ascending')}</option>
                                </SearchableSelect>
                            </div>
                        </div>
                        <div className="flex gap-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.search')}
                            </Button>
                            <Button
                                type="button"
                                color="light"
                                disabled={processing}
                                onClick={() =>
                                    applyFilters({
                                        search: '',
                                        sort_by: 'id',
                                        sort_order: 'desc',
                                        per_page: '15',
                                    })
                                }
                            >
                                {t('global.clear')}
                            </Button>
                        </div>
                    </form>
                    {medicines.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {medicines.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(medicines.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>{item.name}</TableCell>
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
                            {t('global.no_medicines_found') || t('global.no_results_found')}
                        </p>
                    )}
                    {medicines.links.length > 0 && (
                        <ul className="mt-6 inline-flex -space-x-px text-sm">
                            {medicines.links.map((link, index) => renderPaginationLink(link, index))}
                        </ul>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
