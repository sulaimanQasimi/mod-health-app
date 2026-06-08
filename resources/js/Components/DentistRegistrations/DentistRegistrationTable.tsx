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
    DentistRegistrationListItem,
    DentistRegistrationListPermissions,
} from '../../types/dentistRegistration';
import DentistRegistrationStatusBadge from './DentistRegistrationStatusBadge';

interface DentistRegistrationTableProps {
    items: DentistRegistrationListItem[];
    permissions: DentistRegistrationListPermissions;
    showUrlBase: string;
}

export default function DentistRegistrationTable({
    items,
    permissions,
    showUrlBase,
}: DentistRegistrationTableProps) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return <SettingsEmptyState message={t('global.no_registrations_found')} />;
    }

    return (
        <Table>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader>{t('global.ref_no')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.appointment_date')}</TableHeader>
                    <TableHeader>{t('global.dentist')}</TableHeader>
                    <TableHeader>{t('global.registration_date')}</TableHeader>
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
                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                        <TableCell muted dir="ltr">
                            {item.appointment_date ?? '—'}
                        </TableCell>
                        <TableCell muted>{item.dentist_name ?? '—'}</TableCell>
                        <TableCell muted dir="ltr">
                            {item.registration_date ?? '—'}
                        </TableCell>
                        <TableCell>
                            <DentistRegistrationStatusBadge status={item.status} />
                        </TableCell>
                        <TableCell align="center">
                            <div className="flex justify-center gap-1">
                                <Button as={Link} href={`${showUrlBase}/${item.id}`} size="xs" color="blue">
                                    <i className="bx bx-show" />
                                </Button>
                                {permissions.edit && (
                                    <Button
                                        as={Link}
                                        href={`${showUrlBase}/${item.id}?edit=1`}
                                        size="xs"
                                        color="warning"
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
