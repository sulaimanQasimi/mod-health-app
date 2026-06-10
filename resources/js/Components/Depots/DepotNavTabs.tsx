import { Link } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotNavUrls } from '../../types/depot';
import { DEPOT_CARD_CLASS } from './depotUi';

export type DepotNavKey = 'index' | 'transactions' | 'requests' | 'depotToDepot' | 'depotToPharmacy' | 'reports' | 'tools';

interface DepotNavTabsProps {
    active: DepotNavKey;
    urls: DepotNavUrls;
}

const TABS: Array<{
    key: DepotNavKey;
    labelKey: string;
    icon: string;
    activeGradient: string;
    idleIcon: string;
}> = [
    { key: 'index', labelKey: 'global.depot.list', icon: 'bx-store', activeGradient: 'from-amber-500 to-orange-600', idleIcon: 'text-amber-500' },
    { key: 'transactions', labelKey: 'global.depot.transactions', icon: 'bx-transfer', activeGradient: 'from-sky-500 to-blue-600', idleIcon: 'text-sky-500' },
    { key: 'requests', labelKey: 'global.depot.requests', icon: 'bx-git-pull-request', activeGradient: 'from-violet-500 to-purple-600', idleIcon: 'text-violet-500' },
    { key: 'depotToDepot', labelKey: 'global.depot.depot_to_depot', icon: 'bx-shuffle', activeGradient: 'from-emerald-500 to-teal-600', idleIcon: 'text-emerald-500' },
    { key: 'depotToPharmacy', labelKey: 'global.depot.depot_to_pharmacy', icon: 'bx-clinic', activeGradient: 'from-rose-500 to-pink-600', idleIcon: 'text-rose-500' },
    { key: 'reports', labelKey: 'global.depot.reports', icon: 'bx-bar-chart-alt-2', activeGradient: 'from-indigo-500 to-blue-700', idleIcon: 'text-indigo-500' },
    { key: 'tools', labelKey: 'global.depot.tools', icon: 'bx-wrench', activeGradient: 'from-gray-600 to-gray-800', idleIcon: 'text-gray-500' },
];

export default function DepotNavTabs({ active, urls }: DepotNavTabsProps) {
    const { t } = useTranslation();

    return (
        <nav className={`${DEPOT_CARD_CLASS} p-2`}>
            <div className="flex flex-wrap gap-2">
                {TABS.map((tab) => {
                    const href = urls[tab.key];
                    const isActive = active === tab.key;

                    return (
                        <Link
                            key={tab.key}
                            href={href}
                            preserveScroll
                            className={`inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition-all sm:px-4 sm:py-2.5 ${
                                isActive
                                    ? `bg-gradient-to-r ${tab.activeGradient} text-white shadow-md`
                                    : 'bg-gray-50 text-gray-700 hover:bg-gray-100 dark:bg-gray-800/60 dark:text-gray-300 dark:hover:bg-gray-800'
                            }`}
                        >
                            <i className={`bx ${tab.icon} text-lg ${isActive ? 'text-white' : tab.idleIcon}`} />
                            <span className="hidden sm:inline">{t(tab.labelKey)}</span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
