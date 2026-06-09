import { UnderReviewDetail } from '../../types/underReview';
import { useTranslation } from '../../hooks/useTranslation';

interface UnderReviewSummaryProps {
    underReview: UnderReviewDetail;
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

export default function UnderReviewSummary({ underReview }: UnderReviewSummaryProps) {
    const { t } = useTranslation();

    return (
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <SummaryField
                    label={t('global.patient_name')}
                    value={underReview.patient?.name ?? ''}
                    icon="bx-user"
                />
                <SummaryField
                    label={t('global.referred_to')}
                    value={underReview.doctor_name ?? ''}
                    icon="bx-user-check"
                />
                <SummaryField
                    label={t('global.date')}
                    value={underReview.admission_date ?? ''}
                    icon="bx-calendar"
                />
                <SummaryField
                    label={t('global.time')}
                    value={underReview.admission_time ?? ''}
                    icon="bx-time"
                />
            </div>
            <div className="mt-3 grid gap-3 lg:grid-cols-2">
                <SummaryField label={t('global.reason')} value={underReview.reason} icon="bx-info-circle" />
                <SummaryField label={t('global.remarks')} value={underReview.remarks} icon="bx-note" />
            </div>
            <div className="mt-3 grid gap-3 sm:grid-cols-3">
                <SummaryField
                    label={t('global.card_number')}
                    value={underReview.patient?.id_card ?? ''}
                />
                <SummaryField label={t('global.room')} value={underReview.room_name ?? ''} />
                <SummaryField label={t('global.bed')} value={String(underReview.bed_number ?? '')} />
            </div>
        </div>
    );
}
