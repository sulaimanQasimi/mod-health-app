import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import TableActionButton from '../ui/TableActionButton';
import { TableActions, TableActionsCell } from '../ui/TableActions';
import SettingsEmptyState from '../Settings/SettingsEmptyState';
import { useTranslation } from '../../hooks/useTranslation';
import { ProstheticCatalogItem } from '../../types/prosthetics';

interface ProstheticCatalogTableProps {
    items: ProstheticCatalogItem[];
    editUrlBase: string;
    canManage: boolean;
}

export default function ProstheticCatalogTable({ items, editUrlBase, canManage }: ProstheticCatalogTableProps) {
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
                    <TableHeader>{t('global.category')}</TableHeader>
                    <TableHeader>{t('global.cost')}</TableHeader>
                    {canManage && <TableHeader align="center">{t('global.actions')}</TableHeader>}
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((item) => (
                    <TableRow key={item.id}>
                        <TableCell>
                            <code className="text-sm">{item.item_code}</code>
                        </TableCell>
                        <TableCell>{item.name}</TableCell>
                        <TableCell muted>{item.category ?? '—'}</TableCell>
                        <TableCell muted dir="ltr">
                            {item.standard_cost ?? '—'}
                        </TableCell>
                        {canManage && (
                            <TableActionsCell>
                                <TableActions>
                                    <TableActionButton
                                        kind="edit"
                                        href={`${editUrlBase}/${item.id}/edit`}
                                    />
                                </TableActions>
                            </TableActionsCell>
                        )}
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
