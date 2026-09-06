import { usePage } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea, Select } from 'flowbite-react';
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
import { EYE_GLASSES_STATUS_COLORS, eyeGlassesStatusLabel } from '../../EyeGlasses/status';

interface Props {
    appointmentId: number;
}

interface OrderItem {
    id: number;
    ref_no: string;
    patient_name: string | null;
    examiner_name: string | null;
    request_date: string | null;
    status: string;
    frame_type: string | null;
    lens_type: string | null;
    urls: { show: string };
}

export default function EyeGlassesSection({ appointmentId }: Props) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/appointments/${appointmentId}/eye-glasses`;
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [open, setOpen] = useState(false);
    const [items, setItems] = useState<OrderItem[]>([]);
    const [canView, setCanView] = useState(true);
    const [canCreate, setCanCreate] = useState(false);
    const [doctors, setDoctors] = useState<Array<{ id: number; name: string }>>([]);
    const [frameTypes, setFrameTypes] = useState<string[]>([]);
    const [lensTypes, setLensTypes] = useState<string[]>([]);
    const [form, setForm] = useState({
        examiner_id: '',
        request_date: '',
        frame_type: '',
        lens_type: '',
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

    const optionLabel = (prefix: string, value: string | null) => {
        if (!value) return '—';
        const key = `global.eye_glasses_${prefix}_${value}`;
        const translated = t(key);
        return translated === key ? value : translated;
    };

    const openCreate = async () => {
        setOpen(true);
        if (doctors.length === 0) {
            const response = await fetch(`${baseUrl}/meta`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setDoctors(payload.data.doctors ?? []);
                setFrameTypes(payload.data.frame_types ?? []);
                setLensTypes(payload.data.lens_types ?? []);
            }
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
                    frame_type: form.frame_type || null,
                    lens_type: form.lens_type || null,
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

    if (!loading && !canView) {
        return null;
    }

    return (
        <AppointmentSectionAccordion
            id={`eye-glasses-${appointmentId}`}
            icon="bx-glasses"
            iconClassName="text-indigo-600"
            title={t('global.eye_glasses_orders')}
            count={items.length}
            badgeColor="info"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    <AccordionButton onClick={openCreate} permission={canCreate}>
                        {t('global.eye_glasses_order')}
                    </AccordionButton>
                    {items.length ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.ref_no')}</TableHeader>
                                    <TableHeader>{t('global.patient')}</TableHeader>
                                    <TableHeader>{t('global.examiner')}</TableHeader>
                                    <TableHeader>{t('global.eye_glasses_request_date')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {items.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>
                                            <TableBadge color="info">{item.ref_no}</TableBadge>
                                        </TableCell>
                                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.examiner_name ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.request_date ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <TableBadge color={EYE_GLASSES_STATUS_COLORS[item.status] ?? 'gray'}>
                                                {eyeGlassesStatusLabel(item.status, t)}
                                            </TableBadge>
                                        </TableCell>
                                        <TableCell align="center">
                                            <SectionActionButton
                                                icon="bx-expand"
                                                title={t('global.view_details')}
                                                href={item.urls.show}
                                                colorClass="text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                            />
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SectionEmptyState message={t('global.eye_glasses_no_orders')} />
                    )}
                </>
            )}

            <Modal show={open} onClose={() => setOpen(false)} size="lg">
                <ModalHeader>{t('global.eye_glasses_order')}</ModalHeader>
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
                            <Label>{t('global.eye_glasses_request_date')} *</Label>
                            <PersianDateInput
                                value={form.request_date}
                                onChange={(request_date) => setForm((current) => ({ ...current, request_date }))}
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.eye_glasses_frame_type')}</Label>
                            <Select
                                value={form.frame_type}
                                onChange={(event) => setForm((current) => ({ ...current, frame_type: event.target.value }))}
                            >
                                <option value="">{t('global.please_select')}</option>
                                {frameTypes.map((value) => (
                                    <option key={value} value={value}>
                                        {optionLabel('frame', value)}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.eye_glasses_lens_type')}</Label>
                            <Select
                                value={form.lens_type}
                                onChange={(event) => setForm((current) => ({ ...current, lens_type: event.target.value }))}
                            >
                                <option value="">{t('global.please_select')}</option>
                                {lensTypes.map((value) => (
                                    <option key={value} value={value}>
                                        {optionLabel('lens', value)}
                                    </option>
                                ))}
                            </Select>
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
