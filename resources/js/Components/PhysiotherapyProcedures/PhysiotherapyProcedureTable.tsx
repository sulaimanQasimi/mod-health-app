import { Badge, Button } from 'flowbite-react';
import { Link } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import SettingsEmptyState from '../Settings/SettingsEmptyState';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PhysiotherapyProcedureListItem,
    PhysiotherapyProcedureListPermissions,
} from '../../types/physiotherapyProcedure';
import PhysiotherapyProcedureProgressBar from './PhysiotherapyProcedureProgressBar';
import PhysiotherapyProcedureStatusBadge from './PhysiotherapyProcedureStatusBadge';

interface PhysiotherapyProcedureTableProps {
    items: PhysiotherapyProcedureListItem[];
    permissions: PhysiotherapyProcedureListPermissions;
    showUrlBase: string;
    showPhysiotherapistColumn: boolean;
    showFatherNameColumn?: boolean;
    onUpdateProgress?: (item: PhysiotherapyProcedureListItem) => void;
}

export default function PhysiotherapyProcedureTable({
    items,
    permissions,
    showUrlBase,
    showPhysiotherapistColumn,
    showFatherNameColumn = false,
    onUpdateProgress,
}: PhysiotherapyProcedureTableProps) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return <SettingsEmptyState message={t('global.no_procedures_found')} />;
    }

    const canUpdateProgress = (item: PhysiotherapyProcedureListItem) =>
        permissions.updateProgress && item.status !== 'completed' && item.status !== 'cancelled';

    return (
        <Table>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader>{t('global.number')}</TableHeader>
                    <TableHeader>{t('global.card_number')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    {showFatherNameColumn && <TableHeader>{t('global.father_name')}</TableHeader>}
                    <TableHeader>{t('global.physiotherapy_type')}</TableHeader>
                    {showPhysiotherapistColumn && <TableHeader>{t('global.physiotherapist')}</TableHeader>}
                    <TableHeader>{t('global.type')}</TableHeader>
                    <TableHeader>{t('global.duration')}</TableHeader>
                    <TableHeader>{t('global.progress')}</TableHeader>
                    <TableHeader>{t('global.status')}</TableHeader>
                    <TableHeader>{t('global.start_date')}</TableHeader>
                    <TableHeader>{t('global.reviews')}</TableHeader>
                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((item, index) => (
                    <TableRow key={item.id}>
                        <TableCell>
                            <Badge color="info">{index + 1}</Badge>
                        </TableCell>
                        <TableCell muted>{item.patient_id_card ?? '—'}</TableCell>
                        <TableCell>
                            <div className="font-medium text-gray-900 dark:text-white">
                                {item.patient_name ?? '—'}
                            </div>
                            {item.patient_phone && (
                                <div className="text-xs text-gray-500">{item.patient_phone}</div>
                            )}
                        </TableCell>
                        {showFatherNameColumn && <TableCell muted>{item.patient_father_name ?? '—'}</TableCell>}
                        <TableCell>{item.physiotherapy_type ?? '—'}</TableCell>
                        {showPhysiotherapistColumn && (
                            <TableCell>
                                <Badge color="gray">{item.physiotherapist ?? '—'}</Badge>
                            </TableCell>
                        )}
                        <TableCell>{item.type ?? '—'}</TableCell>
                        <TableCell>
                            {item.duration != null ? `${item.duration} ${t('global.minutes')}` : '—'}
                        </TableCell>
                        <TableCell>
                            <PhysiotherapyProcedureProgressBar
                                counter={item.progress_counter}
                                total={item.progress_total}
                                percentage={item.progress_percentage}
                                compact
                            />
                        </TableCell>
                        <TableCell>
                            <PhysiotherapyProcedureStatusBadge status={item.status} />
                        </TableCell>
                        <TableCell muted>{item.start_date ?? '—'}</TableCell>
                        <TableCell>
                            <Badge color={item.reviews_count > 0 ? 'info' : 'gray'}>{item.reviews_count}</Badge>
                        </TableCell>
                        <TableCell align="center">
                            <div className="flex justify-center gap-1">
                                <Button
                                    as={Link}
                                    href={`${showUrlBase}/${item.id}`}
                                    size="xs"
                                    color="light"
                                    title={t('global.view')}
                                >
                                    <i className="bx bx-show" />
                                </Button>
                                {canUpdateProgress(item) && onUpdateProgress && (
                                    <Button
                                        size="xs"
                                        color="warning"
                                        title={t('global.update_progress')}
                                        onClick={() => onUpdateProgress(item)}
                                    >
                                        <i className="bx bx-edit" />
                                    </Button>
                                )}
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
