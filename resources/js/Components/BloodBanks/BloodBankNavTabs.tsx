import { Link } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';
import { BloodBankListUrls, BloodRequestListVariant } from '../../types/bloodBank';

interface BloodBankNavTabsProps {
    active: BloodRequestListVariant | 'dashboard' | 'inventory' | 'movements' | 'report';
    urls: BloodBankListUrls;
}

const TABS: { key: BloodBankNavTabsProps['active']; labelKey: string; icon: string; urlKey: keyof BloodBankListUrls }[] = [
    { key: 'dashboard', labelKey: 'global.blood_bank_dashboard', icon: 'bx-grid-alt', urlKey: 'dashboard' },
    { key: 'new', labelKey: 'global.new_blood_requests', icon: 'bx-donate-blood', urlKey: 'new' },
    { key: 'approved', labelKey: 'global.approved_blood_requests', icon: 'bx-check-circle', urlKey: 'approved' },
    { key: 'delivered', labelKey: 'global.delivered_blood_requests', icon: 'bx-package', urlKey: 'delivered' },
    { key: 'rejected', labelKey: 'global.rejected_blood_requests', icon: 'bx-x-circle', urlKey: 'rejected' },
    { key: 'inventory', labelKey: 'global.blood_inventory', icon: 'bx-box', urlKey: 'inventory' },
    { key: 'movements', labelKey: 'global.stock_movement_audit', icon: 'bx-list-ul', urlKey: 'movements' },
    { key: 'report', labelKey: 'global.reports', icon: 'bx-bar-chart-alt-2', urlKey: 'report' },
];

export default function BloodBankNavTabs({ active, urls }: BloodBankNavTabsProps) {
    const { t } = useTranslation();

    return (
        <nav className="flex flex-wrap gap-2">
            {TABS.map((tab) => {
                const href = urls[tab.urlKey];
                const isActive = active === tab.key;

                return (
                    <Link
                        key={tab.key}
                        href={href}
                        className={`inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition ${
                            isActive
                                ? 'bg-rose-600 text-white shadow-md'
                                : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-rose-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-rose-950/30'
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
