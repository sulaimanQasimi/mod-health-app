import {
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    Textarea,
} from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
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
import { DetailTextBlock, DetailTile } from '../../ui/DetailTile';
import TableBadge from '../../ui/TableBadge';
import { SectionActionButton } from './SimpleTableSection';

interface NephrologySectionProps {
    appointmentId: number;
}

interface NephrologyListItem {
    id: number;
    ref_no: string | number | null;
    patient_name: string | null;
    doctor_name: string | null;
    disease_name: string | null;
    visit_date: string | null;
    status: string;
    needs_acceptance: boolean;
    urls?: { show?: string };
}

interface NephrologyDetail {
    id: number;
    ref_no: string | number | null;
    patient_name: string | null;
    doctor_name: string | null;
    disease_name: string | null;
    visit_date: string | null;
    status: string;
    chief_complaint: string | null;
    ckd_aki_stage: string | null;
    dialysis_required: boolean;
    dialysis_type: string | null;
    access_type: string | null;
    notes: string | null;
    follow_up_plan: string | null;
    needs_acceptance: boolean;
    created_at: string | null;
    urls?: { show?: string; index?: string };
}

interface NephrologySectionData {
    items: NephrologyListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
    };
}

const STATUS_COLORS: Record<string, 'warning' | 'info' | 'success' | 'failure' | 'gray'> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

export default function NephrologySection({ appointmentId }: NephrologySectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/appointments/${appointmentId}/nephrology`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<NephrologySectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [selectedRegistration, setSelectedRegistration] = useState<NephrologyDetail | null>(null);
    const [notes, setNotes] = useState('');

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

    const handleCreate = async (event: FormEvent) => {
        event.preventDefault();
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
                body: JSON.stringify({ notes: notes || null }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return;
            }
            setCreateOpen(false);
            setNotes('');
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    const viewRegistration = async (registrationId: number) => {
        const response = await fetch(`${baseUrl}/${registrationId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (payload.success) {
            setSelectedRegistration(payload.data);
            setDetailsOpen(true);
        }
    };

    const statusLabel = (status: string) => {
        const labels: Record<string, string> = {
            pending: t('global.status_pending'),
            in_progress: t('global.status_in_progress'),
            completed: t('global.status_completed'),
            cancelled: t('global.status_cancelled'),
        };
        return labels[status] ?? status;
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <AppointmentSectionAccordion
            id={`nephrology-${appointmentId}`}
            icon="bx-droplet"
            iconClassName="text-indigo-500"
            title={t('global.nephrology_registrations')}
            count={data?.count}
            badgeColor="info"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    <AccordionButton onClick={() => setCreateOpen(true)} permission={data?.permissions.create}>
                        {t('global.start_nephrology_visit')}
                    </AccordionButton>

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.ref_no')}</TableHeader>
                                    <TableHeader>{t('global.patient')}</TableHeader>
                                    <TableHeader>{t('global.doctor')}</TableHeader>
                                    <TableHeader>{t('global.visit_date')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.diagnosis')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>
                                            <TableBadge color="info">{item.ref_no ?? '—'}</TableBadge>
                                        </TableCell>
                                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.doctor_name ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.visit_date ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap items-center gap-1">
                                                <TableBadge color={STATUS_COLORS[item.status] ?? 'gray'}>
                                                    {statusLabel(item.status)}
                                                </TableBadge>
                                                {item.needs_acceptance && (
                                                    <TableBadge color="warning">{t('global.pending')}</TableBadge>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>{item.disease_name ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                <SectionActionButton
                                                    icon="bx-show"
                                                    title={t('global.view')}
                                                    onClick={() => viewRegistration(item.id)}
                                                    colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                />
                                                {item.urls?.show && (
                                                    <SectionActionButton
                                                        icon="bx-expand"
                                                        title={t('global.view_details')}
                                                        href={item.urls.show}
                                                        colorClass="text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                    />
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SectionEmptyState message={t('global.no_nephrology_registrations_found')} />
                    )}
                </>
            )}

            <Modal show={createOpen} onClose={() => setCreateOpen(false)} size="lg">
                <ModalHeader>{t('global.start_nephrology_visit')}</ModalHeader>
                <form onSubmit={handleCreate}>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label>{t('global.notes')}</Label>
                            <Textarea
                                rows={3}
                                value={notes}
                                onChange={(event) => setNotes(event.target.value)}
                                placeholder={t('global.optional_notes')}
                            />
                        </div>
                        <p className="text-sm text-gray-500">{t('global.nephrology_accept_on_index_hint')}</p>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setCreateOpen(false)} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={submitting}>
                            {submitting ? <Spinner size="sm" /> : t('global.submit')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="3xl">
                <ModalHeader>{t('global.nephrology_registration')}</ModalHeader>
                <ModalBody>
                    {selectedRegistration && (
                        <div className="space-y-4">
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                {[
                                    [t('global.ref_no'), selectedRegistration.ref_no],
                                    [t('global.patient'), selectedRegistration.patient_name],
                                    [t('global.doctor'), selectedRegistration.doctor_name],
                                    [t('global.visit_date'), selectedRegistration.visit_date],
                                    [t('global.diagnosis'), selectedRegistration.disease_name],
                                ].map(([label, value]) => (
                                    <DetailTile key={String(label)} label={String(label)} value={value} />
                                ))}
                            </div>

                            <TableBadge color={STATUS_COLORS[selectedRegistration.status] ?? 'gray'}>
                                {statusLabel(selectedRegistration.status)}
                            </TableBadge>

                            {selectedRegistration.needs_acceptance && (
                                <div className="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-200">
                                    {t('global.nephrology_accept_on_index_hint')}
                                </div>
                            )}

                            {selectedRegistration.chief_complaint && (
                                <DetailTextBlock label={t('global.chief_complaint')}>
                                    {selectedRegistration.chief_complaint}
                                </DetailTextBlock>
                            )}

                            {selectedRegistration.notes && (
                                <DetailTextBlock label={t('global.notes')}>
                                    {selectedRegistration.notes}
                                </DetailTextBlock>
                            )}

                            {selectedRegistration.follow_up_plan && (
                                <DetailTextBlock label={t('global.follow_up_plan')}>
                                    {selectedRegistration.follow_up_plan}
                                </DetailTextBlock>
                            )}

                            {selectedRegistration.created_at && (
                                <p className="text-sm text-gray-500">
                                    {t('global.created_at')}: {selectedRegistration.created_at}
                                </p>
                            )}
                        </div>
                    )}
                </ModalBody>
                <ModalFooter>
                    {selectedRegistration?.urls?.show && !selectedRegistration.needs_acceptance && (
                        <Button color="light" as="a" href={selectedRegistration.urls.show} target="_blank">
                            <i className="bx bx-expand me-2" />
                            {t('global.view_details')}
                        </Button>
                    )}
                    {selectedRegistration?.urls?.index && selectedRegistration.needs_acceptance && (
                        <Button color="light" as="a" href={selectedRegistration.urls.index}>
                            {t('global.nephrology_registrations')}
                        </Button>
                    )}
                    <Button color="gray" type="button" onClick={() => setDetailsOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>
        </AppointmentSectionAccordion>
    );
}
