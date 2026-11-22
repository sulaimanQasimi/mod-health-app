import { router } from '@inertiajs/react';
import { Badge, Button, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import SettingsPagination from '../../Components/Settings/SettingsPagination';

type MovementRow = {
    id: number;
    type: string;
    batch_number: string;
    medicine?: { id: number; name: string } | null;
    from: string;
    to: string;
    qty_units: number;
    user?: string | null;
    created_at: string | null;
};

const TYPE_ICON: Record<string, string> = {
    purchase_receive: 'bx-log-in-circle',
    depot_to_pharmacy: 'bx-right-arrow-alt',
    depot_to_depot: 'bx-transfer',
    sale: 'bx-cart-alt',
    adjustment: 'bx-slider-alt',
};

export default function StockMovementsIndex({
    movements,
    filters: serverFilters,
    inventoryNav,
    urls,
}: {
    movements: { data: MovementRow[]; links: any[] };
    filters: { search: string; type: string };
    inventoryNav: InventoryNavUrls;
    urls: { index: string };
}) {
    const [filters, setFilters] = useState(serverFilters);

    const apply = (e?: FormEvent) => {
        e?.preventDefault();
        router.get(urls.index, Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')), {
            preserveScroll: true,
        });
    };

    return (
        <InventoryShell title="Stock movements" active="movements" urls={inventoryNav}>
            <form onSubmit={apply} className="mb-2 flex flex-wrap gap-2">
                <TextInput
                    sizing="sm"
                    className="min-w-[180px] flex-1"
                    value={filters.search}
                    onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                    placeholder="Batch / medicine"
                />
                <div className="min-w-[180px]">
                    <SearchableSelect
                        value={filters.type}
                        onChange={(v) => setFilters({ ...filters, type: v })}
                        options={[
                            { value: '', label: 'All types' },
                            { value: 'purchase_receive', label: 'Purchase receive' },
                            { value: 'depot_to_pharmacy', label: 'Depot → Pharmacy' },
                            { value: 'depot_to_depot', label: 'Depot → Depot' },
                            { value: 'sale', label: 'Sale' },
                            { value: 'adjustment', label: 'Adjustment' },
                        ]}
                    />
                </div>
                <Button type="submit" size="xs" color="light" className="h-9">
                    <i className="bx bx-filter-alt me-1" />
                    Filter
                </Button>
            </form>

            <Table>
                <TableHead>
                    <TableRow>
                        <TableHeader>When</TableHeader>
                        <TableHeader>Type</TableHeader>
                        <TableHeader>Medicine</TableHeader>
                        <TableHeader>Batch</TableHeader>
                        <TableHeader>From</TableHeader>
                        <TableHeader>To</TableHeader>
                        <TableHeader>Qty</TableHeader>
                        <TableHeader>User</TableHeader>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {movements.data.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={8} className="py-6 text-center text-sm text-gray-500">
                                <i className="bx bx-transfer-alt text-2xl text-gray-300" />
                                <div>No movements yet</div>
                            </TableCell>
                        </TableRow>
                    ) : (
                        movements.data.map((row) => (
                            <TableRow key={row.id}>
                                <TableCell className="py-1.5 text-xs text-gray-500">{row.created_at}</TableCell>
                                <TableCell className="py-1.5">
                                    <Badge color="info" size="xs">
                                        <i className={`bx ${TYPE_ICON[row.type] ?? 'bx-radio-circle'} me-0.5`} />
                                        {row.type.replace(/_/g, ' ')}
                                    </Badge>
                                </TableCell>
                                <TableCell className="py-1.5 text-sm">{row.medicine?.name}</TableCell>
                                <TableCell className="py-1.5 font-mono text-sm">{row.batch_number}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.from}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.to}</TableCell>
                                <TableCell className="py-1.5 text-sm font-semibold">{row.qty_units}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.user ?? '—'}</TableCell>
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
            <div className="mt-2">
                <SettingsPagination links={movements.links ?? []} />
            </div>
        </InventoryShell>
    );
}
