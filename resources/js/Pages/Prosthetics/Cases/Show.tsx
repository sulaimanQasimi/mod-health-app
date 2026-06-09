import { Head, router } from '@inertiajs/react';
import { Button, Label, Textarea, TextInput } from 'flowbite-react';
import { ChangeEvent, FormEvent, useMemo, useState } from 'react';
import CaseSectionPanel, {
    CaseDataTable,
    CaseDataTableBody,
    CaseDataTableHead,
    CaseDataTableRow,
    CaseDataTableTd,
    CaseDataTableTh,
    CaseFormActions,
} from '../../../Components/ProstheticsCases/CaseSectionPanel';
import CaseSummaryHeader from '../../../Components/ProstheticsCases/CaseSummaryHeader';
import CaseWorkflowStepper from '../../../Components/ProstheticsCases/CaseWorkflowStepper';
import {
    CASE_FILE_INPUT_CLASS,
    CASE_INFO_PANEL_CLASS,
    CASE_MUTED_NOTE_CLASS,
    CASE_SELECT_CLASS,
    CASE_SELECT_SM_CLASS,
} from '../../../Components/ProstheticsCases/caseShowUi';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import TableActionButton from '../../../Components/ui/TableActionButton';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    ProstheticCaseDetail,
    ProstheticCasePermissions,
    ProstheticCaseWorkflowOptions,
    ProstheticMeasurementRow,
    ProstheticPrescriptionLine,
} from '../../../types/prosthetics';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface CatalogOption {
    id: number;
    item_code: string;
    name: string;
    category: string | null;
}

interface ShowProps {
    prostheticCase: ProstheticCaseDetail;
    catalog: CatalogOption[];
    formOptions: ProstheticCaseWorkflowOptions;
    permissions: ProstheticCasePermissions;
    workflowSteps: string[];
    urls: Record<string, string>;
}

