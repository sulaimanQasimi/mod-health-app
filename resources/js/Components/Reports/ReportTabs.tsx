import { ReactNode } from 'react';

export type ReportTabId = 'patients' | 'department';

interface ReportTabItem {
    id: ReportTabId;
    label: string;
    icon: string;
    description: string;
    disabled?: boolean;
}

interface ReportTabsProps {
    tabs: ReportTabItem[];
    active: ReportTabId;
    onChange: (tab: ReportTabId) => void;
    trailing?: ReactNode;
}

export default function ReportTabs({ tabs, active, onChange, trailing }: ReportTabsProps) {
    return (
        <div className="rounded-2xl border border-gray-200/80 bg-white/90 p-2 shadow-sm backdrop-blur dark:border-gray-700 dark:bg-gray-800/90">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div
                    role="tablist"
                    aria-label="Report tabs"
                    className="grid flex-1 gap-2 sm:grid-cols-2"
                >
                    {tabs.map((tab) => {
                        const selected = active === tab.id;

                        return (
                            <button
                                key={tab.id}
                                type="button"
                                role="tab"
                                aria-selected={selected}
                                disabled={tab.disabled}
                                onClick={() => onChange(tab.id)}
                                className={`group relative overflow-hidden rounded-xl px-4 py-3 text-start transition-all ${
                                    selected
                                        ? 'bg-gradient-to-br from-slate-900 to-slate-700 text-white shadow-lg shadow-slate-900/20 dark:from-cyan-600 dark:to-blue-700'
                                        : 'bg-gray-50 text-gray-700 hover:bg-gray-100 dark:bg-gray-900/50 dark:text-gray-200 dark:hover:bg-gray-900'
                                } ${tab.disabled ? 'cursor-not-allowed opacity-50' : ''}`}
                            >
                                <div className="flex items-start gap-3">
                                    <span
                                        className={`mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-xl ${
                                            selected
                                                ? 'bg-white/15 text-white'
                                                : 'bg-white text-slate-600 shadow-sm dark:bg-gray-800 dark:text-cyan-400'
                                        }`}
                                    >
                                        <i className={`bx ${tab.icon}`} />
                                    </span>
                                    <span className="min-w-0">
                                        <span className="block text-sm font-semibold tracking-wide">
                                            {tab.label}
                                        </span>
                                        <span
                                            className={`mt-0.5 block text-xs leading-snug ${
                                                selected
                                                    ? 'text-white/75'
                                                    : 'text-gray-500 dark:text-gray-400'
                                            }`}
                                        >
                                            {tab.description}
                                        </span>
                                    </span>
                                </div>
                            </button>
                        );
                    })}
                </div>
                {trailing ? <div className="shrink-0 px-1 lg:ps-4">{trailing}</div> : null}
            </div>
        </div>
    );
}
