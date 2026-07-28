import { Card } from 'flowbite-react';
import { ReactNode } from 'react';
import ReportEmptyState from './ReportEmptyState';

interface ReportResultsCardProps {
    title: string;
    hasSearch: boolean;
    resultCount?: number;
    resultsLabel?: string;
    emptyMessage: string;
    children: ReactNode;
}

export default function ReportResultsCard({
    title,
    hasSearch,
    resultCount,
    resultsLabel,
    emptyMessage,
    children,
}: ReportResultsCardProps) {
    return (
        <Card className="!shadow-sm">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i className="bx bx-table text-cyan-500" />
                    {title}
                </h2>
                {hasSearch && resultCount !== undefined ? (
                    <span className="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {resultCount}
                        {resultsLabel ? ` ${resultsLabel}` : ''}
                    </span>
                ) : null}
            </div>

            {!hasSearch ? <ReportEmptyState message={emptyMessage} /> : children}
        </Card>
    );
}
