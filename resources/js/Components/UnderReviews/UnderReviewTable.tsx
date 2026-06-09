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
import { UnderReviewListItem } from '../../types/underReview';

interface UnderReviewTableProps {
    items: UnderReviewListItem[];
}

export default function UnderReviewTable({ items }: UnderReviewTableProps) {
    const { t } = useTranslation();

    return (
        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{t('global.id')}</TableHead>
                        <TableHead>{t('global.card_number')}</TableHead>
                        <TableHead>{t('global.patient_name')}</TableHead>
                        <TableHead>{t('global.father_name')}</TableHead>
                        <TableHead>{t('global.room')}</TableHead>
                        <TableHead>{t('global.bed')}</TableHead>
                        <TableHead>{t('global.hospitalization_date')}</TableHead>
                        <TableHead className="w-16 text-end">{t('global.actions')}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {items.map((item) => (
                        <TableRow key={item.id}>
                            <TableCell className="font-medium">{item.id}</TableCell>
                            <TableCell>{item.patient_id_card ?? '—'}</TableCell>
                            <TableCell>{item.patient_name ?? '—'}</TableCell>
                            <TableCell className="text-gray-600">{item.father_name ?? '—'}</TableCell>
                            <TableCell className="text-gray-600">{item.room_name ?? '—'}</TableCell>
                            <TableCell className="text-gray-600">{item.bed_number ?? '—'}</TableCell>
                            <TableCell className="text-gray-500" dir="ltr">
                                {item.admission_date ?? '—'}
                            </TableCell>
                            <TableCell className="text-end">
                                <TableActionButton kind="view" href={item.urls.show} title={t('global.view')} />
                            </TableCell>
                        </TableRow>
                    ))}
                    {items.length === 0 && (
                        <TableRow>
                            <TableCell colSpan={8} className="py-10 text-center text-gray-500">
                                {t('global.no_records_found')}
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
