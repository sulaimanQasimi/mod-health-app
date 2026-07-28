import { Card } from 'flowbite-react';
import { FormEvent, ReactNode, useState } from 'react';

interface ReportFilterPanelProps {
    title: string;
    children: ReactNode;
    actions: ReactNode;
    defaultOpen?: boolean;
    accentIconClass?: string;
    onSubmit?: (event: FormEvent) => void;
}

export default function ReportFilterPanel({
    title,
    children,
    actions,
    defaultOpen = true,
    accentIconClass = 'text-cyan-500',
    onSubmit,
}: ReportFilterPanelProps) {
    const [open, setOpen] = useState(defaultOpen);

    return (
        <Card className="!shadow-sm">
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                className="flex w-full items-center justify-between gap-3 border-b border-gray-100 px-1 pb-4 text-start dark:border-gray-700"
            >
                <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i className={`bx bx-filter-alt ${accentIconClass}`} />
                    {title}
                </span>
                <i className={`bx ${open ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
            </button>

            {open ? (
                <form onSubmit={onSubmit} className="space-y-4 pt-4">
                    {children}
                    <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                        {actions}
                    </div>
                </form>
            ) : null}
        </Card>
    );
}
