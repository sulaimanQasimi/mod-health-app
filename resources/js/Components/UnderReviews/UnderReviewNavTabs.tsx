import { Link } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';

export type UnderReviewWorkflowTab = 'index' | 'pending' | 'myCases' | 'discharged';

interface UnderReviewNavTabsProps {
    activeTab: UnderReviewWorkflowTab;
    urls: {
        index: string;
        pending: string;
        myCases: string;
        discharged: string;
    };
}

const TABS: Array<{ id: UnderReviewWorkflowTab; labelKey: string; icon: string; hrefKey: keyof UnderReviewNavTabsProps['urls'] }> = [
    { id: 'pending', labelKey: 'global.pending', icon: 'bx-time-five', hrefKey: 'pending' },
    { id: 'myCases', labelKey: 'global.ongoing_appointments', icon: 'bx-user-check', hrefKey: 'myCases' },
    { id: 'discharged', labelKey: 'global.discharged', icon: 'bx-check-circle', hrefKey: 'discharged' },
    { id: 'index', labelKey: 'global.all', icon: 'bx-list-ul', hrefKey: 'index' },
];

export default function UnderReviewNavTabs({ activeTab, urls }: UnderReviewNavTabsProps) {
    const { t } = useTranslation();

    return (
        <div className="mb-6 flex flex-wrap gap-2 border-b border-gray-200 pb-4 dark:border-gray-700">
            {TABS.map((tab) => {
                const isActive = activeTab === tab.id;

                return (
                    <Link
                        key={tab.id}
                        href={urls[tab.hrefKey]}
                        preserveScroll
                        className={`inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition ${
                            isActive
                                ? 'border-cyan-300 bg-cyan-50 text-cyan-700 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-300'
                                : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700/60'
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
