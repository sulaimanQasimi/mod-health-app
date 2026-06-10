import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { IcuDetail } from '../../types/icu';
import {
    ICU_CARD_CLASS,
    ICU_HERO_AVATAR_CLASS,
    icuStatusBadgeColor,
    icuStatusLabel,
    patientInitials,
} from './icuUi';

interface IcuSummaryProps {
    icu: IcuDetail;
}

function SummaryField({
    label,
    value,
    icon,
    highlight,
}: {
    label: string;
    value: string;
    icon?: string;
    highlight?: boolean;
}) {
    return (
        <div
            className={`rounded-xl border px-3.5 py-3 transition-colors ${
                highlight
                    ? 'border-rose-200/80 bg-rose-50/50 dark:border-rose-900/50 dark:bg-rose-950/20'
                    : 'border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900/50'
            }`}
        >
            <p className="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {icon && <i className={`bx ${icon} text-sm text-rose-500/80`} />}
                {label}
            </p>
            <p className="mt-1 text-sm font-medium text-gray-900 dark:text-white">{value || '—'}</p>
        </div>
    );
}

function HighlightPill({ icon, label, value }: { icon: string; label: string; value: string }) {
    return (
        <div className="flex min-w-0 items-center gap-2.5 rounded-xl bg-white/15 px-3 py-2 backdrop-blur-sm">
            <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/20">
                <i className={`bx ${icon} text-base text-white`} />
            </div>
            <div className="min-w-0">
                <p className="text-[10px] font-semibold uppercase tracking-wider text-rose-100/90">
                    {label}
                </p>
                <p className="truncate text-sm font-semibold text-white">{value || '—'}</p>
            </div>
        </div>
    );
}

export default function IcuSummary({ icu }: IcuSummaryProps) {
    const { t } = useTranslation();
    const patientName = [icu.patient?.name, icu.patient?.last_name].filter(Boolean).join(' ');
    const locationLabel = [icu.room_name, icu.bed_number].filter(Boolean).join(' · ');

    return (
        <div className={ICU_CARD_CLASS}>
            <div className="bg-gradient-to-br from-rose-600 via-rose-600 to-red-700 px-5 py-5 text-white">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-4">
                        <div className={ICU_HERO_AVATAR_CLASS}>
                            {patientInitials(icu.patient?.name)}
                        </div>
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <h2 className="text-lg font-bold tracking-tight">{patientName || '—'}</h2>
                                <Badge color={icuStatusBadgeColor(icu)} className="w-fit">
                                    {icuStatusLabel(icu, t)}
                                </Badge>
                            </div>
                            {icu.patient?.father_name && (
                                <p className="mt-0.5 text-sm text-rose-100/90">
                                    {t('global.father_name')}: {icu.patient.father_name}
                                </p>
                            )}
                            <p className="mt-1 text-xs text-rose-100/75">
                                #{icu.id}
                                {icu.patient?.id_card
                                    ? ` · ${t('global.card_number')}: ${icu.patient.id_card}`
                                    : ''}
                            </p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-4">
                        <HighlightPill
                            icon="bx-building-house"
                            label={t('global.room')}
                            value={locationLabel}
                        />
                        <HighlightPill
                            icon="bx-buildings"
                            label={t('global.department')}
                            value={icu.department_name ?? '—'}
                        />
                        <HighlightPill
                            icon="bx-user-check"
                            label={t('global.doctor')}
                            value={icu.doctor_name ?? '—'}
                        />
                        <HighlightPill
                            icon="bx-calendar"
                            label={t('global.date')}
                            value={icu.created_at ?? '—'}
                        />
                    </div>
                </div>
            </div>

            <div className="space-y-4 p-5">
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryField
                        label={t('global.patient_name')}
                        value={patientName}
                        icon="bx-user"
                    />
                    <SummaryField
                        label={t('global.father_name')}
                        value={icu.patient?.father_name ?? ''}
                        icon="bx-user"
                    />
                    <SummaryField
                        label={t('global.card_number')}
                        value={icu.patient?.id_card ?? ''}
                        icon="bx-id-card"
                    />
                    <SummaryField
                        label={t('global.phone')}
                        value={icu.patient?.phone ?? ''}
                        icon="bx-phone"
                    />
                    <SummaryField
                        label={t('global.doctor_name')}
                        value={icu.doctor_name ?? ''}
                        icon="bx-user-check"
                    />
                    <SummaryField
                        label={t('global.branch')}
                        value={icu.branch_name ?? ''}
                        icon="bx-building"
                    />
                    <SummaryField
                        label={t('global.department')}
                        value={icu.department_name ?? ''}
                        icon="bx-category"
                    />
                    <SummaryField
                        label={t('global.date')}
                        value={icu.created_at ?? ''}
                        icon="bx-calendar"
                    />
                </div>

                <div className="grid gap-3 lg:grid-cols-2">
                    <SummaryField
                        label={t('global.description')}
                        value={icu.description ?? ''}
                        icon="bx-note"
                        highlight
                    />
                    {icu.icu_enterance_note && (
                        <SummaryField
                            label={t('global.icu_enterance_note')}
                            value={icu.icu_enterance_note}
                            icon="bx-notepad"
                            highlight
                        />
                    )}
                    {icu.icu_reject_reason && (
                        <SummaryField
                            label={t('global.icu_reject_reason')}
                            value={icu.icu_reject_reason}
                            icon="bx-x-circle"
                            highlight
                        />
                    )}
                    {icu.discharge_remark && (
                        <SummaryField
                            label={t('global.discharge_remark')}
                            value={icu.discharge_remark}
                            icon="bx-log-out"
                        />
                    )}
                </div>
            </div>
        </div>
    );
}
