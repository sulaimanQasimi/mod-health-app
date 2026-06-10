import { Head, router } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import LabTestSection from '../../Components/Appointments/Sections/LabTestSection';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import HospitalizationVisitSection from '../../Components/Hospitalizations/HospitalizationVisitSection';
import IcuDailyProgressSection from '../../Components/Icus/IcuDailyProgressSection';
import IcuDischargeModal from '../../Components/Icus/IcuDischargeModal';
import IcuProcedureSection from '../../Components/Icus/IcuProcedureSection';
import IcuSummary from '../../Components/Icus/IcuSummary';
import {
    ICU_APPROVE_BTN_CLASS,
    ICU_DISCHARGE_BTN_CLASS,
    ICU_PENDING_PANEL_CLASS,
    ICU_PRINT_BTN_CLASS,
    ICU_REJECT_BTN_CLASS,
} from '../../Components/Icus/icuUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import { IcuDetail, IcuListUrls, IcuShowPermissions } from '../../types/icu';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowProps {
    icu: IcuDetail;
    permissions: IcuShowPermissions;
    sectionPermissions: {
        prescription: boolean;
        lab: boolean;
        visits: boolean;
        procedures: boolean;
        daily_progress: boolean;
    };
    urls: IcuListUrls & {
        update: string;
        destroy: string;
        back: string;
        appointment: string | null;
        print_death_card: string;
        print_move_card: string;
        discharge_meta: string;
    };
}

