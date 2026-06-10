import { Badge } from 'flowbite-react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import TableActionButton from '../ui/TableActionButton';
import { useTranslation } from '../../hooks/useTranslation';
import { IcuListItem, IcuListVariant } from '../../types/icu';

interface IcuTableProps {
    items: IcuListItem[];
    variant: IcuListVariant;
    embedded?: boolean;
}

function truncate(text: string | null, max = 40): string {
    if (!text) return '—';
    return text.length > max ? `${text.slice(0, max)}…` : text;
}

function StatusCell({ item, variant }: { item: IcuListItem; variant: IcuListVariant }) {
    const { t } = useTranslation();

    if (variant === 'approved') {
        if (item.is_discharged) {
            if (item.discharge_status === 'recovered') {
                return <Badge color="success">{t('global.recovered')}</Badge>;
            }
            if (item.discharge_status === 'died') {
                return <Badge color="failure">{t('global.died')}</Badge>;
            }
            if (item.discharge_status === 'moved') {
                return <Badge color="warning">{t('global.moved')}</Badge>;
            }
            return <Badge color="gray">{t('global.discharged')}</Badge>;
        }
        return (
            <Badge color="success" className="inline-flex items-center gap-1">
                <i className="bx bx-pulse" />
                {t('global.in_icu')}
            </Badge>
        );
    }

    if (variant === 'new') {
        return (
            <Badge color="info" className="inline-flex items-center gap-1">
                <i className="bx bx-time-five" />
                {t('global.new_icus')}
            </Badge>
        );
    }

    return (
        <Badge color="failure" className="inline-flex items-center gap-1">
            <i className="bx bx-x-circle" />
            {t('global.rejected_icus')}
        </Badge>
    );
}

function EmptyState({ message }: { message: string }) {
    return (
        <div className="flex flex-col items-center justify-center py-14 text-center">
            <div className="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                <i className="bx bx-search-alt text-3xl text-gray-400" />
            </div>
            <p className="text-sm text-gray-500 dark:text-gray-400">{message}</p>
        </div>
    );
}

export default function IcuTable({ items, variant, embedded = false }: IcuTableProps) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return <EmptyState message={t('global.try_adjusting_your_search_criteria')} />;
    }

    const table = (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead className="w-14">{t('global.number')}</TableHead>
                    <TableHead>{t('global.card_number')}</TableHead>
                    <TableHead>{t('global.patient_name')}</TableHead>
                    <TableHead>{t('global.father_name')}</TableHead>
                    <TableHead>{t('global.room')}</TableHead>
                    <TableHead>{t('global.bed')}</TableHead>
                    <TableHead>{t('global.description')}</TableHead>
                    <TableHead>{t('global.status')}</TableHead>
                    <TableHead className="w-16 text-end">{t('global.actions')}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {items.map((item) => (
                    <TableRow
                        key={item.id}
                        className="transition-colors hover:bg-rose-50/50 dark:hover:bg-rose-950/10"
                    >
                        <TableCell className="font-medium text-gray-500">
                            {item.row_number ?? item.id}
                        </TableCell>
                        <TableCell>
                            {item.patient_id_card ? (
                                <Badge color="gray" className="font-mono">
                                    {item.patient_id_card}
                                </Badge>
                            ) : (
                                '—'
                            )}
                        </TableCell>
                        <TableCell className="font-medium text-gray-900 dark:text-white">
                            {item.patient_name ?? '—'}
                        </TableCell>
                        <TableCell className="text-gray-600 dark:text-gray-400">
                            {item.father_name ?? '—'}
                        </TableCell>
                        <TableCell className="text-gray-600 dark:text-gray-400">
                            {item.room_name ? (
                                <span className="inline-flex items-center gap-1">
                                    <i className="bx bx-building-house text-rose-400" />
                                    {item.room_name}
                                </span>
                            ) : (
                                '—'
                            )}
                        </TableCell>
                        <TableCell className="text-gray-600 dark:text-gray-400">
                            {item.bed_number ?? '—'}
                        </TableCell>
                        <TableCell className="max-w-xs text-gray-600 dark:text-gray-400" title={item.description ?? ''}>
                            {truncate(item.description)}
                        </TableCell>
                        <TableCell>
                            <StatusCell item={item} variant={variant} />
                        </TableCell>
                        <TableCell className="text-end">
                            <TableActionButton kind="view" href={item.urls.show} title={t('global.view')} />
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );

    if (embedded) {
        return <div className="overflow-x-auto">{table}</div>;
    }

    return (
        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            {table}
        </div>
    );
}
