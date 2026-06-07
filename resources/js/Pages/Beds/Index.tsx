import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult, SettingsPermissions } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

interface BedItem {
    id: number;
    number: string;
    room_name: string | null;
    is_occupied: boolean;
}

interface BedFilters {
    search: string;
    room_id: string;
    is_occupied: string;
    per_page: string;
}

export default function IndexBeds({
    beds,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    beds: PaginatedResult<BedItem>;
    filters: BedFilters;
    filterOptions: { rooms: OptionItem[] };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: BedFilters) => {
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
        beds.meta.from && beds.meta.to
            ? `${t('global.showing')} ${beds.meta.from}-${beds.meta.to} ${t('global.of')} ${beds.meta.total}`
            : `${beds.meta.total} ${t('global.results')}`;

    return (
        <DashboardLayout>
            <Head title={t('global.beds')} />
            <div className="mx-auto max-w-6xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.beds')}
                        subtitle={summaryLabel}
                        icon="bx-bed"
                        accent="from-cyan-500 to-blue-600"
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
                                placeholder={t('global.bed_number')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.room')}</Label>
                            <SearchableSelect
                                value={filters.room_id}
                                onChange={(value) => {
                                    const next = { ...filters, room_id: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                                options={filterOptions.rooms.map((room) => ({
                                    value: String(room.id),
                                    label: room.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.status')}</Label>
                            <SearchableSelect
                                value={filters.is_occupied}
                                onChange={(value) => {
                                    const next = { ...filters, is_occupied: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                            >
                                <option value="">{t('global.all')}</option>
                                <option value="0">{t('global.available')}</option>
                                <option value="1">{t('global.occupied')}</option>
                            </SearchableSelect>
                        </div>
                        <div className="flex items-end gap-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.filter')}
                            </Button>
                            <Button
                                type="button"
                                color="light"
                                disabled={processing}
                                onClick={() =>
                                    applyFilters({ search: '', room_id: '', is_occupied: '', per_page: '15' })
                                }
                            >
                                {t('global.reset')}
                            </Button>
                        </div>
                    </form>
                    {beds.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.bed_number')}</TableHeader>
                                    <TableHeader>{t('global.related_room')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {beds.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(beds.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>{item.number}</TableCell>
                                        <TableCell muted>{item.room_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={item.is_occupied ? 'failure' : 'success'}>
                                                {item.is_occupied
                                                    ? t('global.occupied')
                                                    : t('global.available')}
                                            </Badge>
                                        </TableCell>
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
                    {beds.links.length > 0 && (
                        <ul className="mt-6 inline-flex -space-x-px text-sm">
                            {beds.links.map((link, index) => renderPaginationLink(link, index))}
                        </ul>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
