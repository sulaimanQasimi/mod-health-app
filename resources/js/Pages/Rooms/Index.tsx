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

interface RoomItem {
    id: number;
    name: string;
    floor_name: string | null;
    branch_name: string | null;
    department_name: string | null;
}

export default function IndexRooms({
    rooms,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    rooms: PaginatedResult<RoomItem>;
    filters: {
        search: string;
        branch_id: string;
        floor_id: string;
        department_id: string;
        per_page: string;
    };
    filterOptions: { branches: OptionItem[]; floors: OptionItem[]; departments: OptionItem[] };
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

    return (
        <DashboardLayout>
            <Head title={t('global.rooms')} />
            <div className="mx-auto max-w-7xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.rooms')}
                        subtitle={`${rooms.meta.total} ${t('global.results')}`}
                        icon="bx-door-open"
                        accent="from-emerald-500 to-green-600"
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
                        className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(e) => setFilters({ ...filters, search: e.target.value })}
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
                                options={filterOptions.branches.map((b) => ({
                                    value: String(b.id),
                                    label: b.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.floor')}</Label>
                            <SearchableSelect
                                value={filters.floor_id}
                                onChange={(value) => {
                                    const next = { ...filters, floor_id: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                                options={filterOptions.floors.map((f) => ({
                                    value: String(f.id),
                                    label: f.name,
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
                                options={filterOptions.departments.map((d) => ({
                                    value: String(d.id),
                                    label: d.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                    </form>
                    <Table>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.name')}</TableHeader>
                                <TableHeader>{t('global.floor')}</TableHeader>
                                <TableHeader>{t('global.department')}</TableHeader>
                                <TableHeader>{t('global.branch')}</TableHeader>
                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {rooms.data.map((item, i) => (
                                <TableRow key={item.id}>
                                    <TableCell>{(rooms.meta.from ?? 1) + i}</TableCell>
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
                                    <TableCell muted>{item.floor_name ?? '—'}</TableCell>
                                    <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                    <TableCell muted>{item.branch_name ?? '—'}</TableCell>
                                    <TableCell align="center">
                                        <div className="flex justify-center gap-1">
                                            {permissions.view && (
                                                <Link
                                                    href={`${urls.show}/${item.id}`}
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50"
                                                >
                                                    <i className="bx bx-show text-lg" />
                                                </Link>
                                            )}
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
                    <ul className="mt-6 inline-flex -space-x-px text-sm">
                        {rooms.links.map((link, i) => renderPaginationLink(link, i))}
                    </ul>
                </Card>
            </div>
        </DashboardLayout>
    );
}
