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
import { TableActions, TableActionsCell } from '../ui/TableActions';
import SettingsEmptyState from '../Settings/SettingsEmptyState';
import { useTranslation } from '../../hooks/useTranslation';
import { ProstheticCaseListItem } from '../../types/prosthetics';
import { prostheticCaseStatusLabel } from './prostheticsCaseUi';

interface ProstheticCaseTableProps {
    items: ProstheticCaseListItem[];
    showUrlBase: string;
}

export default function ProstheticCaseTable({ items, showUrlBase }: ProstheticCaseTableProps) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return <SettingsEmptyState message={t('global.no_records_found')} />;
    }

    return (
        <Table>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader>{t('global.prosthetics_case_number')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.status')}</TableHeader>
                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((item) => (
                    <TableRow key={item.id}>
                        <TableCell>
                            <code className="text-sm">{item.case_number}</code>
                        </TableCell>
                        <TableCell>
                            {item.patient
                                ? `${item.patient.name} ${item.patient.last_name ?? ''}`.trim()
                                : '—'}
                        </TableCell>
                        <TableCell>
                            <Badge color="info">{prostheticCaseStatusLabel(item.status, t)}</Badge>
                        </TableCell>
                        <TableActionsCell>
                            <TableActions>
                                <TableActionButton kind="view" href={`${showUrlBase}/${item.id}`} />
                            </TableActions>
                        </TableActionsCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
