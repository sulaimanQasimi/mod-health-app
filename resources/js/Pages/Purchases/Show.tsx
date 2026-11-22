import { router } from '@inertiajs/react';
import { Badge, Button } from 'flowbite-react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { inventoryStatusLabel } from '../../utils/inventoryLabels';

export default function ShowPurchase({
    purchase,
    inventoryNav,
    urls,
}: {
    purchase: {
        id: number;
        purchase_number: string;
        supplier: string | null;
        purchase_date: string;
        status: string;
        notes: string | null;
        depot?: { id: number; name: string } | null;
        items: Array<{
            id: number;
            batch_number: string;
            expiry_date: string | null;
            packs: number;
            units: number;
            qty_units: number;
            purchase_price: number;
            unit_sale_price: number;
            pack_price: number;
            medicine?: { id: number; name: string } | null;
        }>;
    };
    inventoryNav: InventoryNavUrls;
    urls: { index: string; receive: string; cancel: string };
}) {
    const { t } = useTranslation();
    const isDraft = purchase.status === 'draft';

    return (
        <InventoryShell
            title={purchase.purchase_number}
            active="purchases"
            urls={inventoryNav}
            action={
                <div className="flex flex-wrap items-center gap-1.5">
                    <Badge color={purchase.status === 'received' ? 'success' : 'warning'} size="xs">
                        {inventoryStatusLabel(t, purchase.status)}
                    </Badge>
                    {isDraft && (
                        <>
                            <Button
                                size="xs"
                                color="success"
                                onClick={() => {
                                    if (confirm(t('global.receive_into_depot_confirm'))) router.post(urls.receive);
                                }}
                            >
                                <i className="bx bx-log-in-circle me-1" />
                                {t('global.receive')}
                            </Button>
                            <Button size="xs" color="failure" outline onClick={() => router.post(urls.cancel)}>
                                <i className="bx bx-x me-1" />
                                {t('global.cancel')}
                            </Button>
                        </>
                    )}
                </div>
            }
        >
            <div className="mb-2 grid gap-1 text-xs text-gray-600 dark:text-gray-300 sm:grid-cols-3">
                <div>
                    <i className="bx bx-store me-1 text-teal-600" />
                    {purchase.depot?.name ?? '—'}
                </div>
                <div>
                    <i className="bx bx-buildings me-1 text-teal-600" />
                    {purchase.supplier ?? '—'}
                </div>
                <div>
                    <i className="bx bx-calendar me-1 text-teal-600" />
                    {String(purchase.purchase_date).slice(0, 10)}
                </div>
            </div>

            <Table>
                <TableHead>
                    <TableRow>
                        <TableHeader>{t('global.medicine')}</TableHeader>
                        <TableHeader>{t('global.batch_number')}</TableHeader>
                        <TableHeader>{t('global.expiry_date')}</TableHeader>
                        <TableHeader>{t('global.packs')}</TableHeader>
                        <TableHeader>{t('global.units')}</TableHeader>
                        <TableHeader>{t('global.total')}</TableHeader>
                        <TableHeader>{t('global.cost')}</TableHeader>
                        <TableHeader>{t('global.unit_sale')}</TableHeader>
                        <TableHeader>{t('global.pack_price')}</TableHeader>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {purchase.items.map((item) => (
                        <TableRow key={item.id}>
                            <TableCell className="py-1.5 text-sm">{item.medicine?.name}</TableCell>
                            <TableCell className="py-1.5 font-mono text-sm">{item.batch_number}</TableCell>
                            <TableCell className="py-1.5 text-sm">
                                {item.expiry_date ? String(item.expiry_date).slice(0, 10) : '—'}
                            </TableCell>
                            <TableCell className="py-1.5 text-sm">{item.packs}</TableCell>
                            <TableCell className="py-1.5 text-sm">{item.units}</TableCell>
                            <TableCell className="py-1.5 text-sm font-semibold">{item.qty_units}</TableCell>
                            <TableCell className="py-1.5 text-sm">{item.purchase_price}</TableCell>
                            <TableCell className="py-1.5 text-sm">{item.unit_sale_price}</TableCell>
                            <TableCell className="py-1.5 text-sm">{item.pack_price}</TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </InventoryShell>
    );
}