export default function IcusShow({ icu, permissions, sectionPermissions, urls }: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [approveOpen, setApproveOpen] = useState(false);
    const [rejectOpen, setRejectOpen] = useState(false);
    const [dischargeOpen, setDischargeOpen] = useState(false);
    const [entranceNote, setEntranceNote] = useState('');
    const [rejectReason, setRejectReason] = useState('');

    const patientLabel = icu.patient?.name ?? `#${icu.id}`;
    const hasAppointment = Boolean(icu.appointment_id);
    const showPendingActions = icu.status === 'new' && (permissions.approve || permissions.reject);
    const locationSubtitle = [icu.room_name, icu.bed_number].filter(Boolean).join(' / ');

    const put = (data: Record<string, string>, onSuccess?: () => void) => {
        setProcessing(true);
        router.put(urls.update, data, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onFinish: () => setProcessing(false),
        });
    };

    const handleApprove = (event: FormEvent) => {
        event.preventDefault();
        put(
            {
                status: 'approved',
                icu_enterance_note: entranceNote,
            },
            () => {
                setApproveOpen(false);
                setEntranceNote('');
            }
        );
    };

    const handleReject = (event: FormEvent) => {
        event.preventDefault();
        put(
            {
                status: 'rejected',
                icu_reject_reason: rejectReason,
            },
            () => {
                setRejectOpen(false);
                setRejectReason('');
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
                        `#${icu.id}`,
                        icu.department_name,
                        locationSubtitle || null,
                    ]
                        .filter(Boolean)
                        .join(' · ')}
                    icon="bx-plus-medical"
                    accent="from-rose-600 to-red-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            {permissions.approve && (
                                <button
                                    type="button"
                                    className={ICU_APPROVE_BTN_CLASS}
                                    onClick={() => setApproveOpen(true)}
                                >
                                    <i className="bx bx-check text-lg" />
                                    {t('global.approve')}
                                </button>
                            )}
                            {permissions.reject && (
                                <button
                                    type="button"
                                    className={ICU_REJECT_BTN_CLASS}
                                    onClick={() => setRejectOpen(true)}
                                >
                                    <i className="bx bx-x text-lg" />
                                    {t('global.reject')}
                                </button>
                            )}
                            {permissions.discharge && (
                                <button
                                    type="button"
                                    className={ICU_DISCHARGE_BTN_CLASS}
                                    onClick={() => setDischargeOpen(true)}
                                >
                                    <i className="bx bx-log-out text-lg" />
                                    {t('global.discharge')}
                                </button>
                            )}
                            {icu.discharge_status === 'died' && (
                                <a
                                    href={urls.print_death_card}
                                    target="_blank"
                                    rel="noreferrer"
                                    className={ICU_PRINT_BTN_CLASS}
                                >
                                    <i className="bx bx-printer text-lg" />
                                    {t('global.print')}
                                </a>
                            )}
                            {icu.discharge_status === 'moved' && (
                                <a
                                    href={urls.print_move_card}
                                    target="_blank"
                                    rel="noreferrer"
                                    className={ICU_PRINT_BTN_CLASS}
                                >
                                    <i className="bx bx-printer text-lg" />
                                    {t('global.print')}
                                </a>
                            )}
                        </div>
                    }
                />

                <IcuSummary icu={icu} />

                {showPendingActions && (
                    <div className={ICU_PENDING_PANEL_CLASS}>
                        <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex min-w-0 items-start gap-4">
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-md">
                                    <i className="bx bx-glasses text-2xl" />
                                </div>
                                <div className="min-w-0">
                                    <p className="text-base font-bold text-rose-950 dark:text-rose-50">
                                        {t('global.approve_reject_icu')}
                                    </p>
                                    <p className="mt-1 text-sm text-rose-800/80 dark:text-rose-200/80">
                                        {t('global.description')}: {icu.description ?? '—'}
                                    </p>
                                </div>
                            </div>
                            <div className="flex shrink-0 flex-wrap gap-2">
                                {permissions.approve && (
                                    <button
                                        type="button"
                                        className={ICU_APPROVE_BTN_CLASS}
                                        onClick={() => setApproveOpen(true)}
                                    >
                                        <i className="bx bx-check-circle text-lg" />
                                        {t('global.approve')}
                                    </button>
                                )}
                                {permissions.reject && (
                                    <button
                                        type="button"
                                        className={ICU_REJECT_BTN_CLASS}
                                        onClick={() => setRejectOpen(true)}
                                    >
                                        <i className="bx bx-x-circle text-lg" />
                                        {t('global.reject')}
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {icu.status === 'approved' && (
                    <div className="space-y-4">
                        {sectionPermissions.visits && (
                            <HospitalizationVisitSection
                                icuId={icu.id}
                                isDischarged={icu.is_discharged}
                                iconClassName="text-rose-500"
                            />
                        )}
                        {sectionPermissions.procedures && (
                            <IcuProcedureSection
                                icuId={icu.id}
                                isDischarged={icu.is_discharged}
                                iconClassName="text-violet-500"
                            />
                        )}
                        {sectionPermissions.daily_progress && (
                            <IcuDailyProgressSection
                                icuId={icu.id}
                                isDischarged={icu.is_discharged}
                                iconClassName="text-sky-500"
                            />
                        )}
                        {hasAppointment && sectionPermissions.prescription && icu.appointment_id && (
                            <PrescriptionSection appointmentId={icu.appointment_id} />
                        )}
                        {hasAppointment && sectionPermissions.lab && icu.appointment_id && (
                            <LabTestSection appointmentId={icu.appointment_id} />
                        )}
                    </div>
                )}
            </div>

            <Modal show={approveOpen} onClose={() => !processing && setApproveOpen(false)} size="md">
                <div className="overflow-hidden rounded-lg">
                    <div className="bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-4 text-white">
                        <ModalHeader className="border-0 p-0 text-white">
                            <span className="flex items-center gap-2">
                                <i className="bx bx-check-circle text-xl" />
                                {t('global.approve_icu')}
                            </span>
                        </ModalHeader>
                    </div>
                    <form onSubmit={handleApprove}>
                        <ModalBody>
                            <div>
                                <Label htmlFor="icu-entrance-note" className="mb-2 block font-medium">
                                    {t('global.icu_enterance_note')}
                                </Label>
                                <Textarea
                                    id="icu-entrance-note"
                                    rows={4}
                                    placeholder={t('global.icu_enterance_note')}
                                    value={entranceNote}
                                    onChange={(e) => setEntranceNote(e.target.value)}
                                />
                            </div>
                        </ModalBody>
                        <ModalFooter>
                            <Button
                                color="light"
                                disabled={processing}
                                onClick={() => setApproveOpen(false)}
                            >
                                {t('global.cancel')}
                            </Button>
                            <button type="submit" className={ICU_APPROVE_BTN_CLASS} disabled={processing}>
                                {processing ? (
                                    <Spinner size="sm" className="me-2" />
                                ) : (
                                    <i className="bx bx-check me-1 text-lg" />
                                )}
                                {t('global.save')}
                            </button>
                        </ModalFooter>
                    </form>
                </div>
            </Modal>

            <Modal show={rejectOpen} onClose={() => !processing && setRejectOpen(false)} size="md">
                <div className="overflow-hidden rounded-lg">
                    <div className="bg-gradient-to-r from-rose-500 to-red-600 px-6 py-4 text-white">
                        <ModalHeader className="border-0 p-0 text-white">
                            <span className="flex items-center gap-2">
                                <i className="bx bx-x-circle text-xl" />
                                {t('global.reject_icu')}
                            </span>
                        </ModalHeader>
                    </div>
                    <form onSubmit={handleReject}>
                        <ModalBody>
                            <div>
                                <Label htmlFor="icu-reject-reason" className="mb-2 block font-medium">
                                    {t('global.icu_reject_reason')}
                                </Label>
                                <Textarea
                                    id="icu-reject-reason"
                                    rows={4}
                                    placeholder={t('global.icu_reject_reason')}
                                    value={rejectReason}
                                    onChange={(e) => setRejectReason(e.target.value)}
                                />
                            </div>
                        </ModalBody>
                        <ModalFooter>
                            <Button
                                color="light"
                                disabled={processing}
                                onClick={() => setRejectOpen(false)}
                            >
                                {t('global.cancel')}
                            </Button>
                            <button type="submit" className={ICU_REJECT_BTN_CLASS} disabled={processing}>
                                {processing ? (
                                    <Spinner size="sm" className="me-2" />
                                ) : (
                                    <i className="bx bx-x me-1 text-lg" />
                                )}
                                {t('global.save')}
                            </button>
                        </ModalFooter>
                    </form>
                </div>
            </Modal>

            <IcuDischargeModal
                open={dischargeOpen}
                updateUrl={urls.update}
                metaUrl={urls.discharge_meta}
                processing={processing}
                onClose={() => setDischargeOpen(false)}
                onProcessingChange={setProcessing}
            />
        </DashboardLayout>
    );
}
