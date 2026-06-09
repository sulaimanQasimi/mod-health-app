import { ReactNode, useState } from 'react';

interface CaseSectionPanelProps {
    id: string;
    icon: string;
    title: string;
    badge?: string;
    badgeTone?: 'neutral' | 'locked' | 'done';
    defaultOpen?: boolean;
    children: ReactNode;
}

function badgeClasses(tone: CaseSectionPanelProps['badgeTone']) {
    switch (tone) {
        case 'done':
            return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/80 dark:bg-emerald-950/30 dark:text-emerald-300 dark:ring-emerald-800';
        case 'locked':
            return 'bg-amber-50 text-amber-800 ring-1 ring-amber-200/80 dark:bg-amber-950/30 dark:text-amber-200 dark:ring-amber-800';
        default:
            return 'bg-gray-100 text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700';
    }
}

export function CaseFormActions({ children }: { children: ReactNode }) {
    return <div className="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">{children}</div>;
}

export function CaseDataTable({ children }: { children: ReactNode }) {
    return (
        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table className="w-full min-w-[640px] border-collapse text-sm">{children}</table>
        </div>
    );
}

export function CaseDataTableHead({ children }: { children: ReactNode }) {
    return (
        <thead>
            <tr className="border-b border-gray-200 bg-gray-50/80 text-xs font-medium uppercase tracking-wide text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                {children}
            </tr>
        </thead>
    );
}

export function CaseDataTableTh({ children, className = '' }: { children: ReactNode; className?: string }) {
    return <th className={`px-3 py-2.5 text-start font-medium ${className}`}>{children}</th>;
}

export function CaseDataTableBody({ children }: { children: ReactNode }) {
    return <tbody className="divide-y divide-gray-100 dark:divide-gray-800">{children}</tbody>;
}

export function CaseDataTableRow({ children }: { children: ReactNode }) {
    return <tr className="bg-white transition-colors hover:bg-gray-50/50 dark:bg-gray-900 dark:hover:bg-gray-800/40">{children}</tr>;
}

export function CaseDataTableTd({
    children,
    className = '',
    colSpan,
    dir,
}: {
    children: ReactNode;
    className?: string;
    colSpan?: number;
    dir?: string;
}) {
    return (
        <td colSpan={colSpan} dir={dir} className={`px-3 py-2 align-middle ${className}`}>
            {children}
        </td>
    );
}

export default function CaseSectionPanel({
    id,
    icon,
    title,
    badge,
    badgeTone = 'neutral',
    defaultOpen = false,
    children,
}: CaseSectionPanelProps) {
    const [open, setOpen] = useState(defaultOpen);

    return (
        <section className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <button
                type="button"
                id={`${id}-header`}
                aria-expanded={open}
                aria-controls={`${id}-panel`}
                onClick={() => setOpen((current) => !current)}
                className="flex w-full items-center justify-between gap-3 px-4 py-3.5 text-start transition hover:bg-gray-50 dark:hover:bg-gray-800/60"
            >
                <span className="flex min-w-0 items-center gap-3">
                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <i className={`bx ${icon} text-lg`} />
                    </span>
                    <span className="truncate text-sm font-semibold text-gray-900 dark:text-white">{title}</span>
                    {badge && (
                        <span className={`shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium ${badgeClasses(badgeTone)}`}>
                            {badge}
                        </span>
                    )}
                </span>
                <i
                    className={`bx ${open ? 'bx-chevron-up' : 'bx-chevron-down'} shrink-0 text-xl text-gray-400`}
                    aria-hidden
                />
            </button>
            <div
                id={`${id}-panel`}
                hidden={!open}
                className="border-t border-gray-100 px-4 py-4 dark:border-gray-800"
            >
                {children}
            </div>
        </section>
    );
}
