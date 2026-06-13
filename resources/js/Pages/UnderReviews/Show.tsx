import { Head, Link, router } from '@inertiajs/react';
import {
    Badge,
    Button,
    Card,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    Textarea,
} from 'flowbite-react';
import { FormEvent, useState } from 'react';
import BloodBankSection from '../../Components/Appointments/Sections/BloodBankSection';
import LabTestSection from '../../Components/Appointments/Sections/LabTestSection';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import PhysiotherapySection from '../../Components/Appointments/Sections/PhysiotherapySection';
import HospitalizationSection from '../../Components/Appointments/Sections/HospitalizationSection';
import AppointmentSectionAccordion, {
    SectionEmptyState,
} from '../../Components/Appointments/Sections/AppointmentSectionAccordion';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import BackArrowIcon from '../../Components/ui/BackArrowIcon';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import TableActionButton from '../../Components/ui/TableActionButton';
import { useTranslation } from '../../hooks/useTranslation';
import {
    UnderReviewDetail,
    UnderReviewSectionPermissions,
    UnderReviewShowPermissions,
} from '../../types/underReview';

interface ShowProps {
    underReview: UnderReviewDetail;
    permissions: UnderReviewShowPermissions;
    sectionPermissions: UnderReviewSectionPermissions;
    urls: {
        index: string;
        edit: string;
        discharge: string;
        visit_store: string;
        visit_update: string;
        appointment: string | null;
    };
}

