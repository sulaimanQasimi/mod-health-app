import StatCard from '../ui/StatCard';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginationMeta } from '../../types/settings';
import { OperationListVariant } from '../../types/operation';
import { OPERATION_LIST_VARIANT_CONFIG } from './operationUi';

interface OperationListStatsProps {
    variant: OperationListVariant;
    meta: PaginationMeta;
}

interface StatCardStyle {
    iconClass: string;
    iconBgClass: string;
    borderClass: string;
    valueClass: string;
}

const variantStatStyles: Record<OperationListVariant, StatCardStyle> = {
    new: {
        iconClass: 'bx bx-time-five',
        iconBgClass: 'bg-amber-500',
        borderClass: 'border-amber-200 dark:border-amber-800',
        valueClass: 'text-amber-700 dark:text-amber-300',
    },
    approved: {
        iconClass: 'bx bx-check-shield',
        iconBgClass: 'bg-emerald-600',
        borderClass: 'border-emerald-200 dark:border-emerald-800',
        valueClass: 'text-emerald-700 dark:text-emerald-300',
    },
    reserved: {
        iconClass: 'bx bx-pause-circle',
        iconBgClass: 'bg-violet-600',
        borderClass: 'border-violet-200 dark:border-violet-800',
        valueClass: 'text-violet-700 dark:text-violet-300',
    },
    completed: {
        iconClass: 'bx bx-badge-check',
        iconBgClass: 'bg-sky-600',
        borderClass: 'border-sky-200 dark:border-sky-800',
        valueClass: 'text-sky-700 dark:text-sky-300',
    },
};

const showingStatStyle: StatCardStyle = {
    iconClass: 'bx bx-show',
    iconBgClass: 'bg-indigo-600',
    borderClass: 'border-indigo-200 dark:border-indigo-800',
    valueClass: 'text-indigo-700 dark:text-indigo-300',
};

const pageStatStyle: StatCardStyle = {
    iconClass: 'bx bx-book-open',
    iconBgClass: 'bg-slate-600',
    borderClass: 'border-slate-200 dark:border-slate-700',
    valueClass: 'text-slate-700 dark:text-slate-300',
};

export default function OperationListStats({ variant, meta }: OperationListStatsProps) {
    const { t } = useTranslation();
    const config = OPERATION_LIST_VARIANT_CONFIG[variant];
    const totalStyle = variantStatStyles[variant];

    const stats = [
        {
            title: t('global.total'),
            value: String(meta.total ?? 0),
            subtitle: t(config.subtitleKey),
            ...totalStyle,
        },
        {
            title: t('global.showing'),
            value: `${meta.from ?? 0} – ${meta.to ?? 0}`,
            subtitle: '',
            ...showingStatStyle,
        },
        {
            title: t('global.page'),
            value: `${meta.current_page ?? 1} / ${meta.last_page ?? 1}`,
            subtitle: `${meta.per_page ?? 15} ${t('global.per_page')}`,
            ...pageStatStyle,
        },
    ];

    return (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {stats.map((stat) => (
                <StatCard key={stat.title} {...stat} />
            ))}
        </div>
    );
}
