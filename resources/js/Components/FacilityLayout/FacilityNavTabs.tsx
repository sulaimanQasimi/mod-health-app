import { Link } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';

export type FacilityNavKey = 'dashboard' | 'floors' | 'rooms' | 'beds';

export interface FacilityNavProps {
    active: FacilityNavKey;
    urls: Record<FacilityNavKey, string>;
    permissions?: Partial<Record<FacilityNavKey, boolean>>;
}

const TABS: Array<{ key: FacilityNavKey; labelKey: string; icon: string }> = [
    { key: 'dashboard', labelKey: 'global.dashboard', icon: 'bx-grid-alt' },
    { key: 'floors', labelKey: 'global.floors', icon: 'bx-layer' },
    { key: 'rooms', labelKey: 'global.rooms', icon: 'bx-door-open' },
    { key: 'beds', labelKey: 'global.beds', icon: 'bx-bed' },
];

export default function FacilityNavTabs({ active, urls, permissions }: FacilityNavProps) {
    const { t } = useTranslation();
    const visibleTabs = TABS.filter((tab) => permissions?.[tab.key] !== false);

    if (visibleTabs.length === 0) {
        return null;
    }

    return (
        <nav className="border-b border-gray-200 dark:border-gray-700">
            <div className="-mb-px flex flex-wrap gap-1">
                {visibleTabs.map((tab) => {
                    const isActive = active === tab.key;
                    return (
                        <Link
                            key={tab.key}
                            href={urls[tab.key]}
                            preserveScroll
                            className={`inline-flex items-center gap-1.5 border-b-2 px-2.5 py-1.5 text-xs font-medium transition ${
                                isActive
                                    ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                            }`}
                        >
                            <i className={`bx ${tab.icon} text-sm`} />
                            {t(tab.labelKey)}
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
