import { DEPOT_SECTION_CLASS, DepotRequestContextInfo } from './depotUi';
import { useTranslation } from '../../hooks/useTranslation';

interface DepotRequestContextPanelProps {
    context: DepotRequestContextInfo;
}

export default function DepotRequestContextPanel({ context }: DepotRequestContextPanelProps) {
    const { t } = useTranslation();

    const fields: Array<[string, string | null]> = [
        [t('global.depot.branch'), context.branch_name],
        [t('global.depot.requesting_department'), context.department_name],
        [t('global.depot.request_user'), context.request_user_name],
        [t('global.depot.pharmacy_depot'), context.pharmacy_depot_label],
    ];

    return (
        <div className={DEPOT_SECTION_CLASS}>
            <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-300">
                {t('global.depot.request_context')}
            </p>
            <dl className="grid gap-3 sm:grid-cols-2">
                {fields.map(([label, value]) => (
                    <div key={label}>
                        <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</dt>
                        <dd className="mt-1 text-sm font-medium text-gray-900 dark:text-white">{value ?? '—'}</dd>
                    </div>
                ))}
            </dl>
        </div>
    );
}
