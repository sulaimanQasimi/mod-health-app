import { Badge } from 'flowbite-react';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
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

const COLUMN_COUNT = 9;

function truncate(text: string | null, max = 48): string {
    if (!text) return '—';
    return text.length > max ? `${text.slice(0, max)}…` : text;
}

function StatusCell({ item, variant }: { item: IcuListItem; variant: IcuListVariant }) {
    const { t } = useTranslation();

    if (variant === 'approved') {
        if (item.is_discharged) {
            if (item.discharge_status === 'recovered') {
                return (
                    <Badge color="success" className="w-fit font-normal">
                        {t('global.recovered')}
                    </Badge>
                );
            }
            if (item.discharge_status === 'died') {
                return (
                    <Badge color="failure" className="w-fit font-normal">
                        {t('global.died')}
                    </Badge>
                );
            }
            if (item.discharge_status === 'moved') {
                return (
                    <Badge color="warning" className="w-fit font-normal">
                        {t('global.moved')}
                    </Badge>
                );
            }
            return (
                <Badge color="gray" className="w-fit font-normal">
                    {t('global.discharged')}
                </Badge>
            );
        }
        return (
            <Badge color="success" className="w-fit font-normal">
                <span className="inline-flex items-center gap-1">
                    <i className="bx bx-pulse" />
                    {t('global.in_icu')}
                </span>
            </Badge>
        );
    }

    if (variant === 'new') {
        return (
            <Badge color="info" className="w-fit font-normal">
                <span className="inline-flex items-center gap-1">
                    <i className="bx bx-time-five" />
                    {t('global.new_icus')}
                </span>
            </Badge>
        );
    }

    return (
        <Badge color="failure" className="w-fit font-normal">
            <span className="inline-flex items-center gap-1">
                <i className="bx bx-x-circle" />
                {t('global.rejected_icus')}
            </span>
        </Badge>
    );
}

export default function IcuTable({ items, variant, embedded = true }: IcuTableProps) {
    const { t } = useTranslation();

    return (
        <Table embedded={embedded}>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader className="w-16">{t('global.number')}</TableHeader>
                    <TableHeader>{t('global.card_number')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.father_name')}</TableHeader>
                    <TableHeader>{t('global.room')}</TableHeader>
                    <TableHeader>{t('global.bed')}</TableHeader>
                    <TableHeader className="min-w-[10rem]">{t('global.description')}</TableHeader>
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
                            {item.room_name ? (
                                <span className="inline-flex items-center gap-1.5">
                                    <i className="bx bx-building-house text-rose-500" />
                                    {item.room_name}
                                </span>
                            ) : (
                                '—'
                            )}
                        </TableCell>
                        <TableCell muted>{item.bed_number ?? '—'}</TableCell>
                        <TableCell muted className="max-w-xs" title={item.description ?? undefined}>
                            {truncate(item.description)}
                        </TableCell>
                        <TableCell>
                            <StatusCell item={item} variant={variant} />
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
