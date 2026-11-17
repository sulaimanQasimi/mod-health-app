import { ReactNode } from 'react';
import { Link } from '@inertiajs/react';

interface FacilityPageHeaderProps {
    title: string;
    subtitle?: string;
    icon?: string;
    backHref?: string;
    backLabel?: string;
    action?: ReactNode;
}

export default function FacilityPageHeader({
    title,
    subtitle,
    icon,
    backHref,
    backLabel,
    action,
}: FacilityPageHeaderProps) {
    return (
        <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div className="min-w-0">
                <div className="flex items-center gap-1.5">
                    {icon && <i className={`bx ${icon} text-base text-gray-500`} />}
                    <h1 className="truncate text-base font-semibold text-gray-900 dark:text-white">{title}</h1>
                </div>
                {subtitle && (
                    <p className="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">{subtitle}</p>
                )}
            </div>
            <div className="flex flex-wrap items-center gap-1.5">
                {action}
                {backHref && (
                    <Link
                        href={backHref}
                        className="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <i className="bx bx-arrow-back text-sm" />
                        {backLabel}
                    </Link>
                )}
            </div>
        </div>
    );
}
