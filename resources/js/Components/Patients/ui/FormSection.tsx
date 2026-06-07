import { ReactNode } from 'react';

interface FormSectionProps {
    icon: string;
    title: string;
    description?: string;
    accent?: 'blue' | 'emerald' | 'violet' | 'amber' | 'rose' | 'cyan';
    children: ReactNode;
    className?: string;
    isFirst?: boolean;
}

const accentStyles = {
    blue: {
        icon: 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300',
        border: 'border-blue-100 dark:border-blue-900/50',
    },
    emerald: {
        icon: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300',
        border: 'border-emerald-100 dark:border-emerald-900/50',
    },
    violet: {
        icon: 'bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-300',
        border: 'border-violet-100 dark:border-violet-900/50',
    },
    amber: {
        icon: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300',
        border: 'border-amber-100 dark:border-amber-900/50',
    },
    rose: {
        icon: 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300',
        border: 'border-rose-100 dark:border-rose-900/50',
    },
    cyan: {
        icon: 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-300',
        border: 'border-cyan-100 dark:border-cyan-900/50',
    },
};

export default function FormSection({
    icon,
    title,
    description,
    accent = 'blue',
    children,
    className = '',
    isFirst = false,
}: FormSectionProps) {
    const styles = accentStyles[accent];

    return (
        <section
            className={`${isFirst ? 'pt-0' : 'border-t border-gray-200 pt-6 dark:border-gray-700'} ${className}`}
        >
            <div className="mb-4 flex items-start gap-3">
                {/* <div className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${styles.icon}`}>
                    <i className={`bx ${icon} text-lg`} />
                </div>
                <div>
                    <h3 className="text-sm font-semibold text-gray-900 dark:text-white">{title}</h3>
                    {description && (
                        <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{description}</p>
                    )}
                </div> */}
            </div>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">{children}</div>
        </section>
    );
}
