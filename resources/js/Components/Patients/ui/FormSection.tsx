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
    blue: 'text-blue-600 dark:text-blue-400',
    emerald: 'text-emerald-600 dark:text-emerald-400',
    violet: 'text-violet-600 dark:text-violet-400',
    amber: 'text-amber-600 dark:text-amber-400',
    rose: 'text-rose-600 dark:text-rose-400',
    cyan: 'text-cyan-600 dark:text-cyan-400',
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
    return (
        <section
            className={mergeClasses(
                !isFirst && 'mt-8 border-t border-gray-200 pt-8 dark:border-gray-700',
                className,
            )}
        >
            <div className="mb-5">
                <div className="flex items-center gap-2.5">
                    <i className={`bx ${icon} text-lg ${accentStyles[accent]}`} />
                    <h3 className="text-base font-semibold text-gray-900 dark:text-white">{title}</h3>
                </div>
                {description && (
                    <p className="mt-1 ps-7 text-sm text-gray-500 dark:text-gray-400">{description}</p>
                )}
            </div>
            <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2 xl:grid-cols-3">{children}</div>
        </section>
    );
}

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}
