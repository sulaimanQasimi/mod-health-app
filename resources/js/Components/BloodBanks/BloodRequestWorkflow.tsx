import { Link, router } from '@inertiajs/react';
import { Alert, Badge, Button, Checkbox, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, ReactNode, useEffect, useState } from 'react';
import BloodFormSegmented from './BloodFormSegmented';
import BloodUnitDetailTile from './BloodUnitDetailTile';
import {
    BLOOD_BANK_PRIMARY_BTN_CLASS,
    bloodGroupLabel,
    bloodRhLabel,
    crossmatchStatusBadgeColor,
    screeningStatusBadgeColor,
    bloodUnitStatusBadgeColor,
} from './bloodBankUi';
import SearchableSelect from '../ui/SearchableSelect';
import PersianDateTimeField from '../ui/PersianDateTimeField';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    BloodRequestDetail,
    BloodRequestShowPermissions,
    BloodRequestShowUrls,
    BloodRequestWorkflowData,
} from '../../types/bloodBank';

const STEP_CARD_BASE = 'overflow-hidden rounded-2xl border bg-white shadow-sm transition dark:bg-gray-900';
const STEP_CARD_CURRENT = 'border-rose-500 shadow-md ring-1 ring-rose-200 dark:border-rose-700 dark:ring-rose-900/50';
const STEP_CARD_DEFAULT = 'border-gray-200 dark:border-gray-700';

function WorkflowStepCard({
    step,
    title,
    hint,
    status,
    isCurrent,
    children,
    action,
}: {
    step: number;
    title: string;
    hint?: string;
    status: 'done' | 'current' | 'pending';
    isCurrent: boolean;
    children: ReactNode;
    action?: ReactNode;
}) {
    const { t } = useTranslation();

    const statusBadge =
        status === 'done' ? (
            <Badge color="success" className="font-normal">
                {t('global.workflow_step_status_done')}
            </Badge>
        ) : status === 'current' ? (
            <Badge color="warning" className="font-normal">
                {t('global.workflow_step_status_current')}
            </Badge>
        ) : (
            <Badge color="gray" className="font-normal">
                {t('global.workflow_step_status_pending')}
            </Badge>
        );

    return (
        <div className={`${STEP_CARD_BASE} ${isCurrent ? STEP_CARD_CURRENT : STEP_CARD_DEFAULT}`}>
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gradient-to-r from-rose-50/80 to-white px-5 py-4 dark:border-gray-800 dark:from-rose-950/20 dark:to-gray-900">
                <div className="flex items-center gap-3">
                    <span className="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-b from-rose-500 to-rose-600 text-sm font-bold text-white shadow-sm">
                        {step}
                    </span>
                    <div>
                        <h3 className="text-sm font-bold text-gray-900 dark:text-white">{title}</h3>
                        {hint && <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{hint}</p>}
                    </div>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    {action}
                    {statusBadge}
                </div>
            </div>
            <div className="p-5">{children}</div>
        </div>
    );
}

function formatOrderQuantity(bloodRequest: BloodRequestDetail): string {
    const display = bloodRequest.order_quantity_display;
    if (display.mode === 'volume_ml' && display.ml != null) {
        return `${display.ml} ml`;
    }
    if (display.mode === 'units' && display.units != null) {
        return String(display.units);
    }
    return String(bloodRequest.requested_qty);
}

interface BloodRequestWorkflowProps {
    bloodRequest: BloodRequestDetail;
    workflowData: BloodRequestWorkflowData;
    permissions: BloodRequestShowPermissions;
    urls: BloodRequestShowUrls;
    receiverDepartments: { id: number; name: string }[];
}

