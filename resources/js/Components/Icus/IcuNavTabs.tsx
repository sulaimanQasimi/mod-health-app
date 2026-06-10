import { Link } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';
import { IcuListUrls, IcuListVariant } from '../../types/icu';

interface IcuNavTabsProps {
    active: IcuListVariant | 'report';
    urls: IcuListUrls;
}

const TABS: { key: IcuListVariant | 'report'; labelKey: string; icon: string }[] = [
    { key: 'new', labelKey: 'global.new_icus', icon: 'bx-plus-circle' },
    { key: 'approved', labelKey: 'global.approved_icus', icon: 'bx-check-circle' },
    { key: 'rejected', labelKey: 'global.rejected_icus', icon: 'bx-x-circle' },
    { key: 'report', labelKey: 'global.reports', icon: 'bx-bar-chart-alt-2' },
];

export default function IcuNavTabs({ active, urls }: IcuNavTabsProps) {
    const { t } = useTranslation();

    return (
        <div className="flex flex-wrap gap-2 border-b border-gray-200 pb-4 dark:border-gray-700">
            {TABS.map((tab) => {
                const href = urls[tab.key];
                const isActive = active === tab.key;

                return (
                    <Link
                        key={tab.key}
                        href={href}
                        preserveScroll
                        className={`inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors ${
                            isActive
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                        }`}
                    >
                        <i className={`bx ${tab.icon}`} />
                        {t(tab.labelKey)}
                    </Link>
                );
            })}
        </div>
    );
}
