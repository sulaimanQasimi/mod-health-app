import { useTranslation } from '../../hooks/useTranslation';
import { PacuDetail } from '../../types/pacu';
import { PACU_CARD_CLASS } from './pacuUi';

interface PacuSummaryProps {
    pacu: PacuDetail;
}

function SummaryField({
    label,
    value,
    icon,
}: {
    label: string;
    value: string;
    icon?: string;
}) {
    return (
        <div className="rounded-xl border border-gray-100 bg-white px-3.5 py-3 transition-colors dark:border-gray-800 dark:bg-gray-900/50">
            <p className="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {icon && <i className={`bx ${icon} text-sm text-cyan-500/80`} />}
                {label}
            </p>
            <p className="mt-1 text-sm font-medium text-gray-900 dark:text-white">{value || '—'}</p>
        </div>
    );
}

export default function PacuSummary({ pacu }: PacuSummaryProps) {
    const { t } = useTranslation();
    const patientName = [pacu.patient?.name, pacu.patient?.last_name].filter(Boolean).join(' ');

    return (
        <div className={PACU_CARD_CLASS}>
            <div className="space-y-4 p-5">
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryField
                        label={t('global.patient_name')}
                        value={patientName}
                        icon="bx-user"
                    />
                    <SummaryField
                        label={t('global.father_name')}
                        value={pacu.patient?.father_name ?? ''}
                        icon="bx-user"
                    />
                    <SummaryField
                        label={t('global.card_number')}
                        value={pacu.patient?.id_card ?? ''}
                        icon="bx-id-card"
                    />
                    <SummaryField
                        label={t('global.phone')}
                        value={pacu.patient?.phone ?? ''}
                        icon="bx-phone"
                    />
                    <SummaryField
                        label={t('global.nid')}
                        value={pacu.patient?.nid ?? ''}
                        icon="bx-id-card"
                    />
                    <SummaryField
                        label={t('global.province')}
                        value={pacu.patient?.province_name ?? ''}
                        icon="bx-map"
                    />
                    <SummaryField
                        label={t('global.district')}
                        value={pacu.patient?.district_name ?? ''}
                        icon="bx-map-pin"
                    />
                    <SummaryField
                        label={t('global.referred_by')}
                        value={pacu.patient?.recipient_name ?? ''}
                        icon="bx-user-voice"
                    />
                    <SummaryField
                        label={t('global.department')}
                        value={pacu.department_name ?? ''}
                        icon="bx-category"
                    />
                    <SummaryField
                        label={t('global.branch')}
                        value={pacu.branch_name ?? ''}
                        icon="bx-building"
                    />
                    <SummaryField
                        label={t('global.description')}
                        value={pacu.description ?? ''}
                        icon="bx-note"
                    />
                    <SummaryField
                        label={t('global.register_date')}
                        value={pacu.created_at ?? ''}
                        icon="bx-calendar"
                    />
                </div>
            </div>
        </div>
    );
}
