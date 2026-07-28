import { usePage } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import PersianDateInput from '../../ui/PersianDateInput';
import SearchableSelect from '../../ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../ui/Table';
import TableBadge from '../../ui/TableBadge';
import AppointmentSectionAccordion, {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
} from './AppointmentSectionAccordion';
import { SectionActionButton } from './SimpleTableSection';

interface Props {
    appointmentId: number;
}

interface Registration {
    id: number;
    ref_no: string;
    patient_name: string | null;
    examiner_name: string | null;
    registration_date: string | null;
    status: string;
    diagnosis: string | null;
    tests_count: number;
    urls: { show: string };
}

const STATUS_COLORS: Record<string, 'warning' | 'info' | 'success' | 'failure'> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

export default function OphthalmologySection({ appointmentId }: Props) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/ophthalmology`;
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [open, setOpen] = useState(false);
    const [items, setItems] = useState<Registration[]>([]);
    const [canView, setCanView] = useState(true);
    const [canCreate, setCanCreate] = useState(false);
    const [doctors, setDoctors] = useState<Array<{ id: number; name: string }>>([]);
    const [form, setForm] = useState({
        examiner_id: '',
        registration_date: '',
        chief_complaint: '',
        notes: '',
    });

    const loadData = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch(baseUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setItems(payload.data.items ?? []);
                setCanView(payload.data.permissions?.view !== false);
                setCanCreate(Boolean(payload.data.permissions?.create));
            }
        } finally {
            setLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const doctorOptions = useMemo(
        () => doctors.map((doctor) => ({ value: String(doctor.id), label: doctor.name })),
        [doctors],
    );

    const openCreate = async () => {
        setOpen(true);
        if (doctors.length === 0) {
            const response = await fetch(`${baseUrl}/meta`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) setDoctors(payload.data.doctors ?? []);
        }
    };

    const submit = async (event: FormEvent) => {
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
                    ...form,
                    examiner_id: form.examiner_id ? Number(form.examiner_id) : null,
                }),
            });
            const payload = await response.json();
            if (response.ok && payload.success) {
                window.location.href = payload.data.url;
            }
        } finally {
            setSubmitting(false);
        }
    };

    const statusLabel = (status: string) =>
        ({
            pending: t('global.status_pending'),
            in_progress: t('global.status_in_progress'),
            completed: t('global.status_completed'),
            cancelled: t('global.status_cancelled'),
        })[status] ?? status;

    if (!loading && !canView) {
        return null;
    }

    return (
        <AppointmentSectionAccordion
            id={`ophthalmology-${appointmentId}`}
            icon="bx-low-vision"
            iconClassName="text-cyan-600"
            title={t('global.ophthalmology_registrations')}
            count={items.length}
            badgeColor="info"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    <AccordionButton onClick={openCreate} permission={canCreate}>
                        {t('global.ophthalmology_registration')}
                    </AccordionButton>
                    {items.length ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.ref_no')}</TableHeader>
                                    <TableHeader>{t('global.patient')}</TableHeader>
                                    <TableHeader>{t('global.examiner')}</TableHeader>
                                    <TableHeader>{t('global.registration_date')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.diagnosis')}</TableHeader>
                                    <TableHeader>{t('global.diagnostic_tests')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {items.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell><TableBadge color="info">{item.ref_no}</TableBadge></TableCell>
                                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.examiner_name ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">{item.registration_date ?? '—'}</TableCell>
                                        <TableCell>
                                            <TableBadge color={STATUS_COLORS[item.status] ?? 'gray'}>
                                                {statusLabel(item.status)}
                                            </TableBadge>
                                        </TableCell>
                                        <TableCell>{item.diagnosis || '—'}</TableCell>
                                        <TableCell><TableBadge color="warning">{item.tests_count}</TableBadge></TableCell>
                                        <TableCell align="center">
                                            <SectionActionButton
                                                icon="bx-expand"
                                                title={t('global.view_details')}
                                                href={item.urls.show}
                                                colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                            />
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

            <Modal show={open} onClose={() => setOpen(false)} size="lg">
                <ModalHeader>{t('global.ophthalmology_registration')}</ModalHeader>
                <form onSubmit={submit}>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label>{t('global.examiner')}</Label>
                            <SearchableSelect
                                value={form.examiner_id}
                                onChange={(examiner_id) => setForm((current) => ({ ...current, examiner_id }))}
                                options={doctorOptions}
                                placeholder={t('global.please_select_doctor')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.registration_date')} *</Label>
                            <PersianDateInput
                                value={form.registration_date}
                                onChange={(registration_date) => setForm((current) => ({ ...current, registration_date }))}
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.chief_complaint')}</Label>
                            <Textarea
                                value={form.chief_complaint}
                                onChange={(event) => setForm((current) => ({ ...current, chief_complaint: event.target.value }))}
                                rows={3}
                            />
                        </div>
                        <div>
                            <Label>{t('global.notes')}</Label>
                            <Textarea
                                value={form.notes}
                                onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))}
                                rows={2}
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button color="blue" type="submit" disabled={submitting}>
                            {submitting ? <Spinner size="sm" /> : t('global.create_and_open')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </AppointmentSectionAccordion>
    );
}
