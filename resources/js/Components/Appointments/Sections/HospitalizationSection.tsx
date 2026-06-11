import { useCallback, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import HospitalizationFormModal from '../../Hospitalizations/HospitalizationFormModal';
import { HospitalizationFormValues } from '../../Hospitalizations/hospitalizationFormTypes';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import AppointmentSectionAccordion, {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
} from './AppointmentSectionAccordion';
import { SectionActionButton } from './SimpleTableSection';

interface HospitalizationSectionProps {
    appointmentId: number;
}

interface HospitalizationListItem {
    id: number;
    reason: string | null;
    remarks: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    is_active: boolean;
    urls?: { show?: string; edit?: string };
}

interface HospitalizationSectionData {
    items: HospitalizationListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
}

export default function HospitalizationSection({ appointmentId }: HospitalizationSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/hospitalization`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<HospitalizationSectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);

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

    const handleCreate = async (form: HospitalizationFormValues) => {
        setSubmitting(true);
        try {
            const response = await fetch(baseUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(form),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return;
            }
            setCreateOpen(false);
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    const handleDelete = async (itemId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        setSubmitting(true);
        try {
            const response = await fetch(`${baseUrl}/${itemId}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const payload = await response.json();
            if (payload.success) {
                await loadData();
            }
        } finally {
            setSubmitting(false);
        }
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <AppointmentSectionAccordion
                id={`hospitalization-${appointmentId}`}
                icon="bx-bed"
                iconClassName="text-emerald-500"
                title={t('global.hospitalize')}
                count={data?.count}
                badgeColor="success"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        <AccordionButton onClick={() => setCreateOpen(true)} permission={data?.permissions.create}>
                            {t('global.add')}
                        </AccordionButton>

                        {data && data.items.length > 0 ? (
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.reason')}</TableHeader>
                                        <TableHeader>{t('global.remarks')}</TableHeader>
                                        <TableHeader>{t('global.room')}</TableHeader>
                                        <TableHeader>{t('global.bed')}</TableHeader>
                                        <TableHeader>{t('global.status')}</TableHeader>
                                        <TableHeader className="text-end">{t('global.actions')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data.items.map((item) => (
                                        <TableRow key={item.id}>
                                            <TableCell>{item.reason ?? '—'}</TableCell>
                                            <TableCell className="text-gray-600">{item.remarks ?? '—'}</TableCell>
                                            <TableCell className="text-gray-600">{item.room_name ?? '—'}</TableCell>
                                            <TableCell className="text-gray-600">{item.bed_number ?? '—'}</TableCell>
                                            <TableCell>
                                                {item.is_active ? (
                                                    <span className="text-emerald-600">{t('global.active')}</span>
                                                ) : (
                                                    <span className="text-gray-500">{t('global.discharged')}</span>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-end">
                                                {item.urls?.show && (
                                                    <SectionActionButton
                                                        icon="bx-show"
                                                        title={t('global.show')}
                                                        href={item.urls.show}
                                                        colorClass="text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30"
                                                    />
                                                )}
                                                {data.permissions.edit && item.urls?.edit && (
                                                    <SectionActionButton
                                                        icon="bx-edit"
                                                        title={t('global.edit')}
                                                        href={item.urls.edit}
                                                        colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                    />
                                                )}
                                                {data.permissions.delete && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => handleDelete(item.id)}
                                                        colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_previous_hospitalizations')} />
                        )}
                    </>
                )}
            </AppointmentSectionAccordion>

            <HospitalizationFormModal
                show={createOpen}
                onClose={() => setCreateOpen(false)}
                onSubmit={handleCreate}
                submitting={submitting}
                metaUrl={`${baseUrl}/meta`}
            />
        </>
    );
}
