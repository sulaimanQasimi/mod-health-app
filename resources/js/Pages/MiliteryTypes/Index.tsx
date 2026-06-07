import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedResult, SettingsPermissions } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

interface MiliteryTypeItem {
    id: number;
    name: string;
}

export default function IndexMiliteryTypes({
    militeryTypes,
    filters: serverFilters,
    permissions,
    urls,
}: {
    militeryTypes: PaginatedResult<MiliteryTypeItem>;
    filters: { search: string; per_page: string };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

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

    const summaryLabel =
        militeryTypes.meta.from && militeryTypes.meta.to
            ? `${t('global.showing')} ${militeryTypes.meta.from}-${militeryTypes.meta.to} ${t('global.of')} ${militeryTypes.meta.total}`
            : `${militeryTypes.meta.total} ${t('global.results')}`;

    return (
        <DashboardLayout>
            <Head title={t('global.militery_types')} />
            <div className="mx-auto max-w-5xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.militery_types')}
                        subtitle={summaryLabel}
                        icon="bx-shield"
                        accent="from-slate-500 to-gray-600"
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
                        className="mb-6 flex gap-4"
                    >
                        <div className="flex-1">
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                            />
                        </div>
                        <div className="flex items-end">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.search')}
                            </Button>
                        </div>
                    </form>
                    {militeryTypes.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {militeryTypes.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(militeryTypes.meta.from ?? 1) + index}</TableCell>
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
                            {t('global.no_results_found')}
                        </p>
                    )}
                    {militeryTypes.links.length > 0 && (
                        <ul className="mt-6 inline-flex -space-x-px text-sm">
                            {militeryTypes.links.map((link, index) => renderPaginationLink(link, index))}
                        </ul>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
