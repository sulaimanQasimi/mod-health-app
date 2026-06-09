import { ReactNode } from 'react';
import {
    HOSPITALIZATION_FILTER_PANEL_CLASS,
    HOSPITALIZATION_TABLE_PANEL_CLASS,
} from './hospitalizationUi';

interface HospitalizationPanelProps {
    title: string;
    icon: string;
    iconClassName?: string;
    description?: string;
    action?: ReactNode;
    variant?: 'filter' | 'table';
    children: ReactNode;
}

export default function HospitalizationPanel({
    title,
    icon,
    iconClassName = 'text-emerald-600 dark:text-emerald-400',
    description,
    action,
    variant = 'table',
    children,
}: HospitalizationPanelProps) {
    const panelClass =
        variant === 'filter' ? HOSPITALIZATION_FILTER_PANEL_CLASS : HOSPITALIZATION_TABLE_PANEL_CLASS;

    return (
        <section className={panelClass}>
            <div className="mb-4 flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                <div className="flex items-start gap-3">
                    <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                        <i className={`bx ${icon} text-lg ${iconClassName}`} />
                    </div>
                    <div>
                        <h2 className="text-sm font-semibold text-gray-900 dark:text-white">{title}</h2>
                        {description && (
                            <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{description}</p>
                        )}
                    </div>
                </div>
                {action}
            </div>
            {children}
        </section>
    );
}
