import { Link, router } from '@inertiajs/react';
import { Badge, Button, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import { inventoryStatusLabel } from '../../utils/inventoryLabels';

type PurchaseRow = {
    id: number;
    purchase_number: string;
    supplier: string | null;
    purchase_date: string;
    status: string;
    items_count: number;
    depot?: { id: number; name: string } | null;
};

export default function PurchasesIndex({
    purchases,
    filters: serverFilters,
    depots,
    inventoryNav,
    urls,
}: {
    purchases: { data: PurchaseRow[]; links: any[] };
    filters: { search: string; status: string; depot_id: string; per_page: string };
    depots: Array<{ id: number; name: string }>;
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
            title={t('global.purchases')}
            active="purchases"
            urls={inventoryNav}
            action={
                <Link
                    href={urls.create}
                    className="inline-flex items-center gap-1 rounded-md bg-teal-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-teal-700"
                >
                    <i className="bx bx-plus text-sm" />
                    {t('global.new_purchase')}
                </Link>
            }
        >
            <form onSubmit={apply} className="mb-2 grid grid-cols-2 gap-2 md:grid-cols-5">
                <TextInput
                    sizing="sm"
                    value={filters.search}
                    onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                    placeholder={t('global.search_po_supplier')}
                />
                <SearchableSelect
                    value={filters.status}
                    onChange={(v) => setFilters({ ...filters, status: v })}
                    options={[
                        { value: '', label: t('global.all_status') },
                        { value: 'draft', label: t('global.draft') },
                        { value: 'received', label: t('global.received') },
                        { value: 'cancelled', label: t('global.cancelled') },
                    ]}
                />
                <SearchableSelect
                    value={filters.depot_id}
                    onChange={(v) => setFilters({ ...filters, depot_id: v })}
                    options={[
                        { value: '', label: t('global.all_depots') },
                        ...depots.map((d) => ({ value: String(d.id), label: d.name })),
                    ]}
                />
                <Button type="submit" size="xs" color="light" className="h-9">
                    <i className="bx bx-filter-alt me-1" />
                    {t('global.filter')}
                </Button>
            </form>

            <Table>
                <TableHead>
                    <TableRow>
                        <TableHeader>{t('global.po_number')}</TableHeader>
                        <TableHeader>{t('global.depot.name')}</TableHeader>
                        <TableHeader>{t('global.supplier')}</TableHeader>
                        <TableHeader>{t('global.date')}</TableHeader>
                        <TableHeader>{t('global.items_count')}</TableHeader>
                        <TableHeader>{t('global.status')}</TableHeader>
                        <TableHeader />
                    </TableRow>
                </TableHead>
                <TableBody>
                    {purchases.data.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={7} className="py-6 text-center text-sm text-gray-500">
                                <i className="bx bx-cart text-2xl text-gray-300" />
                                <div>{t('global.no_purchases_yet')}</div>
                            </TableCell>
                        </TableRow>
                    ) : (
                        purchases.data.map((row) => (
                            <TableRow key={row.id}>
                                <TableCell className="py-1.5 text-sm font-semibold">{row.purchase_number}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.depot?.name ?? '—'}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.supplier ?? '—'}</TableCell>
                                <TableCell className="py-1.5 text-sm">{String(row.purchase_date).slice(0, 10)}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.items_count}</TableCell>
                                <TableCell className="py-1.5">
                                    <Badge
                                        color={
                                            row.status === 'received'
                                                ? 'success'
                                                : row.status === 'cancelled'
                                                  ? 'failure'
                                                  : 'warning'
                                        }
                                        size="xs"
                                    >
                                        {inventoryStatusLabel(t, row.status)}
                                    </Badge>
                                </TableCell>
                                <TableCell className="py-1.5">
                                    <Link
                                        href={urls.show.replace('__ID__', String(row.id))}
                                        className="inline-flex h-7 w-7 items-center justify-center rounded-md text-teal-600 hover:bg-teal-50"
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
                <SettingsPagination links={purchases.links ?? []} />
            </div>
        </InventoryShell>
    );
}
