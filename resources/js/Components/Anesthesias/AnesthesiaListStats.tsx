import StatCard from '../ui/StatCard';
import { useTranslation } from '../../hooks/useTranslation';
import { AnesthesiaListVariant } from '../../types/anesthesia';
import { PaginationMeta } from '../../types/settings';
import { ANESTHESIA_LIST_VARIANT_CONFIG } from './anesthesiaUi';

interface AnesthesiaListStatsProps {
    variant: AnesthesiaListVariant;
    meta: PaginationMeta;
}

interface StatCardStyle {
    iconClass: string;
    iconBgClass: string;
    borderClass: string;
    valueClass: string;
}

const variantStatStyles: Record<AnesthesiaListVariant, StatCardStyle> = {
    new: {
        iconClass: 'bx bx-time-five',
        iconBgClass: 'bg-sky-600',
        borderClass: 'border-sky-200 dark:border-sky-800',
        valueClass: 'text-sky-700 dark:text-sky-300',
    },
    approved: {
        iconClass: 'bx bx-check-shield',
        iconBgClass: 'bg-emerald-600',
        borderClass: 'border-emerald-200 dark:border-emerald-800',
        valueClass: 'text-emerald-700 dark:text-emerald-300',
    },
    rejected: {
        iconClass: 'bx bx-block',
        iconBgClass: 'bg-rose-600',
        borderClass: 'border-rose-200 dark:border-rose-800',
        valueClass: 'text-rose-700 dark:text-rose-300',
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

export default function AnesthesiaListStats({ variant, meta }: AnesthesiaListStatsProps) {
    const { t } = useTranslation();
    const config = ANESTHESIA_LIST_VARIANT_CONFIG[variant];
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
