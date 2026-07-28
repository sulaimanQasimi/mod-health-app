interface ReportEmptyStateProps {
    message: string;
    icon?: string;
}

export default function ReportEmptyState({
    message,
    icon = 'bx-search-alt',
}: ReportEmptyStateProps) {
    return (
        <div className="flex flex-col items-center gap-3 py-16 text-center text-gray-500 dark:text-gray-400">
            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-cyan-50 dark:bg-cyan-950/30">
                <i className={`bx ${icon} text-2xl text-cyan-500`} />
            </div>
            <p className="text-sm">{message}</p>
        </div>
    );
}
