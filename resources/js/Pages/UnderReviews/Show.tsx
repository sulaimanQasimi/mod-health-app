import { Head, Link, router } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import BloodBankSection from '../../Components/Appointments/Sections/BloodBankSection';
import LabTestSection from '../../Components/Appointments/Sections/LabTestSection';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import PhysiotherapySection from '../../Components/Appointments/Sections/PhysiotherapySection';
import HospitalizationSection from '../../Components/Appointments/Sections/HospitalizationSection';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import UnderReviewSectionPanel, {
    UnderReviewDataTable,
    UnderReviewDataTableBody,
    UnderReviewDataTableHead,
    UnderReviewDataTableRow,
    UnderReviewDataTableTd,
    UnderReviewDataTableTh,
} from '../../Components/UnderReviews/UnderReviewSectionPanel';
import UnderReviewSummary from '../../Components/UnderReviews/UnderReviewSummary';
import {
    UNDER_REVIEW_DISCHARGED_PANEL_CLASS,
    UNDER_REVIEW_MUTED_NOTE_CLASS,
} from '../../Components/UnderReviews/underReviewUi';
import TableActionButton from '../../Components/ui/TableActionButton';
import { useTranslation } from '../../hooks/useTranslation';
import {
    UnderReviewDetail,
    UnderReviewSectionPermissions,
    UnderReviewShowPermissions,
} from '../../types/underReview';
import { SETTINGS_INDEX_WIDTH, settingsHeaderButtonClass } from '../../utils/settingsUi';

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

function ClinicalDataTable({
    headers,
    rows,
    emptyMessage,
}: {
    headers: string[];
    rows: (string | number | null)[][];
    emptyMessage: string;
}) {
    if (rows.length === 0) {
        return <p className={UNDER_REVIEW_MUTED_NOTE_CLASS}>{emptyMessage}</p>;
    }

    return (
        <UnderReviewDataTable>
            <UnderReviewDataTableHead>
                {headers.map((header) => (
                    <UnderReviewDataTableTh key={header}>{header}</UnderReviewDataTableTh>
                ))}
            </UnderReviewDataTableHead>
            <UnderReviewDataTableBody>
                {rows.map((row, index) => (
                    <UnderReviewDataTableRow key={index}>
                        {row.map((cell, cellIndex) => (
                            <UnderReviewDataTableTd
                                key={cellIndex}
                                className={cellIndex > 0 ? 'text-gray-600 dark:text-gray-400' : ''}
                            >
                                {cell ?? '—'}
                            </UnderReviewDataTableTd>
                        ))}
                    </UnderReviewDataTableRow>
                ))}
            </UnderReviewDataTableBody>
        </UnderReviewDataTable>
    );
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

    return (
        <DashboardLayout>
            <Head title={patientLabel} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.under_review_details')}
                    subtitle={`#${underReview.id}`}
                    icon="bx-revision"
                    accent="from-slate-600 to-slate-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        <SettingsPageActions>
                            {permissions.edit && (
                                <Button
                                    as={Link}
                                    href={urls.edit}
                                    size="sm"
                                    className={settingsHeaderButtonClass.secondary}
                                >
                                    <i className="bx bx-edit me-2" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            {permissions.discharge && (
                                <Button
                                    size="sm"
                                    onClick={() => setDischargeOpen(true)}
                                    className={settingsHeaderButtonClass.secondary}
                                >
                                    <i className="bx bx-log-out me-2" />
                                    {t('global.discharge_patient')}
                                </Button>
                            )}
                            {urls.appointment && (
                                <Button
                                    as={Link}
                                    href={urls.appointment}
                                    size="sm"
                                    className={settingsHeaderButtonClass.secondary}
                                >
                                    <i className="bx bx-calendar me-2" />
                                    {t('global.appointment')}
                                </Button>
                            )}
                        </SettingsPageActions>
                    }
                />

                <UnderReviewSummary underReview={underReview} />

                {underReview.is_discharged && (
                    <div className={UNDER_REVIEW_DISCHARGED_PANEL_CLASS}>
                        <p className="font-medium">{t('global.discharge_patient')}</p>
                        {underReview.discharge_remark && (
                            <p className="mt-1">{underReview.discharge_remark}</p>
                        )}
                    </div>
                )}

                {hasAppointment ? (
                    <div className="space-y-4">
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
                    </div>
                ) : (
                    <div className={UNDER_REVIEW_MUTED_NOTE_CLASS}>{t('global.not_available')}</div>
                )}

                <UnderReviewSectionPanel
                    id="under-review-visits"
                    icon="bx-glasses"
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

                    <UnderReviewDataTable>
                        <UnderReviewDataTableHead>
                            <UnderReviewDataTableTh>{t('global.number')}</UnderReviewDataTableTh>
                            <UnderReviewDataTableTh>{t('global.description')}</UnderReviewDataTableTh>
                            <UnderReviewDataTableTh>{t('global.by')}</UnderReviewDataTableTh>
                            <UnderReviewDataTableTh className="w-24 text-end">
                                {t('global.actions')}
                            </UnderReviewDataTableTh>
                        </UnderReviewDataTableHead>
                        <UnderReviewDataTableBody>
                            {underReview.visits.map((visit, index) => (
                                <UnderReviewDataTableRow key={visit.id}>
                                    <UnderReviewDataTableTd>{index + 1}</UnderReviewDataTableTd>
                                    <UnderReviewDataTableTd>
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
                                    </UnderReviewDataTableTd>
                                    <UnderReviewDataTableTd className="text-gray-600 dark:text-gray-400">
                                        {visit.doctor_name ?? '—'}
                                    </UnderReviewDataTableTd>
                                    <UnderReviewDataTableTd className="text-end">
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
                                    </UnderReviewDataTableTd>
                                </UnderReviewDataTableRow>
                            ))}
                            {underReview.visits.length === 0 && (
                                <UnderReviewDataTableRow>
                                    <UnderReviewDataTableTd
                                        colSpan={4}
                                        className="py-8 text-center text-gray-500"
                                    >
                                        {t('global.no_previous_visits')}
                                    </UnderReviewDataTableTd>
                                </UnderReviewDataTableRow>
                            )}
                        </UnderReviewDataTableBody>
                    </UnderReviewDataTable>
                </UnderReviewSectionPanel>

                <div className="space-y-4">
                    <div className="flex items-center gap-2 px-1 pt-1">
                        <i className="bx bx-clinic text-lg text-emerald-600 dark:text-emerald-400" />
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.clinical_findings')}
                        </h3>
                    </div>

                    <UnderReviewSectionPanel
                        id="under-review-mar"
                        icon="bx-capsule"
                        title={t('global.medication_administration_records')}
                        count={underReview.medication_records.length}
                    >
                        <ClinicalDataTable
                            headers={[t('global.date'), t('global.medicine'), t('global.nurse')]}
                            rows={underReview.medication_records.map((row) => [
                                row.order_date,
                                row.medicine_name,
                                row.nurse_name,
                            ])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </UnderReviewSectionPanel>

                    {(underReview.nursing_assessments_count > 0 ||
                        underReview.hospitalizations_count > 0) && (
                        <UnderReviewSectionPanel
                            id="under-review-related"
                            icon="bx-link"
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
                        </UnderReviewSectionPanel>
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
