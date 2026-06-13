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
import { ProstheticReferralListItem } from '../../types/prosthetics';
import { prostheticReferralStatusLabel } from './prostheticsReferralUi';

interface ProstheticReferralTableProps {
    items: ProstheticReferralListItem[];
    showUrlBase: string;
}

export default function ProstheticReferralTable({ items, showUrlBase }: ProstheticReferralTableProps) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return <SettingsEmptyState message={t('global.no_records_found')} />;
    }

    return (
        <Table>
            <TableHead>
                <TableRow variant="header">
                    <TableHeader>{t('global.prosthetics_referral_number')}</TableHeader>
                    <TableHeader>{t('global.patient_name')}</TableHeader>
                    <TableHeader>{t('global.nid')}</TableHeader>
                    <TableHeader>{t('global.urgency')}</TableHeader>
                    <TableHeader>{t('global.prosthetics_service_type')}</TableHeader>
                    <TableHeader>{t('global.status')}</TableHeader>
                    <TableHeader>{t('global.date')}</TableHeader>
                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                {items.map((referral) => (
                    <TableRow key={referral.id}>
                        <TableCell>
                            <code className="text-sm">{referral.referral_number}</code>
                        </TableCell>
                        <TableCell>
                            {referral.patient_name ?? '—'}
                        </TableCell>
                        <TableCell muted>{referral.patient_nid ?? '—'}</TableCell>
                        <TableCell>
                            {referral.urgency ? (
                                <Badge color="gray">{referral.urgency}</Badge>
                            ) : (
                                '—'
                            )}
                        </TableCell>
                        <TableCell muted>{referral.requested_service_type ?? '—'}</TableCell>
                        <TableCell>
                            <Badge color="info">{prostheticReferralStatusLabel(referral.status, t)}</Badge>
                        </TableCell>
                        <TableCell muted dir="ltr">
                            {referral.referral_date ?? '—'}
                        </TableCell>
                        <TableActionsCell>
                            <TableActions>
                                <TableActionButton
                                    kind="view"
                                    href={referral.urls?.show ?? `${showUrlBase}/${referral.id}`}
                                />
                            </TableActions>
                        </TableActionsCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
