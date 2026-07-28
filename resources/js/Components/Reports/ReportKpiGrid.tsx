import { Card } from 'flowbite-react';
import { ReportKpiStat } from './reportTypes';

interface ReportKpiGridProps {
    stats: ReportKpiStat[];
    columns?: string;
}

export default function ReportKpiGrid({
    stats,
    columns = 'sm:grid-cols-2 lg:grid-cols-4',
}: ReportKpiGridProps) {
    if (!stats.length) {
        return null;
    }

    return (
        <div className={`grid gap-4 ${columns}`}>
            {stats.map((stat) => (
                <Card key={stat.key} className="overflow-hidden !shadow-sm">
                    <div className="flex items-center gap-4">
                        <div
                            className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${stat.accent} text-white shadow-md`}
                        >
                            <i className={`bx ${stat.icon} text-xl`} />
                        </div>
                        <div className="min-w-0">
                            <p className="truncate text-sm text-gray-500 dark:text-gray-400">{stat.label}</p>
                            <p className="text-2xl font-bold tabular-nums text-gray-900 dark:text-white">
                                {stat.value}
                            </p>
                            {stat.subtitle ? (
                                <p className="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{stat.subtitle}</p>
                            ) : null}
                        </div>
                    </div>
                </Card>
            ))}
        </div>
    );
}
