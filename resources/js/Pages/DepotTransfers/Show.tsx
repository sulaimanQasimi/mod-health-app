import { router } from '@inertiajs/react';
import { Badge, Button } from 'flowbite-react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { inventoryStatusLabel } from '../../utils/inventoryLabels';

export default function ShowDepotTransfer({
    transfer,
    inventoryNav,
    urls,
}: {
    transfer: {
        id: number;
        request_number: string;
        status: string;
        source_depot?: { id: number; name: string } | null;
        pharmacy?: { id: number; name: string } | null;
        destination_depot?: { id: number; name: string } | null;
        items: Array<{
            id: number;
            batch_number: string | null;
            packs: number;
            units: number;
            qty_units: number;
            medicine?: { id: number; name: string } | null;
        }>;
    };
    inventoryNav: InventoryNavUrls;
    urls: {
        index: string;
        submit: string;
        approve: string;
        reject: string;
        fulfill: string;
        cancel: string;
    };
}) {
    const { t } = useTranslation();
    const status = transfer.status;

    return (
        <InventoryShell
            title={transfer.request_number}
            active="transfers"
            urls={inventoryNav}
            action={
                <div className="flex flex-wrap items-center gap-1.5">
                    <Badge color="purple" size="xs">
                        {inventoryStatusLabel(t, status)}
                    </Badge>
                    {status === 'draft' && (
                        <Button size="xs" color="purple" onClick={() => router.post(urls.submit)}>
                            <i className="bx bx-send me-1" />
                            {t('global.submit')}
                        </Button>
                    )}
                    {status === 'pending' && (
                        <>
                            <Button size="xs" color="success" onClick={() => router.post(urls.approve)}>
                                <i className="bx bx-check me-1" />
                                {t('global.approve')}
                            </Button>
                            <Button size="xs" color="failure" outline onClick={() => router.post(urls.reject)}>
                                <i className="bx bx-x me-1" />
                                {t('global.reject')}
                            </Button>
                        </>
                    )}
                    {(status === 'approved' || status === 'pending') && (
                        <Button
                            size="xs"
                            color="success"
                            onClick={() => {
                                if (confirm(t('global.fulfill_transfer_confirm'))) router.post(urls.fulfill);
                            }}
                        >
                            <i className="bx bx-transfer me-1" />
                            {t('global.fulfill')}
                        </Button>
                    )}
                    {['draft', 'pending', 'approved'].includes(status) && (
                        <Button size="xs" color="light" onClick={() => router.post(urls.cancel)}>
                            {t('global.cancel')}
                        </Button>
                    )}
                </div>
            }
        >
            <div className="mb-2 text-xs text-gray-600 dark:text-gray-300">
                <i className="bx bx-right-arrow-alt me-1 text-violet-600" />
                {transfer.source_depot?.name ?? t('global.depot.name')} →{' '}
                {transfer.pharmacy?.name ?? transfer.destination_depot?.name ?? t('global.destination')}
            </div>

            <Table>
                <TableHead>
                    <TableRow>
                        <TableHeader>{t('global.medicine')}</TableHeader>
                        <TableHeader>{t('global.batch_number')}</TableHeader>
                        <TableHeader>{t('global.packs')}</TableHeader>
                        <TableHeader>{t('global.units')}</TableHeader>
                        <TableHeader>{t('global.total')}</TableHeader>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {transfer.items.map((item) => (
                        <TableRow key={item.id}>
                            <TableCell className="py-1.5 text-sm">{item.medicine?.name}</TableCell>
                            <TableCell className="py-1.5 font-mono text-sm">{item.batch_number ?? t('global.fefo')}</TableCell>
                            <TableCell className="py-1.5 text-sm">{item.packs}</TableCell>
                            <TableCell className="py-1.5 text-sm">{item.units}</TableCell>
                            <TableCell className="py-1.5 text-sm font-semibold">{item.qty_units}</TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </InventoryShell>
    );
}
