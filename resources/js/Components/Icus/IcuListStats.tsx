import { useTranslation } from '../../hooks/useTranslation';
import { IcuListVariant } from '../../types/icu';
import { PaginationMeta } from '../../types/settings';
import { ICU_LIST_VARIANT_CONFIG } from './icuUi';

interface IcuListStatsProps {
    variant: IcuListVariant;
    meta: PaginationMeta;
}

export default function IcuListStats({ variant, meta }: IcuListStatsProps) {
    const { t } = useTranslation();
    const config = ICU_LIST_VARIANT_CONFIG[variant];

    const stats = [
        {
            label: t('global.results'),
            value: String(meta.total ?? 0),
            icon: config.statIcon,
            gradient: config.statGradient,
        },
        {
            label: t('global.per_page'),
            value: String(meta.per_page ?? 15),
            icon: 'bx-list-ul',
            gradient: 'from-violet-500 to-purple-600',
        },
        {
            label: t('global.page'),
            value: `${meta.current_page ?? 1} / ${meta.last_page ?? 1}`,
            icon: 'bx-book-open',
            gradient: 'from-amber-500 to-orange-500',
        },
    ];

    return (
        <div className="grid gap-3 sm:grid-cols-3">
            {stats.map((stat) => (
                <div
                    key={stat.label}
                    className="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900"
                >
                    <div
                        className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${stat.gradient} text-white shadow-md`}
                    >
                        <i className={`bx ${stat.icon} text-xl`} />
                    </div>
                    <div className="min-w-0">
                        <p className="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {stat.label}
                        </p>
                        <p className="text-lg font-bold text-gray-900 dark:text-white">{stat.value}</p>
                    </div>
                </div>
            ))}
        </div>
    );
}
