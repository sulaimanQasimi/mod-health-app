import { Badge, Button } from 'flowbite-react';
import { Fragment } from 'react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { PrescriptionShowItem } from '../../types/prescription';
import TableActionButton from '../ui/TableActionButton';

interface PrescriptionItemsTableProps {
    items: PrescriptionShowItem[];
    readonly: boolean;
    processing: boolean;
    onToggleItemStatus: (item: PrescriptionShowItem) => void;
    onToggleAlternativeStatus: (alternativeId: number, isDelivered: boolean) => void;
    onEditAmount: (item: PrescriptionShowItem) => void;
    onOpenAlternatives: (item: PrescriptionShowItem) => void;
}

function DeliveryStatusButton({
    isDelivered,
    disabled,
    onClick,
    label,
}: {
    isDelivered: boolean;
    disabled: boolean;
    onClick: () => void;
    label: string;
}) {
    return (
        <button
            type="button"
            disabled={disabled}
            onClick={onClick}
            className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-50 ${
                isDelivered
                    ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-200 dark:hover:bg-emerald-900/60'
                    : 'bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:hover:bg-amber-900/60'
            }`}
        >
            <i className={`bx ${isDelivered ? 'bx-check-circle' : 'bx-time-five'} text-sm`} />
            {label}
        </button>
    );
}

export default function PrescriptionItemsTable({
    items,
    readonly,
    processing,
    onToggleItemStatus,
    onToggleAlternativeStatus,
    onEditAmount,
    onOpenAlternatives,
}: PrescriptionItemsTableProps) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return (
            <div className="rounded-xl border border-dashed border-gray-300 bg-gray-50/50 px-6 py-12 text-center dark:border-gray-600 dark:bg-gray-800/30">
                <i className="bx bx-capsule mb-3 text-4xl text-gray-400" />
                <p className="text-sm text-gray-500">{t('global.no_item_is_found')}</p>
            </div>
        );
    }

    return (
        <Table>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader className="w-12">#</TableHeader>
                    <TableHeader>{t('global.type')}</TableHeader>
                    <TableHeader>{t('global.name')}</TableHeader>
                    <TableHeader>{t('global.usage_type')}</TableHeader>
                    <TableHeader>{t('global.dosage')}</TableHeader>
                    <TableHeader>{t('global.frequency')}</TableHeader>
                    <TableHeader>{t('global.amount')}</TableHeader>
                    <TableHeader>{t('global.status')}</TableHeader>
                    <TableHeader align="center">{t('global.alternatives')}</TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((item, index) => (
                    <Fragment key={item.id}>
                        <TableRow>
                            <TableCell>
                                <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                    {index + 1}
                                </span>
                            </TableCell>
                            <TableCell>
                                {item.medicine_type ? (
                                    <Badge color="gray" className="w-fit">
                                        {item.medicine_type}
                                    </Badge>
                                ) : (
                                    '—'
                                )}
                            </TableCell>
                            <TableCell>
                                <div className="font-semibold text-gray-900 dark:text-white">
                                    {item.medicine_name}
                                </div>
                                {item.selected_alternative && (
                                    <Badge color="warning" className="mt-1.5 w-fit">
                                        {t('global.original_not_used') || t('global.original')}
                                    </Badge>
                                )}
                            </TableCell>
                            <TableCell muted>{item.usage_type_name ?? '—'}</TableCell>
                            <TableCell>
                                <span className="font-medium">{item.dosage}</span>
                            </TableCell>
                            <TableCell>
                                <span className="font-medium">{item.frequency}</span>
                            </TableCell>
                            <TableCell>
                                <div className="flex items-center gap-2">
                                    <span className="inline-flex min-w-[2rem] rounded-md bg-gray-100 px-2 py-1 text-center text-sm font-semibold dark:bg-gray-700">
                                        {item.amount}
                                    </span>
                                    {!readonly && (
                                        <TableActionButton
                                            kind="custom"
                                            icon="bx-edit"
                                            title={t('global.edit_amount')}
                                            onClick={() => onEditAmount(item)}
                                        />
                                    )}
                                </div>
                            </TableCell>
                            <TableCell>
                                {item.selected_alternative ? (
                                    <Badge color="gray">{t('global.not_used')}</Badge>
                                ) : (
                                    <DeliveryStatusButton
                                        isDelivered={item.is_delivered}
                                        disabled={readonly || processing}
                                        onClick={() => onToggleItemStatus(item)}
                                        label={
                                            item.is_delivered ? t('global.delivered') : t('global.pending')
                                        }
                                    />
                                )}
                            </TableCell>
                            <TableCell align="center">
                                <Button
                                    size="xs"
                                    color="light"
                                    onClick={() => onOpenAlternatives(item)}
                                    className="inline-flex items-center gap-1.5"
                                >
                                    <i className="bx bx-list-ul" />
                                    {item.alternatives_count > 0 && (
                                        <Badge color="info" className="ms-0.5">
                                            {item.alternatives_count}
                                        </Badge>
                                    )}
                                </Button>
                            </TableCell>
                        </TableRow>

                        {item.selected_alternative && (
                            <TableRow className="bg-emerald-50/60 dark:bg-emerald-950/20">
                                <TableCell>
                                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-200 text-xs font-bold text-emerald-900 dark:bg-emerald-800 dark:text-emerald-100">
                                        {index + 1}.1
                                    </span>
                                </TableCell>
                                <TableCell>
                                    {item.selected_alternative.medicine_type ? (
                                        <Badge color="success" className="w-fit">
                                            {item.selected_alternative.medicine_type}
                                        </Badge>
                                    ) : (
                                        '—'
                                    )}
                                </TableCell>
                                <TableCell>
                                    <div className="font-semibold text-gray-900 dark:text-white">
                                        {item.selected_alternative.medicine_name}
                                    </div>
                                    <Badge color="success" className="mt-1.5 w-fit">
                                        {t('global.selected_alternative')}
                                    </Badge>
                                </TableCell>
                                <TableCell muted>
                                    {item.selected_alternative.usage_type_name ?? '—'}
                                </TableCell>
                                <TableCell>
                                    <span className="font-medium">{item.selected_alternative.dosage}</span>
                                </TableCell>
                                <TableCell>
                                    <span className="font-medium">{item.selected_alternative.frequency}</span>
                                </TableCell>
                                <TableCell>
                                    <span className="inline-flex min-w-[2rem] rounded-md bg-emerald-100 px-2 py-1 text-center text-sm font-semibold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                                        {item.selected_alternative.amount}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <DeliveryStatusButton
                                        isDelivered={item.selected_alternative.is_delivered}
                                        disabled={readonly || processing}
                                        onClick={() =>
                                            onToggleAlternativeStatus(
                                                item.selected_alternative!.id,
                                                item.selected_alternative!.is_delivered,
                                            )
                                        }
                                        label={
                                            item.selected_alternative.is_delivered
                                                ? t('global.delivered')
                                                : t('global.pending')
                                        }
                                    />
                                </TableCell>
                                <TableCell align="center">
                                    <Button
                                        size="xs"
                                        color="light"
                                        onClick={() => onOpenAlternatives(item)}
                                    >
                                        <i className="bx bx-list-ul" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        )}
                    </Fragment>
                ))}
            </TableBody>
        </Table>
    );
}
