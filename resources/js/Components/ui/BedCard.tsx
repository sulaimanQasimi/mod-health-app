import { Card } from 'flowbite-react';
import { ReactNode } from 'react';

export interface BedCardProps {
    title: string;
    value: number | string;
    className?: string;
    badgeClass?: string;
    iconClass?: string;
    icon?: ReactNode;
}

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

export default function BedCard({
    title,
    value,
    className,
    badgeClass = 'bg-blue-600',
    iconClass = 'bx bx-bed',
    icon,
}: BedCardProps) {
    return (
        <Card className={className}>
            <div className="flex items-start justify-between gap-3">
                <div>
                    <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300">{title}</h4>
                    <p className="mt-3 text-4xl font-bold text-gray-900 dark:text-white">{value}</p>
                </div>
                <span className={mergeClasses('rounded-lg p-2', badgeClass)}>
                    {icon ?? <i className={mergeClasses(iconClass, 'text-2xl text-white')} />}
                </span>
            </div>
        </Card>
    );
}
