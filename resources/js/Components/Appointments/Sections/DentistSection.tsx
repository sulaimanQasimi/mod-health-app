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
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../ui/Table';
import PersianDateInput from '../../ui/PersianDateInput';
import SearchableSelect from '../../ui/SearchableSelect';
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

interface DentistSectionProps {
    appointmentId: number;
}

interface LookupOption {
    id: number;
    name: string;
}

interface DentistListItem {
    id: number;
    ref_no: string | number | null;
    patient_name: string | null;
    dentist_name: string | null;
    registration_date: string | null;
    status: string;
    examinations_count: number;
    treatments_count: number;
    xrays_count: number;
    notes_count: number;
    urls?: { show?: string };
}

interface DentistDetail {
    id: number;
    ref_no: string | number | null;
    patient_name: string | null;
    dentist_name: string | null;
    registration_date: string | null;
    status: string;
    notes: string | null;
    examinations_count: number;
    treatments_count: number;
    xrays_count: number;
    notes_count: number;
    created_at: string | null;
    urls?: { show?: string };
}

interface DentistSectionData {
    items: DentistListItem[];
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

const EMPTY_FORM = {
    dentist_id: '',
    registration_date: '',
    notes: '',
};

export default function DentistSection({ appointmentId }: DentistSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/appointments/${appointmentId}/dentist`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [metaLoading, setMetaLoading] = useState(false);
    const [data, setData] = useState<DentistSectionData | null>(null);
    const [dentists, setDentists] = useState<LookupOption[]>([]);
    const [createOpen, setCreateOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [selectedRegistration, setSelectedRegistration] = useState<DentistDetail | null>(null);
    const [form, setForm] = useState(EMPTY_FORM);

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

    const loadMeta = useCallback(async () => {
        setMetaLoading(true);
        try {
            const response = await fetch(`${baseUrl}/meta`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setDentists(payload.data.dentists ?? []);
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const dentistOptions = useMemo(
        () =>
            dentists.map((item) => ({
                value: String(item.id),
                label: item.name,
            })),
        [dentists],
    );

    const resetForm = () => setForm(EMPTY_FORM);

    const openCreate = async () => {
        resetForm();
        setCreateOpen(true);
        if (dentists.length === 0) {
            await loadMeta();
        }
    };

    const closeCreate = () => {
        setCreateOpen(false);
        resetForm();
    };

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
                body: JSON.stringify({
                    dentist_id: Number(form.dentist_id),
                    registration_date: form.registration_date,
                    notes: form.notes || null,
                }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return;
            }
            closeCreate();
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
            id={`dentist-${appointmentId}`}
            icon="bx-plus-medical"
            iconClassName="text-blue-500"
            title={t('global.dentist_registrations')}
            count={data?.count}
            badgeColor="info"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    <AccordionButton onClick={openCreate} permission={data?.permissions.create}>
                        {t('global.dentist_registration')}
                    </AccordionButton>

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.ref_no')}</TableHeader>
                                    <TableHeader>{t('global.patient')}</TableHeader>
                                    <TableHeader>{t('global.dentist')}</TableHeader>
                                    <TableHeader>{t('global.registration_date')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.examinations')}</TableHeader>
                                    <TableHeader>{t('global.treatments')}</TableHeader>
                                    <TableHeader>{t('global.xrays')}</TableHeader>
                                    <TableHeader>{t('global.notes')}</TableHeader>
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
                                        <TableCell muted>{item.dentist_name ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.registration_date ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <TableBadge color={STATUS_COLORS[item.status] ?? 'gray'}>
                                                {statusLabel(item.status)}
                                            </TableBadge>
                                        </TableCell>
                                        <TableCell>
                                            <TableBadge color="info">{item.examinations_count}</TableBadge>
                                        </TableCell>
                                        <TableCell>
                                            <TableBadge color="success">{item.treatments_count}</TableBadge>
                                        </TableCell>
                                        <TableCell>
                                            <TableBadge color="warning">{item.xrays_count}</TableBadge>
                                        </TableCell>
                                        <TableCell>
                                            <TableBadge color="gray">{item.notes_count}</TableBadge>
                                        </TableCell>
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
                                                        colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                                    />
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SectionEmptyState message={t('global.no_registrations_found')} />
                    )}
                </>
            )}

            <Modal show={createOpen} onClose={closeCreate} size="lg">
                <ModalHeader>{t('global.dentist_registration')}</ModalHeader>
                <form onSubmit={handleCreate}>
                    <ModalBody className="space-y-4">
                        {metaLoading ? (
                            <SectionLoadingState />
                        ) : (
                            <>
                                <div>
                                    <Label>{t('global.dentist')} *</Label>
                                    <SearchableSelect
                                        value={form.dentist_id}
                                        onChange={(value) =>
                                            setForm((current) => ({ ...current, dentist_id: value }))
                                        }
                                        options={dentistOptions}
                                        placeholder={t('global.please_select_dentist')}
                                        required
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.registration_date')} *</Label>
                                    <PersianDateInput
                                        value={form.registration_date}
                                        onChange={(registration_date) =>
                                            setForm((current) => ({ ...current, registration_date }))
                                        }
                                        placeholder="1403/01/01"
                                        required
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.notes')}</Label>
                                    <Textarea
                                        rows={3}
                                        value={form.notes}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                notes: event.target.value,
                                            }))
                                        }
                                        placeholder={t('global.optional_notes')}
                                    />
                                </div>
                            </>
                        )}
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={closeCreate} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={submitting || metaLoading}>
                            {submitting ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="3xl">
                <ModalHeader>{t('global.dentist_registration_details')}</ModalHeader>
                <ModalBody>
                    {selectedRegistration && (
                        <div className="space-y-4">
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                {[
                                    [t('global.ref_no'), selectedRegistration.ref_no],
                                    [t('global.patient'), selectedRegistration.patient_name],
                                    [t('global.dentist'), selectedRegistration.dentist_name],
                                    [t('global.registration_date'), selectedRegistration.registration_date],
                                ].map(([label, value]) => (
                                    <DetailTile key={String(label)} label={String(label)} value={value} />
                                ))}
                            </div>

                            <div className="flex flex-wrap items-center gap-3">
                                <TableBadge color={STATUS_COLORS[selectedRegistration.status] ?? 'gray'}>
                                    {statusLabel(selectedRegistration.status)}
                                </TableBadge>
                                <TableBadge color="info">
                                    {t('global.examinations')}: {selectedRegistration.examinations_count}
                                </TableBadge>
                                <TableBadge color="success">
                                    {t('global.treatments')}: {selectedRegistration.treatments_count}
                                </TableBadge>
                                <TableBadge color="warning">
                                    {t('global.xrays')}: {selectedRegistration.xrays_count}
                                </TableBadge>
                                <TableBadge color="gray">
                                    {t('global.notes')}: {selectedRegistration.notes_count}
                                </TableBadge>
                            </div>

                            {selectedRegistration.notes && (
                                <DetailTextBlock label={t('global.notes')}>
                                    {selectedRegistration.notes}
                                </DetailTextBlock>
                            )}

                            {selectedRegistration.created_at && (
                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                    {t('global.created_at')}: {selectedRegistration.created_at}
                                </p>
                            )}
                        </div>
                    )}
                </ModalBody>
                <ModalFooter>
                    {selectedRegistration?.urls?.show && (
                        <Button color="light" as="a" href={selectedRegistration.urls.show} target="_blank">
                            <i className="bx bx-expand me-2" />
                            {t('global.view_details')}
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
