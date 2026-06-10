import { ReactNode } from 'react';
import BackLink from '../ui/BackLink';

interface SettingsPageHeaderProps {
    title: string;
    subtitle?: string;
    icon: string;
    accent?: string;
    backHref?: string;
    backLabel: string;
    action?: ReactNode;
}

export default function SettingsPageHeader({
    title,
    subtitle,
    icon,
    accent = 'from-slate-500 to-gray-600',
    backHref,
    backLabel,
    action,
}: SettingsPageHeaderProps) {
    return (
        <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
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
            <div className="flex flex-wrap gap-2">
                {action}
                {backHref && <BackLink href={backHref}>{backLabel}</BackLink>}
            </div>
        </div>
    );
}
