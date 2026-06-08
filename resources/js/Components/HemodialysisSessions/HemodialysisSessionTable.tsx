import { Badge } from 'flowbite-react';
import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
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
    HemodialysisSessionListItem,
    HemodialysisSessionListPermissions,
} from '../../types/hemodialysisSession';
import { settingsActionClasses } from '../../utils/settingsUi';
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

    const handleDelete = (id: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        setDeletingId(id);
        router.delete(`${showUrlBase}/${id}`, {
            preserveScroll: true,
            onFinish: () => setDeletingId(null),
        });
    };

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
                        <TableCell align="center">
                            <div className="flex justify-center gap-1">
                                <Link
                                    href={`${showUrlBase}/${item.id}`}
                                    className={settingsActionClasses.view}
                                    title={t('global.show')}
                                >
                                    <i className="bx bx-show text-lg" />
                                </Link>
                                {permissions.edit && (
                                    <Link
                                        href={`${editUrlBase}/${item.id}/edit`}
                                        className={settingsActionClasses.edit}
                                        title={t('global.edit')}
                                    >
                                        <i className="bx bx-edit text-lg" />
                                    </Link>
                                )}
                                {permissions.delete && (
                                    <button
                                        type="button"
                                        disabled={deletingId === item.id}
                                        onClick={() => handleDelete(item.id)}
                                        className={settingsActionClasses.delete}
                                        title={t('global.delete')}
                                    >
                                        <i className="bx bx-trash text-lg" />
                                    </button>
                                )}
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
