import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import LabTestSection from '../../Components/Appointments/Sections/LabTestSection';
import BloodBankSection from '../../Components/Appointments/Sections/BloodBankSection';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import HospitalizationOperationSection from '../../Components/Hospitalizations/HospitalizationOperationSection';
import PhysiotherapySection from '../../Components/Appointments/Sections/PhysiotherapySection';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import HospitalizationSummary from '../../Components/Hospitalizations/HospitalizationSummary';
import HospitalizationDiabetesChartSection from '../../Components/Hospitalizations/HospitalizationDiabetesChartSection';
import HospitalizationNurseNoteSection from '../../Components/Hospitalizations/HospitalizationNurseNoteSection';
import HospitalizationAnesthesiaSection from '../../Components/Hospitalizations/HospitalizationAnesthesiaSection';
import HospitalizationIcuSection from '../../Components/Hospitalizations/HospitalizationIcuSection';
import HospitalizationPacuSection from '../../Components/Hospitalizations/HospitalizationPacuSection';
import HospitalizationNutritionCareSection from '../../Components/Hospitalizations/HospitalizationNutritionCareSection';
import HospitalizationVisitSection from '../../Components/Hospitalizations/HospitalizationVisitSection';
import HospitalizationVitalSignSection from '../../Components/Hospitalizations/HospitalizationVitalSignSection';
import {
    dischargeStatusBadgeColor,
    HOSPITALIZATION_DISCHARGED_PANEL_CLASS,
    HOSPITALIZATION_MUTED_NOTE_CLASS,
} from '../../Components/Hospitalizations/hospitalizationUi';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import UnderReviewSectionPanel, {
    UnderReviewDataTable,
    UnderReviewDataTableBody,
    UnderReviewDataTableHead,
    UnderReviewDataTableRow,
    UnderReviewDataTableTd,
    UnderReviewDataTableTh,
} from '../../Components/UnderReviews/UnderReviewSectionPanel';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { HospitalizationDetail, HospitalizationShowPermissions } from '../../types/hospitalization';
import { SETTINGS_INDEX_WIDTH, settingsHeaderButtonClass } from '../../utils/settingsUi';

