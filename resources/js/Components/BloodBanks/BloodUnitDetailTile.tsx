import { ReactNode } from 'react';

interface BloodUnitDetailTileProps {
    icon?: string;
    label: string;
    value?: ReactNode;
    children?: ReactNode;
}

export default function BloodUnitDetailTile({ icon, label, value, children }: BloodUnitDetailTileProps) {
    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div className="flex items-center gap-2 bg-rose-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-rose-800 dark:bg-rose-950/40 dark:text-rose-200">
                {icon && <i className={`bx ${icon}`} />}
                <span>{label}</span>
            </div>
            <div className="min-h-[3.25rem] bg-white px-4 py-4 text-sm font-medium text-gray-900 dark:bg-gray-900 dark:text-white">
                {children ?? value ?? '—'}
            </div>
        </div>
    );
}
