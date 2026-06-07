import {
    PatientIndexPermissions,
    PatientIndexUrls,
    PatientListItem,
} from '../../types/patient';
import { useTranslation } from '../../hooks/useTranslation';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import { TableActionButton, TableActionLink, TableActions } from '../ui/TableActions';

interface PatientsTableProps {
    patients: PatientListItem[];
    permissions: PatientIndexPermissions;
    urls: PatientIndexUrls;
    onDelete: (patientId: number) => void;
}

function displayValue(value: string | null | undefined) {
    return value?.trim() ? value : '—';
}

export default function PatientsTable({
    patients,
    permissions,
    urls,
    onDelete,
}: PatientsTableProps) {
    const { t } = useTranslation();
    const columnCount = 11;

    return (
        <Table id="patients-table">
            <TableHead>
                <TableRow variant="header">
                    <TableHeader>{t('global.id')}</TableHeader>
                    <TableHeader>{t('global.id_card')}</TableHeader>
                    <TableHeader>{t('global.name')}</TableHeader>
                    <TableHeader>{t('global.last_name')}</TableHeader>
                    <TableHeader>{t('global.father_name')}</TableHeader>
                    <TableHeader>
                        {t('global.province')} / {t('global.district')}
                    </TableHeader>
                    <TableHeader>{t('global.age')}</TableHeader>
                    <TableHeader>{t('global.militery_type')}</TableHeader>
                    <TableHeader>{t('global.phone')}</TableHeader>
                    <TableHeader>{t('global.created_by')}</TableHeader>
                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {patients.length === 0 ? (
                    <TableEmpty
                        colSpan={columnCount}
                        title={t('global.no_results_found')}
                    />
                ) : (
                    patients.map((patient) => (
                        <TableRow key={patient.id}>
                            <TableCell className="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                {patient.id}
                            </TableCell>
                            <TableCell muted className="whitespace-nowrap">
                                {displayValue(patient.id_card)}
                            </TableCell>
                            <TableCell className="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                {patient.name}
                            </TableCell>
                            <TableCell muted className="whitespace-nowrap">
                                {displayValue(patient.last_name)}
                            </TableCell>
                            <TableCell muted className="whitespace-nowrap">
                                {displayValue(patient.father_name)}
                            </TableCell>
                            <TableCell muted className="max-w-[180px] truncate">
                                {displayValue(patient.location)}
                            </TableCell>
                            <TableCell muted className="whitespace-nowrap">
                                {displayValue(patient.age)}
                            </TableCell>
                            <TableCell muted className="whitespace-nowrap">
                                {displayValue(patient.militery_type)}
                            </TableCell>
                            <TableCell muted className="whitespace-nowrap">
                                {displayValue(patient.phone)}
                            </TableCell>
                            <TableCell muted className="whitespace-nowrap">
                                {displayValue(patient.created_by)}
                            </TableCell>
                            <TableCell align="center" className="whitespace-nowrap">
                                <TableActions>
                                    <TableActionLink
                                        href={`${urls.show}/${patient.id}`}
                                        icon="bx-show"
                                        title={t('global.view')}
                                        variant="view"
                                    />
                                    {permissions.edit && (
                                        <TableActionLink
                                            href={`${urls.edit}/${patient.id}/edit`}
                                            icon="bx-edit"
                                            title={t('global.edit')}
                                            variant="edit"
                                        />
                                    )}
                                    {permissions.delete && (
                                        <TableActionButton
                                            icon="bx-trash"
                                            title={t('global.delete')}
                                            variant="delete"
                                            onClick={() => onDelete(patient.id)}
                                        />
                                    )}
                                </TableActions>
                            </TableCell>
                        </TableRow>
                    ))
                )}
            </TableBody>
        </Table>
    );
}
