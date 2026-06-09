import { Head, Link, router } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import LabTestSection from '../../Components/Appointments/Sections/LabTestSection';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import PhysiotherapySection from '../../Components/Appointments/Sections/PhysiotherapySection';
import { SectionShell } from '../../Components/Appointments/Sections/AppointmentSectionAccordion';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import UnderReviewSummary from '../../Components/UnderReviews/UnderReviewSummary';
import TableActionButton from '../../Components/ui/TableActionButton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { UnderReviewDetail, UnderReviewShowPermissions } from '../../types/underReview';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowProps {
    underReview: UnderReviewDetail;
    permissions: UnderReviewShowPermissions;
    urls: {
        index: string;
        edit: string;
        discharge: string;
        visit_store: string;
        visit_update: string;
        legacy_show: string;
        appointment: string | null;
    };
}

function ReadOnlyTable({
    headers,
    rows,
    emptyMessage,
}: {
    headers: string[];
    rows: (string | number | null)[][];
    emptyMessage: string;
}) {
    if (rows.length === 0) {
        return <p className="text-sm text-gray-500">{emptyMessage}</p>;
    }

    return (
        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <Table>
                <TableHeader>
                    <TableRow>
                        {headers.map((header) => (
                            <TableHead key={header}>{header}</TableHead>
                        ))}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {rows.map((row, index) => (
                        <TableRow key={index}>
                            {row.map((cell, cellIndex) => (
                                <TableCell key={cellIndex} className={cellIndex > 0 ? 'text-gray-600' : ''}>
                                    {cell ?? '—'}
                                </TableCell>
                            ))}
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    );
}

export default function UnderReviewsShow({ underReview, permissions, urls }: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [dischargeOpen, setDischargeOpen] = useState(false);
    const [dischargeRemark, setDischargeRemark] = useState('');
    const [visitDescription, setVisitDescription] = useState('');
    const [editingVisitId, setEditingVisitId] = useState<number | null>(null);
    const [editingVisitDescription, setEditingVisitDescription] = useState('');

    const patientLabel = underReview.patient?.name ?? `#${underReview.id}`;

    const post = (url: string, data: Record<string, string>) => {
        setProcessing(true);
        router.post(url, data, { preserveScroll: true, onFinish: () => setProcessing(false) });
    };

    const put = (url: string, data: Record<string, string>) => {
        setProcessing(true);
        router.put(url, data, { preserveScroll: true, onFinish: () => setProcessing(false) });
    };

    const handleDischarge = (event: FormEvent) => {
        event.preventDefault();
        post(urls.discharge, { discharge_remark: dischargeRemark });
        setDischargeOpen(false);
    };

    const handleAddVisit = (event: FormEvent) => {
        event.preventDefault();
        if (!visitDescription.trim()) {
            return;
        }
        post(urls.visit_store, { description: visitDescription });
        setVisitDescription('');
    };

    const handleUpdateVisit = (event: FormEvent) => {
        event.preventDefault();
        if (!editingVisitId || !editingVisitDescription.trim()) {
            return;
        }
        put(`${urls.visit_update}/${editingVisitId}`, { description: editingVisitDescription });
        setEditingVisitId(null);
        setEditingVisitDescription('');
    };

    return (
        <DashboardLayout>
            <Head title={patientLabel} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.under_review_details')}
                    subtitle={patientLabel}
                    icon="bx-revision"
                    accent="from-slate-600 to-slate-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            {permissions.edit && (
                                <Button as={Link} href={urls.edit} color="blue" size="sm">
                                    <i className="bx bx-edit me-2" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            {permissions.discharge && (
                                <Button color="light" size="sm" onClick={() => setDischargeOpen(true)}>
                                    <i className="bx bx-log-out me-2" />
                                    {t('global.discharge_patient')}
                                </Button>
                            )}
                            {urls.appointment && (
                                <Button as={Link} href={urls.appointment} color="light" size="sm">
                                    <i className="bx bx-calendar me-2" />
                                    {t('global.appointment')}
                                </Button>
                            )}
                        </div>
                    }
                />

                <UnderReviewSummary underReview={underReview} />

                {underReview.appointment_id && (
                    <>
                        <PrescriptionSection
                            appointmentId={underReview.appointment_id}
                            underReviewId={underReview.id}
                            embedded
                        />
                        <LabTestSection appointmentId={underReview.appointment_id} embedded />
                    </>
                )}

                {underReview.is_discharged && underReview.discharge_remark && (
                    <div className="rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-emerald-100">
                        <p className="font-medium">{t('global.discharge_remark')}</p>
                        <p className="mt-1">{underReview.discharge_remark}</p>
                    </div>
                )}

                <div className="space-y-4">
                    <SectionShell
                        id="under-review-visits"
                        icon="bx-glasses"
                        iconClassName="text-sky-600"
                        title={t('global.visits')}
                        count={underReview.visits.length}
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

                        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('global.number')}</TableHead>
                                        <TableHead>{t('global.description')}</TableHead>
                                        <TableHead>{t('global.by')}</TableHead>
                                        <TableHead className="w-24 text-end">{t('global.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {underReview.visits.map((visit, index) => (
                                        <TableRow key={visit.id}>
                                            <TableCell>{index + 1}</TableCell>
                                            <TableCell>
                                                {editingVisitId === visit.id ? (
                                                    <form onSubmit={handleUpdateVisit} className="flex gap-2">
                                                        <Textarea
                                                            rows={2}
                                                            className="min-w-[200px]"
                                                            value={editingVisitDescription}
                                                            onChange={(e) => setEditingVisitDescription(e.target.value)}
                                                        />
                                                        <div className="flex flex-col gap-1">
                                                            <Button type="submit" size="xs" color="blue" disabled={processing}>
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
                                            <TableCell className="text-gray-600">{visit.doctor_name ?? '—'}</TableCell>
                                            <TableCell className="text-end">
                                                {permissions.edit_visit && editingVisitId !== visit.id && (
                                                    <TableActionButton
                                                        kind="edit"
                                                        title={t('global.edit')}
                                                        onClick={() => {
                                                            setEditingVisitId(visit.id);
                                                            setEditingVisitDescription(visit.description ?? '');
                                                        }}
                                                    />
                                                )}
                                                {permissions.delete_visit && (
                                                    <TableActionButton
                                                        kind="delete"
                                                        confirm={t('global.confirm_delete')}
                                                        disabled={processing}
                                                        onClick={() =>
                                                            router.delete(`${urls.visit_update}/${visit.id}`, {
                                                                preserveScroll: true,
                                                            })
                                                        }
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {underReview.visits.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={4} className="py-8 text-center text-gray-500">
                                                {t('global.no_previous_visits')}
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </SectionShell>

                    {underReview.appointment_id && (
                        <PhysiotherapySection appointmentId={underReview.appointment_id} />
                    )}

                    <SectionShell
                        id="under-review-diabetes"
                        icon="bx-droplet"
                        iconClassName="text-amber-600"
                        title={t('global.diabetes_charts')}
                        count={underReview.diabetes_charts.length}
                    >
                        <ReadOnlyTable
                            headers={[t('global.date'), t('global.time'), 'RBS', 'FBS', t('global.nurse'), t('global.medicine')]}
                            rows={underReview.diabetes_charts.map((row) => [
                                row.date,
                                row.time,
                                row.rbs,
                                row.fbs,
                                row.nurse_name,
                                row.medicine_name,
                            ])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </SectionShell>

                    <SectionShell
                        id="under-review-nurse-notes"
                        icon="bx-note"
                        iconClassName="text-indigo-600"
                        title={t('global.nurse_notes')}
                        count={underReview.nurse_notes.length}
                    >
                        <ReadOnlyTable
                            headers={[t('global.date'), t('global.morning'), t('global.evening'), t('global.nurse')]}
                            rows={underReview.nurse_notes.map((row) => [
                                row.date,
                                row.note_am,
                                row.note_pm,
                                row.nurse_name,
                            ])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </SectionShell>

                    <SectionShell
                        id="under-review-mar"
                        icon="bx-capsule"
                        iconClassName="text-rose-600"
                        title={t('global.medication_administration_records')}
                        count={underReview.medication_records.length}
                    >
                        <ReadOnlyTable
                            headers={[t('global.date'), t('global.medicine'), t('global.nurse')]}
                            rows={underReview.medication_records.map((row) => [
                                row.order_date,
                                row.medicine_name,
                                row.nurse_name,
                            ])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </SectionShell>

                    <SectionShell
                        id="under-review-vitals"
                        icon="bx-pulse"
                        iconClassName="text-gray-600"
                        title={t('global.vital_signs')}
                        count={underReview.vital_signs.length}
                    >
                        <ReadOnlyTable
                            headers={[t('global.type'), t('global.schedules'), t('global.date')]}
                            rows={underReview.vital_signs.map((row) => [
                                row.type_name,
                                row.schedules_count,
                                row.recorded_at,
                            ])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </SectionShell>

                    <SectionShell
                        id="under-review-nutrition"
                        icon="bx-food-menu"
                        iconClassName="text-gray-500"
                        title={t('global.nutrition_care')}
                        count={underReview.nutrition_cares.length}
                    >
                        <ReadOnlyTable
                            headers={[t('global.date'), t('global.nurse')]}
                            rows={underReview.nutrition_cares.map((row) => [row.date, row.nurse_name])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </SectionShell>

                    {(underReview.nursing_assessments_count > 0 || underReview.hospitalizations_count > 0) && (
                        <div className="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            {underReview.nursing_assessments_count > 0 && (
                                <p>
                                    {t('global.nursing_assessment')}: {underReview.nursing_assessments_count}
                                </p>
                            )}
                            {underReview.hospitalizations_count > 0 && (
                                <p className="mt-1">
                                    {t('global.hospitalize')}: {underReview.hospitalizations_count}
                                </p>
                            )}
                        </div>
                    )}
                </div>
            </div>

            <Modal show={dischargeOpen} onClose={() => setDischargeOpen(false)}>
                <form onSubmit={handleDischarge}>
                    <ModalHeader>{t('global.discharge_patient')}</ModalHeader>
                    <ModalBody>
                        <Label htmlFor="discharge-remark">{t('global.discharge_remark')}</Label>
                        <Textarea
                            id="discharge-remark"
                            rows={3}
                            required
                            className="mt-2"
                            value={dischargeRemark}
                            onChange={(e) => setDischargeRemark(e.target.value)}
                        />
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={() => setDischargeOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={processing}>
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </DashboardLayout>
    );
}
