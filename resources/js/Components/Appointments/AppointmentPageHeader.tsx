import { ReactNode } from 'react';

interface AppointmentPageHeaderProps {
    title: string;
    subtitle?: string;
    icon?: string;
    accent?: string;
    action?: ReactNode;
}

export function AppointmentPageActions({ children }: { children: ReactNode }) {
    return (
        <div className="flex w-full flex-wrap items-center justify-start gap-2 sm:w-auto sm:justify-end">
            {children}
        </div>
    );
}

export default function AppointmentPageHeader({
    title,
    subtitle,
    icon = 'bx-calendar',
    accent = 'from-cyan-500 to-blue-600',
    action,
}: AppointmentPageHeaderProps) {
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
            {action}
        </div>
    );
}
