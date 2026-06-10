import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { AnesthesiaDetail } from '../../types/anesthesia';
import {
    anesthesiaStatusBadgeColor,
    anesthesiaStatusLabel,
    anesthesiaTypeLabel,
} from './anesthesiaUi';

interface AnesthesiaSummaryProps {
    anesthesia: AnesthesiaDetail;
}

function SummaryField({ label, value, icon }: { label: string; value: string; icon?: string }) {
    return (
        <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 dark:border-gray-800 dark:bg-gray-800/40">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {icon && <i className={`bx ${icon} me-1.5 align-middle`} />}
                {label}
            </p>
            <p className="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{value || '—'}</p>
        </div>
    );
}

export default function AnesthesiaSummary({ anesthesia }: AnesthesiaSummaryProps) {
    const { t } = useTranslation();

    const patientName = [anesthesia.patient?.name, anesthesia.patient?.father_name]
        .filter(Boolean)
        .join(' / ');

    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div className="bg-gradient-to-r from-violet-700 to-indigo-800 px-5 py-4 text-white">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p className="text-lg font-bold">{t('global.anesthesia_details')}</p>
                        <p className="mt-1 text-sm text-violet-100">{patientName || '—'}</p>
                    </div>
                    <Badge color={anesthesiaStatusBadgeColor(anesthesia.status)} className="font-normal">
                        {anesthesiaStatusLabel(anesthesia.status, t)}
                    </Badge>
                </div>
            </div>

            <div className="space-y-4 p-5">
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryField
                        label={t('global.patient_name')}
                        value={anesthesia.patient?.name ?? ''}
                        icon="bx-user"
                    />
                    <SummaryField
                        label={t('global.operation_type')}
                        value={anesthesia.operation_type_name ?? ''}
                        icon="bx-plus-medical"
                    />
                    <SummaryField label={t('global.date')} value={anesthesia.date ?? ''} icon="bx-calendar" />
                    <SummaryField label={t('global.time')} value={anesthesia.time ?? ''} icon="bx-time" />
                </div>

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
                        label={t('global.anesthesia_type')}
                        value={anesthesiaTypeLabel(anesthesia.anesthesia_type, t)}
                        icon="bx-pulse"
                    />
                    <SummaryField
                        label={t('global.estimated_blood_waste')}
                        value={anesthesia.estimated_blood_waste ?? ''}
                        icon="bx-droplet"
                    />
                </div>

                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryField
                        label={t('global.operation_surgion')}
                        value={anesthesia.surgion_name ?? ''}
                        icon="bx-user-md"
                    />
                    <SummaryField
                        label={t('global.anesthesist')}
                        value={anesthesia.anesthesist_name ?? ''}
                        icon="bx-user-circle"
                    />
                    <SummaryField
                        label={t('global.anesthesia_log')}
                        value={anesthesia.anesthesia_log_name ?? ''}
                        icon="bx-file-blank"
                    />
                    <SummaryField
                        label={t('global.department')}
                        value={anesthesia.department_name ?? ''}
                        icon="bx-group"
                    />
                </div>

                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryField
                        label={t('global.scrub_nurse')}
                        value={anesthesia.scrub_nurse_name ?? ''}
                        icon="bx-user-pin"
                    />
                    <SummaryField
                        label={t('global.circulation_nurse')}
                        value={anesthesia.circulation_nurse_name ?? ''}
                        icon="bx-user-voice"
                    />
                    <SummaryField label={t('global.room')} value={anesthesia.room_name ?? ''} icon="bx-building" />
                    <SummaryField label={t('global.bed')} value={String(anesthesia.bed_number ?? '')} icon="bx-bed" />
                </div>

                <div className="grid gap-3 lg:grid-cols-2">
                    <SummaryField label={t('global.operation_plan')} value={anesthesia.plan ?? ''} icon="bx-clipboard" />
                    <SummaryField
                        label={t('global.other_problems')}
                        value={anesthesia.other_problems ?? ''}
                        icon="bx-error"
                    />
                </div>

                <div className="grid gap-3 lg:grid-cols-2">
                    <SummaryField
                        label={t('global.anesthesia_plan')}
                        value={anesthesia.anesthesia_plan ?? ''}
                        icon="bx-note"
                    />
                    <SummaryField
                        label={t('global.anesthesia_log_reply')}
                        value={anesthesia.anesthesia_log_reply ?? ''}
                        icon="bx-message-dots"
                    />
                </div>
            </div>
        </div>
    );
}
