import { Link } from '@inertiajs/react';
import { ReactNode } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { LaboratoryNavUrls } from '../../types/laboratory';

interface LaboratoryPageHeaderProps {
    title: string;
    subtitle?: string;
    icon?: string;
    accent?: string;
    action?: ReactNode;
    navUrls?: LaboratoryNavUrls;
    activeTab?: 'pending' | 'inProgress' | 'completed' | 'grouped' | 'scan';
}

const tabClasses = (active: boolean) =>
    active
        ? 'border-teal-500 bg-teal-50 text-teal-700 dark:border-teal-400 dark:bg-teal-950/40 dark:text-teal-300'
        : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800';

export default function LaboratoryPageHeader({
    title,
    subtitle,
    icon = 'bx-test-tube',
    accent = 'from-teal-500 to-cyan-600',
    action,
    navUrls,
    activeTab,
}: LaboratoryPageHeaderProps) {
    const { t } = useTranslation();

    const tabs = navUrls
        ? [
              { key: 'pending' as const, labelKey: 'global.pending_tests', href: navUrls.pending },
              { key: 'inProgress' as const, labelKey: 'global.in_progress_tests', href: navUrls.inProgress },
              { key: 'completed' as const, labelKey: 'global.completed_tests', href: navUrls.completed },
              { key: 'grouped' as const, labelKey: 'global.grouped_test_results', href: navUrls.grouped },
              { key: 'scan' as const, labelKey: 'global.scan_test', href: navUrls.scan },
          ]
        : [];

    return (
        <div className="mb-6 space-y-4 border-b border-gray-200 pb-6 dark:border-gray-700">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-4">
                    <div
                        className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${accent} text-white shadow-md`}
                    >
                        <i className={`bx ${icon} text-xl`} />
                    </div>
                    <div>
                        <h1 className="text-xl font-bold text-gray-900 dark:text-white">{title}</h1>
                        {subtitle && (
                            <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{subtitle}</p>
                        )}
                    </div>
                </div>
                {action && <div className="flex flex-wrap gap-2">{action}</div>}
            </div>

            {tabs.length > 0 && activeTab && (
                <nav className="flex flex-wrap gap-2">
                    {tabs.map((tab) => (
                        <Link
                            key={tab.key}
                            href={tab.href}
                            className={`rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors ${tabClasses(activeTab === tab.key)}`}
                        >
                            {t(tab.labelKey)}
                        </Link>
                    ))}
                </nav>
            )}
        </div>
    );
}
