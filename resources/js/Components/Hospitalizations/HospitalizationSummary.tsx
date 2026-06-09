import { Badge } from 'flowbite-react';
import { HospitalizationDetail } from '../../types/hospitalization';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HOSPITALIZATION_CARD_CLASS,
    HOSPITALIZATION_HERO_AVATAR_CLASS,
    patientInitials,
} from './hospitalizationUi';

interface HospitalizationSummaryProps {
    hospitalization: HospitalizationDetail;
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
                    ? 'border-emerald-200/80 bg-emerald-50/50 dark:border-emerald-900/50 dark:bg-emerald-950/20'
                    : 'border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900/50'
            }`}
        >
            <p className="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {icon && <i className={`bx ${icon} text-sm`} />}
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
                <p className="text-[10px] font-semibold uppercase tracking-wider text-emerald-100/90">
                    {label}
                </p>
                <p className="truncate text-sm font-semibold text-white">{value || '—'}</p>
            </div>
        </div>
    );
}

export default function HospitalizationSummary({ hospitalization }: HospitalizationSummaryProps) {
    const { t } = useTranslation();

    const patientName = hospitalization.patient?.name ?? '—';
    const fatherName = hospitalization.patient?.father_name;
    const locationLabel = [hospitalization.room_name, hospitalization.bed_number]
        .filter(Boolean)
        .join(' · ');

    return (
        <div className={`${HOSPITALIZATION_CARD_CLASS} overflow-hidden`}>
            <div className="bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 px-5 py-5 text-white">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-4">
                        <div className={HOSPITALIZATION_HERO_AVATAR_CLASS}>
                            {patientInitials(hospitalization.patient?.name)}
                        </div>
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <h2 className="text-lg font-bold tracking-tight">{patientName}</h2>
                                <Badge
                                    color={hospitalization.is_discharged ? 'gray' : 'success'}
                                    className="w-fit"
                                >
                                    {hospitalization.is_discharged
                                        ? t('global.discharged')
                                        : t('global.active')}
                                </Badge>
                            </div>
                            {fatherName && (
                                <p className="mt-0.5 text-sm text-emerald-100/90">
                                    {t('global.father_name')}: {fatherName}
                                </p>
                            )}
                            <p className="mt-1 text-xs text-emerald-100/75">
                                #{hospitalization.id}
                                {hospitalization.patient?.id_card
                                    ? ` · ${t('global.card_number')}: ${hospitalization.patient.id_card}`
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
                            value={hospitalization.department_name ?? '—'}
                        />
                        <HighlightPill
                            icon="bx-user-check"
                            label={t('global.doctor')}
                            value={hospitalization.doctor_name ?? '—'}
                        />
                        <HighlightPill
                            icon="bx-calendar"
                            label={t('global.date')}
                            value={
                                hospitalization.admission_date
                                    ? `${hospitalization.admission_date}${hospitalization.admission_time ? ` · ${hospitalization.admission_time}` : ''}`
                                    : '—'
                            }
                        />
                    </div>
                </div>
            </div>

            <div className="space-y-4 p-5">
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryField
                        label={t('global.patient_name')}
                        value={hospitalization.patient?.name ?? ''}
                        icon="bx-user"
                    />
                    <SummaryField
                        label={t('global.father_name')}
                        value={hospitalization.patient?.father_name ?? ''}
                        icon="bx-user"
                    />
                    <SummaryField
                        label={t('global.phone')}
                        value={hospitalization.patient?.phone ?? ''}
                        icon="bx-phone"
                    />
                    <SummaryField
                        label={t('global.referred_to')}
                        value={hospitalization.doctor_name ?? ''}
                        icon="bx-user-check"
                    />
                </div>

                <div className="grid gap-3 lg:grid-cols-2">
                    <SummaryField
                        label={t('global.reason')}
                        value={hospitalization.reason}
                        icon="bx-info-circle"
                        highlight
                    />
                    <SummaryField
                        label={t('global.remarks')}
                        value={hospitalization.remarks}
                        icon="bx-note"
                        highlight
                    />
                </div>
            </div>
        </div>
    );
}