export default function BloodRequestWorkflow({
    bloodRequest,
    workflowData,
    permissions,
    urls,
    receiverDepartments,
}: BloodRequestWorkflowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [bloodCheckOpen, setBloodCheckOpen] = useState(false);
    const [bloodCheckForm, setBloodCheckForm] = useState(workflowData.bloodCheckForm);
    const [verifyLabTyping, setVerifyLabTyping] = useState(false);

    const [sampleForm, setSampleForm] = useState({ sample_id: '', collected_date: '', collected_time: '', notes: '' });

    const [receiverDepartmentId, setReceiverDepartmentId] = useState(
        workflowData.deliveryDefaults.receiver_department_id
            ? String(workflowData.deliveryDefaults.receiver_department_id)
            : '',
    );
    const [receiverNurseId, setReceiverNurseId] = useState(
        workflowData.deliveryDefaults.receiver_nurse_id
            ? String(workflowData.deliveryDefaults.receiver_nurse_id)
            : '',
    );
    const [nurseOptions, setNurseOptions] = useState<{ value: string; label: string }[]>([]);
    const [nursesLoading, setNursesLoading] = useState(false);
    const [selectedUnitIds, setSelectedUnitIds] = useState<number[]>(() => {
        if (!workflowData.hasCrossmatchFlow) {
            return [];
        }
        return workflowData.deliverableUnitIds;
    });

    const [overrideReasons, setOverrideReasons] = useState<Record<number, string>>({});

    const currentStep = bloodRequest.workflow.current_step;
    const stepStatus = (step: number): 'done' | 'current' | 'pending' => {
        const wfStep = bloodRequest.workflow.steps.find((s) => s.number === step);
        if (!wfStep) return 'pending';
        if (wfStep.done) return 'done';
        if (wfStep.current) return 'current';
        return 'pending';
    };

    useEffect(() => {
        if (!receiverDepartmentId) {
            setNurseOptions([]);
            if (!workflowData.deliveryDefaults.receiver_department_id) {
                setReceiverNurseId('');
            }
            return;
        }

        const nursesUrl = urls.nursesByDepartment.replace('__DEPARTMENT__', receiverDepartmentId);
        setNursesLoading(true);
        fetch(nursesUrl, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => r.json())
            .then((data) => {
                const options = (data.nurses ?? []).map((n: { id: number; name: string }) => ({
                    value: String(n.id),
                    label: n.name,
                }));
                setNurseOptions(options);
                const initial = workflowData.deliveryDefaults.receiver_nurse_id;
                if (initial && options.some((o: { value: string }) => o.value === String(initial))) {
                    setReceiverNurseId(String(initial));
                }
            })
            .finally(() => setNursesLoading(false));
    }, [receiverDepartmentId, urls.nursesByDepartment, workflowData.deliveryDefaults.receiver_nurse_id]);

    const post = (url: string, data: Record<string, unknown> = {}, onSuccess?: () => void) => {
        setProcessing(true);
        router.post(url, data, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onFinish: () => setProcessing(false),
        });
    };

    const handleBloodCheck = (event: FormEvent) => {
        event.preventDefault();
        post(
            urls.bloodCheck,
            {
                ...bloodCheckForm,
                patient_typed_group: bloodCheckForm.patient_typed_group || null,
                patient_typed_rh: bloodCheckForm.patient_typed_rh || null,
                verify_lab_typing: verifyLabTyping ? 1 : 0,
            },
            () => setBloodCheckOpen(false),
        );
    };

    const handleSample = (event: FormEvent) => {
        event.preventDefault();
        post(urls.storeSample, {
            sample_id: sampleForm.sample_id || null,
            collected_date: sampleForm.collected_date || null,
            collected_time: sampleForm.collected_time || null,
            notes: sampleForm.notes || null,
        });
    };

    const handleDeliver = (event: FormEvent) => {
        event.preventDefault();
        post(urls.deliver, {
            receiver_department_id: receiverDepartmentId,
            receiver_nurse_id: receiverNurseId,
            unit_ids: selectedUnitIds,
        });
    };

    const toggleUnit = (unitId: number, disabled: boolean) => {
        if (disabled) return;
        setSelectedUnitIds((prev) =>
            prev.includes(unitId) ? prev.filter((id) => id !== unitId) : [...prev, unitId],
        );
    };

    const deliveryReadiness = () => {
        if (bloodRequest.remaining_qty === 0) {
            return { color: 'success' as const, text: t('global.ready') };
        }
        if (bloodRequest.reserved_compatible_qty >= bloodRequest.remaining_qty) {
            return { color: 'success' as const, text: t('global.ready_for_partial_or_full_delivery') };
        }
        return { color: 'failure' as const, text: t('global.not_ready_need_more_reserved_compatible_units') };
    };

    const readiness = deliveryReadiness();

    return (
        <div className="space-y-5">
            {/* Visual stepper */}
            <div className="overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-5 shadow-sm dark:border-gray-700 dark:from-gray-900 dark:to-gray-900/80">
                <p className="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {t('global.blood_bank_workflow_title')}
                </p>
                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {bloodRequest.workflow.steps.map((step) => (
                        <div
                            key={step.number}
                            className={`rounded-xl border p-4 text-center transition ${
                                step.current
                                    ? 'border-rose-500 bg-rose-50 shadow-sm ring-1 ring-rose-200 dark:border-rose-700 dark:bg-rose-950/30 dark:ring-rose-900/50'
                                    : step.done
                                      ? 'border-emerald-300 bg-emerald-50/70 dark:border-emerald-900/40 dark:bg-emerald-950/20'
                                      : 'border-gray-200 bg-gray-50/50 dark:border-gray-700 dark:bg-gray-800/30'
                            }`}
                        >
                            <div
                                className={`mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold shadow-sm ${
                                    step.done
                                        ? 'bg-emerald-500 text-white'
                                        : step.current
                                          ? 'bg-rose-600 text-white'
                                          : 'bg-white text-gray-500 dark:bg-gray-900 dark:text-gray-400'
                                }`}
                            >
                                {step.done && !step.current ? <i className="bx bx-check text-lg" /> : step.number}
                            </div>
                            <p className="text-xs font-semibold text-gray-800 dark:text-gray-200">
                                {t(`global.blood_bank_workflow_step_${step.number}`)}
                            </p>
                            <Badge
                                color={step.done ? 'success' : step.current ? 'warning' : 'gray'}
                                className="mt-2 w-fit font-normal"
                            >
                                {step.done
                                    ? t('global.workflow_step_status_done')
                                    : step.current
                                      ? t('global.workflow_step_status_current')
                                      : t('global.workflow_step_status_pending')}
                            </Badge>
                        </div>
                    ))}
                </div>
            </div>

            {/* Step 1: Blood check */}
            <WorkflowStepCard
                step={1}
                title={t('global.blood_bank_workflow_step_1')}
                hint={t('global.blood_bank_workflow_step_1_hint')}
                status={stepStatus(1)}
                isCurrent={currentStep === 1}
                action={
                    permissions.manageCrossmatch ? (
                        <button
                            type="button"
                            className="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700"
                            onClick={() => setBloodCheckOpen(true)}
                        >
                            <i className="bx bx-edit-alt" />
                            {t('global.blood_check_fill_modal')}
                        </button>
                    ) : undefined
                }
            >
                {bloodRequest.blood_check && (
                    <Alert color="success" className="mb-4 rounded-xl">
                        <div>
                            <p className="font-semibold">{t('global.blood_check_record_saved')}</p>
                            <p className="mt-1 text-sm" dir="ltr">
                                {t('global.patient_typed_group')}: {bloodRequest.blood_check.patient_typed_group ?? '—'} —{' '}
                                {t('global.patient_typed_rh')}: {bloodRequest.blood_check.patient_typed_rh ?? '—'}
                            </p>
                            {bloodRequest.blood_check.verified_at && (
                                <p className="mt-1 text-xs opacity-80" dir="ltr">
                                    {t('global.verified_at')} {bloodRequest.blood_check.verified_at}
                                    {bloodRequest.blood_check.verified_by_name
                                        ? ` — ${bloodRequest.blood_check.verified_by_name}`
                                        : ''}
                                </p>
                            )}
                        </div>
                    </Alert>
                )}
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <BloodUnitDetailTile icon="bx-user" label={t('global.patient_name')} value={bloodRequest.patient.name ?? '—'} />
                    <BloodUnitDetailTile icon="bx-buildings" label={t('global.requested_department')} value={bloodRequest.department_name ?? '—'} />
                    <BloodUnitDetailTile icon="bx-droplet" label={t('global.blood_group')} value={bloodGroupLabel(bloodRequest.group)} />
                    <BloodUnitDetailTile icon="bx-plus-medical" label={t('global.blood_rh')} value={bloodRhLabel(bloodRequest.rh)} />
                    <BloodUnitDetailTile icon="bx-hash" label={t('global.quantity')} value={formatOrderQuantity(bloodRequest)} />
                    <BloodUnitDetailTile icon="bx-cylinder" label={t('global.blood_type')} value={bloodRequest.type ?? '—'} />
                    <BloodUnitDetailTile icon="bx-user-check" label={t('global.created_by')} value={bloodRequest.created_by_name ?? '—'} />
                    <BloodUnitDetailTile icon="bx-calendar" label={t('global.created_at')}>
                        <span dir="ltr">{bloodRequest.created_at ?? '—'}</span>
                    </BloodUnitDetailTile>
                </div>
            </WorkflowStepCard>

            {/* Step 2: Patient sample */}
            <WorkflowStepCard
                step={2}
                title={t('global.blood_bank_workflow_step_2')}
                hint={t('global.blood_bank_workflow_step_2_hint')}
                status={stepStatus(2)}
                isCurrent={currentStep === 2}
            >
                {permissions.manageCrossmatch && (
                    <form onSubmit={handleSample} className="mb-4 grid gap-3 rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/40 sm:grid-cols-12">
                        <div className="sm:col-span-3">
                            <Label className="mb-1 block text-xs">{t('global.crossmatch_sample_id')}</Label>
                            <TextInput
                                value={sampleForm.sample_id}
                                onChange={(e) => setSampleForm((f) => ({ ...f, sample_id: e.target.value }))}
                                placeholder={t('global.optional')}
                                className="rounded-lg"
                            />
                        </div>
                        <div className="sm:col-span-4">
                            <Label className="mb-1 block text-xs">{t('global.collected_at')}</Label>
                            <PersianDateTimeField
                                dateValue={sampleForm.collected_date}
                                timeValue={sampleForm.collected_time}
                                onDateChange={(value) => setSampleForm((f) => ({ ...f, collected_date: value }))}
                                onTimeChange={(value) => setSampleForm((f) => ({ ...f, collected_time: value }))}
                                timeHint={t('global.optional')}
                            />
                        </div>
                        <div className="sm:col-span-3">
                            <Label className="mb-1 block text-xs">{t('global.notes')}</Label>
                            <TextInput
                                value={sampleForm.notes}
                                onChange={(e) => setSampleForm((f) => ({ ...f, notes: e.target.value }))}
                                className="rounded-lg"
                            />
                        </div>
                        <div className="flex items-end sm:col-span-2">
                            <Button type="submit" className={BLOOD_BANK_PRIMARY_BTN_CLASS} disabled={processing}>
                                {processing ? <Spinner size="sm" /> : null}
                                {t('global.save_sample')}
                            </Button>
                        </div>
                    </form>
                )}

                {bloodRequest.patient_samples.length > 0 ? (
                    <div>
                        <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{t('global.patient_samples')}</p>
                        <Table embedded>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.crossmatch_sample_id')}</TableHeader>
                                    <TableHeader>{t('global.collected_at')}</TableHeader>
                                    <TableHeader>{t('global.notes')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {bloodRequest.patient_samples.map((sample) => (
                                    <TableRow key={sample.id}>
                                        <TableCell className="font-mono">{sample.sample_id ?? `#${sample.id}`}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {sample.collected_at ?? '—'}
                                        </TableCell>
                                        <TableCell muted className="max-w-md whitespace-normal">
                                            {sample.notes ?? '—'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                ) : (
                    <Alert color="warning" className="rounded-xl">
                        <span className="text-sm">{t('global.blood_bank_workflow_step_2_empty')}</span>
                    </Alert>
                )}
            </WorkflowStepCard>

            {/* Step 3: Crossmatch & reserve */}
            <WorkflowStepCard
                step={3}
                title={t('global.blood_bank_workflow_step_3')}
                hint={t('global.blood_bank_workflow_step_3_hint')}
                status={stepStatus(3)}
                isCurrent={currentStep === 3}
            >
                <Alert color="info" className="mb-4 rounded-xl">
                    <p className="font-semibold">{t('global.crossmatch_reserve_progress_title')}</p>
                    {bloodRequest.remaining_qty < 1 ? (
                        <p className="mt-1 text-sm text-emerald-700 dark:text-emerald-300">
                            {t('global.crossmatch_no_units_left_to_reserve')}
                        </p>
                    ) : (
                        <p className="mt-1 text-sm" dir="ltr">
                            <strong>{bloodRequest.reserved_compatible_qty}</strong> /{' '}
                            <strong>{bloodRequest.remaining_qty}</strong>
                            <span className="ms-1 text-gray-500">({t('global.crossmatch_reserved_vs_remaining_caption')})</span>
                        </p>
                    )}
                </Alert>

                <div className="overflow-x-auto">
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>{t('global.bag_number')}</TableHeader>
                                <TableHeader>{t('global.blood_group')}</TableHeader>
                                <TableHeader>{t('global.blood_rh')}</TableHeader>
                                <TableHeader>{t('global.component_type')}</TableHeader>
                                <TableHeader>{t('global.volume_ml')}</TableHeader>
                                <TableHeader>{t('global.expires_at')}</TableHeader>
                                <TableHeader>{t('global.screening_status')}</TableHeader>
                                <TableHeader>{t('global.crossmatch_auto_check')}</TableHeader>
                                <TableHeader>{t('global.crossmatch_status')}</TableHeader>
                                {permissions.manageCrossmatch && <TableHeader>{t('global.actions')}</TableHeader>}
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {workflowData.availableUnits.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={permissions.manageCrossmatch ? 10 : 9} muted className="py-6 text-center">
                                        {t('global.no_item_is_found')}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                workflowData.availableUnits.map((unit) => (
                                    <CrossmatchUnitRow
                                        key={unit.id}
                                        unit={unit}
                                        samples={bloodRequest.patient_samples}
                                        resultValues={workflowData.crossmatchResultValues}
                                        canManage={permissions.manageCrossmatch}
                                        canOverride={permissions.manageInventory}
                                        processing={processing}
                                        overrideReason={overrideReasons[unit.crossmatch?.id ?? 0] ?? ''}
                                        onOverrideReasonChange={(reason) =>
                                            unit.crossmatch &&
                                            setOverrideReasons((prev) => ({ ...prev, [unit.crossmatch!.id]: reason }))
                                        }
                                        onPost={post}
                                    />
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                <Alert color="gray" className="mt-4 rounded-xl">
                    <span className="text-sm">
                        {t('global.delivery_readiness')}:{' '}
                        <Badge color={readiness.color} className="ms-1 font-normal">
                            {readiness.text}
                        </Badge>
                    </span>
                </Alert>
            </WorkflowStepCard>

            {/* Step 4: Issue & complete */}
            <WorkflowStepCard
                step={4}
                title={t('global.blood_bank_workflow_step_4')}
                hint={t('global.blood_bank_workflow_step_4_hint')}
                status={stepStatus(4)}
                isCurrent={currentStep === 4}
            >
                <div className="mb-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                        <span className="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                            {t('global.inventory_preview')}
                        </span>
                        <Link
                            href={urls.inventory}
                            className="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50 dark:border-rose-900/40 dark:text-rose-300"
                        >
                            <i className="bx bx-link-external" />
                            {t('global.open_full_inventory')}
                        </Link>
                    </div>
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>{t('global.bag_number')}</TableHeader>
                                <TableHeader>{t('global.blood_group')}</TableHeader>
                                <TableHeader>{t('global.blood_rh')}</TableHeader>
                                <TableHeader>{t('global.component_type')}</TableHeader>
                                <TableHeader>{t('global.expires_at')}</TableHeader>
                                <TableHeader>{t('global.screening_status')}</TableHeader>
                                <TableHeader>{t('global.crossmatch_status')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {workflowData.inventoryPreviewUnits.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={7} muted className="py-6 text-center">
                                        {t('global.no_item_is_found')}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                workflowData.inventoryPreviewUnits.map((unit) => (
                                    <TableRow key={unit.id}>
                                        <TableCell>
                                            <Link href={unit.urls.show} className="font-medium text-rose-700 hover:underline dark:text-rose-300">
                                                {unit.bag_number ?? '—'}
                                            </Link>
                                        </TableCell>
                                        <TableCell muted>{unit.blood_group ?? '—'}</TableCell>
                                        <TableCell muted>{unit.rh ?? '—'}</TableCell>
                                        <TableCell muted>{unit.component_type ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {unit.expires_at ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge color={screeningStatusBadgeColor(unit.screening_status)} className="w-fit font-normal capitalize">
                                                {unit.screening_status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            {unit.crossmatch_status ? (
                                                <Badge color={crossmatchStatusBadgeColor(unit.crossmatch_status)} className="w-fit font-normal capitalize">
                                                    {unit.crossmatch_status}
                                                </Badge>
                                            ) : (
                                                <Badge color="gray" className="w-fit font-normal">
                                                    {t('global.not_tested')}
                                                </Badge>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {permissions.deliver && (
                    <form onSubmit={handleDeliver} className="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-800/30">
                        <h4 className="text-sm font-bold text-gray-900 dark:text-white">{t('global.blood_bank_delivery_select_units')}</h4>
                        <p className="mt-1 text-xs text-gray-500">{t('global.blood_bank_delivery_receiver_hint')}</p>

                        <div className="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label className="mb-2 block text-xs">{t('global.blood_bank_receiver_department')}</Label>
                                <SearchableSelect
                                    value={receiverDepartmentId}
                                    onChange={setReceiverDepartmentId}
                                    options={receiverDepartments.map((d) => ({ value: String(d.id), label: d.name }))}
                                    placeholder={t('global.select')}
                                    required
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block text-xs">{t('global.blood_bank_receiver_nurse')}</Label>
                                <SearchableSelect
                                    value={receiverNurseId}
                                    onChange={setReceiverNurseId}
                                    options={nurseOptions}
                                    placeholder={nursesLoading ? t('global.loading') : t('global.select')}
                                    required
                                    disabled={!receiverDepartmentId || nursesLoading}
                                />
                            </div>
                        </div>

                        <p className="mt-3 text-xs text-gray-500">{t('global.deliver_blood_fifo_hint')}</p>
                        {workflowData.hasCrossmatchFlow && (
                            <p className="mt-1 text-xs text-red-600 dark:text-red-400">
                                {t('global.crossmatch_delivery_uses_reserved_hint')}
                            </p>
                        )}

                        {workflowData.availableUnits.length > 0 ? (
                            <div className="mt-4 max-h-72 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <Table embedded>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader className="w-12 text-center">{t('global.blood_bank_delivery_select_column')}</TableHeader>
                                            <TableHeader>{t('global.bag_number')}</TableHeader>
                                            <TableHeader>{t('global.blood_group')}</TableHeader>
                                            <TableHeader>{t('global.blood_rh')}</TableHeader>
                                            <TableHeader>{t('global.component_type')}</TableHeader>
                                            <TableHeader>{t('global.expires_at')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {workflowData.availableUnits.map((unit) => {
                                            const disabled =
                                                workflowData.hasCrossmatchFlow &&
                                                !workflowData.deliverableUnitIds.includes(unit.id);
                                            const checked = selectedUnitIds.includes(unit.id);

                                            return (
                                                <TableRow key={unit.id} className={disabled ? 'opacity-50' : undefined}>
                                                    <TableCell className="text-center">
                                                        <Checkbox
                                                            checked={checked}
                                                            disabled={disabled}
                                                            onChange={() => toggleUnit(unit.id, disabled)}
                                                        />
                                                    </TableCell>
                                                    <TableCell>{unit.bag_number ?? '—'}</TableCell>
                                                    <TableCell muted>{unit.blood_group ?? '—'}</TableCell>
                                                    <TableCell muted>{unit.rh ?? '—'}</TableCell>
                                                    <TableCell muted>{unit.component_type ?? '—'}</TableCell>
                                                    <TableCell muted dir="ltr">
                                                        {unit.expires_at ?? '—'}
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <Alert color="warning" className="mt-4 rounded-xl">
                                <span className="text-sm">{t('global.insufficient_blood_stock')}</span>
                            </Alert>
                        )}

                        <button
                            type="submit"
                            className={`${BLOOD_BANK_PRIMARY_BTN_CLASS} mt-4 bg-gradient-to-b from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700`}
                            disabled={processing || !receiverDepartmentId || !receiverNurseId}
                        >
                            {processing ? <Spinner size="sm" /> : <i className="bx bxs-check-circle" />}
                            {t('global.complete')}
                        </button>
                    </form>
                )}
            </WorkflowStepCard>

            {/* Blood check modal */}
            <Modal show={bloodCheckOpen} onClose={() => !processing && setBloodCheckOpen(false)} size="lg">
                <form onSubmit={handleBloodCheck}>
                    <ModalHeader>{t('global.blood_check_modal_title')}</ModalHeader>
                    <ModalBody className="space-y-4">
                        <p className="text-sm text-gray-500">{t('global.blood_check_modal_intro')}</p>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <Label className="mb-2 block text-xs">{t('global.blood_group')}</Label>
                                <BloodFormSegmented
                                    value={bloodCheckForm.abo_group}
                                    onChange={(v) => setBloodCheckForm((f) => ({ ...f, abo_group: v }))}
                                    options={['A', 'B', 'AB', 'O'].map((g) => ({ value: g, label: g, tone: 'rose' as const }))}
                                    columns={4}
                                    size="sm"
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block text-xs">{t('global.blood_rh')}</Label>
                                <BloodFormSegmented
                                    value={bloodCheckForm.rh}
                                    onChange={(v) => setBloodCheckForm((f) => ({ ...f, rh: v }))}
                                    options={[
                                        { value: '+', label: 'Rh+', tone: 'rose' },
                                        { value: '-', label: 'Rh−', tone: 'rose' },
                                    ]}
                                    columns={2}
                                    size="sm"
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block text-xs">{t('global.blood_type')}</Label>
                                <SearchableSelect
                                    value={bloodCheckForm.component_type}
                                    onChange={(v) => setBloodCheckForm((f) => ({ ...f, component_type: v }))}
                                    options={workflowData.bloodComponentTypes.map((type) => ({ value: type, label: type }))}
                                    required
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block text-xs">{t('global.quantity')}</Label>
                                <TextInput
                                    type="number"
                                    min={0}
                                    value={String(bloodCheckForm.quantity)}
                                    onChange={(e) =>
                                        setBloodCheckForm((f) => ({ ...f, quantity: parseInt(e.target.value, 10) || 0 }))
                                    }
                                    required
                                    className="rounded-lg"
                                />
                            </div>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label className="mb-2 block text-xs">
                                    {t('global.patient_typed_group')} ({t('global.optional')})
                                </Label>
                                <BloodFormSegmented
                                    value={bloodCheckForm.patient_typed_group}
                                    onChange={(v) => setBloodCheckForm((f) => ({ ...f, patient_typed_group: v }))}
                                    options={['A', 'B', 'AB', 'O'].map((g) => ({ value: g, label: g, tone: 'sky' as const }))}
                                    columns={4}
                                    size="sm"
                                    allowEmpty
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block text-xs">
                                    {t('global.patient_typed_rh')} ({t('global.optional')})
                                </Label>
                                <BloodFormSegmented
                                    value={bloodCheckForm.patient_typed_rh}
                                    onChange={(v) => setBloodCheckForm((f) => ({ ...f, patient_typed_rh: v }))}
                                    options={[
                                        { value: '+', label: 'Rh+', tone: 'sky' },
                                        { value: '-', label: 'Rh−', tone: 'sky' },
                                    ]}
                                    columns={2}
                                    size="sm"
                                    allowEmpty
                                />
                            </div>
                        </div>
                        <div>
                            <Label className="mb-2 block text-xs">{t('global.notes')}</Label>
                            <Textarea
                                rows={2}
                                value={bloodCheckForm.notes}
                                onChange={(e) => setBloodCheckForm((f) => ({ ...f, notes: e.target.value }))}
                                className="rounded-xl"
                            />
                        </div>
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="verify-lab-typing"
                                checked={verifyLabTyping}
                                onChange={(e) => setVerifyLabTyping(e.target.checked)}
                            />
                            <Label htmlFor="verify-lab-typing" className="text-sm">
                                {t('global.blood_check_verify_lab')}
                            </Label>
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={() => setBloodCheckOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" className={BLOOD_BANK_PRIMARY_BTN_CLASS} disabled={processing}>
                            {processing ? <Spinner size="sm" /> : null}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </div>
    );
}

function CrossmatchUnitRow({
    unit,
    samples,
    resultValues,
    canManage,
    canOverride,
    processing,
    overrideReason,
    onOverrideReasonChange,
    onPost,
}: {
    unit: BloodRequestWorkflowData['availableUnits'][number];
    samples: BloodRequestDetail['patient_samples'];
    resultValues: string[];
    canManage: boolean;
    canOverride: boolean;
    processing: boolean;
    overrideReason: string;
    onOverrideReasonChange: (reason: string) => void;
    onPost: (url: string, data?: Record<string, unknown>) => void;
}) {
    const { t } = useTranslation();
    const cx = unit.crossmatch;
    const [major, setMajor] = useState(cx?.major_result ?? 'pending');
    const [minor, setMinor] = useState(cx?.minor_result ?? 'pending');
    const [sampleId, setSampleId] = useState(cx?.patient_sample_id ? String(cx.patient_sample_id) : '');

    const compatible = cx && ['compatible', 'overridden'].includes(cx.status);

    return (
        <TableRow>
            <TableCell>
                <Link href={unit.urls.inventoryShow} className="font-medium text-rose-700 hover:underline dark:text-rose-300">
                    {unit.bag_number ?? '—'}
                </Link>
            </TableCell>
            <TableCell muted>{unit.blood_group ?? '—'}</TableCell>
            <TableCell muted>{unit.rh ?? '—'}</TableCell>
            <TableCell muted>{unit.component_type ?? '—'}</TableCell>
            <TableCell muted dir="ltr">
                {unit.volume_ml != null ? `${unit.volume_ml} ml` : '—'}
            </TableCell>
            <TableCell muted dir="ltr">
                {unit.expires_at ?? '—'}
            </TableCell>
            <TableCell>
                <div className="flex flex-wrap gap-1">
                    <Badge color={screeningStatusBadgeColor(unit.screening_status)} className="w-fit font-normal capitalize">
                        {unit.screening_status}
                    </Badge>
                    {unit.status !== 'available' && (
                        <Badge color={bloodUnitStatusBadgeColor(unit.status)} className="w-fit font-normal capitalize">
                            {unit.status}
                        </Badge>
                    )}
                </div>
            </TableCell>
            <TableCell>
                <Badge color={unit.auto_abo_rh_compatible ? 'success' : 'failure'} className="w-fit font-normal">
                    {unit.auto_abo_rh_compatible ? t('global.compatible') : t('global.incompatible')}
                </Badge>
            </TableCell>
            <TableCell>
                {cx ? (
                    <div>
                        <Badge color={crossmatchStatusBadgeColor(cx.status)} className="w-fit font-normal capitalize">
                            {cx.status}
                        </Badge>
                        {cx.auto_reason && <p className="mt-1 text-xs text-gray-500">{cx.auto_reason}</p>}
                    </div>
                ) : (
                    <Badge color="gray" className="w-fit font-normal">
                        {t('global.not_tested')}
                    </Badge>
                )}
            </TableCell>
            {canManage && (
                <TableCell className="min-w-[220px]">
                    <div className="space-y-2">
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                onPost(unit.urls.saveCrossmatch, {
                                    major_result: major,
                                    minor_result: minor,
                                    patient_sample_id: sampleId || null,
                                });
                            }}
                            className="grid gap-1.5"
                        >
                            <div className="grid grid-cols-2 gap-1.5">
                                <SearchableSelect
                                    value={major}
                                    onChange={setMajor}
                                    options={resultValues.map((v) => ({ value: v, label: v }))}
                                    compact
                                />
                                <SearchableSelect
                                    value={minor}
                                    onChange={setMinor}
                                    options={resultValues.map((v) => ({ value: v, label: v }))}
                                    compact
                                />
                            </div>
                            <div className="grid grid-cols-5 gap-1.5">
                                <div className="col-span-3">
                                    <SearchableSelect
                                        value={sampleId}
                                        onChange={setSampleId}
                                        options={[
                                            { value: '', label: t('global.select_sample') },
                                            ...samples.map((s) => ({
                                                value: String(s.id),
                                                label: s.sample_id ?? `#${s.id}`,
                                            })),
                                        ]}
                                        compact
                                    />
                                </div>
                                <div className="col-span-2">
                                    <button
                                        type="submit"
                                        className="w-full rounded-lg border border-rose-200 bg-white px-2 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 dark:border-rose-900/40 dark:bg-gray-900 dark:text-rose-300"
                                        disabled={processing}
                                    >
                                        {t('global.save')}
                                    </button>
                                </div>
                            </div>
                        </form>

                        {compatible &&
                            (unit.is_reserved ? (
                                <button
                                    type="button"
                                    className="w-full rounded-lg border border-amber-300 bg-amber-50 px-2 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200"
                                    disabled={processing}
                                    onClick={() => onPost(unit.urls.unreserve)}
                                >
                                    {t('global.unreserve_unit')}
                                </button>
                            ) : (
                                cx && unit.can_reserve && (
                                    <button
                                        type="button"
                                        className="w-full rounded-lg bg-emerald-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        disabled={processing || !unit.can_reserve}
                                        onClick={() => onPost(cx.urls.reserve)}
                                    >
                                        {t('global.reserve_unit')}
                                    </button>
                                )
                            ))}

                        {compatible && cx && !unit.can_reserve && !unit.is_reserved && unit.screening_status !== 'passed' && (
                            <p className="text-xs text-amber-700 dark:text-amber-300">
                                {t('global.screening_status')}: {unit.screening_status}
                            </p>
                        )}

                        {cx && cx.status === 'incompatible' && canOverride && (
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    onPost(cx.urls.override, { override_reason: overrideReason });
                                }}
                                className="flex gap-1"
                            >
                                <TextInput
                                    value={overrideReason}
                                    onChange={(e) => onOverrideReasonChange(e.target.value)}
                                    placeholder={t('global.override_reason')}
                                    required
                                    sizing="sm"
                                    className="min-w-0 flex-1 rounded-lg"
                                />
                                <button
                                    type="submit"
                                    className="shrink-0 rounded-lg bg-red-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                                    disabled={processing || !overrideReason}
                                >
                                    {t('global.override_compatible')}
                                </button>
                            </form>
                        )}
                    </div>
                </TableCell>
            )}
        </TableRow>
    );
}
