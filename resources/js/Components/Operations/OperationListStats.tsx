import { useTranslation } from '../../hooks/useTranslation';
import { PaginationMeta } from '../../types/settings';
import { OperationListVariant } from '../../types/operation';
import { OPERATION_LIST_VARIANT_CONFIG } from './operationUi';

interface OperationListStatsProps {
    variant: OperationListVariant;
    meta: PaginationMeta;
}

export default function OperationListStats({ variant, meta }: OperationListStatsProps) {
    const { t } = useTranslation();
    const config = OPERATION_LIST_VARIANT_CONFIG[variant];

    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div className={`overflow-hidden rounded-xl bg-gradient-to-br ${config.statGradient} p-4 text-white shadow-md`}>
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-sm text-white/80">{t('global.total')}</p>
                        <p className="mt-1 text-2xl font-bold">{meta.total ?? 0}</p>
                    </div>
                    <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-white/20">
                        <i className={`bx ${config.statIcon} text-2xl`} />
                    </span>
                </div>
            </div>
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p className="text-sm text-gray-500 dark:text-gray-400">{t('global.showing')}</p>
                <p className="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                    {meta.from ?? 0} – {meta.to ?? 0}
                </p>
            </div>
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p className="text-sm text-gray-500 dark:text-gray-400">{t('global.page')}</p>
                <p className="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                    {meta.current_page ?? 1} / {meta.last_page ?? 1}
                </p>
            </div>
        </div>
    );
}
