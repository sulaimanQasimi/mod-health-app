import { ReactNode } from 'react';

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

interface DetailTileProps {
    label: string;
    value?: ReactNode;
    className?: string;
}

export function DetailTile({ label, value, className }: DetailTileProps) {
    return (
        <div
            className={mergeClasses(
                'rounded-xl border border-gray-100 bg-gray-50 p-3 text-sm dark:border-gray-700 dark:bg-gray-800/40',
                className,
            )}
        >
            <p className="text-xs text-gray-500 dark:text-gray-400">{label}</p>
            <div className="mt-0.5 font-medium text-gray-900 dark:text-white">{value ?? '—'}</div>
        </div>
    );
}

type DetailTextBlockVariant = 'default' | 'emerald' | 'cyan' | 'amber';

const textBlockVariants: Record<DetailTextBlockVariant, string> = {
    default:
        'border-gray-200 bg-gray-50 text-gray-900 dark:border-gray-700 dark:bg-gray-800/40 dark:text-white',
    emerald:
        'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100',
    cyan: 'border-cyan-200 bg-cyan-50 text-cyan-900 dark:border-cyan-900/40 dark:bg-cyan-900/20 dark:text-cyan-100',
    amber:
        'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-200',
};

interface DetailTextBlockProps {
    label: string;
    children: ReactNode;
    variant?: DetailTextBlockVariant;
    className?: string;
}

export function DetailTextBlock({
    label,
    children,
    variant = 'default',
    className,
}: DetailTextBlockProps) {
    return (
        <div
            className={mergeClasses(
                'rounded-xl border p-4 text-sm',
                textBlockVariants[variant],
                className,
            )}
        >
            <strong className="font-semibold">{label}:</strong> {children}
        </div>
    );
}
