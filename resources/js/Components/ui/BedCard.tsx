import { Card } from 'flowbite-react';
import { ReactNode } from 'react';

export interface BedCardProps {
    title: string;
    value: number | string;
    iconClass?: string;
    iconBgClass?: string;
    borderClass?: string;
    valueClass?: string;
    className?: string;
    icon?: ReactNode;
}

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

export default function BedCard({
    title,
    value,
    iconClass = 'bx bx-bed',
    iconBgClass = 'bg-blue-600',
    borderClass,
    valueClass,
    className,
    icon,
}: BedCardProps) {
    const iconElement = icon ?? (
        <span
            className={mergeClasses(
                'flex h-12 w-12 items-center justify-center rounded-full text-white',
                iconBgClass,
            )}
        >
            <i className={mergeClasses(iconClass, 'text-2xl')} />
        </span>
    );

    return (
        <Card
            className={mergeClasses(
                'border !shadow-sm',
                borderClass ?? 'border-gray-200 dark:border-gray-700',
                className,
            )}
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
                </div>
                {iconElement}
            </div>
        </Card>
    );
}