export default function UnderReviewsShow({
    underReview,
    permissions,
    sectionPermissions,
    urls,
}: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [dischargeOpen, setDischargeOpen] = useState(false);
    const [dischargeRemark, setDischargeRemark] = useState('');
    const [visitDescription, setVisitDescription] = useState('');
    const [editingVisitId, setEditingVisitId] = useState<number | null>(null);
    const [editingVisitDescription, setEditingVisitDescription] = useState('');

    const patientLabel = underReview.patient?.name ?? `#${underReview.id}`;
    const hasAppointment = Boolean(underReview.appointment_id);

    const post = (url: string, data: Record<string, string>, onSuccess?: () => void) => {
        setProcessing(true);
        router.post(url, data, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onFinish: () => setProcessing(false),
        });
    };

    const put = (url: string, data: Record<string, string>, onSuccess?: () => void) => {
        setProcessing(true);
        router.put(url, data, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onFinish: () => setProcessing(false),
        });
    };

    const handleDischarge = (event: FormEvent) => {
        event.preventDefault();
        post(urls.discharge, { discharge_remark: dischargeRemark }, () => {
            setDischargeOpen(false);
            setDischargeRemark('');
        });
    };

    const handleAddVisit = (event: FormEvent) => {
        event.preventDefault();
        if (!visitDescription.trim()) {
            return;
        }
        post(urls.visit_store, { description: visitDescription }, () => setVisitDescription(''));
    };

    const handleUpdateVisit = (event: FormEvent) => {
        event.preventDefault();
        if (!editingVisitId || !editingVisitDescription.trim()) {
            return;
        }
        put(`${urls.visit_update}/${editingVisitId}`, { description: editingVisitDescription }, () => {
            setEditingVisitId(null);
            setEditingVisitDescription('');
        });
    };

    const summaryFields = [
        { label: t('global.patient_name'), value: underReview.patient?.name, icon: 'bx-user' },
        { label: t('global.referred_to'), value: underReview.doctor_name, icon: 'bx-user-check' },
        { label: t('global.date'), value: underReview.admission_date, icon: 'bx-calendar' },
        { label: t('global.time'), value: underReview.admission_time, icon: 'bx-time' },
    ];

    return (
        <DashboardLayout>
            <Head title={patientLabel} />

            <div className="mx-auto max-w-6xl space-y-6">
                <Card className="border !shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h1 className="flex items-center gap-2 text-xl text-gray-900 dark:text-white">
                                <i className="bx bx-revision text-cyan-500" />
                                {t('global.under_review_details')}
                            </h1>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                #{underReview.id}
                            </p>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            {underReview.is_discharged ? (
                                <Badge color="gray">{t('global.discharged')}</Badge>
                            ) : (
                                <Badge color="success">{t('global.active')}</Badge>
                            )}
                            {permissions.discharge && (
                                <Button size="sm" color="info" onClick={() => setDischargeOpen(true)}>
                                    <i className="bx bx-log-out me-2" />
                                    {t('global.discharge_patient')}
                                </Button>
                            )}
                            {permissions.edit && (
                                <Button size="sm" color="light" as={Link} href={urls.edit}>
                                    <i className="bx bx-edit me-2" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            {urls.appointment && (
                                <Button size="sm" color="light" as={Link} href={urls.appointment}>
                                    <i className="bx bx-calendar me-2" />
                                    {t('global.appointment')}
                                </Button>
                            )}
                            <Button size="sm" color="light" as={Link} href={urls.index}>
                                <BackArrowIcon className="me-2" />
                                {t('global.back')}
                            </Button>
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {summaryFields.map((field) => (
                            <div
                                key={field.label}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-4 text-center dark:border-gray-700/60 dark:bg-gray-800/40"
                            >
                                <p className="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {field.label}
                                </p>
                                <p className="mt-2 flex items-center justify-center gap-1 text-sm text-gray-900 dark:text-white">
                                    <i className={`bx ${field.icon} text-cyan-500`} />
                                    {field.value ?? '—'}
                                </p>
                            </div>
                        ))}
                    </div>
                </Card>

                <Card className="border !shadow-sm">
                    <h2 className="mb-4 flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                        <i className="bx bx-info-circle text-cyan-500" />
                        {t('global.details')}
                    </h2>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {[
                            {
                                label: t('global.card_number'),
                                value: underReview.patient?.id_card,
                            },
                            { label: t('global.phone'), value: underReview.patient?.phone },
                            { label: t('global.room'), value: underReview.room_name },
                            {
                                label: t('global.bed'),
                                value: underReview.bed_number ? String(underReview.bed_number) : null,
                            },
                        ].map((field) => (
                            <div
                                key={field.label}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-4 text-center dark:border-gray-700/60 dark:bg-gray-800/40"
                            >
                                <p className="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {field.label}
                                </p>
                                <p className="mt-2 text-sm text-gray-900 dark:text-white">
                                    {field.value ?? '—'}
                                </p>
                            </div>
                        ))}
                    </div>
                    <div className="mt-4 grid gap-4 lg:grid-cols-2">
                        <div>
                            <h3 className="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-center text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                                {t('global.reason')}
                            </h3>
                            <p className="rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-300">
                                {underReview.reason || '—'}
                            </p>
                        </div>
                        <div>
                            <h3 className="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">
                                {t('global.remarks')}
                            </h3>
                            <p className="rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-300">
                                {underReview.remarks || '—'}
                            </p>
                        </div>
                    </div>
                    {underReview.is_discharged && underReview.discharge_remark && (
                        <div className="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-300">
                            <p className="font-medium text-gray-900 dark:text-white">
                                {t('global.discharge_remark')}
                            </p>
                            <p className="mt-1">{underReview.discharge_remark}</p>
                        </div>
                    )}
                </Card>

                <div className="space-y-4">
                    {hasAppointment ? (
                        <>
                            {sectionPermissions.blood && (
                                <BloodBankSection appointmentId={underReview.appointment_id!} />
                            )}
                            {sectionPermissions.prescription && (
                                <PrescriptionSection
                                    appointmentId={underReview.appointment_id!}
                                    underReviewId={underReview.id}
                                />
                            )}
                            {sectionPermissions.lab && (
                                <LabTestSection appointmentId={underReview.appointment_id!} />
                            )}
                            {sectionPermissions.physiotherapy && (
                                <PhysiotherapySection appointmentId={underReview.appointment_id!} />
                            )}
                            {sectionPermissions.hospitalization && (
                                <HospitalizationSection appointmentId={underReview.appointment_id!} />
                            )}
                        </>
                    ) : (
                        <SectionEmptyState message={t('global.not_available')} />
                    )}

                    <AppointmentSectionAccordion
                        id="under-review-visits"
                        icon="bx-glasses"
                        iconClassName="text-violet-500"
                        title={t('global.visits')}
                        count={underReview.visits.length}
                        badgeColor="info"
                        defaultOpen
                    >
                        {permissions.store_visit && (
                            <form onSubmit={handleAddVisit} className="mb-4 space-y-3">
                                <div>
                                    <Label htmlFor="visit-description">{t('global.description')}</Label>
                                    <Textarea
                                        id="visit-description"
                                        rows={2}
                                        value={visitDescription}
                                        onChange={(e) => setVisitDescription(e.target.value)}
                                    />
                                </div>
                                <Button type="submit" color="blue" size="sm" disabled={processing}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.add_visit')}
                                </Button>
                            </form>
                        )}

                        <Table embedded>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.description')}</TableHeader>
                                    <TableHeader>{t('global.by')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {underReview.visits.length === 0 ? (
                                    <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                        <TableCell colSpan={4} align="center" muted className="py-8">
                                            {t('global.no_previous_visits')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    underReview.visits.map((visit, index) => (
                                        <TableRow key={visit.id}>
                                            <TableCell>{index + 1}</TableCell>
                                            <TableCell>
                                                {editingVisitId === visit.id ? (
                                                    <form
                                                        onSubmit={handleUpdateVisit}
                                                        className="flex flex-col gap-2 sm:flex-row"
                                                    >
                                                        <Textarea
                                                            rows={2}
                                                            className="min-w-0 flex-1"
                                                            value={editingVisitDescription}
                                                            onChange={(e) =>
                                                                setEditingVisitDescription(e.target.value)
                                                            }
                                                        />
                                                        <div className="flex shrink-0 gap-1">
                                                            <Button
                                                                type="submit"
                                                                size="xs"
                                                                color="blue"
                                                                disabled={processing}
                                                            >
                                                                {t('global.save')}
                                                            </Button>
                                                            <Button
                                                                type="button"
                                                                size="xs"
                                                                color="light"
                                                                onClick={() => setEditingVisitId(null)}
                                                            >
                                                                {t('global.cancel')}
                                                            </Button>
                                                        </div>
                                                    </form>
                                                ) : (
                                                    visit.description
                                                )}
                                            </TableCell>
                                            <TableCell muted>{visit.doctor_name ?? '—'}</TableCell>
                                            <TableCell align="center">
                                                {permissions.edit_visit && editingVisitId !== visit.id && (
                                                    <TableActionButton
                                                        kind="edit"
                                                        title={t('global.edit')}
                                                        onClick={() => {
                                                            setEditingVisitId(visit.id);
                                                            setEditingVisitDescription(
                                                                visit.description ?? ''
                                                            );
                                                        }}
                                                    />
                                                )}
                                                {permissions.delete_visit && (
                                                    <TableActionButton
                                                        kind="delete"
                                                        confirm={t('global.confirm_delete')}
                                                        disabled={processing}
                                                        onClick={() =>
                                                            router.delete(
                                                                `${urls.visit_update}/${visit.id}`,
                                                                { preserveScroll: true }
                                                            )
                                                        }
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </AppointmentSectionAccordion>

                    <AppointmentSectionAccordion
                        id="under-review-mar"
                        icon="bx-capsule"
                        iconClassName="text-emerald-500"
                        title={t('global.medication_administration_records')}
                        count={underReview.medication_records.length}
                        badgeColor="success"
                    >
                        <Table embedded>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.date')}</TableHeader>
                                    <TableHeader>{t('global.medicine')}</TableHeader>
                                    <TableHeader>{t('global.nurse')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {underReview.medication_records.length === 0 ? (
                                    <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                        <TableCell colSpan={3} align="center" muted className="py-8">
                                            {t('global.no_records_found')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    underReview.medication_records.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell muted>{row.order_date ?? '—'}</TableCell>
                                            <TableCell>{row.medicine_name ?? '—'}</TableCell>
                                            <TableCell muted>{row.nurse_name ?? '—'}</TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </AppointmentSectionAccordion>

                    {(underReview.nursing_assessments_count > 0 ||
                        underReview.hospitalizations_count > 0) && (
                        <AppointmentSectionAccordion
                            id="under-review-related"
                            icon="bx-link"
                            iconClassName="text-blue-500"
                            title={t('global.related_record')}
                            count={
                                underReview.nursing_assessments_count +
                                underReview.hospitalizations_count
                            }
                        >
                            <div className="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                {underReview.nursing_assessments_count > 0 && (
                                    <p>
                                        {t('global.nursing_assessment')}:{' '}
                                        <span className="font-medium">
                                            {underReview.nursing_assessments_count}
                                        </span>
                                    </p>
                                )}
                                {underReview.hospitalizations_count > 0 && (
                                    <p>
                                        {t('global.hospitalize')}:{' '}
                                        <span className="font-medium">
                                            {underReview.hospitalizations_count}
                                        </span>
                                    </p>
                                )}
                            </div>
                        </AppointmentSectionAccordion>
                    )}
                </div>
            </div>

            <Modal show={dischargeOpen} onClose={() => !processing && setDischargeOpen(false)} size="md">
                <form onSubmit={handleDischarge}>
                    <ModalHeader>{t('global.discharge_patient')}</ModalHeader>
                    <ModalBody>
                        <div>
                            <Label htmlFor="discharge-remark">{t('global.discharge_remark')}</Label>
                            <Textarea
                                id="discharge-remark"
                                rows={4}
                                required
                                className="mt-2"
                                value={dischargeRemark}
                                onChange={(e) => setDischargeRemark(e.target.value)}
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button
                            type="button"
                            color="light"
                            disabled={processing}
                            onClick={() => setDischargeOpen(false)}
                        >
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="success" disabled={processing}>
                            {processing && <Spinner size="sm" className="me-2" />}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </DashboardLayout>
    );
}
