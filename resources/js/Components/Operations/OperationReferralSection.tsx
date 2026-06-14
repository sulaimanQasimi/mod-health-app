import { Badge } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';
import OperationReferralFormModal from './OperationReferralFormModal';
import {
    operationApprovalLabel,
    operationDoneLabel,
    operationReservedLabel,
} from './operationUi';

interface OperationListItem {
    id: number;
    operation_type: string | null;
    patient_name: string | null;
    date: string | null;
    is_operation_approved: boolean;
    is_operation_done: boolean;
    is_reserved: boolean;
    urls?: { show?: string };
}

interface SectionData {
    items: OperationListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
    };
}

export interface OperationReferralSectionProps {
    baseUrl: string;
    accordionId: string;
    isDischarged?: boolean;
    reloadPageOnSuccess?: boolean;
}

function operationStatusBadge(
    item: OperationListItem,
    t: (key: string) => string,
): { label: string; color: 'info' | 'success' | 'warning' | 'failure' | 'purple' } {
    if (item.is_operation_done) {
        return { label: operationDoneLabel(true, t), color: 'info' };
    }

    if (item.is_reserved) {
        return { label: operationReservedLabel(true, t), color: 'purple' };
    }

    if (item.is_operation_approved) {
        return { label: operationApprovalLabel(true, t), color: 'success' };
    }

    return { label: t('global.pending'), color: 'warning' };
}

export default function OperationReferralSection({
    baseUrl,
    accordionId,
    isDischarged = false,
    reloadPageOnSuccess = false,
}: OperationReferralSectionProps) {
    const { t } = useTranslation();

    const [loading, setLoading] = useState(true);
    const [data, setData] = useState<SectionData | null>(null);
    const [referOpen, setReferOpen] = useState(false);

    const loadData = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch(baseUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setData(payload.data);
            }
        } finally {
            setLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const handleReferSuccess = async () => {
        await loadData();
        if (reloadPageOnSuccess) {
            router.reload({ only: ['hospitalization'] });
        }
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <SectionShell
                id={accordionId}
                icon="bx-cut"
                iconClassName="text-amber-500"
                title={t('global.operations')}
                count={data?.count}
                badgeColor="warning"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        {!isDischarged && (
                            <div className="mb-4">
                                <AccordionButton onClick={() => setReferOpen(true)} permission={data?.permissions.create}>
                                    {t('global.refere_to_operation')}
                                </AccordionButton>
                            </div>
                        )}

                        {(data?.items.length ?? 0) > 0 ? (
                            <div className="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                <Table embedded className="min-w-[900px]">
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader className="w-12">{t('global.number')}</TableHeader>
                                            <TableHeader>{t('global.operation_type')}</TableHeader>
                                            <TableHeader>{t('global.patient_name')}</TableHeader>
                                            <TableHeader>{t('global.status')}</TableHeader>
                                            <TableHeader>{t('global.date')}</TableHeader>
                                            <TableHeader align="center">{t('global.actions')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {data?.items.map((item, index) => {
                                            const status = operationStatusBadge(item, t);

                                            return (
                                                <TableRow key={item.id}>
                                                    <TableCell className="font-medium text-gray-500 dark:text-gray-400">
                                                        {index + 1}
                                                    </TableCell>
                                                    <TableCell>
                                                        {item.operation_type ? (
                                                            <Badge color="warning" className="w-fit font-normal">
                                                                {item.operation_type}
                                                            </Badge>
                                                        ) : (
                                                            '—'
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="font-medium text-gray-900 dark:text-white">
                                                        {item.patient_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge color={status.color} className="w-fit font-normal">
                                                            {status.label}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell muted dir="ltr">
                                                        {item.date ?? '—'}
                                                    </TableCell>
                                                    <TableCell align="center">
                                                        {item.urls?.show && (
                                                            <SectionActionButton
                                                                icon="bx-show"
                                                                title={t('global.view')}
                                                                href={item.urls.show}
                                                                colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                            />
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <SectionEmptyState message={t('global.not_referred_to_operation')} />
                        )}
                    </>
                )}
            </SectionShell>

            <OperationReferralFormModal
                show={referOpen}
                onClose={() => setReferOpen(false)}
                onSuccess={handleReferSuccess}
                baseUrl={baseUrl}
                accordionId={accordionId}
            />
        </>
    );
}
