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
    onClick?: () => void;
    active?: boolean;
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
    onClick,
    active = false,
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
        <Card
            className={mergeClasses(
                'border !shadow-sm',
                borderClass ?? 'border-gray-200 dark:border-gray-700',
                onClick && 'cursor-pointer transition hover:shadow-md',
                active && 'ring-2 ring-offset-2 ring-gray-400 dark:ring-gray-500',
                className,
            )}
            onClick={onClick}
        >
            <div className="flex items-start justify-between gap-3">
                <div>
                    <h4 className="text-sm text-gray-700 dark:text-gray-300">{title}</h4>
                    <p
                        className={mergeClasses(
                            'mt-2 text-3xl',
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
