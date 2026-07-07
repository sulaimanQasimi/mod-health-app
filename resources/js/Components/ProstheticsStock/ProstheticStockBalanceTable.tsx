import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import SettingsEmptyState from '../Settings/SettingsEmptyState';
import { useTranslation } from '../../hooks/useTranslation';

interface StockBalance {
    id: number;
    quantity: number;
    catalog_item?: { item_code: string; name: string };
}

interface ProstheticStockBalanceTableProps {
    items: StockBalance[];
}

export default function ProstheticStockBalanceTable({ items }: ProstheticStockBalanceTableProps) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return <SettingsEmptyState message={t('global.no_records_found')} />;
    }

    return (
        <Table>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader>{t('global.code')}</TableHeader>
                    <TableHeader>{t('global.name')}</TableHeader>
                    <TableHeader>{t('global.quantity')}</TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((balance) => (
                    <TableRow key={balance.id}>
                        <TableCell>
                            <code className="text-sm">{balance.catalog_item?.item_code ?? '—'}</code>
                        </TableCell>
                        <TableCell>{balance.catalog_item?.name ?? '—'}</TableCell>
                        <TableCell muted dir="ltr">
                            {balance.quantity}
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
