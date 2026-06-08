import { Badge } from 'flowbite-react';
import { router } from '@inertiajs/react';
import { useState } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import TableActionButton from '../ui/TableActionButton';
import { TableActionsCell } from '../ui/TableActions';
import SettingsEmptyState from '../Settings/SettingsEmptyState';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HemodialysisSessionListItem,
    HemodialysisSessionListPermissions,
} from '../../types/hemodialysisSession';
import HemodialysisSessionStatusBadge from './HemodialysisSessionStatusBadge';

interface HemodialysisSessionTableProps {
    items: HemodialysisSessionListItem[];
    permissions: HemodialysisSessionListPermissions;
    showUrlBase: string;
    editUrlBase: string;
}

export default function HemodialysisSessionTable({
    items,
    permissions,
    showUrlBase,
    editUrlBase,
}: HemodialysisSessionTableProps) {
    const { t } = useTranslation();
    const [deletingId, setDeletingId] = useState<number | null>(null);

    if (items.length === 0) {
        return <SettingsEmptyState message={t('global.no_hemodialysis_sessions_found')} />;
    }

    return (
        <Table>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader>{t('global.ref_no')}</TableHeader>
                    <TableHeader>{t('global.patient_id')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.diagnosis')}</TableHeader>
                    <TableHeader>{t('global.session_date')}</TableHeader>
                    <TableHeader>{t('global.session_time')}</TableHeader>
                    <TableHeader>{t('global.duration_minutes')}</TableHeader>
                    <TableHeader>{t('global.attending_nephrologist')}</TableHeader>
                    <TableHeader>{t('global.status')}</TableHeader>
                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((item) => (
                    <TableRow key={item.id}>
                        <TableCell>
                            <Badge color="info">{item.ref_no ?? '—'}</Badge>
                        </TableCell>
                        <TableCell muted>{item.patient_identifier ?? '—'}</TableCell>
                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                        <TableCell muted>{item.diagnosis ?? '—'}</TableCell>
                        <TableCell muted dir="ltr">
                            {item.session_date ?? '—'}
                        </TableCell>
                        <TableCell muted dir="ltr">
                            {item.session_time ?? '—'}
                        </TableCell>
                        <TableCell muted>{item.duration_minutes ?? '—'}</TableCell>
                        <TableCell muted>{item.doctor_name ?? '—'}</TableCell>
                        <TableCell>
                            <HemodialysisSessionStatusBadge status={item.status} />
                        </TableCell>
                        <TableActionsCell>
                            <TableActionButton kind="view" href={`${showUrlBase}/${item.id}`} />
                            <TableActionButton
                                kind="edit"
                                href={`${editUrlBase}/${item.id}/edit`}
                                permission={permissions.edit}
                            />
                            <TableActionButton
                                kind="delete"
                                permission={permissions.delete}
                                disabled={deletingId === item.id}
                                confirm={t('global.confirm_delete')}
                                onClick={() => {
                                    setDeletingId(item.id);
                                    router.delete(`${showUrlBase}/${item.id}`, {
                                        preserveScroll: true,
                                        onFinish: () => setDeletingId(null),
                                    });
                                }}
                            />
                        </TableActionsCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
