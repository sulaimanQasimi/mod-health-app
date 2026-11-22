import { Link, router } from '@inertiajs/react';
import { Badge, Button } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import { inventoryStatusLabel } from '../../utils/inventoryLabels';

type TransferRow = {
    id: number;
    request_number: string;
    status: string;
    items_count: number;
    source_depot?: { id: number; name: string } | null;
    pharmacy?: { id: number; name: string } | null;
    destination_depot?: { id: number; name: string } | null;
};

export default function DepotTransfersIndex({
    transfers,
    filters: serverFilters,
    inventoryNav,
    urls,
}: {
    transfers: { data: TransferRow[]; links: any[] };
    filters: { status: string };
    inventoryNav: InventoryNavUrls;
    urls: { index: string; create: string; show: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);

    const apply = (e?: FormEvent) => {
        e?.preventDefault();
        router.get(urls.index, Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')), {
            preserveScroll: true,
        });
    };

    return (
        <InventoryShell
            title={t('global.transfers')}
            active="transfers"
            urls={inventoryNav}
            action={
                <Link
                    href={urls.create}
                    className="inline-flex items-center gap-1 rounded-md bg-violet-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-violet-700"
                >
                    <i className="bx bx-plus text-sm" />
                    {t('global.new_transfer')}
                </Link>
            }
        >
            <form onSubmit={apply} className="mb-2 flex max-w-sm gap-2">
                <div className="flex-1">
                    <SearchableSelect
                        value={filters.status}
                        onChange={(v) => setFilters({ status: v })}
                        options={[
                            { value: '', label: t('global.all_status') },
                            { value: 'draft', label: t('global.draft') },
                            { value: 'pending', label: t('global.pending') },
                            { value: 'approved', label: t('global.approved') },
                            { value: 'fulfilled', label: t('global.fulfilled') },
                            { value: 'rejected', label: t('global.rejected') },
                            { value: 'cancelled', label: t('global.cancelled') },
                        ]}
                    />
                </div>
                <Button type="submit" size="xs" color="light" className="h-9">
                    <i className="bx bx-filter-alt" />
                </Button>
            </form>

            <Table>
                <TableHead>
                    <TableRow>
                        <TableHeader>{t('global.request_number')}</TableHeader>
                        <TableHeader>{t('global.from')}</TableHeader>
                        <TableHeader>{t('global.to')}</TableHeader>
                        <TableHeader>{t('global.items_count')}</TableHeader>
                        <TableHeader>{t('global.status')}</TableHeader>
                        <TableHeader />
                    </TableRow>
                </TableHead>
                <TableBody>
                    {transfers.data.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={6} className="py-6 text-center text-sm text-gray-500">
                                <i className="bx bx-git-pull-request text-2xl text-gray-300" />
                                <div>{t('global.no_transfers_yet')}</div>
                            </TableCell>
                        </TableRow>
                    ) : (
                        transfers.data.map((row) => (
                            <TableRow key={row.id}>
                                <TableCell className="py-1.5 text-sm font-semibold">{row.request_number}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.source_depot?.name}</TableCell>
                                <TableCell className="py-1.5 text-sm">
                                    {row.pharmacy?.name ?? row.destination_depot?.name ?? '—'}
                                </TableCell>
                                <TableCell className="py-1.5 text-sm">{row.items_count}</TableCell>
                                <TableCell className="py-1.5">
                                    <Badge color="purple" size="xs">
                                        {inventoryStatusLabel(t, row.status)}
                                    </Badge>
                                </TableCell>
                                <TableCell className="py-1.5">
                                    <Link
                                        href={urls.show.replace('__ID__', String(row.id))}
                                        className="inline-flex h-7 w-7 items-center justify-center rounded-md text-violet-600 hover:bg-violet-50"
                                        title={t('global.open')}
                                    >
                                        <i className="bx bx-show" />
                                    </Link>
                                </TableCell>
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
            <div className="mt-2">
                <SettingsPagination links={transfers.links ?? []} />
            </div>
        </InventoryShell>
    );
}
