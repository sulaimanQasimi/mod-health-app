import StatCard from '../ui/StatCard';
import { useTranslation } from '../../hooks/useTranslation';
import { PacuListVariant } from '../../types/pacu';
import { PaginationMeta } from '../../types/settings';
import { PACU_LIST_VARIANT_CONFIG } from './pacuUi';

interface PacuListStatsProps {
    variant: PacuListVariant;
    meta: PaginationMeta;
}

interface StatCardStyle {
    iconClass: string;
    iconBgClass: string;
    borderClass: string;
    valueClass: string;
}

const variantStatStyles: Record<PacuListVariant, StatCardStyle> = {
    new: {
        iconClass: 'bx bx-time-five',
        iconBgClass: 'bg-sky-600',
        borderClass: 'border-sky-200 dark:border-sky-800',
        valueClass: 'text-sky-700 dark:text-sky-300',
    },
    completed: {
        iconClass: 'bx bx-check-circle',
        iconBgClass: 'bg-emerald-600',
        borderClass: 'border-emerald-200 dark:border-emerald-800',
        valueClass: 'text-emerald-700 dark:text-emerald-300',
    },
};

const perPageStatStyle: StatCardStyle = {
    iconClass: 'bx bx-list-ul',
    iconBgClass: 'bg-violet-600',
    borderClass: 'border-violet-200 dark:border-violet-800',
    valueClass: 'text-violet-700 dark:text-violet-300',
};

const pageStatStyle: StatCardStyle = {
    iconClass: 'bx bx-book-open',
    iconBgClass: 'bg-amber-500',
    borderClass: 'border-amber-200 dark:border-amber-800',
    valueClass: 'text-amber-700 dark:text-amber-300',
};

export default function PacuListStats({ variant, meta }: PacuListStatsProps) {
    const { t } = useTranslation();
    const config = PACU_LIST_VARIANT_CONFIG[variant];
    const resultsStyle = variantStatStyles[variant];

    const stats = [
        {
            title: t('global.results'),
            value: String(meta.total ?? 0),
            subtitle: t(config.subtitleKey),
            ...resultsStyle,
        },
        {
            title: t('global.per_page'),
            value: String(meta.per_page ?? 15),
            subtitle: '',
            ...perPageStatStyle,
        },
        {
            title: t('global.page'),
            value: `${meta.current_page ?? 1} / ${meta.last_page ?? 1}`,
            subtitle:
                meta.from != null && meta.to != null
                    ? `${meta.from}–${meta.to} ${t('global.showing')}`
                    : '',
            ...pageStatStyle,
        },
    ];

    return (
        <div className="grid gap-4 sm:grid-cols-3">
            {stats.map((stat) => (
                <StatCard key={stat.title} {...stat} />
            ))}
        </div>
    );
}
