import { router } from '@inertiajs/react';
import { Badge, Button, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import { inventoryMovementTypeLabel } from '../../utils/inventoryLabels';

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
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);

    const apply = (e?: FormEvent) => {
        e?.preventDefault();
        router.get(urls.index, Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')), {
            preserveScroll: true,
        });
    };

    return (
        <InventoryShell title={t('global.stock_movements')} active="movements" urls={inventoryNav}>
            <form onSubmit={apply} className="mb-2 flex flex-wrap gap-2">
                <TextInput
                    sizing="sm"
                    className="min-w-[180px] flex-1"
                    value={filters.search}
                    onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                    placeholder={t('global.search_batch_medicine')}
                />
                <div className="min-w-[180px]">
                    <SearchableSelect
                        value={filters.type}
                        onChange={(v) => setFilters({ ...filters, type: v })}
                        options={[
                            { value: '', label: t('global.all_types') },
                            { value: 'purchase_receive', label: t('global.purchase_receive') },
                            { value: 'depot_to_pharmacy', label: t('global.depot.depot_to_pharmacy') },
                            { value: 'depot_to_depot', label: t('global.depot.depot_to_depot') },
                            { value: 'sale', label: t('global.movement_sale') },
                            { value: 'adjustment', label: t('global.movement_adjustment') },
                        ]}
                    />
                </div>
                <Button type="submit" size="xs" color="light" className="h-9">
                    <i className="bx bx-filter-alt me-1" />
                    {t('global.filter')}
                </Button>
            </form>

            <Table>
                <TableHead>
                    <TableRow>
                        <TableHeader>{t('global.when')}</TableHeader>
                        <TableHeader>{t('global.type')}</TableHeader>
                        <TableHeader>{t('global.medicine')}</TableHeader>
                        <TableHeader>{t('global.batch_number')}</TableHeader>
                        <TableHeader>{t('global.from')}</TableHeader>
                        <TableHeader>{t('global.to')}</TableHeader>
                        <TableHeader>{t('global.quantity')}</TableHeader>
                        <TableHeader>{t('global.user')}</TableHeader>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {movements.data.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={8} className="py-6 text-center text-sm text-gray-500">
                                <i className="bx bx-transfer-alt text-2xl text-gray-300" />
                                <div>{t('global.no_movements_yet')}</div>
                            </TableCell>
                        </TableRow>
                    ) : (
                        movements.data.map((row) => (
                            <TableRow key={row.id}>
                                <TableCell className="py-1.5 text-xs text-gray-500">{row.created_at}</TableCell>
                                <TableCell className="py-1.5">
                                    <Badge color="info" size="xs">
                                        <i className={`bx ${TYPE_ICON[row.type] ?? 'bx-radio-circle'} me-0.5`} />
                                        {inventoryMovementTypeLabel(t, row.type)}
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
