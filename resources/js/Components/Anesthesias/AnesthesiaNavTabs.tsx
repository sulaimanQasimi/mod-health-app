import { Link } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';
import { AnesthesiaListUrls, AnesthesiaListVariant } from '../../types/anesthesia';
import { ANESTHESIA_CARD_CLASS } from './anesthesiaUi';

interface AnesthesiaNavTabsProps {
    active: AnesthesiaListVariant | 'report';
    urls: AnesthesiaListUrls;
}

const TABS: {
    key: AnesthesiaListVariant | 'report';
    labelKey: string;
    icon: string;
    activeGradient: string;
    idleIcon: string;
}[] = [
    {
        key: 'new',
        labelKey: 'global.new_anesthesias',
        icon: 'bx-time-five',
        activeGradient: 'from-sky-500 to-blue-600',
        idleIcon: 'text-sky-500',
    },
    {
        key: 'approved',
        labelKey: 'global.approved_anesthesias',
        icon: 'bx-check-circle',
        activeGradient: 'from-emerald-500 to-teal-600',
        idleIcon: 'text-emerald-500',
    },
    {
        key: 'rejected',
        labelKey: 'global.rejected_anesthesias',
        icon: 'bx-x-circle',
        activeGradient: 'from-rose-500 to-red-600',
        idleIcon: 'text-rose-500',
    },
    {
        key: 'report',
        labelKey: 'global.reports',
        icon: 'bx-bar-chart-alt-2',
        activeGradient: 'from-violet-500 to-purple-600',
        idleIcon: 'text-violet-500',
    },
];

export default function AnesthesiaNavTabs({ active, urls }: AnesthesiaNavTabsProps) {
    const { t } = useTranslation();

    return (
        <nav className={`${ANESTHESIA_CARD_CLASS} p-2`}>
            <div className="flex flex-wrap gap-2">
                {TABS.map((tab) => {
                    const href = urls[tab.key];
                    const isActive = active === tab.key;

                    return (
                        <Link
                            key={tab.key}
                            href={href}
                            preserveScroll
                            className={`inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all ${
                                isActive
                                    ? `bg-gradient-to-r ${tab.activeGradient} text-white shadow-md`
                                    : 'bg-gray-50 text-gray-700 hover:bg-gray-100 dark:bg-gray-800/60 dark:text-gray-300 dark:hover:bg-gray-800'
                            }`}
                        >
                            <i
                                className={`bx ${tab.icon} text-lg ${isActive ? 'text-white' : tab.idleIcon}`}
                            />
                            {t(tab.labelKey)}
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
