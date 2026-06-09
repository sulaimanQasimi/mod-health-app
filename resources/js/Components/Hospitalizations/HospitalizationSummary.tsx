import { Badge } from 'flowbite-react';
import { HospitalizationDetail } from '../../types/hospitalization';
import { useTranslation } from '../../hooks/useTranslation';

interface HospitalizationSummaryProps {
    hospitalization: HospitalizationDetail;
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

export default function HospitalizationSummary({ hospitalization }: HospitalizationSummaryProps) {
    const { t } = useTranslation();

    const patientName = [hospitalization.patient?.name, hospitalization.patient?.father_name]
        .filter(Boolean)
        .join(' / ');

    return (
        <div className="rounded-xl border border-emerald-200 bg-white p-4 shadow-sm dark:border-emerald-900/40 dark:bg-gray-900">
            <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                <p className="text-sm font-medium text-gray-900 dark:text-white">{patientName || '—'}</p>
                <Badge color={hospitalization.is_discharged ? 'gray' : 'success'}>
                    {hospitalization.is_discharged ? t('global.discharged') : t('global.active')}
                </Badge>
            </div>
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
                    label={t('global.referred_to')}
                    value={hospitalization.doctor_name ?? ''}
                    icon="bx-user-check"
                />
                <SummaryField
                    label={t('global.date')}
                    value={hospitalization.admission_date ?? ''}
                    icon="bx-calendar"
                />
                <SummaryField
                    label={t('global.time')}
                    value={hospitalization.admission_time ?? ''}
                    icon="bx-time"
                />
                <SummaryField label={t('global.room')} value={hospitalization.room_name ?? ''} icon="bx-building" />
                <SummaryField label={t('global.bed')} value={String(hospitalization.bed_number ?? '')} icon="bx-bed" />
                <SummaryField
                    label={t('global.card_number')}
                    value={hospitalization.patient?.id_card ?? ''}
                />
            </div>
            <div className="mt-3 grid gap-3 lg:grid-cols-2">
                <SummaryField label={t('global.reason')} value={hospitalization.reason} icon="bx-info-circle" />
                <SummaryField label={t('global.remarks')} value={hospitalization.remarks} icon="bx-note" />
            </div>
        </div>
    );
}