interface ShowProps {
    hospitalization: HospitalizationDetail;
    permissions: HospitalizationShowPermissions;
    sectionPermissions: {
        prescription: boolean;
        lab: boolean;
        blood: boolean;
        physiotherapy: boolean;
        vital_signs: boolean;
        visits: boolean;
        diabetes_charts: boolean;
        nurse_notes: boolean;
        nutrition_cares: boolean;
        icu: boolean;
        pacu: boolean;
        anesthesia: boolean;
        operations: boolean;
    };
    urls: {
        index: string;
        edit: string;
        discharge: string;
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

    return (
        <DashboardLayout>
            <Head title={patientLabel} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={patientLabel}
                    subtitle={[
                        `#${hospitalization.id}`,
                        hospitalization.department_name,
                        hospitalization.room_name && hospitalization.bed_number
                            ? `${hospitalization.room_name} / ${hospitalization.bed_number}`
                            : null,
                    ]
                        .filter(Boolean)
                        .join(' · ')}
                    icon="bx-bed"
                    accent="from-emerald-600 to-teal-700"
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
                            {permissions.change_room_bed && (
                                <Button
                                    as="a"
                                    href={urls.change_room_bed}
                                    size="sm"
                                    className={settingsHeaderButtonClass.warning}
                                >
                                    <i className="bx bx-transfer me-2" />
                                    {t('global.change_room_bed')}
                                </Button>
                            )}
                            {permissions.discharge && (
                                <Button
                                    size="sm"
                                    onClick={() => setDischargeOpen(true)}
                                    className={settingsHeaderButtonClass.danger}
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

                <HospitalizationSummary hospitalization={hospitalization} />

                {hospitalization.is_discharged && (
                    <div className={HOSPITALIZATION_DISCHARGED_PANEL_CLASS}>
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-200/60 dark:bg-amber-900/40">
                            <i className="bx bx-info-circle text-lg text-amber-800 dark:text-amber-200" />
                        </div>
                        <div className="min-w-0 flex-1">
                            <div className="flex flex-wrap items-center gap-2">
                                <p className="font-semibold">{t('global.discharged')}</p>
                                {hospitalization.discharge_status && (
                                    <Badge color={dischargeStatusBadgeColor(hospitalization.discharge_status)}>
                                        {t(`global.${hospitalization.discharge_status}` as 'global.recovered')}
                                    </Badge>
                                )}
                            </div>
                            {hospitalization.discharge_remark && (
                                <p className="mt-1 text-amber-900/90 dark:text-amber-100/90">
                                    {hospitalization.discharge_remark}
                                </p>
                            )}
                            {hospitalization.discharged_at && (
                                <p className="mt-1 text-xs opacity-80">
                                    {t('global.discharge_date')}: {hospitalization.discharged_at}
                                </p>
                            )}
                        </div>
                    </div>
                )}

                {hasAppointment && (
                    <div className="space-y-4">
                        {sectionPermissions.blood && (
                            <BloodBankSection appointmentId={hospitalization.appointment_id!} />
                        )}
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

                {sectionPermissions.visits && (
                    <HospitalizationVisitSection
                        hospitalizationId={hospitalization.id}
                        isDischarged={hospitalization.is_discharged}
                    />
                )}

                {sectionPermissions.diabetes_charts && (
                    <HospitalizationDiabetesChartSection
                        hospitalizationId={hospitalization.id}
                        isDischarged={hospitalization.is_discharged}
                    />
                )}

                {sectionPermissions.nurse_notes && (
                    <HospitalizationNurseNoteSection
                        hospitalizationId={hospitalization.id}
                        isDischarged={hospitalization.is_discharged}
                    />
                )}

                {sectionPermissions.nutrition_cares && (
                    <HospitalizationNutritionCareSection
                        hospitalizationId={hospitalization.id}
                        isDischarged={hospitalization.is_discharged}
                    />
                )}

                {sectionPermissions.icu && (
                    <HospitalizationIcuSection
                        hospitalizationId={hospitalization.id}
                        isDischarged={hospitalization.is_discharged}
                    />
                )}

                {sectionPermissions.pacu && (
                    <HospitalizationPacuSection
                        hospitalizationId={hospitalization.id}
                        isDischarged={hospitalization.is_discharged}
                    />
                )}

                {sectionPermissions.anesthesia && (
                    <HospitalizationAnesthesiaSection
                        hospitalizationId={hospitalization.id}
                        isDischarged={hospitalization.is_discharged}
                    />
                )}

                {sectionPermissions.operations && (
                    <HospitalizationOperationSection
                        hospitalizationId={hospitalization.id}
                        isDischarged={hospitalization.is_discharged}
                    />
                )}

                <div className="space-y-4">
                    <div className="flex items-center gap-2 px-1 pt-1">
                        <i className="bx bx-clinic text-lg text-emerald-600 dark:text-emerald-400" />
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.clinical_findings')}
                        </h3>
                    </div>

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

                    {(hospitalization.nursing_assessments_count > 0 ||
                        hospitalization.advices_count > 0 ||
                        hospitalization.complaints_count > 0 ||
                        hospitalization.anesthesia_count > 0) && (
                        <UnderReviewSectionPanel
                            id="hospitalization-related"
                            icon="bx-link"
                            title={t('global.related_record')}
                            count={
                                hospitalization.nursing_assessments_count +
                                hospitalization.advices_count +
                                hospitalization.complaints_count +
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

            <Modal show={dischargeOpen} onClose={() => setDischargeOpen(false)} size="md">
                <form onSubmit={handleDischarge}>
                    <ModalHeader>
                        <span className="flex items-center gap-2">
                            <i className="bx bx-log-out text-emerald-600" />
                            {t('global.discharge_patient')}
                        </span>
                    </ModalHeader>
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