export default function ProstheticsCasesShow({
    prostheticCase,
    catalog,
    formOptions,
    permissions,
    workflowSteps,
    urls,
}: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);

    const caseRank = useMemo(() => {
        const ranks = workflowSteps.reduce<Record<string, number>>((acc, status, index) => {
            acc[status] = index;
            return acc;
        }, {});
        return ranks[prostheticCase.status] ?? -1;
    }, [prostheticCase.status, workflowSteps]);

    const isStatus = (...statuses: string[]) => statuses.includes(prostheticCase.status);

    const [assessment, setAssessment] = useState({
        fit_outcome: prostheticCase.assessment?.fit_outcome ?? 'pending',
        history_present_condition: prostheticCase.assessment?.history_present_condition ?? '',
        skin_stump_notes: prostheticCase.assessment?.skin_stump_notes ?? '',
        functional_goals: prostheticCase.assessment?.functional_goals ?? '',
    });

    const [measurementRows, setMeasurementRows] = useState<ProstheticMeasurementRow[]>(
        prostheticCase.measurement_set.rows
    );

    const defaultPrescription = prostheticCase.prescription ?? {
        device_timing: 'definitive',
        special_instructions: '',
        lines: Array.from({ length: 8 }, () => ({ catalog_id: '', quantity: '1', notes: '' })),
    };

    const [prescription, setPrescription] = useState(defaultPrescription);

    const [estimateForm, setEstimateForm] = useState({
        labor_total: prostheticCase.estimate?.labor_total ?? 0,
        discount: prostheticCase.estimate?.discount ?? 0,
    });

    const [workOrderStage, setWorkOrderStage] = useState(
        prostheticCase.work_order?.production_stage ?? 'pending'
    );

    const [fittingForm, setFittingForm] = useState({
        session_date: new Date().toISOString().slice(0, 10),
        outcome: 'pending',
        notes: '',
    });

    const [deliveryForm, setDeliveryForm] = useState({
        delivered_at: new Date().toISOString().slice(0, 10),
        received_by_name: '',
        handover_signed: false,
        notes: '',
    });

    const [followUpForm, setFollowUpForm] = useState({
        scheduled_at: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
        follow_up_type: '1_month',
    });

    const [attachmentForm, setAttachmentForm] = useState({
        category: 'general',
        description: '',
        files: null as FileList | null,
    });

    const post = (url: string, data: Record<string, unknown> = {}) => {
        setProcessing(true);
        router.post(url, data as Parameters<typeof router.post>[1], {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const put = (url: string, data: Record<string, unknown>) => {
        setProcessing(true);
        router.put(url, data as Parameters<typeof router.put>[1], {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const updateMeasurementRow = (index: number, field: keyof ProstheticMeasurementRow, value: string) => {
        setMeasurementRows((rows) =>
            rows.map((row, i) => (i === index ? { ...row, [field]: value } : row))
        );
    };

    const updatePrescriptionLine = (index: number, field: keyof ProstheticPrescriptionLine, value: string) => {
        setPrescription((prev) => ({
            ...prev,
            lines: prev.lines.map((line, i) => (i === index ? { ...line, [field]: value } : line)),
        }));
    };

    const measurementsDisabled =
        !permissions.edit_measurements || prostheticCase.measurement_set.is_locked;

    const handleAttachmentUpload = (e: FormEvent) => {
        e.preventDefault();
        if (!attachmentForm.files?.length) {
            return;
        }
        const formData = new FormData();
        formData.append('category', attachmentForm.category);
        if (attachmentForm.description) {
            formData.append('description', attachmentForm.description);
        }
        Array.from(attachmentForm.files).forEach((file) => formData.append('files[]', file));
        setProcessing(true);
        router.post(urls.attachments_upload, formData, {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const completedBadge = t('global.completed');
    const readOnlyBadge = t('global.prosthetics_read_only_badge');
    const lockedBadge = `${t('global.prosthetics_locked')} v${prostheticCase.measurement_set.version}`;

    return (
        <DashboardLayout>
            <Head title={prostheticCase.case_number} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={prostheticCase.case_number}
                    subtitle={t('global.prosthetics_case_detail')}
                    icon="bx-briefcase"
                    accent="from-slate-600 to-slate-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                <CaseSummaryHeader prostheticCase={prostheticCase} printUrl={urls.print} />

                {permissions.is_read_only && (
                    <div className={CASE_INFO_PANEL_CLASS}>
                        <i className="bx bx-lock-alt me-2 align-middle" />
                        {t('global.prosthetics_case_readonly_notice')}
                    </div>
                )}

                <div className="rounded-xl border border-gray-200 bg-white px-4 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <p className="mb-3 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {t('global.prosthetics_workflow')}
                    </p>
                    <CaseWorkflowStepper steps={workflowSteps} currentRank={caseRank} />
                </div>

                <div className="space-y-4">
                    <CaseSectionPanel
                        id="case-assessment"
                        icon="bx-user-check"
                        title={t('global.prosthetics_assessment')}
                        badge={!permissions.edit_assessment ? completedBadge : undefined}
                        badgeTone={!permissions.edit_assessment ? 'done' : 'neutral'}
                        defaultOpen={isStatus('new', 'referred', 'under_assessment')}
                    >
                        <form
                            className="space-y-4"
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(urls.assessment, assessment);
                            }}
                        >
                            <div>
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.prosthetics_fit_outcome_label')}</Label>
                                </div>
                                <select
                                    disabled={!permissions.edit_assessment}
                                    className={CASE_SELECT_CLASS}
                                    value={assessment.fit_outcome}
                                    onChange={(e) =>
                                        setAssessment((prev) => ({ ...prev, fit_outcome: e.target.value }))
                                    }
                                >
                                    {formOptions.fit_outcomes.map((option) => (
                                        <option key={option} value={option}>
                                            {t(`global.prosthetics_fit_outcome_${option}`)}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="grid gap-4 lg:grid-cols-2">
                                <Textarea
                                    rows={3}
                                    disabled={!permissions.edit_assessment}
                                    placeholder={t('global.prosthetics_history_present_condition_placeholder')}
                                    value={assessment.history_present_condition}
                                    onChange={(e) =>
                                        setAssessment((prev) => ({
                                            ...prev,
                                            history_present_condition: e.target.value,
                                        }))
                                    }
                                />
                                <Textarea
                                    rows={3}
                                    disabled={!permissions.edit_assessment}
                                    placeholder={t('global.prosthetics_skin_stump_placeholder')}
                                    value={assessment.skin_stump_notes}
                                    onChange={(e) =>
                                        setAssessment((prev) => ({ ...prev, skin_stump_notes: e.target.value }))
                                    }
                                />
                            </div>
                            <Textarea
                                rows={2}
                                disabled={!permissions.edit_assessment}
                                placeholder={t('global.prosthetics_functional_goals_placeholder')}
                                value={assessment.functional_goals}
                                onChange={(e) =>
                                    setAssessment((prev) => ({ ...prev, functional_goals: e.target.value }))
                                }
                            />
                            {permissions.edit_assessment && (
                                <CaseFormActions>
                                    <Button type="submit" color="blue" size="sm" disabled={processing}>
                                        {t('global.save')}
                                    </Button>
                                </CaseFormActions>
                            )}
                        </form>
                    </CaseSectionPanel>

                    <CaseSectionPanel
                        id="case-measurements"
                        icon="bx-ruler"
                        title={t('global.prosthetics_measurements')}
                        badge={
                            prostheticCase.measurement_set.is_locked
                                ? lockedBadge
                                : !permissions.edit_measurements
                                  ? completedBadge
                                  : undefined
                        }
                        badgeTone={prostheticCase.measurement_set.is_locked ? 'locked' : 'done'}
                        defaultOpen={isStatus('under_assessment') && !prostheticCase.measurement_set.is_locked}
                    >
                        <form
                            className="space-y-4"
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(urls.measurements, { rows: measurementRows });
                            }}
                        >
                            <CaseDataTable>
                                <CaseDataTableHead>
                                    <CaseDataTableTh>{t('global.name')}</CaseDataTableTh>
                                    <CaseDataTableTh>{t('global.value')}</CaseDataTableTh>
                                    <CaseDataTableTh>{t('global.unit')}</CaseDataTableTh>
                                    <CaseDataTableTh>{t('global.notes')}</CaseDataTableTh>
                                </CaseDataTableHead>
                                <CaseDataTableBody>
                                    {measurementRows.map((row, index) => (
                                        <CaseDataTableRow key={index}>
                                            {(['name', 'value_numeric', 'unit', 'notes'] as const).map((field) => (
                                                <CaseDataTableTd key={field}>
                                                    <TextInput
                                                        sizing="sm"
                                                        disabled={measurementsDisabled}
                                                        value={String(row[field] ?? '')}
                                                        onChange={(e) =>
                                                            updateMeasurementRow(index, field, e.target.value)
                                                        }
                                                    />
                                                </CaseDataTableTd>
                                            ))}
                                        </CaseDataTableRow>
                                    ))}
                                </CaseDataTableBody>
                            </CaseDataTable>
                            {!measurementsDisabled && (
                                <CaseFormActions>
                                    <Button type="submit" color="blue" size="sm" disabled={processing}>
                                        {t('global.save')}
                                    </Button>
                                    {permissions.edit_measurements && !prostheticCase.measurement_set.is_locked && (
                                        <Button
                                            type="button"
                                            color="light"
                                            size="sm"
                                            disabled={processing}
                                            onClick={() => post(urls.measurements_lock)}
                                        >
                                            {t('global.prosthetics_lock_measurement_set')}
                                        </Button>
                                    )}
                                </CaseFormActions>
                            )}
                        </form>
                    </CaseSectionPanel>

                    <CaseSectionPanel
                        id="case-prescription"
                        icon="bx-file"
                        title={t('global.prosthetics_prescription')}
                        badge={!permissions.edit_prescription ? completedBadge : undefined}
                        badgeTone={!permissions.edit_prescription ? 'done' : 'neutral'}
                        defaultOpen={isStatus('measurement_completed')}
                    >
                        <form
                            className="space-y-4"
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(urls.prescription, {
                                    device_timing: prescription.device_timing,
                                    special_instructions: prescription.special_instructions,
                                    lines: prescription.lines,
                                });
                            }}
                        >
                            <div className="max-w-sm">
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.prosthetics_device_timing_label')}</Label>
                                </div>
                                <select
                                    disabled={!permissions.edit_prescription}
                                    className={CASE_SELECT_CLASS}
                                    value={prescription.device_timing}
                                    onChange={(e) =>
                                        setPrescription((prev) => ({ ...prev, device_timing: e.target.value }))
                                    }
                                >
                                    {formOptions.device_timings.map((timing) => (
                                        <option key={timing} value={timing}>
                                            {t(`global.prosthetics_device_timing_${timing}`)}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <Textarea
                                rows={2}
                                disabled={!permissions.edit_prescription}
                                placeholder={t('global.prosthetics_special_instructions_placeholder')}
                                value={prescription.special_instructions ?? ''}
                                onChange={(e) =>
                                    setPrescription((prev) => ({
                                        ...prev,
                                        special_instructions: e.target.value,
                                    }))
                                }
                            />
                            <CaseDataTable>
                                <CaseDataTableHead>
                                    <CaseDataTableTh>{t('global.prosthetics_component')}</CaseDataTableTh>
                                    <CaseDataTableTh className="w-28">{t('global.quantity')}</CaseDataTableTh>
                                    <CaseDataTableTh>{t('global.notes')}</CaseDataTableTh>
                                </CaseDataTableHead>
                                <CaseDataTableBody>
                                    {prescription.lines.map((line, index) => (
                                        <CaseDataTableRow key={index}>
                                            <CaseDataTableTd>
                                                <select
                                                    disabled={!permissions.edit_prescription}
                                                    className={CASE_SELECT_SM_CLASS}
                                                    value={String(line.catalog_id)}
                                                    onChange={(e) =>
                                                        updatePrescriptionLine(index, 'catalog_id', e.target.value)
                                                    }
                                                >
                                                    <option value="">—</option>
                                                    {catalog.map((item) => (
                                                        <option key={item.id} value={item.id}>
                                                            {item.item_code} — {item.name}
                                                        </option>
                                                    ))}
                                                </select>
                                            </CaseDataTableTd>
                                            <CaseDataTableTd>
                                                <TextInput
                                                    sizing="sm"
                                                    type="number"
                                                    step="0.001"
                                                    min={0}
                                                    disabled={!permissions.edit_prescription}
                                                    value={String(line.quantity)}
                                                    onChange={(e) =>
                                                        updatePrescriptionLine(index, 'quantity', e.target.value)
                                                    }
                                                />
                                            </CaseDataTableTd>
                                            <CaseDataTableTd>
                                                <TextInput
                                                    sizing="sm"
                                                    disabled={!permissions.edit_prescription}
                                                    value={line.notes}
                                                    onChange={(e) =>
                                                        updatePrescriptionLine(index, 'notes', e.target.value)
                                                    }
                                                />
                                            </CaseDataTableTd>
                                        </CaseDataTableRow>
                                    ))}
                                </CaseDataTableBody>
                            </CaseDataTable>
                            {permissions.edit_prescription && (
                                <CaseFormActions>
                                    <Button type="submit" color="blue" size="sm" disabled={processing}>
                                        {t('global.save')} & {t('global.prosthetics_finalize_prescription')}
                                    </Button>
                                </CaseFormActions>
                            )}
                        </form>
                    </CaseSectionPanel>

                    {prostheticCase.estimate && (
                        <CaseSectionPanel
                            id="case-estimate"
                            icon="bx-calculator"
                            title={t('global.prosthetics_estimate')}
                            badge={!permissions.edit_estimate ? readOnlyBadge : undefined}
                            badgeTone="locked"
                            defaultOpen={isStatus('prescription_completed')}
                        >
                            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                                <div className="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 dark:border-gray-800 dark:bg-gray-800/50">
                                    <p className="text-xs text-gray-500">{t('global.parts')}</p>
                                    <p className="text-sm font-semibold text-gray-900 dark:text-white">
                                        {prostheticCase.estimate.parts_total.toFixed(2)} {prostheticCase.estimate.currency}
                                    </p>
                                </div>
                                <div className="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 dark:border-gray-800 dark:bg-gray-800/50">
                                    <p className="text-xs text-gray-500">{t('global.total')}</p>
                                    <p className="text-sm font-semibold text-gray-900 dark:text-white">
                                        {prostheticCase.estimate.total.toFixed(2)} {prostheticCase.estimate.currency}
                                    </p>
                                </div>
                                <div className="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 dark:border-gray-800 dark:bg-gray-800/50">
                                    <p className="text-xs text-gray-500">{t('global.status')}</p>
                                    <p className="text-sm font-semibold capitalize text-gray-900 dark:text-white">
                                        {prostheticCase.estimate.status}
                                    </p>
                                </div>
                            </div>
                            <form
                                className="flex flex-wrap items-end gap-4"
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    post(urls.estimate, {
                                        estimate_id: prostheticCase.estimate?.id,
                                        ...estimateForm,
                                    });
                                }}
                            >
                                <div className="min-w-[140px]">
                                    <div className="mb-1.5 text-gray-600">
                                        <Label>{t('global.prosthetics_labor')}</Label>
                                    </div>
                                    <TextInput
                                        sizing="sm"
                                        type="number"
                                        step="0.01"
                                        disabled={!permissions.edit_estimate}
                                        value={String(estimateForm.labor_total)}
                                        onChange={(e) =>
                                            setEstimateForm((prev) => ({
                                                ...prev,
                                                labor_total: Number(e.target.value),
                                            }))
                                        }
                                    />
                                </div>
                                <div className="min-w-[140px]">
                                    <div className="mb-1.5 text-gray-600">
                                        <Label>{t('global.prosthetics_discount')}</Label>
                                    </div>
                                    <TextInput
                                        sizing="sm"
                                        type="number"
                                        step="0.01"
                                        disabled={!permissions.edit_estimate}
                                        value={String(estimateForm.discount)}
                                        onChange={(e) =>
                                            setEstimateForm((prev) => ({
                                                ...prev,
                                                discount: Number(e.target.value),
                                            }))
                                        }
                                    />
                                </div>
                                {permissions.edit_estimate && (
                                    <Button type="submit" color="blue" size="sm" disabled={processing}>
                                        {t('global.save')}
                                    </Button>
                                )}
                            </form>
                        </CaseSectionPanel>
                    )}

                    <CaseSectionPanel
                        id="case-workflow"
                        icon="bx-git-merge"
                        title={t('global.prosthetics_workflow')}
                        defaultOpen={isStatus('prescription_completed', 'waiting_approval')}
                    >
                        <div className="flex flex-wrap gap-2">
                            {permissions.submit_for_approval && (
                                <Button
                                    color="blue"
                                    size="sm"
                                    disabled={processing}
                                    onClick={() => post(urls.submit_approval)}
                                >
                                    {t('global.prosthetics_submit_for_approval')}
                                </Button>
                            )}
                            {permissions.approve_case && (
                                <Button color="blue" size="sm" disabled={processing} onClick={() => post(urls.approve)}>
                                    {t('global.prosthetics_approve_case')}
                                </Button>
                            )}
                            {!permissions.submit_for_approval && !permissions.approve_case && (
                                <p className={CASE_MUTED_NOTE_CLASS}>
                                    {t('global.prosthetics_workflow_actions_locked')}
                                </p>
                            )}
                        </div>
                    </CaseSectionPanel>

                    <CaseSectionPanel
                        id="case-work-order"
                        icon="bx-wrench"
                        title={t('global.prosthetics_work_order')}
                        defaultOpen={isStatus('approved', 'in_production')}
                    >
                        {prostheticCase.work_order ? (
                            <div className="space-y-4">
                                <div className="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 dark:border-gray-800 dark:bg-gray-800/50">
                                    <p className="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                        {prostheticCase.work_order.work_order_number}
                                    </p>
                                    <p className={`mt-1 ${CASE_MUTED_NOTE_CLASS}`}>
                                        {prostheticCase.work_order.status} ·{' '}
                                        {t(
                                            `global.prosthetics_work_order_stage_${prostheticCase.work_order.production_stage}`
                                        )}
                                    </p>
                                </div>
                                <form
                                    className="flex flex-wrap items-end gap-3"
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        if (urls.work_order_update) {
                                            put(urls.work_order_update, { production_stage: workOrderStage });
                                        }
                                    }}
                                >
                                    <div className="min-w-[220px]">
                                        <div className="mb-1.5 text-gray-600">
                                            <Label>{t('global.status')}</Label>
                                        </div>
                                        <select
                                            disabled={!permissions.update_work_order}
                                            className={CASE_SELECT_CLASS}
                                            value={workOrderStage}
                                            onChange={(e) => setWorkOrderStage(e.target.value)}
                                        >
                                            {formOptions.work_order_stages.map((stage) => (
                                                <option key={stage} value={stage}>
                                                    {t(`global.prosthetics_work_order_stage_${stage}`)}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    {permissions.update_work_order && (
                                        <Button type="submit" color="blue" size="sm" disabled={processing}>
                                            {t('global.save')}
                                        </Button>
                                    )}
                                </form>
                                {permissions.issue_stock ? (
                                    <Button
                                        color="light"
                                        size="sm"
                                        disabled={processing}
                                        onClick={() =>
                                            post(urls.issue_stock, {
                                                prosthetic_work_order_id: prostheticCase.work_order?.id,
                                            })
                                        }
                                    >
                                        {t('global.prosthetics_issue_components')}
                                    </Button>
                                ) : (
                                    <p className={CASE_MUTED_NOTE_CLASS}>
                                        {t('global.prosthetics_stock_issue_locked')}
                                    </p>
                                )}
                            </div>
                        ) : permissions.create_work_order ? (
                            <Button color="blue" size="sm" disabled={processing} onClick={() => post(urls.work_order)}>
                                {t('global.prosthetics_create_work_order')}
                            </Button>
                        ) : (
                            <p className={CASE_MUTED_NOTE_CLASS}>
                                {t('global.prosthetics_work_order_available_after_approval')}
                            </p>
                        )}
                    </CaseSectionPanel>

                    <CaseSectionPanel
                        id="case-fitting"
                        icon="bx-walk"
                        title={t('global.prosthetics_fitting')}
                        badge={!permissions.store_fitting ? readOnlyBadge : undefined}
                        badgeTone="locked"
                        defaultOpen={isStatus('in_production', 'trial_fit')}
                    >
                        <form
                            className="grid gap-3 md:grid-cols-2 lg:grid-cols-4 lg:items-end"
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(urls.fitting, fittingForm);
                            }}
                        >
                            <div>
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.date')}</Label>
                                </div>
                                <TextInput
                                    type="date"
                                    sizing="sm"
                                    disabled={!permissions.store_fitting}
                                    value={fittingForm.session_date}
                                    onChange={(e) =>
                                        setFittingForm((prev) => ({ ...prev, session_date: e.target.value }))
                                    }
                                />
                            </div>
                            <div>
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.status')}</Label>
                                </div>
                                <select
                                    disabled={!permissions.store_fitting}
                                    className={CASE_SELECT_CLASS}
                                    value={fittingForm.outcome}
                                    onChange={(e) =>
                                        setFittingForm((prev) => ({ ...prev, outcome: e.target.value }))
                                    }
                                >
                                    {formOptions.fitting_outcomes.map((outcome) => (
                                        <option key={outcome} value={outcome}>
                                            {t(`global.prosthetics_fitting_outcome_${outcome}`)}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="lg:col-span-2">
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.notes')}</Label>
                                </div>
                                <TextInput
                                    sizing="sm"
                                    placeholder={t('global.notes')}
                                    disabled={!permissions.store_fitting}
                                    value={fittingForm.notes}
                                    onChange={(e) =>
                                        setFittingForm((prev) => ({ ...prev, notes: e.target.value }))
                                    }
                                />
                            </div>
                            {permissions.store_fitting && (
                                <div className="md:col-span-2 lg:col-span-4">
                                    <Button type="submit" color="blue" size="sm" disabled={processing}>
                                        {t('global.save')}
                                    </Button>
                                </div>
                            )}
                        </form>
                    </CaseSectionPanel>

                    <CaseSectionPanel
                        id="case-delivery"
                        icon="bx-package"
                        title={t('global.prosthetics_delivery')}
                        badge={!permissions.store_delivery ? readOnlyBadge : undefined}
                        badgeTone="locked"
                        defaultOpen={isStatus('trial_fit')}
                    >
                        <form
                            className="grid gap-4 md:grid-cols-2"
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(urls.delivery, {
                                    ...deliveryForm,
                                    handover_signed: deliveryForm.handover_signed ? '1' : '0',
                                });
                            }}
                        >
                            <div>
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.prosthetics_delivery_date')}</Label>
                                </div>
                                <TextInput
                                    type="date"
                                    sizing="sm"
                                    disabled={!permissions.store_delivery}
                                    value={deliveryForm.delivered_at}
                                    onChange={(e) =>
                                        setDeliveryForm((prev) => ({ ...prev, delivered_at: e.target.value }))
                                    }
                                />
                            </div>
                            <div>
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.prosthetics_received_by')}</Label>
                                </div>
                                <TextInput
                                    sizing="sm"
                                    disabled={!permissions.store_delivery}
                                    value={deliveryForm.received_by_name}
                                    onChange={(e) =>
                                        setDeliveryForm((prev) => ({ ...prev, received_by_name: e.target.value }))
                                    }
                                />
                            </div>
                            <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input
                                    type="checkbox"
                                    className="rounded border-gray-300 text-slate-700 focus:ring-slate-500"
                                    disabled={!permissions.store_delivery}
                                    checked={deliveryForm.handover_signed}
                                    onChange={(e) =>
                                        setDeliveryForm((prev) => ({ ...prev, handover_signed: e.target.checked }))
                                    }
                                />
                                {t('global.prosthetics_handover_signed')}
                            </label>
                            <div className="md:col-span-2">
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.notes')}</Label>
                                </div>
                                <Textarea
                                    rows={2}
                                    disabled={!permissions.store_delivery}
                                    value={deliveryForm.notes}
                                    onChange={(e) =>
                                        setDeliveryForm((prev) => ({ ...prev, notes: e.target.value }))
                                    }
                                />
                            </div>
                            {permissions.store_delivery && (
                                <div className="md:col-span-2">
                                    <Button type="submit" color="blue" size="sm" disabled={processing}>
                                        {t('global.save')}
                                    </Button>
                                </div>
                            )}
                        </form>
                    </CaseSectionPanel>

                    <CaseSectionPanel
                        id="case-follow-up"
                        icon="bx-calendar"
                        title={t('global.prosthetics_follow_up')}
                        badge={!permissions.store_follow_up ? readOnlyBadge : undefined}
                        badgeTone="locked"
                        defaultOpen={isStatus('delivered')}
                    >
                        <form
                            className="flex flex-wrap items-end gap-3"
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(urls.follow_up, followUpForm);
                            }}
                        >
                            <div>
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.date')}</Label>
                                </div>
                                <TextInput
                                    type="date"
                                    sizing="sm"
                                    disabled={!permissions.store_follow_up}
                                    value={followUpForm.scheduled_at}
                                    onChange={(e) =>
                                        setFollowUpForm((prev) => ({ ...prev, scheduled_at: e.target.value }))
                                    }
                                />
                            </div>
                            <div className="min-w-[180px]">
                                <div className="mb-1.5 text-gray-600">
                                    <Label>{t('global.type')}</Label>
                                </div>
                                <select
                                    disabled={!permissions.store_follow_up}
                                    className={CASE_SELECT_CLASS}
                                    value={followUpForm.follow_up_type}
                                    onChange={(e) =>
                                        setFollowUpForm((prev) => ({ ...prev, follow_up_type: e.target.value }))
                                    }
                                >
                                    {formOptions.follow_up_types.map((type) => (
                                        <option key={type} value={type}>
                                            {t(`global.prosthetics_follow_up_type_${type}`)}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            {permissions.store_follow_up && (
                                <Button type="submit" color="blue" size="sm" disabled={processing}>
                                    {t('global.save')}
                                </Button>
                            )}
                        </form>
                    </CaseSectionPanel>

                    <CaseSectionPanel
                        id="case-attachments"
                        icon="bx-paperclip"
                        title={t('global.prosthetics_attachments')}
                        badge={String(prostheticCase.attachments.length)}
                        defaultOpen={false}
                    >
                        <form className="mb-5 space-y-4" onSubmit={handleAttachmentUpload}>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div className="mb-1.5 text-gray-600">
                                        <Label>{t('global.category')}</Label>
                                    </div>
                                    <TextInput
                                        sizing="sm"
                                        disabled={!permissions.manage_attachments}
                                        value={attachmentForm.category}
                                        onChange={(e) =>
                                            setAttachmentForm((prev) => ({ ...prev, category: e.target.value }))
                                        }
                                    />
                                </div>
                                <div>
                                    <div className="mb-1.5 text-gray-600">
                                        <Label>{t('global.prosthetics_files')}</Label>
                                    </div>
                                    <input
                                        type="file"
                                        multiple
                                        disabled={!permissions.manage_attachments}
                                        className={CASE_FILE_INPUT_CLASS}
                                        onChange={(e: ChangeEvent<HTMLInputElement>) =>
                                            setAttachmentForm((prev) => ({ ...prev, files: e.target.files }))
                                        }
                                    />
                                </div>
                            </div>
                            <TextInput
                                sizing="sm"
                                placeholder={t('global.prosthetics_description_optional')}
                                disabled={!permissions.manage_attachments}
                                value={attachmentForm.description}
                                onChange={(e) =>
                                    setAttachmentForm((prev) => ({ ...prev, description: e.target.value }))
                                }
                            />
                            {permissions.manage_attachments ? (
                                <Button type="submit" color="blue" size="sm" disabled={processing}>
                                    {t('global.prosthetics_upload')}
                                </Button>
                            ) : (
                                <p className={CASE_MUTED_NOTE_CLASS}>
                                    {t('global.prosthetics_attachments_readonly_notice')}
                                </p>
                            )}
                        </form>

                        <CaseDataTable>
                            <CaseDataTableHead>
                                <CaseDataTableTh>{t('global.file')}</CaseDataTableTh>
                                <CaseDataTableTh>{t('global.category')}</CaseDataTableTh>
                                <CaseDataTableTh>{t('global.date')}</CaseDataTableTh>
                                <CaseDataTableTh className="w-16 text-end">{t('global.actions')}</CaseDataTableTh>
                            </CaseDataTableHead>
                            <CaseDataTableBody>
                                {prostheticCase.attachments.map((attachment) => (
                                    <CaseDataTableRow key={attachment.id}>
                                        <CaseDataTableTd>
                                            {attachment.file_url ? (
                                                <a
                                                    href={attachment.file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="font-medium text-slate-700 hover:text-slate-900 hover:underline dark:text-slate-300"
                                                >
                                                    {attachment.original_name}
                                                </a>
                                            ) : (
                                                attachment.original_name
                                            )}
                                        </CaseDataTableTd>
                                        <CaseDataTableTd className="text-gray-600">{attachment.category}</CaseDataTableTd>
                                        <CaseDataTableTd className="text-gray-500" dir="ltr">
                                            {attachment.created_at ?? '—'}
                                        </CaseDataTableTd>
                                        <CaseDataTableTd className="text-end">
                                            {permissions.manage_attachments && (
                                                <TableActionButton
                                                    kind="delete"
                                                    confirm={t('global.prosthetics_delete_attachment_confirm')}
                                                    disabled={processing}
                                                    onClick={() =>
                                                        router.delete(
                                                            `${urls.attachment_delete}/${attachment.id}`,
                                                            { preserveScroll: true }
                                                        )
                                                    }
                                                />
                                            )}
                                        </CaseDataTableTd>
                                    </CaseDataTableRow>
                                ))}
                                {prostheticCase.attachments.length === 0 && (
                                    <CaseDataTableRow>
                                        <CaseDataTableTd colSpan={4} className="py-8 text-center text-gray-500">
                                            {t('global.no_attachments')}
                                        </CaseDataTableTd>
                                    </CaseDataTableRow>
                                )}
                            </CaseDataTableBody>
                        </CaseDataTable>
                    </CaseSectionPanel>
                </div>

                {permissions.close_case && (
                    <div className="flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                        <Button
                            color="light"
                            size="sm"
                            disabled={processing}
                            onClick={() => {
                                if (window.confirm(t('global.prosthetics_close_case_confirm'))) {
                                    post(urls.close);
                                }
                            }}
                        >
                            {t('global.prosthetics_close_case')}
                        </Button>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
