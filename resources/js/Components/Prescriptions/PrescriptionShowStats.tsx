import { Badge } from 'flowbite-react';
import { useMemo } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { PrescriptionDetail } from '../../types/prescription';

interface PrescriptionShowStatsProps {
    prescription: PrescriptionDetail;
}

const statCards = [
    {
        key: 'patient',
        labelKey: 'global.patient_name',
        icon: 'bx-user',
        accent: 'from-blue-500 to-indigo-600',
        getValue: (p: PrescriptionDetail) => p.patient_name,
    },
    {
        key: 'doctor',
        labelKey: 'global.doctor_name',
        icon: 'bx-user-pin',
        accent: 'from-violet-500 to-purple-600',
        getValue: (p: PrescriptionDetail) => p.doctor_name,
    },
    {
        key: 'pharmacy',
        labelKey: 'global.pharmacy',
        icon: 'bx-clinic',
        accent: 'from-cyan-500 to-teal-600',
        getValue: (p: PrescriptionDetail) => p.pharmacy_name ?? '—',
    },
    {
        key: 'created',
        labelKey: 'global.created_at',
        icon: 'bx-calendar',
        accent: 'from-slate-500 to-gray-600',
        getValue: (p: PrescriptionDetail) => p.created_at ?? '—',
    },
] as const;

function computeDeliveryStats(prescription: PrescriptionDetail) {
    let delivered = 0;

    prescription.items.forEach((item) => {
        if (item.selected_alternative) {
            if (item.selected_alternative.is_delivered) {
                delivered += 1;
            }
            return;
        }

        if (item.is_delivered) {
            delivered += 1;
        }
    });

    const total = prescription.items.length;
    const pending = total - delivered;
    const percent = total > 0 ? Math.round((delivered / total) * 100) : 0;

    return { total, delivered, pending, percent };
}

export default function PrescriptionShowStats({ prescription }: PrescriptionShowStatsProps) {
    const { t } = useTranslation();
    const stats = useMemo(() => computeDeliveryStats(prescription), [prescription]);

    return (
        <div className="mb-6 space-y-4">
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {statCards.map((card) => (
                    <div
                        key={card.key}
                        className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800/60"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {t(card.labelKey)}
                                </p>
                                <p className="mt-1 truncate font-semibold text-gray-900 dark:text-white">
                                    {card.getValue(prescription)}
                                </p>
                            </div>
                            <div
                                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br ${card.accent} text-white shadow-sm`}
                            >
                                <i className={`bx ${card.icon} text-lg`} />
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div className="rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                    <p className="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                        {t('global.status')}
                    </p>
                    <Badge
                        color={prescription.is_completed ? 'success' : 'warning'}
                        className="mt-2 w-fit"
                    >
                        {prescription.is_completed ? t('global.completed') : t('global.in_progress')}
                    </Badge>
                </div>
                <div className="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800/60">
                    <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        {t('global.prescription_items')}
                    </p>
                    <p className="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{stats.total}</p>
                </div>
                <div className="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/15">
                    <p className="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                        {t('global.delivered')}
                    </p>
                    <p className="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">
                        {stats.delivered}
                    </p>
                </div>
                <div className="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900/40 dark:bg-amber-950/15">
                    <p className="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                        {t('global.pending')}
                    </p>
                    <p className="mt-1 text-2xl font-bold text-amber-700 dark:text-amber-300">
                        {stats.pending}
                    </p>
                </div>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800/60">
                <div className="mb-2 flex items-center justify-between gap-3">
                    <p className="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {t('global.delivered')} ({stats.delivered}/{stats.total})
                    </p>
                    <span className="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                        {stats.percent}%
                    </span>
                </div>
                <div className="h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                    <div
                        className="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500"
                        style={{ width: `${stats.percent}%` }}
                    />
                </div>
            </div>
        </div>
    );
}

export { computeDeliveryStats };
