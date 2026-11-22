import { Link, router } from '@inertiajs/react';
import { Badge, Button, TextInput } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';

type StockRow = {
    id: number;
    batch_number: string;
    medicine?: { id: number; name: string } | null;
    location_type: string;
    location_id: number;
    location_name: string;
    qty_on_hand: number;
    qty_sold: number;
    packs_on_hand: number;
    units_on_hand: number;
    expiry_date: string | null;
    unit_sale_price: number;
    pack_price: number;
};

export default function StocksIndex({
    stocks,
    filters: serverFilters,
    filterOptions,
    inventoryNav,
    urls,
}: {
    stocks: StockRow[];
    filters: { location_type: string; location_id: string; search: string; batch_number: string };
    filterOptions: {
        depots: Array<{ id: number; name: string }>;
        pharmacies: Array<{ id: number; name: string }>;
    };
    inventoryNav: InventoryNavUrls;
    urls: { index: string; batch: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);

    const locationOptions = useMemo(() => {
        if (filters.location_type === 'depot') {
            return filterOptions.depots.map((d) => ({ value: String(d.id), label: d.name }));
        }
        if (filters.location_type === 'pharmacy') {
            return filterOptions.pharmacies.map((p) => ({ value: String(p.id), label: p.name }));
        }
        return [];
    }, [filters.location_type, filterOptions]);

    const apply = (e?: FormEvent) => {
        e?.preventDefault();
        router.get(urls.index, Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')), {
            preserveScroll: true,
        });
    };

    return (
        <InventoryShell title={t('global.stock')} active="stocks" urls={inventoryNav}>
            <form onSubmit={apply} className="mb-2 grid grid-cols-2 gap-2 md:grid-cols-5">
                <SearchableSelect
                    value={filters.location_type}
                    onChange={(v) => setFilters({ ...filters, location_type: v, location_id: '' })}
                    options={[
                        { value: '', label: t('global.all_locations') },
                        { value: 'depot', label: t('global.depot.name') },
                        { value: 'pharmacy', label: t('global.pharmacy') },
                    ]}
                />
                <SearchableSelect
                    value={filters.location_id}
                    onChange={(v) => setFilters({ ...filters, location_id: v })}
                    options={[{ value: '', label: t('global.all') }, ...locationOptions]}
                    disabled={!filters.location_type}
                />
                <TextInput
                    sizing="sm"
                    value={filters.search}
                    onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                    placeholder={t('global.medicine')}
                />
                <TextInput
                    sizing="sm"
                    value={filters.batch_number}
                    onChange={(e) => setFilters({ ...filters, batch_number: e.target.value })}
                    placeholder={t('global.batch_number')}
                />
                <Button type="submit" size="xs" color="light" className="h-9">
                    <i className="bx bx-filter-alt me-1" />
                    {t('global.filter')}
                </Button>
            </form>

            {filters.batch_number ? (
                <div className="mb-2 flex items-center gap-1.5 rounded-md bg-sky-50 px-2 py-1 text-xs text-sky-800 dark:bg-sky-900/30 dark:text-sky-100">
                    <i className="bx bx-info-circle" />
                    {t('global.batch_number')} <strong>{filters.batch_number}</strong> {t('global.batch_across_locations')}
                </div>
            ) : null}

            <Table>
                <TableHead>
                    <TableRow>
                        <TableHeader>{t('global.medicine')}</TableHeader>
                        <TableHeader>{t('global.batch_number')}</TableHeader>
                        <TableHeader>{t('global.location')}</TableHeader>
                        <TableHeader>{t('global.on_hand')}</TableHeader>
                        <TableHeader>{t('global.qty_sold')}</TableHeader>
                        <TableHeader>{t('global.expiry_date')}</TableHeader>
                        <TableHeader>{t('global.unit_sale')}</TableHeader>
                        <TableHeader>{t('global.pack_price')}</TableHeader>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {stocks.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={8} className="py-6 text-center text-sm text-gray-500">
                                <i className="bx bx-package text-2xl text-gray-300" />
                                <div>{t('global.no_stock_yet')}</div>
                            </TableCell>
                        </TableRow>
                    ) : (
                        stocks.map((row) => (
                            <TableRow key={row.id}>
                                <TableCell className="py-1.5 text-sm">{row.medicine?.name}</TableCell>
                                <TableCell className="py-1.5">
                                    <Link
                                        href={urls.batch.replace('__BATCH__', encodeURIComponent(row.batch_number))}
                                        className="font-mono text-sm text-sky-700 hover:underline"
                                    >
                                        {row.batch_number}
                                    </Link>
                                </TableCell>
                                <TableCell className="py-1.5">
                                    <Badge color={row.location_type === 'pharmacy' ? 'purple' : 'info'} size="xs">
                                        <i
                                            className={`bx ${row.location_type === 'pharmacy' ? 'bx-plus-medical' : 'bx-store'} me-0.5`}
                                        />
                                        {row.location_name}
                                    </Badge>
                                </TableCell>
                                <TableCell className="py-1.5 text-sm">
                                    <span className="font-semibold">{row.qty_on_hand}</span>
                                    <span className="ms-1 text-xs text-gray-500">
                                        ({row.packs_on_hand}p + {row.units_on_hand}u)
                                    </span>
                                </TableCell>
                                <TableCell className="py-1.5 text-sm">{row.qty_sold}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.expiry_date ?? '—'}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.unit_sale_price}</TableCell>
                                <TableCell className="py-1.5 text-sm">{row.pack_price}</TableCell>
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </InventoryShell>
    );
}
