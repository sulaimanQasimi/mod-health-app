import { Badge, Button } from 'flowbite-react';
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
    NephrologyRegistrationListItem,
    NephrologyRegistrationListPermissions,
} from '../../types/nephrologyRegistration';
import { settingsActionClasses } from '../../utils/settingsUi';
import NephrologyRegistrationStatusBadge from './NephrologyRegistrationStatusBadge';

interface NephrologyRegistrationTableProps {
    items: NephrologyRegistrationListItem[];
    permissions: NephrologyRegistrationListPermissions;
    showUrlBase: string;
    acceptUrlBase: string;
}

function formatGender(gender: string | number | null | undefined, t: (key: string) => string): string {
    if (gender === null || gender === undefined || gender === '') {
        return '—';
    }
    if (gender === 0 || gender === '0' || gender === 'male') {
        return t('global.male');
    }
    if (gender === 1 || gender === '1' || gender === 'female') {
        return t('global.female');
    }
    return String(gender);
}

export default function NephrologyRegistrationTable({
    items,
    permissions,
    showUrlBase,
    acceptUrlBase,
}: NephrologyRegistrationTableProps) {
    const { t } = useTranslation();
    const [acceptingId, setAcceptingId] = useState<number | null>(null);

    if (items.length === 0) {
        return <SettingsEmptyState message={t('global.no_registrations_found')} />;
    }

    const handleAccept = (id: number) => {
        setAcceptingId(id);
        router.post(`${acceptUrlBase}/${id}/accept`, {}, {
            preserveScroll: true,
            onFinish: () => setAcceptingId(null),
        });
    };

    return (
        <Table>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader>{t('global.ref_no')}</TableHeader>
                    <TableHeader>{t('global.patient_id')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.father_name')}</TableHeader>
                    <TableHeader>{t('global.phone_number')}</TableHeader>
                    <TableHeader>{t('global.old')}</TableHeader>
                    <TableHeader>{t('global.gender')}</TableHeader>
                    <TableHeader>{t('global.visit_date')}</TableHeader>
                    <TableHeader>{t('global.doctor')}</TableHeader>
                    <TableHeader>{t('global.status')}</TableHeader>
                    <TableHeader>{t('global.diseases')}</TableHeader>
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
                        <TableCell muted>{item.father_name ?? '—'}</TableCell>
                        <TableCell muted>{item.phone ?? '—'}</TableCell>
                        <TableCell muted>{item.age ?? '—'}</TableCell>
                        <TableCell muted>{formatGender(item.gender, t)}</TableCell>
                        <TableCell muted dir="ltr">
                            {item.visit_date ?? '—'}
                        </TableCell>
                        <TableCell muted>{item.doctor_name ?? t('global.not_available')}</TableCell>
                        <TableCell>
                            <NephrologyRegistrationStatusBadge status={item.status} />
                        </TableCell>
                        <TableCell muted>{item.diagnosis ?? '—'}</TableCell>
                        <TableCell align="center">
                            <div className="flex justify-center gap-1">
                                {item.needs_acceptance && permissions.accept ? (
                                    <Button
                                        size="sm"
                                        color="success"
                                        disabled={acceptingId === item.id}
                                        onClick={() => handleAccept(item.id)}
                                    >
                                        <i className="bx bx-check me-1" />
                                        {t('global.accept')}
                                    </Button>
                                ) : (
                                    <Link
                                        href={`${showUrlBase}/${item.id}`}
                                        className={settingsActionClasses.view}
                                        title={t('global.show')}
                                    >
                                        <i className="bx bx-show text-lg" />
                                    </Link>
                                )}
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
