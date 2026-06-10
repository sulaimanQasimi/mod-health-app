import { Head, router } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import AnesthesiaSummary from '../../Components/Anesthesias/AnesthesiaSummary';
import {
    ANESTHESIA_APPLY_BTN_CLASS,
    ANESTHESIA_APPROVE_BTN_CLASS,
    ANESTHESIA_PENDING_PANEL_CLASS,
    ANESTHESIA_REJECT_BTN_CLASS,
    anesthesiaPatientLabel,
} from '../../Components/Anesthesias/anesthesiaUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import {
    AnesthesiaDetail,
    AnesthesiaDoctorOption,
    AnesthesiaListUrls,
    AnesthesiaShowPermissions,
} from '../../types/anesthesia';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowProps {
    anesthesia: AnesthesiaDetail;
    hospitalDoctors: AnesthesiaDoctorOption[];
    permissions: AnesthesiaShowPermissions;
    sectionPermissions: {
        prescription: boolean;
    };
    urls: AnesthesiaListUrls & {
        update: string;
        referToOperation: string;
        destroy: string;
        edit: string;
        back: string;
        appointment: string | null;
    };
}

export default function AnesthesiasShow({
    anesthesia,
    hospitalDoctors,
    permissions,
    sectionPermissions,
    urls,
}: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [approveOpen, setApproveOpen] = useState(false);
    const [rejectOpen, setRejectOpen] = useState(false);
    const [anesthesiaLogReply, setAnesthesiaLogReply] = useState('');
    const [anesthesiaPlan, setAnesthesiaPlan] = useState(anesthesia.anesthesia_plan ?? '');
    const [anesthesiaType, setAnesthesiaType] = useState(anesthesia.anesthesia_type ?? '');
    const [anesthesiaLogId, setAnesthesiaLogId] = useState(
        String(anesthesia.operation_anesthesia_log_id ?? ''),
    );
    const [anesthesistId, setAnesthesistId] = useState(String(anesthesia.operation_anesthesist_id ?? ''));

    const patientLabel = anesthesiaPatientLabel(anesthesia);
    const showPendingActions = anesthesia.status === 'new' && (permissions.approve || permissions.reject);
    const showReferToOperation = permissions.referToOperation;
    const hasAppointment = Boolean(anesthesia.appointment_id);

    const doctorOptions = useMemo(
        () => hospitalDoctors.map((doctor) => ({ value: String(doctor.id), label: doctor.name })),
        [hospitalDoctors],
    );

    const anesthesiaTypeOptions = useMemo(
        () => [
            { value: 'local', label: t('global.local') },
            { value: 'spinal', label: t('global.spinal') },
            { value: 'general', label: t('global.general') },
        ],
        [t],
    );

    const put = (data: Record<string, string>, onSuccess?: () => void) => {
        setProcessing(true);
        router.put(urls.update, data, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onFinish: () => setProcessing(false),
        });
    };

    const openApprove = () => {
        setAnesthesiaLogReply('');
        setAnesthesiaPlan(anesthesia.anesthesia_plan ?? '');
        setAnesthesiaType(anesthesia.anesthesia_type ?? '');
        setAnesthesiaLogId(String(anesthesia.operation_anesthesia_log_id ?? ''));
        setAnesthesistId(String(anesthesia.operation_anesthesist_id ?? ''));
        setApproveOpen(true);
    };

    const openReject = () => {
        setAnesthesiaLogReply('');
        setAnesthesiaPlan(anesthesia.anesthesia_plan ?? '');
        setRejectOpen(true);
    };

    const handleApprove = (event: FormEvent) => {
        event.preventDefault();
        put(
            {
                status: 'approved',
                anesthesia_log_reply: anesthesiaLogReply,
                anesthesia_plan: anesthesiaPlan,
                anesthesia_type: anesthesiaType,
                operation_anesthesia_log_id: anesthesiaLogId,
                operation_anesthesist_id: anesthesistId,
            },
            () => setApproveOpen(false),
        );
    };

    const handleReject = (event: FormEvent) => {
        event.preventDefault();
        put(
            {
                status: 'rejected',
                anesthesia_log_reply: anesthesiaLogReply,
                anesthesia_plan: anesthesiaPlan,
            },
            () => setRejectOpen(false),
        );
    };

    const handleReferToOperation = () => {
        if (!window.confirm(t('global.refere_to_operation'))) {
            return;
        }

        setProcessing(true);
        router.post(urls.referToOperation, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <DashboardLayout>
            <Head title={patientLabel} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={patientLabel}
                    subtitle={[
                        `#${anesthesia.id}`,
                        anesthesia.operation_type_name,
                        anesthesia.department_name,
                    ]
                        .filter(Boolean)
                        .join(' · ')}
                    icon="bx-plus-medical"
                    accent="from-violet-600 to-indigo-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                    action={
                        <SettingsPageActions>
                            {permissions.approve && anesthesia.status === 'new' && (
                                <button type="button" className={ANESTHESIA_APPROVE_BTN_CLASS} onClick={openApprove}>
                                    <i className="bx bx-check text-lg" />
                                    {t('global.approve')}
                                </button>
                            )}
                            {permissions.reject && anesthesia.status === 'new' && (
                                <button type="button" className={ANESTHESIA_REJECT_BTN_CLASS} onClick={openReject}>
                                    <i className="bx bx-x text-lg" />
                                    {t('global.reject')}
                                </button>
                            )}
                            {showReferToOperation && (
                                <button
                                    type="button"
                                    className={ANESTHESIA_APPLY_BTN_CLASS}
                                    onClick={handleReferToOperation}
                                    disabled={processing}
                                >
                                    <i className="bx bx-cut text-lg" />
                                    {t('global.refere_to_operation')}
                                </button>
                            )}
                        </SettingsPageActions>
                    }
                />

                <AnesthesiaSummary anesthesia={anesthesia} />

                {showPendingActions && (
                    <div className={ANESTHESIA_PENDING_PANEL_CLASS}>
                        <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex min-w-0 items-start gap-4">
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-md">
                                    <i className="bx bx-time-five text-2xl" />
                                </div>
                                <div className="min-w-0">
                                    <p className="text-base font-bold text-violet-950 dark:text-violet-50">
                                        {t('global.anesthesia_pending_review')}
                                    </p>
                                    <p className="mt-1 line-clamp-2 text-sm text-violet-800/80 dark:text-violet-200/80">
                                        {t('global.anesthesia_details')}: {anesthesia.plan ?? '—'}
                                    </p>
                                </div>
                            </div>
                            <div className="flex shrink-0 flex-wrap gap-2">
                                {permissions.approve && (
                                    <button type="button" className={ANESTHESIA_APPROVE_BTN_CLASS} onClick={openApprove}>
                                        <i className="bx bx-check-circle text-lg" />
                                        {t('global.approve')}
                                    </button>
                                )}
                                {permissions.reject && (
                                    <button type="button" className={ANESTHESIA_REJECT_BTN_CLASS} onClick={openReject}>
                                        <i className="bx bx-x-circle text-lg" />
                                        {t('global.reject')}
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {showReferToOperation && (
                    <div className="overflow-hidden rounded-xl border border-amber-200/90 bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 shadow-md dark:border-amber-900/50 dark:from-amber-950/50 dark:via-orange-950/30 dark:to-yellow-950/20">
                        <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex min-w-0 items-start gap-4">
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md">
                                    <i className="bx bx-cut text-2xl" />
                                </div>
                                <div className="min-w-0">
                                    <p className="text-base font-bold text-amber-950 dark:text-amber-50">
                                        {t('global.refere_to_operation')}
                                    </p>
                                    <p className="mt-1 text-sm text-amber-800/80 dark:text-amber-200/80">
                                        {t('global.approved')}: {anesthesia.operation_type_name ?? '—'}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                className={ANESTHESIA_APPLY_BTN_CLASS}
                                onClick={handleReferToOperation}
                                disabled={processing}
                            >
                                <i className="bx bx-send text-lg" />
                                {t('global.refere_to_operation')}
                            </button>
                        </div>
                    </div>
                )}

                {hasAppointment && sectionPermissions.prescription && anesthesia.appointment_id && (
                    <PrescriptionSection appointmentId={anesthesia.appointment_id} />
                )}
            </div>

            <Modal show={approveOpen} onClose={() => !processing && setApproveOpen(false)} size="lg">
                <div className="overflow-hidden rounded-lg">
                    <div className="bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-4 text-white">
                        <ModalHeader className="border-0 p-0 text-white">
                            <span className="flex items-center gap-2">
                                <i className="bx bx-check-circle text-xl" />
                                {t('global.approve')}
                            </span>
                        </ModalHeader>
                    </div>
                    <form onSubmit={handleApprove}>
                        <ModalBody className="space-y-4 bg-gray-50/60 p-5 dark:bg-gray-950/40">
                            <div>
                                <Label htmlFor="approve-reply" className="mb-2 flex items-center gap-1.5">
                                    <i className="bx bx-message-dots text-emerald-600" />
                                    {t('global.anesthesia_log_reply')}
                                </Label>
                                <Textarea
                                    id="approve-reply"
                                    rows={3}
                                    required
                                    value={anesthesiaLogReply}
                                    onChange={(e) => setAnesthesiaLogReply(e.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="approve-plan" className="mb-2 flex items-center gap-1.5">
                                    <i className="bx bx-note text-emerald-600" />
                                    {t('global.anesthesia_plan')}
                                </Label>
                                <Textarea
                                    id="approve-plan"
                                    rows={3}
                                    value={anesthesiaPlan}
                                    onChange={(e) => setAnesthesiaPlan(e.target.value)}
                                />
                            </div>
                            <div className="grid gap-4 md:grid-cols-3">
                                <div>
                                    <Label className="mb-2 flex items-center gap-1.5">
                                        <i className="bx bx-file-blank text-emerald-600" />
                                        {t('global.anesthesia_log')}
                                    </Label>
                                    <SearchableSelect
                                        value={anesthesiaLogId}
                                        onChange={setAnesthesiaLogId}
                                        options={doctorOptions}
                                        placeholder={t('global.select')}
                                    />
                                </div>
                                <div>
                                    <Label className="mb-2 flex items-center gap-1.5">
                                        <i className="bx bx-user-circle text-emerald-600" />
                                        {t('global.anesthesist')}
                                    </Label>
                                    <SearchableSelect
                                        value={anesthesistId}
                                        onChange={setAnesthesistId}
                                        options={doctorOptions}
                                        placeholder={t('global.select')}
                                    />
                                </div>
                                <div>
                                    <Label className="mb-2 flex items-center gap-1.5">
                                        <i className="bx bx-pulse text-emerald-600" />
                                        {t('global.anesthesia_type')}
                                    </Label>
                                    <SearchableSelect
                                        value={anesthesiaType}
                                        onChange={setAnesthesiaType}
                                        options={anesthesiaTypeOptions}
                                        placeholder={t('global.select')}
                                    />
                                </div>
                            </div>
                        </ModalBody>
                        <ModalFooter>
                            <Button
                                type="button"
                                color="light"
                                onClick={() => setApproveOpen(false)}
                                disabled={processing}
                            >
                                {t('global.cancel')}
                            </Button>
                            <button type="submit" className={ANESTHESIA_APPROVE_BTN_CLASS} disabled={processing}>
                                {processing ? <Spinner size="sm" /> : <i className="bx bx-check" />}
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
                                {t('global.rejection_reason')}
                            </span>
                        </ModalHeader>
                    </div>
                    <form onSubmit={handleReject}>
                        <ModalBody className="space-y-4 bg-gray-50/60 p-5 dark:bg-gray-950/40">
                            <div>
                                <Label htmlFor="reject-reason" className="mb-2 flex items-center gap-1.5">
                                    <i className="bx bx-x-circle text-rose-600" />
                                    {t('global.rejection_reason')}
                                </Label>
                                <Textarea
                                    id="reject-reason"
                                    rows={3}
                                    required
                                    value={anesthesiaLogReply}
                                    onChange={(e) => setAnesthesiaLogReply(e.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="reject-plan" className="mb-2 flex items-center gap-1.5">
                                    <i className="bx bx-note text-rose-600" />
                                    {t('global.anesthesia_plan')}
                                </Label>
                                <Textarea
                                    id="reject-plan"
                                    rows={3}
                                    value={anesthesiaPlan}
                                    onChange={(e) => setAnesthesiaPlan(e.target.value)}
                                />
                            </div>
                        </ModalBody>
                        <ModalFooter>
                            <Button
                                type="button"
                                color="light"
                                onClick={() => setRejectOpen(false)}
                                disabled={processing}
                            >
                                {t('global.cancel')}
                            </Button>
                            <button type="submit" className={ANESTHESIA_REJECT_BTN_CLASS} disabled={processing}>
                                {processing ? <Spinner size="sm" /> : <i className="bx bx-x" />}
                                {t('global.save')}
                            </button>
                        </ModalFooter>
                    </form>
                </div>
            </Modal>
        </DashboardLayout>
    );
}
