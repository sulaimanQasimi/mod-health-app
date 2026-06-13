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
import { PacuListItem, PacuListVariant } from '../../types/pacu';

interface PacuTableProps {
    items: PacuListItem[];
    variant: PacuListVariant;
    embedded?: boolean;
}

const COLUMN_COUNT = 8;

function truncate(text: string | null, max = 48): string {
    if (!text) return '—';
    return text.length > max ? `${text.slice(0, max)}…` : text;
}

function StatusCell({ status, variant }: { status: string; variant: PacuListVariant }) {
    const { t } = useTranslation();

    if (variant === 'completed' || status === 'completed') {
        return (
            <Badge color="success" className="w-fit font-normal">
                <span className="inline-flex items-center gap-1">
                    <i className="bx bx-check-circle" />
                    {t('global.completed_pacus')}
                </span>
            </Badge>
        );
    }

    return (
        <Badge color="info" className="w-fit font-normal">
            <span className="inline-flex items-center gap-1">
                <i className="bx bx-time-five" />
                {t('global.new_pacus')}
            </span>
        </Badge>
    );
}

export default function PacuTable({ items, variant, embedded = true }: PacuTableProps) {
    const { t } = useTranslation();
    const showStatus = variant === 'completed';

    return (
        <Table embedded={embedded}>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader className="w-16">{t('global.number')}</TableHeader>
                    <TableHeader>{t('global.card_number')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.father_name')}</TableHeader>
                    <TableHeader className="min-w-[10rem]">{t('global.description')}</TableHeader>
                    <TableHeader>{t('global.register_date')}</TableHeader>
                    {showStatus && <TableHeader>{t('global.status')}</TableHeader>}
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
                        <TableCell muted className="max-w-xs" title={item.description ?? undefined}>
                            {truncate(item.description)}
                        </TableCell>
                        <TableCell muted dir="ltr">
                            {item.created_at ?? '—'}
                        </TableCell>
                        {showStatus && (
                            <TableCell>
                                <StatusCell status={item.status} variant={variant} />
                            </TableCell>
                        )}
                        <TableCell align="right">
                            <TableActionButton kind="view" href={item.urls.show} title={t('global.view')} />
                        </TableCell>
                    </TableRow>
                ))}
                {items.length === 0 && (
                    <TableEmpty
                        colSpan={showStatus ? COLUMN_COUNT : COLUMN_COUNT - 1}
                        icon="bx-tv"
                        title={t('global.no_records_found')}
                        description={t('global.try_adjusting_your_search_criteria')}
                    />
                )}
            </TableBody>
        </Table>
    );
}
