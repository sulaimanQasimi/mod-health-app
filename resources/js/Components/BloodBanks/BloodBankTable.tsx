import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { BloodRequestListItem } from '../../types/bloodBank';
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
import { bloodGroupLabel, bloodRhLabel, bloodStatusBadgeColor } from './bloodBankUi';

interface BloodBankTableProps {
    items: BloodRequestListItem[];
    embedded?: boolean;
}

export default function BloodBankTable({ items, embedded = true }: BloodBankTableProps) {
    const { t } = useTranslation();

    return (
        <Table embedded={embedded}>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader className="w-16">{t('global.number')}</TableHeader>
                    <TableHeader>{t('global.card_number')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.father_name')}</TableHeader>
                    <TableHeader>{t('global.requested_department')}</TableHeader>
                    <TableHeader>{t('global.blood_group')}</TableHeader>
                    <TableHeader>{t('global.rh')}</TableHeader>
                    <TableHeader>{t('global.blood_type')}</TableHeader>
                    <TableHeader>{t('global.quantity')}</TableHeader>
                    <TableHeader>{t('global.status')}</TableHeader>
                    <TableHeader align="right" className="w-16">
                        {t('global.actions')}
                    </TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.length === 0 ? (
                    <TableEmpty colSpan={11} message={t('global.no_item_is_found')} />
                ) : (
                    items.map((item) => (
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
                            <TableCell muted>{item.department_name ?? '—'}</TableCell>
                            <TableCell>
                                <Badge color="failure" className="w-fit font-normal">
                                    {bloodGroupLabel(item.group)}
                                </Badge>
                            </TableCell>
                            <TableCell muted>{bloodRhLabel(item.rh)}</TableCell>
                            <TableCell muted>{item.type ?? '—'}</TableCell>
                            <TableCell muted>{item.quantity ?? '—'}</TableCell>
                            <TableCell>
                                <Badge color={bloodStatusBadgeColor(item.status)} className="w-fit font-normal">
                                    {item.status}
                                </Badge>
                            </TableCell>
                            <TableCell align="right">
                                <TableActionButton
                                    href={item.urls.show}
                                    icon="bx-show"
                                    title={t('global.show')}
                                    colorClass="text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-900/30"
                                />
                            </TableCell>
                        </TableRow>
                    ))
                )}
            </TableBody>
        </Table>
    );
}
