import { Head, Link, router } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import LabTestSection from '../../Components/Appointments/Sections/LabTestSection';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import PhysiotherapySection from '../../Components/Appointments/Sections/PhysiotherapySection';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import HospitalizationSummary from '../../Components/Hospitalizations/HospitalizationSummary';
import HospitalizationVitalSignSection from '../../Components/Hospitalizations/HospitalizationVitalSignSection';
import {
    HOSPITALIZATION_DISCHARGED_PANEL_CLASS,
    HOSPITALIZATION_MUTED_NOTE_CLASS,
} from '../../Components/Hospitalizations/hospitalizationUi';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import UnderReviewSectionPanel, {
    UnderReviewDataTable,
    UnderReviewDataTableBody,
    UnderReviewDataTableHead,
    UnderReviewDataTableRow,
    UnderReviewDataTableTd,
    UnderReviewDataTableTh,
} from '../../Components/UnderReviews/UnderReviewSectionPanel';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import TableActionButton from '../../Components/ui/TableActionButton';
import { useTranslation } from '../../hooks/useTranslation';
import { HospitalizationDetail, HospitalizationShowPermissions } from '../../types/hospitalization';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowProps {
    hospitalization: HospitalizationDetail;
    permissions: HospitalizationShowPermissions;
    sectionPermissions: {
        prescription: boolean;
        lab: boolean;
        physiotherapy: boolean;
        vital_signs: boolean;
    };
    urls: {
        index: string;
        edit: string;
        discharge: string;
        visit_store: string;
        visit_update: string;
        appointment: string | null;
        change_room_bed: string;
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
        return <p className={HOSPITALIZATION_MUTED_NOTE_CLASS}>{emptyMessage}</p>;
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

const DISCHARGE_STATUS_OPTIONS = ['recovered', 'died', 'moved'] as const;

export default function HospitalizationsShow({
    hospitalization,
    permissions,
    sectionPermissions,
    urls,
}: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [dischargeOpen, setDischargeOpen] = useState(false);
    const [dischargeRemark, setDischargeRemark] = useState('');
    const [dischargeStatus, setDischargeStatus] = useState('');
    const [visitDescription, setVisitDescription] = useState('');
    const [editingVisitId, setEditingVisitId] = useState<number | null>(null);
    const [editingVisitDescription, setEditingVisitDescription] = useState('');

    const patientLabel = hospitalization.patient?.name ?? `#${hospitalization.id}`;
    const hasAppointment = Boolean(hospitalization.appointment_id);

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
        post(
            urls.discharge,
            { discharge_remark: dischargeRemark, discharge_status: dischargeStatus },
            () => {
                setDischargeOpen(false);
                setDischargeRemark('');
                setDischargeStatus('');
            }
        );
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
                    title={t('global.hospitalization_details')}
                    subtitle={`#${hospitalization.id}`}
                    icon="bx-bed"
                    accent="from-emerald-600 to-emerald-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            {permissions.edit && (
                                <Button as={Link} href={urls.edit} color="success" size="sm">
                                    <i className="bx bx-edit me-2" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            {permissions.change_room_bed && (
                                <Button as="a" href={urls.change_room_bed} color="warning" size="sm">
                                    <i className="bx bx-transfer me-2" />
                                    {t('global.change_room_bed')}
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

                <HospitalizationSummary hospitalization={hospitalization} />

                {hospitalization.is_discharged && (
                    <div className={HOSPITALIZATION_DISCHARGED_PANEL_CLASS}>
                        {hospitalization.discharge_status && (
                            <p className="font-medium">
                                {t('global.discharge_status')}:{' '}
                                {t(`global.${hospitalization.discharge_status}` as 'global.recovered')}
                            </p>
                        )}
                        {hospitalization.discharge_remark && (
                            <p className="mt-1">
                                {t('global.discharge_remark')}: {hospitalization.discharge_remark}
                            </p>
                        )}
                        {hospitalization.discharged_at && (
                            <p className="mt-1 text-sm opacity-80">
                                {t('global.discharge_date')}: {hospitalization.discharged_at}
                            </p>
                        )}
                    </div>
                )}

                {hasAppointment && (
                    <div className="space-y-4">
                        {sectionPermissions.prescription && (
                            <PrescriptionSection appointmentId={hospitalization.appointment_id!} />
                        )}
                        {sectionPermissions.lab && (
                            <LabTestSection appointmentId={hospitalization.appointment_id!} />
                        )}
                        {sectionPermissions.physiotherapy && (
                            <PhysiotherapySection appointmentId={hospitalization.appointment_id!} />
                        )}
                    </div>
                )}

                {!hasAppointment && (
                    <div className={HOSPITALIZATION_MUTED_NOTE_CLASS}>{t('global.not_available')}</div>
                )}

                {sectionPermissions.vital_signs && (
                    <HospitalizationVitalSignSection
                        hospitalizationId={hospitalization.id}
                        isDischarged={hospitalization.is_discharged}
                    />
                )}

                <div className="space-y-4">
                    <UnderReviewSectionPanel
                        id="hospitalization-blood-bank"
                        icon="bx-donate-blood"
                        title={t('global.request_blood')}
                        count={hospitalization.blood_banks.length}
                    >
                        <ClinicalDataTable
                            headers={[t('global.blood_group'), t('global.date')]}
                            rows={hospitalization.blood_banks.map((row) => [row.group, row.created_at])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </UnderReviewSectionPanel>

                    <UnderReviewSectionPanel
                        id="hospitalization-visits"
                        icon="bx-glasses"
                        title={t('global.visits')}
                        count={hospitalization.visits.length}
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
                                <Button type="submit" color="success" size="sm" disabled={processing}>
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
                                {hospitalization.visits.map((visit, index) => (
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
                                                            color="success"
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
                                {hospitalization.visits.length === 0 && (
                                    <UnderReviewDataTableRow>
                                        <UnderReviewDataTableTd colSpan={4} className="py-8 text-center text-gray-500">
                                            {t('global.no_previous_visits')}
                                        </UnderReviewDataTableTd>
                                    </UnderReviewDataTableRow>
                                )}
                            </UnderReviewDataTableBody>
                        </UnderReviewDataTable>
                    </UnderReviewSectionPanel>

                    <UnderReviewSectionPanel
                        id="hospitalization-diabetes"
                        icon="bx-droplet"
                        title={t('global.diabetes_charts')}
                        count={hospitalization.diabetes_charts.length}
                    >
                        <ClinicalDataTable
                            headers={[
                                t('global.date'),
                                t('global.time'),
                                'RBS',
                                'FBS',
                                t('global.nurse'),
                                t('global.medicine'),
                            ]}
                            rows={hospitalization.diabetes_charts.map((row) => [
                                row.date,
                                row.time,
                                row.rbs,
                                row.fbs,
                                row.nurse_name,
                                row.medicine_name,
                            ])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </UnderReviewSectionPanel>

                    <UnderReviewSectionPanel
                        id="hospitalization-nurse-notes"
                        icon="bx-note"
                        title={t('global.nurse_notes')}
                        count={hospitalization.nurse_notes.length}
                    >
                        <ClinicalDataTable
                            headers={[
                                t('global.date'),
                                t('global.morning'),
                                t('global.evening'),
                                t('global.nurse'),
                            ]}
                            rows={hospitalization.nurse_notes.map((row) => [
                                row.date,
                                row.note_am,
                                row.note_pm,
                                row.nurse_name,
                            ])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </UnderReviewSectionPanel>

                    <UnderReviewSectionPanel
                        id="hospitalization-mar"
                        icon="bx-capsule"
                        title={t('global.medication_administration_records')}
                        count={hospitalization.medication_records.length}
                    >
                        <ClinicalDataTable
                            headers={[t('global.date'), t('global.medicine'), t('global.nurse')]}
                            rows={hospitalization.medication_records.map((row) => [
                                row.order_date,
                                row.medicine_name,
                                row.nurse_name,
                            ])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </UnderReviewSectionPanel>

                    <UnderReviewSectionPanel
                        id="hospitalization-nutrition"
                        icon="bx-food-menu"
                        title={t('global.nutrition_care')}
                        count={hospitalization.nutrition_cares.length}
                    >
                        <ClinicalDataTable
                            headers={[t('global.date'), t('global.nurse')]}
                            rows={hospitalization.nutrition_cares.map((row) => [row.date, row.nurse_name])}
                            emptyMessage={t('global.no_records_found')}
                        />
                    </UnderReviewSectionPanel>

                    {(hospitalization.nursing_assessments_count > 0 ||
                        hospitalization.advices_count > 0 ||
                        hospitalization.complaints_count > 0 ||
                        hospitalization.icu_count > 0 ||
                        hospitalization.anesthesia_count > 0) && (
                        <UnderReviewSectionPanel
                            id="hospitalization-related"
                            icon="bx-link"
                            title={t('global.related_record')}
                            count={
                                hospitalization.nursing_assessments_count +
                                hospitalization.advices_count +
                                hospitalization.complaints_count +
                                hospitalization.icu_count +
                                hospitalization.anesthesia_count
                            }
                        >
                            <div className="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                {hospitalization.nursing_assessments_count > 0 && (
                                    <p>
                                        {t('global.nursing_assessment')}:{' '}
                                        <span className="font-medium">
                                            {hospitalization.nursing_assessments_count}
                                        </span>
                                    </p>
                                )}
                                {hospitalization.advices_count > 0 && (
                                    <p>
                                        {t('global.advice')}:{' '}
                                        <span className="font-medium">{hospitalization.advices_count}</span>
                                    </p>
                                )}
                                {hospitalization.complaints_count > 0 && (
                                    <p>
                                        {t('global.add_complaint')}:{' '}
                                        <span className="font-medium">{hospitalization.complaints_count}</span>
                                    </p>
                                )}
                                {hospitalization.icu_count > 0 && (
                                    <p>
                                        ICU: <span className="font-medium">{hospitalization.icu_count}</span>
                                    </p>
                                )}
                                {hospitalization.anesthesia_count > 0 && (
                                    <p>
                                        {t('global.anesthesias')}:{' '}
                                        <span className="font-medium">{hospitalization.anesthesia_count}</span>
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
                    <ModalBody className="space-y-4">
                        <div>
                            <Label htmlFor="discharge-status">{t('global.discharge_status')}</Label>
                            <SearchableSelect
                                id="discharge-status"
                                required
                                value={dischargeStatus}
                                onChange={setDischargeStatus}
                                options={DISCHARGE_STATUS_OPTIONS.map((status) => ({
                                    value: status,
                                    label: t(`global.${status}` as 'global.recovered'),
                                }))}
                                placeholder={t('global.select')}
                            />
                        </div>
                        <div>
                            <Label htmlFor="discharge-remark">{t('global.discharge_remark')}</Label>
                            <Textarea
                                id="discharge-remark"
                                rows={3}
                                required
                                className="mt-2"
                                value={dischargeRemark}
                                onChange={(e) => setDischargeRemark(e.target.value)}
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={() => setDischargeOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="success" disabled={processing}>
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </DashboardLayout>
    );
}
