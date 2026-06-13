import { HospitalizationDetail } from '../../types/hospitalization';
import { useTranslation } from '../../hooks/useTranslation';
import { HOSPITALIZATION_CARD_CLASS } from './hospitalizationUi';

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

export default function HospitalizationSummary({ hospitalization }: HospitalizationSummaryProps) {
    const { t } = useTranslation();

    return (
        <div className={`${HOSPITALIZATION_CARD_CLASS} overflow-hidden`}>
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
