import { Link } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';
import { OperationListUrls, OperationListVariant } from '../../types/operation';

interface OperationNavTabsProps {
    active: OperationListVariant | 'report';
    urls: OperationListUrls;
}

const TABS: { key: OperationListVariant | 'report'; labelKey: string; icon: string }[] = [
    { key: 'new', labelKey: 'global.new_operations', icon: 'bx-cut' },
    { key: 'approved', labelKey: 'global.approved_operations', icon: 'bx-check-circle' },
    { key: 'reserved', labelKey: 'global.reserved_operations', icon: 'bx-calendar-check' },
    { key: 'completed', labelKey: 'global.completed_operations', icon: 'bx-check-double' },
    { key: 'report', labelKey: 'global.reports', icon: 'bx-bar-chart-alt-2' },
];

export default function OperationNavTabs({ active, urls }: OperationNavTabsProps) {
    const { t } = useTranslation();

    return (
        <nav className="flex flex-wrap gap-2">
            {TABS.map((tab) => {
                const href = urls[tab.key];
                const isActive = active === tab.key;

                return (
                    <Link
                        key={tab.key}
                        href={href}
                        className={`inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition ${
                            isActive
                                ? 'bg-amber-600 text-white shadow-md'
                                : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-amber-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-amber-950/30'
                        }`}
                    >
                        <i className={`bx ${tab.icon}`} />
                        {t(tab.labelKey)}
                    </Link>
                );
            })}
        </nav>
    );
}
