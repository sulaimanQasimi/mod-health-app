import { Head, router } from '@inertiajs/react';
import { Button, Checkbox, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import OperationSummary from '../../Components/Operations/OperationSummary';
import {
    OPERATION_APPROVE_BTN_CLASS,
    OPERATION_COMPLETE_BTN_CLASS,
    OPERATION_PENDING_PANEL_CLASS,
    OPERATION_RESERVE_BTN_CLASS,
    operationPatientLabel,
} from '../../Components/Operations/operationUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import {
    OperationDetail,
    OperationListUrls,
    OperationNurseOption,
    OperationShowPermissions,
} from '../../types/operation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowProps {
    operation: OperationDetail;
    nurses: OperationNurseOption[];
    permissions: OperationShowPermissions;
    urls: OperationListUrls & {
        update: string;
        complete: string;
        reserve: string;
        unreserve: string;
        back: string;
        appointment: string | null;
    };
}

export default function OperationsShow({ operation, nurses, permissions, urls }: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [approveOpen, setApproveOpen] = useState(false);
    const [completeOpen, setCompleteOpen] = useState(false);
    const [reserveOpen, setReserveOpen] = useState(false);

    const [scrubNurseId, setScrubNurseId] = useState(String(operation.operation_scrub_nurse_id ?? ''));
    const [circulationNurseId, setCirculationNurseId] = useState(
        String(operation.operation_circulation_nurse_id ?? ''),
    );
    const [date, setDate] = useState(operation.date);
    const [time, setTime] = useState(operation.time ?? '');
    const [approveChecked, setApproveChecked] = useState(true);
    const [operationResult, setOperationResult] = useState('1');
    const [operationRemark, setOperationRemark] = useState(operation.operation_remark ?? '');
    const [reserveReason, setReserveReason] = useState('');

    const patientLabel = operationPatientLabel(operation);
    const canApprove = !operation.is_operation_approved && !operation.is_operation_done && !operation.is_reserved;
    const canComplete = operation.is_operation_approved && !operation.is_operation_done && !operation.is_reserved;
    const canReserve = !operation.is_operation_done && !operation.is_reserved;
    const canUnreserve = operation.is_reserved && !operation.is_operation_done;
    const hasAppointment = Boolean(operation.appointment_id);

    const nurseOptions = useMemo(
        () => nurses.map((nurse) => ({ value: String(nurse.id), label: nurse.name })),
        [nurses],
    );

    const put = (url: string, data: Record<string, string | number>, onSuccess?: () => void) => {
        setProcessing(true);
        router.put(url, data, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onFinish: () => setProcessing(false),
        });
    };

    const post = (url: string, onSuccess?: () => void) => {
        setProcessing(true);
        router.post(url, {}, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onFinish: () => setProcessing(false),
        });
    };

    const handleApprove = (event: FormEvent) => {
        event.preventDefault();
        put(
            urls.update,
            {
                operation_scrub_nurse_id: scrubNurseId,
                operation_circulation_nurse_id: circulationNurseId,
                date,
                time,
                is_operation_approved: approveChecked ? 1 : 0,
            },
            () => setApproveOpen(false),
        );
    };

    const handleComplete = (event: FormEvent) => {
        event.preventDefault();
        put(
            urls.complete,
            {
                operation_result: operationResult,
                operation_remark: operationRemark,
            },
            () => setCompleteOpen(false),
        );
    };

    const handleReserve = (event: FormEvent) => {
        event.preventDefault();
        put(urls.reserve, { reserve_reason: reserveReason }, () => setReserveOpen(false));
    };

    const handleUnreserve = () => {
        if (!window.confirm(t('global.move_operation'))) {
            return;
        }
        post(urls.unreserve);
    };

    return (
        <DashboardLayout>
            <Head title={patientLabel} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={patientLabel}
                    subtitle={[
                        `#${operation.id}`,
                        operation.operation_type_name,
                        operation.department_name,
                    ]
                        .filter(Boolean)
                        .join(' · ')}
                    icon="bx-cut"
                    accent="from-amber-600 to-orange-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                    action={
                        <SettingsPageActions>
                            {canApprove && (
                                <button type="button" className={OPERATION_APPROVE_BTN_CLASS} onClick={() => setApproveOpen(true)}>
                                    <i className="bx bx-check text-lg" />
                                    {t('global.operation_approval')}
                                </button>
                            )}
                            {canComplete && (
                                <button type="button" className={OPERATION_COMPLETE_BTN_CLASS} onClick={() => setCompleteOpen(true)}>
                                    <i className="bx bx-check-double text-lg" />
                                    {t('global.complete_operation')}
                                </button>
                            )}
                            {canReserve && (
                                <button type="button" className={OPERATION_RESERVE_BTN_CLASS} onClick={() => setReserveOpen(true)}>
                                    <i className="bx bx-calendar-check text-lg" />
                                    {t('global.reserve_operation')}
                                </button>
                            )}
                            {canUnreserve && (
                                <button type="button" className={OPERATION_RESERVE_BTN_CLASS} onClick={handleUnreserve} disabled={processing}>
                                    <i className="bx bx-transfer text-lg" />
                                    {t('global.move_operation')}
                                </button>
                            )}
                        </SettingsPageActions>
                    }
                />

                <OperationSummary operation={operation} />

                {(canApprove || canComplete || canReserve) && (
                    <div className={OPERATION_PENDING_PANEL_CLASS}>
                        <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex min-w-0 items-start gap-4">
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md">
                                    <i className="bx bx-cut text-2xl" />
                                </div>
                                <div className="min-w-0">
                                    <p className="text-base font-bold text-amber-950 dark:text-amber-50">
                                        {t('global.operation_details')}
                                    </p>
                                    <p className="mt-1 line-clamp-2 text-sm text-amber-800/80 dark:text-amber-200/80">
                                        {operation.plan ?? '—'}
                                    </p>
                                </div>
                            </div>
                            <div className="flex shrink-0 flex-wrap gap-2">
                                {canApprove && (
                                    <button type="button" className={OPERATION_APPROVE_BTN_CLASS} onClick={() => setApproveOpen(true)}>
                                        <i className="bx bx-check-circle text-lg" />
                                        {t('global.operation_approval')}
                                    </button>
                                )}
                                {canComplete && (
                                    <button type="button" className={OPERATION_COMPLETE_BTN_CLASS} onClick={() => setCompleteOpen(true)}>
                                        <i className="bx bx-check-double text-lg" />
                                        {t('global.complete_operation')}
                                    </button>
                                )}
                                {canReserve && (
                                    <button type="button" className={OPERATION_RESERVE_BTN_CLASS} onClick={() => setReserveOpen(true)}>
                                        <i className="bx bx-calendar-check text-lg" />
                                        {t('global.reserve_operation')}
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {hasAppointment && permissions.prescription && operation.appointment_id && (
                    <PrescriptionSection appointmentId={operation.appointment_id} />
                )}
            </div>

            <Modal show={approveOpen} onClose={() => !processing && setApproveOpen(false)} size="lg">
                <div className="overflow-hidden rounded-lg">
                    <div className="bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-4 text-white">
                        <ModalHeader className="border-0 p-0 text-white">
                            <span className="flex items-center gap-2">
                                <i className="bx bx-check-circle text-xl" />
                                {t('global.operation_approval')}
                            </span>
                        </ModalHeader>
                    </div>
                    <form onSubmit={handleApprove}>
                        <ModalBody className="space-y-4 bg-gray-50/60 p-5 dark:bg-gray-950/40">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label className="mb-2 block">{t('global.scrub_nurse')}</Label>
                                    <SearchableSelect
                                        value={scrubNurseId}
                                        onChange={setScrubNurseId}
                                        options={nurseOptions}
                                        placeholder={t('global.select')}
                                    />
                                </div>
                                <div>
                                    <Label className="mb-2 block">{t('global.circulation_nurse')}</Label>
                                    <SearchableSelect
                                        value={circulationNurseId}
                                        onChange={setCirculationNurseId}
                                        options={nurseOptions}
                                        placeholder={t('global.select')}
                                    />
                                </div>
                                <div>
                                    <Label className="mb-2 block">{t('global.date')}</Label>
                                    <PersianDateInput value={date} onChange={setDate} />
                                </div>
                                <div>
                                    <Label htmlFor="approve-time" className="mb-2 block">
                                        {t('global.time')}
                                    </Label>
                                    <TextInput
                                        id="approve-time"
                                        type="time"
                                        value={time}
                                        onChange={(e) => setTime(e.target.value)}
                                    />
                                </div>
                            </div>
                            <label className="flex items-center gap-2">
                                <Checkbox checked={approveChecked} onChange={(e) => setApproveChecked(e.target.checked)} />
                                <span className="text-sm text-gray-700 dark:text-gray-300">{t('global.approve')}</span>
                            </label>
                        </ModalBody>
                        <ModalFooter>
                            <Button type="button" color="light" onClick={() => setApproveOpen(false)} disabled={processing}>
                                {t('global.cancel')}
                            </Button>
                            <button type="submit" className={OPERATION_APPROVE_BTN_CLASS} disabled={processing}>
                                {processing ? <Spinner size="sm" /> : <i className="bx bx-check" />}
                                {t('global.save')}
                            </button>
                        </ModalFooter>
                    </form>
                </div>
            </Modal>

            <Modal show={completeOpen} onClose={() => !processing && setCompleteOpen(false)} size="md">
                <div className="overflow-hidden rounded-lg">
                    <div className="bg-gradient-to-r from-sky-500 to-blue-600 px-6 py-4 text-white">
                        <ModalHeader className="border-0 p-0 text-white">
                            <span className="flex items-center gap-2">
                                <i className="bx bx-check-double text-xl" />
                                {t('global.complete_operation')}
                            </span>
                        </ModalHeader>
                    </div>
                    <form onSubmit={handleComplete}>
                        <ModalBody className="space-y-4 bg-gray-50/60 p-5 dark:bg-gray-950/40">
                            <div>
                                <Label className="mb-2 block">{t('global.operation_result')}</Label>
                                <SearchableSelect
                                    value={operationResult}
                                    onChange={setOperationResult}
                                    options={[
                                        { value: '1', label: t('global.success') },
                                        { value: '0', label: t('global.fail') },
                                    ]}
                                    placeholder={t('global.select')}
                                />
                            </div>
                            <div>
                                <Label htmlFor="complete-remark" className="mb-2 block">
                                    {t('global.operation_remark')}
                                </Label>
                                <Textarea
                                    id="complete-remark"
                                    rows={3}
                                    value={operationRemark}
                                    onChange={(e) => setOperationRemark(e.target.value)}
                                />
                            </div>
                        </ModalBody>
                        <ModalFooter>
                            <Button type="button" color="light" onClick={() => setCompleteOpen(false)} disabled={processing}>
                                {t('global.cancel')}
                            </Button>
                            <button type="submit" className={OPERATION_COMPLETE_BTN_CLASS} disabled={processing}>
                                {processing ? <Spinner size="sm" /> : <i className="bx bx-check" />}
                                {t('global.save')}
                            </button>
                        </ModalFooter>
                    </form>
                </div>
            </Modal>

            <Modal show={reserveOpen} onClose={() => !processing && setReserveOpen(false)} size="md">
                <div className="overflow-hidden rounded-lg">
                    <div className="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4 text-white">
                        <ModalHeader className="border-0 p-0 text-white">
                            <span className="flex items-center gap-2">
                                <i className="bx bx-calendar-check text-xl" />
                                {t('global.reserve_operation')}
                            </span>
                        </ModalHeader>
                    </div>
                    <form onSubmit={handleReserve}>
                        <ModalBody className="space-y-4 bg-gray-50/60 p-5 dark:bg-gray-950/40">
                            <div>
                                <Label htmlFor="reserve-reason" className="mb-2 block">
                                    {t('global.reserve_reason')}
                                </Label>
                                <Textarea
                                    id="reserve-reason"
                                    rows={3}
                                    required
                                    value={reserveReason}
                                    onChange={(e) => setReserveReason(e.target.value)}
                                />
                            </div>
                        </ModalBody>
                        <ModalFooter>
                            <Button type="button" color="light" onClick={() => setReserveOpen(false)} disabled={processing}>
                                {t('global.cancel')}
                            </Button>
                            <button type="submit" className={OPERATION_RESERVE_BTN_CLASS} disabled={processing}>
                                {processing ? <Spinner size="sm" /> : <i className="bx bx-check" />}
                                {t('global.save')}
                            </button>
                        </ModalFooter>
                    </form>
                </div>
            </Modal>
        </DashboardLayout>
    );
}
