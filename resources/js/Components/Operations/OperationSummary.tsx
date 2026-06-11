import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { OperationDetail } from '../../types/operation';
import { anesthesiaTypeLabel } from '../Anesthesias/anesthesiaUi';
import {
    OPERATION_CARD_CLASS,
    OPERATION_HERO_AVATAR_CLASS,
    operationApprovalLabel,
    operationDoneLabel,
    operationReservedLabel,
    patientInitials,
} from './operationUi';

interface OperationSummaryProps {
    operation: OperationDetail;
}

function SummaryField({ label, value, icon }: { label: string; value: string; icon?: string }) {
    return (
        <div className="rounded-xl border border-gray-100 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-gray-900/50">
            <p className="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {icon && <i className={`bx ${icon} text-sm text-amber-500/80`} />}
                {label}
            </p>
            <p className="mt-1 text-sm font-medium text-gray-900 dark:text-white">{value || '—'}</p>
        </div>
    );
}

function TextBlock({ label, value, icon }: { label: string; value: string; icon: string }) {
    return (
        <div className="rounded-xl border border-gray-100 bg-gray-50/80 px-4 py-3.5 dark:border-gray-800 dark:bg-gray-800/40">
            <p className="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <i className={`bx ${icon} text-sm text-amber-500/80`} />
                {label}
            </p>
            <p className="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-900 dark:text-white">
                {value || '—'}
            </p>
        </div>
    );
}

export default function OperationSummary({ operation }: OperationSummaryProps) {
    const { t } = useTranslation();
    const scheduleLabel = [operation.date_display, operation.time].filter(Boolean).join(' · ');
    const assistantsLabel = (operation.operation_assistants_names ?? []).join('، ');

    return (
        <div className={OPERATION_CARD_CLASS}>
            <div className="bg-gradient-to-br from-amber-600 via-amber-600 to-orange-700 px-5 py-5 text-white">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-4">
                        <div className={OPERATION_HERO_AVATAR_CLASS}>
                            {patientInitials(operation.patient?.name)}
                        </div>
                        <div className="min-w-0">
                            <h2 className="text-lg font-bold tracking-tight">
                                {operation.patient?.name || '—'}
                            </h2>
                            {operation.patient?.father_name && (
                                <p className="mt-0.5 text-sm text-amber-100/90">
                                    {t('global.father_name')}: {operation.patient.father_name}
                                </p>
                            )}
                            <p className="mt-1 text-xs text-amber-100/75">
                                #{operation.id}
                                {operation.patient?.id_card
                                    ? ` · ${t('global.card_number')}: ${operation.patient.id_card}`
                                    : ''}
                            </p>
                            <div className="mt-2 flex flex-wrap gap-2">
                                <Badge color={operation.is_operation_done ? 'info' : 'warning'}>
                                    {operationDoneLabel(operation.is_operation_done, t)}
                                </Badge>
                                <Badge color={operation.is_operation_approved ? 'success' : 'failure'}>
                                    {operationApprovalLabel(operation.is_operation_approved, t)}
                                </Badge>
                                {operation.is_reserved && (
                                    <Badge color="purple">{operationReservedLabel(true, t)}</Badge>
                                )}
                            </div>
                        </div>
                    </div>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div className="rounded-xl bg-white/15 px-3 py-2 backdrop-blur-sm">
                            <p className="text-[10px] font-semibold uppercase tracking-wider text-amber-100/90">
                                {t('global.operation_type')}
                            </p>
                            <p className="truncate text-sm font-semibold">{operation.operation_type_name ?? '—'}</p>
                        </div>
                        <div className="rounded-xl bg-white/15 px-3 py-2 backdrop-blur-sm">
                            <p className="text-[10px] font-semibold uppercase tracking-wider text-amber-100/90">
                                {t('global.date')}
                            </p>
                            <p className="truncate text-sm font-semibold">{scheduleLabel || '—'}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div className="space-y-4 p-5">
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryField label={t('global.operation_surgion')} value={operation.surgion_name ?? ''} icon="bx-user-md" />
                    <SummaryField label={t('global.anesthesist')} value={operation.anesthesist_name ?? ''} icon="bx-user-circle" />
                    <SummaryField label={t('global.scrub_nurse')} value={operation.scrub_nurse_name ?? ''} icon="bx-user-pin" />
                    <SummaryField label={t('global.circulation_nurse')} value={operation.circulation_nurse_name ?? ''} icon="bx-user-voice" />
                    <SummaryField label={t('global.department')} value={operation.department_name ?? ''} icon="bx-buildings" />
                    <SummaryField
                        label={t('global.anesthesia_type')}
                        value={anesthesiaTypeLabel(operation.anesthesia_type, t)}
                        icon="bx-pulse"
                    />
                    <SummaryField label={t('global.operation_duration')} value={operation.planned_duration ?? ''} icon="bx-timer" />
                    <SummaryField label={t('global.position_on_bed')} value={operation.position_on_bed ?? ''} icon="bx-bed" />
                </div>

                {assistantsLabel && (
                    <SummaryField label={t('global.operation_assistants')} value={assistantsLabel} icon="bx-user-plus" />
                )}

                {operation.reserve_reason && (
                    <TextBlock label={t('global.reserve_reason')} value={operation.reserve_reason} icon="bx-calendar-x" />
                )}

                <div className="grid gap-3 lg:grid-cols-2">
                    <TextBlock label={t('global.operation_plan')} value={operation.plan ?? ''} icon="bx-clipboard" />
                    <TextBlock label={t('global.other_problems')} value={operation.other_problems ?? ''} icon="bx-error-circle" />
                </div>

                {(operation.operation_remark || operation.operation_expense_remarks) && (
                    <div className="grid gap-3 lg:grid-cols-2">
                        {operation.operation_remark && (
                            <TextBlock label={t('global.operation_remark')} value={operation.operation_remark} icon="bx-note" />
                        )}
                        {operation.operation_expense_remarks && (
                            <TextBlock
                                label={t('global.operation_expense_remarks')}
                                value={operation.operation_expense_remarks}
                                icon="bx-receipt"
                            />
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}
