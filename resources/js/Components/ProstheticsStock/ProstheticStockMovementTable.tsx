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

interface StockMovement {
    id: number;
    movement_type: string;
    quantity_delta: number;
    created_at: string | null;
    catalog_item?: { item_code: string; name: string };
}

interface ProstheticStockMovementTableProps {
    items: StockMovement[];
}

export default function ProstheticStockMovementTable({ items }: ProstheticStockMovementTableProps) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return <SettingsEmptyState message={t('global.no_records_found')} />;
    }

    return (
        <Table>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader>{t('global.date')}</TableHeader>
                    <TableHeader>{t('global.type')}</TableHeader>
                    <TableHeader>{t('global.prosthetics_component')}</TableHeader>
                    <TableHeader>Δ</TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((movement) => (
                    <TableRow key={movement.id}>
                        <TableCell muted dir="ltr">
                            {movement.created_at ?? '—'}
                        </TableCell>
                        <TableCell>{movement.movement_type}</TableCell>
                        <TableCell>
                            {movement.catalog_item
                                ? `${movement.catalog_item.item_code} — ${movement.catalog_item.name}`
                                : '—'}
                        </TableCell>
                        <TableCell muted dir="ltr">
                            {movement.quantity_delta}
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
