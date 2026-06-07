import { Card } from 'flowbite-react';
import { ReactNode } from 'react';

interface StatCardProps {
    title: string;
    value: number | string;
    subtitle: string;
    icon: ReactNode;
    borderClass?: string;
    valueClass?: string;
}

export default function StatCard({
    title,
    value,
    subtitle,
    icon,
    borderClass = 'border-gray-200',
    valueClass = 'text-gray-900 dark:text-white',
}: StatCardProps) {
    return (
        <Card className={`h-full ${borderClass}`}>
            <div className="flex items-start justify-between gap-4">
                <div>
                    <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300">{title}</h4>
                    <p className={`mt-2 text-3xl font-bold ${valueClass}`}>{value}</p>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{subtitle}</p>
                </div>
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full">{icon}</div>
            </div>
        </Card>
    );
}
