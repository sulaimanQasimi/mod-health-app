import { Head, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Textarea, TextInput } from 'flowbite-react';
import { ChangeEvent, FormEvent, useMemo, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    ProstheticCaseDetail,
    ProstheticCasePermissions,
    ProstheticCaseWorkflowOptions,
    ProstheticMeasurementRow,
    ProstheticPrescriptionLine,
} from '../../../types/prosthetics';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../../utils/settingsUi';

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

function SectionCard({
    title,
    badge,
    children,
}: {
    title: string;
    badge?: string;
    children: React.ReactNode;
}) {
    return (
        <Card>
            <div className="mb-4 flex items-center justify-between gap-2">
                <h3 className="text-base font-semibold text-gray-900 dark:text-white">{title}</h3>
                {badge && <Badge color="warning">{badge}</Badge>}
            </div>
            {children}
        </Card>
    );
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
        router.post(url, data, { preserveScroll: true, onFinish: () => setProcessing(false) });
    };

    const put = (url: string, data: Record<string, unknown>) => {
        setProcessing(true);
        router.put(url, data, { preserveScroll: true, onFinish: () => setProcessing(false) });
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

    return (
        <DashboardLayout>
            <Head title={prostheticCase.case_number} />

            <div className={`mx-auto space-y-6 ${SETTINGS_WIDE_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={prostheticCase.case_number}
                    subtitle={`${prostheticCase.patient_name ?? '—'} (ID ${prostheticCase.patient_id})`}
                    icon="bx-briefcase"
                    accent="from-emerald-500 to-teal-600"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            <Badge color="info">
                                {t(`global.prosthetics_case_status_${prostheticCase.status}`)}
                            </Badge>
                            <Button as="a" href={urls.print} color="green" outline size="sm" target="_blank">
                                {t('global.prosthetics_print_summary')}
                            </Button>
                        </div>
                    }
                />

                {permissions.is_read_only && (
                    <div className="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">
                        {t('global.prosthetics_case_readonly_notice')}
                    </div>
                )}

                <Card>
                    <div className="flex flex-wrap gap-2">
                        {workflowSteps.map((step, index) => {
                            const done = index <= caseRank;
                            return (
                                <Badge key={step} color={done ? 'info' : 'gray'} className="rounded-full">
                                    {index + 1}. {t(`global.prosthetics_case_status_${step}`)}
                                </Badge>
                            );
                        })}
                    </div>
                </Card>

                <SectionCard
                    title={t('global.prosthetics_assessment')}
                    badge={!permissions.edit_assessment ? t('global.completed') : undefined}
                >
                    <form
                        className="space-y-3"
                        onSubmit={(e) => {
                            e.preventDefault();
                            post(urls.assessment, assessment);
                        }}
                    >
                        <div>
                            <Label value={t('global.prosthetics_fit_outcome_label')} />
                            <select
                                disabled={!permissions.edit_assessment}
                                className="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
                                value={assessment.fit_outcome}
                                onChange={(e) => setAssessment((prev) => ({ ...prev, fit_outcome: e.target.value }))}
                            >
                                {formOptions.fit_outcomes.map((option) => (
                                    <option key={option} value={option}>
                                        {t(`global.prosthetics_fit_outcome_${option}`)}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <Textarea
                            rows={2}
                            disabled={!permissions.edit_assessment}
                            placeholder={t('global.prosthetics_history_present_condition_placeholder')}
                            value={assessment.history_present_condition}
                            onChange={(e) =>
                                setAssessment((prev) => ({ ...prev, history_present_condition: e.target.value }))
                            }
                        />
                        <Textarea
                            rows={2}
                            disabled={!permissions.edit_assessment}
                            placeholder={t('global.prosthetics_skin_stump_placeholder')}
                            value={assessment.skin_stump_notes}
                            onChange={(e) => setAssessment((prev) => ({ ...prev, skin_stump_notes: e.target.value }))}
                        />
                        <Textarea
                            rows={2}
                            disabled={!permissions.edit_assessment}
                            placeholder={t('global.prosthetics_functional_goals_placeholder')}
                            value={assessment.functional_goals}
                            onChange={(e) => setAssessment((prev) => ({ ...prev, functional_goals: e.target.value }))}
                        />
                        {permissions.edit_assessment && (
                            <Button type="submit" color="blue" size="sm" disabled={processing}>
                                {t('global.save')}
                            </Button>
                        )}
                    </form>
                </SectionCard>

                <SectionCard
                    title={t('global.prosthetics_measurements')}
                    badge={
                        prostheticCase.measurement_set.is_locked
                            ? `${t('global.prosthetics_locked')} v${prostheticCase.measurement_set.version}`
                            : undefined
                    }
                >
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            post(urls.measurements, { rows: measurementRows });
                        }}
                    >
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="border-b text-xs uppercase text-gray-500">
                                    <tr>
                                        <th className="px-2 py-2">{t('global.name')}</th>
                                        <th className="px-2 py-2">{t('global.value')}</th>
                                        <th className="px-2 py-2">{t('global.unit')}</th>
                                        <th className="px-2 py-2">{t('global.notes')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {measurementRows.map((row, index) => (
                                        <tr key={index} className="border-b dark:border-gray-700">
                                            {(['name', 'value_numeric', 'unit', 'notes'] as const).map((field) => (
                                                <td key={field} className="px-2 py-1">
                                                    <TextInput
                                                        sizing="sm"
                                                        disabled={measurementsDisabled}
                                                        value={String(row[field] ?? '')}
                                                        onChange={(e) => updateMeasurementRow(index, field, e.target.value)}
                                                    />
                                                </td>
                                            ))}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {!measurementsDisabled && (
                            <Button type="submit" color="blue" size="sm" className="mt-3" disabled={processing}>
                                {t('global.save')}
                            </Button>
                        )}
                    </form>
                    {permissions.edit_measurements && !prostheticCase.measurement_set.is_locked && (
                        <Button
                            color="yellow"
                            outline
                            size="sm"
                            className="mt-2"
                            disabled={processing}
                            onClick={() => post(urls.measurements_lock)}
                        >
                            {t('global.prosthetics_lock_measurement_set')}
                        </Button>
                    )}
                </SectionCard>

                <SectionCard
                    title={t('global.prosthetics_prescription')}
                    badge={!permissions.edit_prescription ? t('global.completed') : undefined}
                >
                    <form
                        className="space-y-3"
                        onSubmit={(e) => {
                            e.preventDefault();
                            post(urls.prescription, {
                                device_timing: prescription.device_timing,
                                special_instructions: prescription.special_instructions,
                                lines: prescription.lines,
                            });
                        }}
                    >
                        <div>
                            <Label value={t('global.prosthetics_device_timing_label')} />
                            <select
                                disabled={!permissions.edit_prescription}
                                className="mt-1 block w-full max-w-xs rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
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
                                setPrescription((prev) => ({ ...prev, special_instructions: e.target.value }))
                            }
                        />
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="border-b text-xs uppercase text-gray-500">
                                    <tr>
                                        <th className="px-2 py-2">{t('global.prosthetics_component')}</th>
                                        <th className="px-2 py-2">{t('global.quantity')}</th>
                                        <th className="px-2 py-2">{t('global.notes')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {prescription.lines.map((line, index) => (
                                        <tr key={index} className="border-b dark:border-gray-700">
                                            <td className="px-2 py-1">
                                                <select
                                                    disabled={!permissions.edit_prescription}
                                                    className="w-full min-w-[220px] rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
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
                                            </td>
                                            <td className="px-2 py-1">
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
                                            </td>
                                            <td className="px-2 py-1">
                                                <TextInput
                                                    sizing="sm"
                                                    disabled={!permissions.edit_prescription}
                                                    value={line.notes}
                                                    onChange={(e) =>
                                                        updatePrescriptionLine(index, 'notes', e.target.value)
                                                    }
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {permissions.edit_prescription && (
                            <Button type="submit" color="blue" size="sm" disabled={processing}>
                                {t('global.save')} & {t('global.prosthetics_finalize_prescription')}
                            </Button>
                        )}
                    </form>
                </SectionCard>

                {prostheticCase.estimate && (
                    <SectionCard
                        title={t('global.prosthetics_estimate')}
                        badge={!permissions.edit_estimate ? t('global.prosthetics_read_only_badge') : undefined}
                    >
                        <p className="mb-2 text-sm">
                            {t('global.parts')}: <strong>{prostheticCase.estimate.parts_total.toFixed(2)}</strong>{' '}
                            {prostheticCase.estimate.currency}
                        </p>
                        <form
                            className="flex flex-wrap items-end gap-3"
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(urls.estimate, {
                                    estimate_id: prostheticCase.estimate?.id,
                                    ...estimateForm,
                                });
                            }}
                        >
                            <div>
                                <Label value={t('global.prosthetics_labor')} />
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
                            <div>
                                <Label value={t('global.prosthetics_discount')} />
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
                        <p className="mt-2 text-sm">
                            <strong>{t('global.total')}:</strong> {prostheticCase.estimate.total.toFixed(2)}{' '}
                            {prostheticCase.estimate.currency} ({prostheticCase.estimate.status})
                        </p>
                    </SectionCard>
                )}

                <SectionCard title={t('global.prosthetics_workflow')}>
                    <div className="flex flex-wrap gap-2">
                        {permissions.submit_for_approval && (
                            <Button color="blue" outline size="sm" disabled={processing} onClick={() => post(urls.submit_approval)}>
                                {t('global.prosthetics_submit_for_approval')}
                            </Button>
                        )}
                        {permissions.approve_case && (
                            <Button color="green" size="sm" disabled={processing} onClick={() => post(urls.approve)}>
                                {t('global.prosthetics_approve_case')}
                            </Button>
                        )}
                        {!permissions.submit_for_approval && !permissions.approve_case && (
                            <span className="text-sm text-gray-500">
                                {t('global.prosthetics_workflow_actions_locked')}
                            </span>
                        )}
                    </div>
                </SectionCard>

                <SectionCard title={t('global.prosthetics_work_order')}>
                    {prostheticCase.work_order ? (
                        <div className="space-y-3">
                            <p className="text-sm">
                                <code>{prostheticCase.work_order.work_order_number}</code>
                                <span className="ml-2 text-gray-500">
                                    — {prostheticCase.work_order.status} /{' '}
                                    {t(`global.prosthetics_work_order_stage_${prostheticCase.work_order.production_stage}`)}
                                </span>
                            </p>
                            <form
                                className="flex flex-wrap items-end gap-2"
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    if (urls.work_order_update) {
                                        put(urls.work_order_update, { production_stage: workOrderStage });
                                    }
                                }}
                            >
                                <select
                                    disabled={!permissions.update_work_order}
                                    className="rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
                                    value={workOrderStage}
                                    onChange={(e) => setWorkOrderStage(e.target.value)}
                                >
                                    {formOptions.work_order_stages.map((stage) => (
                                        <option key={stage} value={stage}>
                                            {t(`global.prosthetics_work_order_stage_${stage}`)}
                                        </option>
                                    ))}
                                </select>
                                {permissions.update_work_order && (
                                    <Button type="submit" color="blue" size="sm" disabled={processing}>
                                        {t('global.save')}
                                    </Button>
                                )}
                            </form>
                            {permissions.issue_stock ? (
                                <Button
                                    color="red"
                                    outline
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
                                <p className="text-sm text-gray-500">{t('global.prosthetics_stock_issue_locked')}</p>
                            )}
                        </div>
                    ) : permissions.create_work_order ? (
                        <Button color="blue" size="sm" disabled={processing} onClick={() => post(urls.work_order)}>
                            {t('global.prosthetics_create_work_order')}
                        </Button>
                    ) : (
                        <p className="text-sm text-gray-500">
                            {t('global.prosthetics_work_order_available_after_approval')}
                        </p>
                    )}
                </SectionCard>

                <SectionCard
                    title={t('global.prosthetics_fitting')}
                    badge={!permissions.store_fitting ? t('global.prosthetics_read_only_badge') : undefined}
                >
                    <form
                        className="flex flex-wrap items-end gap-2"
                        onSubmit={(e) => {
                            e.preventDefault();
                            post(urls.fitting, fittingForm);
                        }}
                    >
                        <TextInput
                            type="date"
                            sizing="sm"
                            disabled={!permissions.store_fitting}
                            value={fittingForm.session_date}
                            onChange={(e) => setFittingForm((prev) => ({ ...prev, session_date: e.target.value }))}
                        />
                        <select
                            disabled={!permissions.store_fitting}
                            className="rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
                            value={fittingForm.outcome}
                            onChange={(e) => setFittingForm((prev) => ({ ...prev, outcome: e.target.value }))}
                        >
                            {formOptions.fitting_outcomes.map((outcome) => (
                                <option key={outcome} value={outcome}>
                                    {t(`global.prosthetics_fitting_outcome_${outcome}`)}
                                </option>
                            ))}
                        </select>
                        <TextInput
                            sizing="sm"
                            placeholder={t('global.notes')}
                            disabled={!permissions.store_fitting}
                            value={fittingForm.notes}
                            onChange={(e) => setFittingForm((prev) => ({ ...prev, notes: e.target.value }))}
                        />
                        {permissions.store_fitting && (
                            <Button type="submit" color="blue" size="sm" disabled={processing}>
                                {t('global.save')}
                            </Button>
                        )}
                    </form>
                </SectionCard>

                <SectionCard
                    title={t('global.prosthetics_delivery')}
                    badge={!permissions.store_delivery ? t('global.prosthetics_read_only_badge') : undefined}
                >
                    <form className="grid gap-3 md:grid-cols-2" onSubmit={(e) => {
                        e.preventDefault();
                        post(urls.delivery, {
                            ...deliveryForm,
                            handover_signed: deliveryForm.handover_signed ? '1' : '0',
                        });
                    }}>
                        <div>
                            <Label value={t('global.prosthetics_delivery_date')} />
                            <TextInput
                                type="date"
                                sizing="sm"
                                disabled={!permissions.store_delivery}
                                value={deliveryForm.delivered_at}
                                onChange={(e) => setDeliveryForm((prev) => ({ ...prev, delivered_at: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label value={t('global.prosthetics_received_by')} />
                            <TextInput
                                sizing="sm"
                                disabled={!permissions.store_delivery}
                                value={deliveryForm.received_by_name}
                                onChange={(e) =>
                                    setDeliveryForm((prev) => ({ ...prev, received_by_name: e.target.value }))
                                }
                            />
                        </div>
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                disabled={!permissions.store_delivery}
                                checked={deliveryForm.handover_signed}
                                onChange={(e) =>
                                    setDeliveryForm((prev) => ({ ...prev, handover_signed: e.target.checked }))
                                }
                            />
                            {t('global.prosthetics_handover_signed')}
                        </label>
                        <div className="md:col-span-2">
                            <Textarea
                                rows={2}
                                placeholder={t('global.notes')}
                                disabled={!permissions.store_delivery}
                                value={deliveryForm.notes}
                                onChange={(e) => setDeliveryForm((prev) => ({ ...prev, notes: e.target.value }))}
                            />
                        </div>
                        {permissions.store_delivery && (
                            <Button type="submit" color="blue" size="sm" disabled={processing}>
                                {t('global.save')}
                            </Button>
                        )}
                    </form>
                </SectionCard>

                <SectionCard
                    title={t('global.prosthetics_follow_up')}
                    badge={!permissions.store_follow_up ? t('global.prosthetics_read_only_badge') : undefined}
                >
                    <form
                        className="flex flex-wrap items-end gap-2"
                        onSubmit={(e) => {
                            e.preventDefault();
                            post(urls.follow_up, followUpForm);
                        }}
                    >
                        <TextInput
                            type="date"
                            sizing="sm"
                            disabled={!permissions.store_follow_up}
                            value={followUpForm.scheduled_at}
                            onChange={(e) => setFollowUpForm((prev) => ({ ...prev, scheduled_at: e.target.value }))}
                        />
                        <select
                            disabled={!permissions.store_follow_up}
                            className="rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
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
                        {permissions.store_follow_up && (
                            <Button type="submit" color="blue" size="sm" disabled={processing}>
                                {t('global.save')}
                            </Button>
                        )}
                    </form>
                </SectionCard>

                <SectionCard title={t('global.prosthetics_attachments')}>
                    <form className="mb-4 space-y-3" onSubmit={handleAttachmentUpload}>
                        <div className="grid gap-3 md:grid-cols-2">
                            <div>
                                <Label value={t('global.category')} />
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
                                <Label value={t('global.prosthetics_files')} />
                                <input
                                    type="file"
                                    multiple
                                    disabled={!permissions.manage_attachments}
                                    className="block w-full text-sm"
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
                        {permissions.manage_attachments && (
                            <Button type="submit" color="blue" size="sm" disabled={processing}>
                                {t('global.prosthetics_upload')}
                            </Button>
                        )}
                        {!permissions.manage_attachments && (
                            <p className="text-sm text-gray-500">{t('global.prosthetics_attachments_readonly_notice')}</p>
                        )}
                    </form>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th className="px-2 py-2">{t('global.file')}</th>
                                    <th className="px-2 py-2">{t('global.category')}</th>
                                    <th className="px-2 py-2">{t('global.date')}</th>
                                    <th className="px-2 py-2" />
                                </tr>
                            </thead>
                            <tbody>
                                {prostheticCase.attachments.map((attachment) => (
                                    <tr key={attachment.id} className="border-b dark:border-gray-700">
                                        <td className="px-2 py-2">
                                            {attachment.file_url ? (
                                                <a
                                                    href={attachment.file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="text-blue-600 hover:underline"
                                                >
                                                    {attachment.original_name}
                                                </a>
                                            ) : (
                                                attachment.original_name
                                            )}
                                        </td>
                                        <td className="px-2 py-2">{attachment.category}</td>
                                        <td className="px-2 py-2">{attachment.created_at ?? '—'}</td>
                                        <td className="px-2 py-2 text-right">
                                            {permissions.manage_attachments && (
                                                <Button
                                                    color="red"
                                                    outline
                                                    size="xs"
                                                    disabled={processing}
                                                    onClick={() => {
                                                        if (
                                                            window.confirm(
                                                                t('global.prosthetics_delete_attachment_confirm')
                                                            )
                                                        ) {
                                                            router.delete(`${urls.attachment_delete}/${attachment.id}`, {
                                                                preserveScroll: true,
                                                            });
                                                        }
                                                    }}
                                                >
                                                    {t('global.delete')}
                                                </Button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                                {prostheticCase.attachments.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-2 py-4 text-center text-gray-500">
                                            {t('global.no_attachments')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </SectionCard>

                {permissions.close_case && (
                    <Button
                        color="light"
                        disabled={processing}
                        onClick={() => {
                            if (window.confirm(t('global.prosthetics_close_case_confirm'))) {
                                post(urls.close);
                            }
                        }}
                    >
                        {t('global.prosthetics_close_case')}
                    </Button>
                )}
            </div>
        </DashboardLayout>
    );
}
