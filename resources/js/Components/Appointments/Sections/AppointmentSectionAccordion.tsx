import { Badge, Spinner } from 'flowbite-react';
import { ReactNode, useState } from 'react';

interface AppointmentSectionAccordionProps {
    id: string;
    icon: string;
    iconClassName?: string;
    title: string;
    count?: number;
    badgeColor?: 'info' | 'success' | 'warning' | 'failure' | 'gray';
    defaultOpen?: boolean;
    children: ReactNode;
}

export default function AppointmentSectionAccordion({
    id,
    icon,
    iconClassName = 'text-blue-500',
    title,
    count,
    badgeColor = 'info',
    defaultOpen = false,
    children,
}: AppointmentSectionAccordionProps) {
    const [open, setOpen] = useState(defaultOpen);

    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <button
                type="button"
                id={`${id}-header`}
                aria-expanded={open}
                aria-controls={`${id}-panel`}
                onClick={() => setOpen((current) => !current)}
                className="flex w-full items-center justify-between gap-3 bg-gray-50 px-4 py-3.5 text-start transition hover:bg-gray-100 dark:bg-gray-800/80 dark:hover:bg-gray-700/60"
            >
                <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i className={`bx ${icon} text-lg ${iconClassName}`} />
                    {title}
                    {count !== undefined && (
                        <Badge color={badgeColor} size="sm">
                            {count}
                        </Badge>
                    )}
                </span>
                <i className={`bx ${open ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
            </button>
            <div
                id={`${id}-panel`}
                hidden={!open}
                className="border-t border-gray-200 p-4 dark:border-gray-700"
            >
                {children}
            </div>
        </div>
    );
}

export function SectionLoadingState() {
    return (
        <div className="flex flex-col items-center justify-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
            <Spinner size="md" />
            <span>Loading...</span>
        </div>
    );
}

export function SectionEmptyState({ message }: { message: string }) {
    return (
        <div className="rounded-xl border border-blue-100 bg-blue-50 px-4 py-6 text-center text-sm text-blue-700 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300">
            <i className="bx bx-info-circle me-2 text-lg" />
            {message}
        </div>
    );
}
