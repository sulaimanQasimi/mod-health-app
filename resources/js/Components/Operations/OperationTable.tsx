import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { OperationListItem, OperationListVariant } from '../../types/operation';
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
import { operationApprovalLabel, operationReservedLabel } from './operationUi';

interface OperationTableProps {
    items: OperationListItem[];
    variant: OperationListVariant;
    embedded?: boolean;
}

export default function OperationTable({ items, variant, embedded = true }: OperationTableProps) {
    const { t } = useTranslation();
    const showNurses = variant === 'approved' || variant === 'completed';
    const showReserveReason = variant === 'reserved';
    const columnCount = showNurses ? 10 : showReserveReason ? 9 : 8;

    return (
        <Table embedded={embedded}>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader className="w-16">{t('global.number')}</TableHeader>
                    <TableHeader>{t('global.card_number')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.father_name')}</TableHeader>
                    <TableHeader>{t('global.operation_type')}</TableHeader>
                    {showNurses && (
                        <>
                            <TableHeader>{t('global.scrub_nurse')}</TableHeader>
                            <TableHeader>{t('global.circulation_nurse')}</TableHeader>
                        </>
                    )}
                    <TableHeader>{t('global.date')}</TableHeader>
                    <TableHeader>{t('global.status')}</TableHeader>
                    {showReserveReason && <TableHeader>{t('global.reserve_reason')}</TableHeader>}
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
                                <Badge color="warning" className="w-fit font-normal">
                                    {item.operation_type_name}
                                </Badge>
                            ) : (
                                '—'
                            )}
                        </TableCell>
                        {showNurses && (
                            <>
                                <TableCell muted>{item.scrub_nurse_name ?? '—'}</TableCell>
                                <TableCell muted>{item.circulation_nurse_name ?? '—'}</TableCell>
                            </>
                        )}
                        <TableCell muted dir="ltr">
                            {[item.date, item.time].filter(Boolean).join(' ') || '—'}
                        </TableCell>
                        <TableCell>
                            {variant === 'reserved' ? (
                                <Badge color={item.is_reserved ? 'warning' : 'success'} className="w-fit font-normal">
                                    {operationReservedLabel(item.is_reserved, t)}
                                </Badge>
                            ) : variant === 'completed' ? (
                                <Badge color="info" className="w-fit font-normal">
                                    {t('global.completed')}
                                </Badge>
                            ) : (
                                <Badge
                                    color={item.is_operation_approved ? 'success' : 'failure'}
                                    className="w-fit font-normal"
                                >
                                    {operationApprovalLabel(item.is_operation_approved, t)}
                                </Badge>
                            )}
                        </TableCell>
                        {showReserveReason && (
                            <TableCell muted className="max-w-xs truncate">
                                {item.reserve_reason ?? '—'}
                            </TableCell>
                        )}
                        <TableCell align="right">
                            <TableActionButton kind="view" href={item.urls.show} title={t('global.view')} />
                        </TableCell>
                    </TableRow>
                ))}
                {items.length === 0 && (
                    <TableEmpty
                        colSpan={columnCount}
                        icon="bx-cut"
                        title={t('global.no_records_found')}
                        description={t('global.try_adjusting_your_search_criteria')}
                    />
                )}
            </TableBody>
        </Table>
    );
}
