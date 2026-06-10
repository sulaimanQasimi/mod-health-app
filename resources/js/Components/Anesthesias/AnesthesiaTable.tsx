import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { AnesthesiaListItem, AnesthesiaListVariant } from '../../types/anesthesia';
import TableActionButton from '../ui/TableActionButton';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import {
    anesthesiaStatusBadgeColor,
    anesthesiaStatusLabel,
    anesthesiaTypeLabel,
} from './anesthesiaUi';

interface AnesthesiaTableProps {
    items: AnesthesiaListItem[];
    variant: AnesthesiaListVariant;
    embedded?: boolean;
}

const COLUMN_COUNT = 9;

export default function AnesthesiaTable({ items, variant, embedded = true }: AnesthesiaTableProps) {
    const { t } = useTranslation();

    return (
        <Table embedded={embedded}>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader className="w-16">{t('global.number')}</TableHeader>
                    <TableHeader>{t('global.card_number')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.father_name')}</TableHeader>
                    <TableHeader>{t('global.operation_type')}</TableHeader>
                    <TableHeader>{t('global.operation_surgion')}</TableHeader>
                    <TableHeader>{t('global.date')}</TableHeader>
                    <TableHeader>{t('global.status')}</TableHeader>
                    <TableHeader align="right" className="w-16">
                        {t('global.actions')}
                    </TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((item) => (
                    <TableRow key={item.id}>
                        <TableCell className="font-medium text-gray-500 dark:text-gray-400">
                            {item.row_number ?? item.id}
                        </TableCell>
                        <TableCell>
                            {item.patient_id_card ? (
                                <Badge color="gray" className="w-fit font-mono font-normal">
                                    {item.patient_id_card}
                                </Badge>
                            ) : (
                                '—'
                            )}
                        </TableCell>
                        <TableCell className="font-medium text-gray-900 dark:text-white">
                            {item.patient_name ?? '—'}
                        </TableCell>
                        <TableCell muted>{item.father_name ?? '—'}</TableCell>
                        <TableCell muted>
                            {item.operation_type_name ? (
                                <Badge color="success" className="w-fit font-normal">
                                    {item.operation_type_name}
                                </Badge>
                            ) : (
                                '—'
                            )}
                        </TableCell>
                        <TableCell muted>{item.surgion_name ?? '—'}</TableCell>
                        <TableCell muted dir="ltr">
                            {[item.date, item.time].filter(Boolean).join(' ') || '—'}
                        </TableCell>
                        <TableCell>
                            <Badge color={anesthesiaStatusBadgeColor(item.status)} className="w-fit font-normal">
                                {anesthesiaStatusLabel(item.status, t)}
                            </Badge>
                            {item.anesthesia_type && variant === 'approved' && (
                                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {anesthesiaTypeLabel(item.anesthesia_type, t)}
                                </p>
                            )}
                        </TableCell>
                        <TableCell align="right">
                            <TableActionButton kind="view" href={item.urls.show} title={t('global.view')} />
                        </TableCell>
                    </TableRow>
                ))}
                {items.length === 0 && (
                    <TableEmpty
                        colSpan={COLUMN_COUNT}
                        icon="bx-plus-medical"
                        title={t('global.no_records_found')}
                        description={t('global.try_adjusting_your_search_criteria')}
                    />
                )}
            </TableBody>
        </Table>
    );
}
