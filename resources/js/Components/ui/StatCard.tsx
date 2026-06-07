import { Card } from 'flowbite-react';
import { ReactNode } from 'react';

export interface StatCardProps {
    title: string;
    value: number | string;
    subtitle: string;
    icon?: ReactNode;
    iconClass?: string;
    iconBgClass?: string;
    borderClass?: string;
    valueClass?: string;
    className?: string;
}

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

export default function StatCard({
    title,
    value,
    subtitle,
    icon,
    iconClass,
    iconBgClass,
    borderClass,
    valueClass,
    className,
}: StatCardProps) {
    const iconElement = icon ?? (
        iconClass ? (
            <span
                className={mergeClasses(
                    'flex h-12 w-12 items-center justify-center rounded-full text-white',
                    iconBgClass,
                )}
            >
                <i className={mergeClasses(iconClass, 'text-2xl')} />
            </span>
        ) : null
    );

    return (
        <Card className={mergeClasses(borderClass, className)}>
            <div className="flex items-start justify-between gap-3">
                <div>
                    <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300">{title}</h4>
                    <p
                        className={mergeClasses(
                            'mt-2 text-3xl font-bold',
                            valueClass ?? 'text-gray-900 dark:text-white',
                        )}
                    >
                        {value}
                    </p>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{subtitle}</p>
                </div>
                {iconElement}
            </div>
        </Card>
    );
}
