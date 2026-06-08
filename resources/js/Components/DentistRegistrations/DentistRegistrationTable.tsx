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
import { TableActionsCell } from '../ui/TableActions';
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
                        <TableActionsCell>
                            <TableActionButton kind="view" href={`${showUrlBase}/${item.id}`} />
                            <TableActionButton
                                kind="edit"
                                href={`${showUrlBase}/${item.id}?edit=1`}
                                permission={permissions.edit}
                            />
                        </TableActionsCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
