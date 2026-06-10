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
        return <Badge color="success">{t('global.in_icu')}</Badge>;
    }

    if (variant === 'new') {
        return <i className="bx bx-x-circle text-lg text-red-500" title={t('global.new_icus')} />;
    }

    return <i className="bx bx-check-circle text-lg text-red-500" title={t('global.rejected_icus')} />;
}

export default function IcuTable({ items, variant }: IcuTableProps) {
    const { t } = useTranslation();

    return (
        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{t('global.number')}</TableHead>
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
                        <TableRow key={item.id}>
                            <TableCell className="font-medium">{item.row_number ?? item.id}</TableCell>
                            <TableCell>
                                {item.patient_id_card ? (
                                    <Badge color="gray">{item.patient_id_card}</Badge>
                                ) : (
                                    '—'
                                )}
                            </TableCell>
                            <TableCell>{item.patient_name ?? '—'}</TableCell>
                            <TableCell className="text-gray-600">{item.father_name ?? '—'}</TableCell>
                            <TableCell className="text-gray-600">{item.room_name ?? '—'}</TableCell>
                            <TableCell className="text-gray-600">{item.bed_number ?? '—'}</TableCell>
                            <TableCell className="max-w-xs text-gray-600">{truncate(item.description)}</TableCell>
                            <TableCell>
                                <StatusCell item={item} variant={variant} />
                            </TableCell>
                            <TableCell className="text-end">
                                <TableActionButton kind="view" href={item.urls.show} title={t('global.view')} />
                            </TableCell>
                        </TableRow>
                    ))}
                    {items.length === 0 && (
                        <TableRow>
                            <TableCell colSpan={9} className="py-10 text-center text-gray-500">
                                {t('global.try_adjusting_your_search_criteria')}
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
