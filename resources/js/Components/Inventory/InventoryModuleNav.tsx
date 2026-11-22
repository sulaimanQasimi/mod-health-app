import { Link } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';

export type InventoryNavKey =
    | 'medicines'
    | 'purchases'
    | 'suppliers'
    | 'stocks'
    | 'movements'
    | 'transfers'
    | 'depots'
    | 'tools';

export type InventoryNavUrls = Record<InventoryNavKey, string>;

const TABS: Array<{ key: InventoryNavKey; labelKey: string; icon: string }> = [
    { key: 'medicines', labelKey: 'global.medicine', icon: 'bx-capsule' },
    { key: 'purchases', labelKey: 'global.purchase', icon: 'bx-cart' },
    { key: 'suppliers', labelKey: 'global.supplier', icon: 'bx-buildings' },
    { key: 'stocks', labelKey: 'global.stock', icon: 'bx-package' },
    { key: 'movements', labelKey: 'global.movement', icon: 'bx-transfer-alt' },
    { key: 'transfers', labelKey: 'global.transfer', icon: 'bx-git-pull-request' },
    { key: 'depots', labelKey: 'global.depots', icon: 'bx-store' },
    { key: 'tools', labelKey: 'global.tools', icon: 'bx-wrench' },
];

export default function InventoryModuleNav({
    active,
    urls,
}: {
    active: InventoryNavKey;
    urls: InventoryNavUrls;
}) {
    const { t } = useTranslation();

    return (
        <nav className="mb-3 overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div className="flex min-w-max items-stretch gap-0.5 p-1">
                {TABS.map((tab) => {
                    const isActive = active === tab.key;
                    return (
                        <Link
                            key={tab.key}
                            href={urls[tab.key]}
                            preserveScroll
                            className={`inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-semibold transition sm:text-sm ${
                                isActive
                                    ? 'bg-teal-600 text-white shadow-sm'
                                    : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/70'
                            }`}
                        >
                            <i className={`bx ${tab.icon} text-base ${isActive ? 'text-white' : 'text-teal-600'}`} />
                            <span>{t(tab.labelKey)}</span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
