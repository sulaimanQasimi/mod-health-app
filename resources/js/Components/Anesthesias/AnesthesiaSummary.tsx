import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { AnesthesiaDetail } from '../../types/anesthesia';
import {
    ANESTHESIA_CARD_CLASS,
    ANESTHESIA_HERO_AVATAR_CLASS,
    anesthesiaStatusBadgeColor,
    anesthesiaStatusLabel,
    anesthesiaTypeLabel,
    patientInitials,
} from './anesthesiaUi';

interface AnesthesiaSummaryProps {
    anesthesia: AnesthesiaDetail;
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
                    ? 'border-violet-200/80 bg-violet-50/50 dark:border-violet-900/50 dark:bg-violet-950/20'
                    : 'border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900/50'
            }`}
        >
            <p className="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {icon && <i className={`bx ${icon} text-sm text-violet-500/80`} />}
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
                <p className="text-[10px] font-semibold uppercase tracking-wider text-violet-100/90">
                    {label}
                </p>
                <p className="truncate text-sm font-semibold text-white">{value || '—'}</p>
            </div>
        </div>
    );
}

function TextBlock({
    label,
    value,
    icon,
    accent = 'default',
}: {
    label: string;
    value: string;
    icon: string;
    accent?: 'default' | 'info' | 'warning';
}) {
    const accentClasses = {
        default: 'border-gray-100 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/40',
        info: 'border-sky-200/80 bg-sky-50/50 dark:border-sky-900/50 dark:bg-sky-950/20',
        warning: 'border-amber-200/80 bg-amber-50/50 dark:border-amber-900/50 dark:bg-amber-950/20',
    }[accent];

    return (
        <div className={`rounded-xl border px-4 py-3.5 ${accentClasses}`}>
            <p className="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <i className={`bx ${icon} text-sm text-violet-500/80`} />
                {label}
            </p>
            <p className="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-900 dark:text-white">
                {value || '—'}
            </p>
        </div>
    );
}

function SectionTitle({ icon, children }: { icon: string; children: string }) {
    return (
        <h3 className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
            <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-300">
                <i className={`bx ${icon} text-base`} />
            </span>
            {children}
        </h3>
    );
}

export default function AnesthesiaSummary({ anesthesia }: AnesthesiaSummaryProps) {
    const { t } = useTranslation();

    const scheduleLabel = [anesthesia.date, anesthesia.time].filter(Boolean).join(' · ');
    const locationLabel = [anesthesia.room_name, anesthesia.bed_number].filter(Boolean).join(' · ');
    const assistantsLabel = (anesthesia.operation_assistants_names ?? []).join('، ');

    const hasTeamDetails =
        anesthesia.surgion_name ||
        anesthesia.anesthesist_name ||
        anesthesia.anesthesia_log_name ||
        assistantsLabel ||
        anesthesia.scrub_nurse_name ||
        anesthesia.circulation_nurse_name;

    const hasReviewNotes =
        anesthesia.anesthesia_plan || anesthesia.anesthesia_log_reply;

    return (
        <div className={ANESTHESIA_CARD_CLASS}>
            <div className="bg-gradient-to-br from-violet-600 via-violet-600 to-indigo-700 px-5 py-5 text-white">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-4">
                        <div className={ANESTHESIA_HERO_AVATAR_CLASS}>
                            {patientInitials(anesthesia.patient?.name)}
                        </div>
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <h2 className="text-lg font-bold tracking-tight">
                                    {anesthesia.patient?.name || '—'}
                                </h2>
                                <Badge color={anesthesiaStatusBadgeColor(anesthesia.status)} className="w-fit">
                                    {anesthesiaStatusLabel(anesthesia.status, t)}
                                </Badge>
                            </div>
                            {anesthesia.patient?.father_name && (
                                <p className="mt-0.5 text-sm text-violet-100/90">
                                    {t('global.father_name')}: {anesthesia.patient.father_name}
                                </p>
                            )}
                            <p className="mt-1 text-xs text-violet-100/75">
                                #{anesthesia.id}
                                {anesthesia.patient?.id_card
                                    ? ` · ${t('global.card_number')}: ${anesthesia.patient.id_card}`
                                    : ''}
                            </p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-4">
                        <HighlightPill
                            icon="bx-plus-medical"
                            label={t('global.operation_type')}
                            value={anesthesia.operation_type_name ?? '—'}
                        />
                        <HighlightPill
                            icon="bx-calendar"
                            label={t('global.date')}
                            value={scheduleLabel || '—'}
                        />
                        <HighlightPill
                            icon="bx-buildings"
                            label={t('global.department')}
                            value={anesthesia.department_name ?? '—'}
                        />
                        <HighlightPill
                            icon="bx-user-md"
                            label={t('global.operation_surgion')}
                            value={anesthesia.surgion_name ?? '—'}
                        />
                    </div>
                </div>
            </div>

            <div className="space-y-6 p-5">
                <section className="space-y-3">
                    <SectionTitle icon="bx-user">{t('global.patient_information')}</SectionTitle>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <SummaryField
                            label={t('global.patient_name')}
                            value={anesthesia.patient?.name ?? ''}
                            icon="bx-user"
                        />
                        <SummaryField
                            label={t('global.father_name')}
                            value={anesthesia.patient?.father_name ?? ''}
                            icon="bx-user"
                        />
                        <SummaryField
                            label={t('global.card_number')}
                            value={anesthesia.patient?.id_card ?? ''}
                            icon="bx-id-card"
                        />
                        <SummaryField
                            label={t('global.phone')}
                            value={anesthesia.patient?.phone ?? ''}
                            icon="bx-phone"
                        />
                        <SummaryField
                            label={t('global.doctor_name')}
                            value={anesthesia.doctor_name ?? ''}
                            icon="bx-user-check"
                        />
                        <SummaryField
                            label={t('global.department')}
                            value={anesthesia.department_name ?? ''}
                            icon="bx-category"
                        />
                        {locationLabel && (
                            <SummaryField
                                label={t('global.room')}
                                value={locationLabel}
                                icon="bx-building-house"
                            />
                        )}
                        <SummaryField
                            label={t('global.anesthesia_type')}
                            value={anesthesiaTypeLabel(anesthesia.anesthesia_type, t)}
                            icon="bx-pulse"
                        />
                    </div>
                </section>

                <section className="space-y-3">
                    <SectionTitle icon="bx-clipboard">{t('global.operation_plan')}</SectionTitle>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <SummaryField
                            label={t('global.operation_duration')}
                            value={anesthesia.planned_duration ?? ''}
                            icon="bx-timer"
                        />
                        <SummaryField
                            label={t('global.position_on_bed')}
                            value={anesthesia.position_on_bed ?? ''}
                            icon="bx-bed"
                        />
                        <SummaryField
                            label={t('global.estimated_blood_waste')}
                            value={anesthesia.estimated_blood_waste ?? ''}
                            icon="bx-droplet"
                        />
                        <SummaryField
                            label={t('global.date')}
                            value={scheduleLabel}
                            icon="bx-calendar"
                        />
                    </div>
                    <div className="grid gap-3 lg:grid-cols-2">
                        <TextBlock
                            label={t('global.operation_plan')}
                            value={anesthesia.plan ?? ''}
                            icon="bx-clipboard"
                            accent="info"
                        />
                        <TextBlock
                            label={t('global.other_problems')}
                            value={anesthesia.other_problems ?? ''}
                            icon="bx-error-circle"
                            accent="warning"
                        />
                    </div>
                </section>

                {hasTeamDetails && (
                    <section className="space-y-3">
                        <SectionTitle icon="bx-group">{t('global.operation_team')}</SectionTitle>
                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            {anesthesia.surgion_name && (
                                <SummaryField
                                    label={t('global.operation_surgion')}
                                    value={anesthesia.surgion_name}
                                    icon="bx-user-md"
                                />
                            )}
                            {anesthesia.anesthesist_name && (
                                <SummaryField
                                    label={t('global.anesthesist')}
                                    value={anesthesia.anesthesist_name}
                                    icon="bx-user-circle"
                                />
                            )}
                            {anesthesia.anesthesia_log_name && (
                                <SummaryField
                                    label={t('global.anesthesia_log')}
                                    value={anesthesia.anesthesia_log_name}
                                    icon="bx-file-blank"
                                />
                            )}
                            {assistantsLabel && (
                                <SummaryField
                                    label={t('global.operation_assistants')}
                                    value={assistantsLabel}
                                    icon="bx-user-plus"
                                />
                            )}
                            {anesthesia.scrub_nurse_name && (
                                <SummaryField
                                    label={t('global.scrub_nurse')}
                                    value={anesthesia.scrub_nurse_name}
                                    icon="bx-user-pin"
                                />
                            )}
                            {anesthesia.circulation_nurse_name && (
                                <SummaryField
                                    label={t('global.circulation_nurse')}
                                    value={anesthesia.circulation_nurse_name}
                                    icon="bx-user-voice"
                                />
                            )}
                        </div>
                    </section>
                )}

                {hasReviewNotes && (
                    <section className="space-y-3">
                        <SectionTitle icon="bx-note">{t('global.anesthesia_details')}</SectionTitle>
                        <div className="grid gap-3 lg:grid-cols-2">
                            {anesthesia.anesthesia_plan && (
                                <TextBlock
                                    label={t('global.anesthesia_plan')}
                                    value={anesthesia.anesthesia_plan}
                                    icon="bx-note"
                                    accent="info"
                                />
                            )}
                            {anesthesia.anesthesia_log_reply && (
                                <TextBlock
                                    label={
                                        anesthesia.status === 'rejected'
                                            ? t('global.rejection_reason')
                                            : t('global.anesthesia_log_reply')
                                    }
                                    value={anesthesia.anesthesia_log_reply}
                                    icon={
                                        anesthesia.status === 'rejected'
                                            ? 'bx-x-circle'
                                            : 'bx-message-dots'
                                    }
                                    accent={anesthesia.status === 'rejected' ? 'warning' : 'default'}
                                />
                            )}
                        </div>
                    </section>
                )}
            </div>
        </div>
    );
}
