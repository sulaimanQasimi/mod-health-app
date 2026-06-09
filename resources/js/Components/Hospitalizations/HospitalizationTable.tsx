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
import { HospitalizationListItem } from '../../types/hospitalization';
import { dischargeStatusBadgeColor } from './hospitalizationUi';

interface HospitalizationTableProps {
    items: HospitalizationListItem[];
    variant?: 'active' | 'discharged';
    embedded?: boolean;
}

export default function HospitalizationTable({
    items,
    variant = 'active',
    embedded = true,
}: HospitalizationTableProps) {
    const { t } = useTranslation();
    const isDischarged = variant === 'discharged';
    const columnCount = isDischarged ? 11 : 10;

    return (
        <Table embedded={embedded}>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader className="w-16">{t('global.id')}</TableHeader>
                    <TableHeader>{t('global.card_number')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.father_name')}</TableHeader>
                    {!isDischarged && <TableHeader>{t('global.department')}</TableHeader>}
                    <TableHeader>{t('global.room')}</TableHeader>
                    <TableHeader>{t('global.bed')}</TableHeader>
                    <TableHeader>{t('global.doctor')}</TableHeader>
                    <TableHeader>{t('global.hospitalization_date')}</TableHeader>
                    {isDischarged && <TableHeader>{t('global.discharge_date')}</TableHeader>}
                    {isDischarged && <TableHeader>{t('global.discharge_status')}</TableHeader>}
                    <TableHeader align="right" className="w-16">
                        {t('global.actions')}
                    </TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((item) => (
                    <TableRow key={item.id}>
                        <TableCell className="font-medium">{item.id}</TableCell>
                        <TableCell>{item.patient_id_card ?? '—'}</TableCell>
                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                        <TableCell muted>{item.father_name ?? '—'}</TableCell>
                        {!isDischarged && (
                            <TableCell>
                                {item.department_name ? (
                                    <Badge color="info" className="w-fit font-normal">
                                        {item.department_name}
                                    </Badge>
                                ) : (
                                    '—'
                                )}
                            </TableCell>
                        )}
                        <TableCell muted>{item.room_name ?? '—'}</TableCell>
                        <TableCell muted>{item.bed_number ?? '—'}</TableCell>
                        <TableCell muted>{item.doctor_name ?? '—'}</TableCell>
                        <TableCell muted dir="ltr">
                            {item.admission_date ?? '—'}
                        </TableCell>
                        {isDischarged && (
                            <TableCell muted dir="ltr">
                                {item.discharged_at ?? '—'}
                            </TableCell>
                        )}
                        {isDischarged && (
                            <TableCell>
                                {item.discharge_status ? (
                                    <Badge
                                        color={dischargeStatusBadgeColor(item.discharge_status)}
                                        className="w-fit font-normal"
                                    >
                                        {{
                                            recovered: t('global.recovered'),
                                            died: t('global.died'),
                                            moved: t('global.moved'),
                                        }[item.discharge_status] ?? item.discharge_status}
                                    </Badge>
                                ) : (
                                    '—'
                                )}
                            </TableCell>
                        )}
                        <TableCell align="right">
                            <TableActionButton kind="view" href={item.urls.show} title={t('global.view')} />
                        </TableCell>
                    </TableRow>
                ))}
                {items.length === 0 && (
                    <TableEmpty colSpan={columnCount} icon="bx-bed" title={t('global.no_records_found')} />
                )}
            </TableBody>
        </Table>
    );
}
