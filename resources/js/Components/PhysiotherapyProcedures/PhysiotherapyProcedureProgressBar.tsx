interface PhysiotherapyProcedureProgressBarProps {
    counter: number;
    total: number;
    percentage?: number;
    compact?: boolean;
}

export default function PhysiotherapyProcedureProgressBar({
    counter,
    total,
    percentage,
    compact = false,
}: PhysiotherapyProcedureProgressBarProps) {
    const resolvedPercentage = percentage ?? (total > 0 ? (counter / total) * 100 : 0);

    return (
        <div className={compact ? 'min-w-[110px]' : 'min-w-[140px]'}>
            <div className="mb-1 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>
                    {counter}/{total}
                </span>
                <span>{resolvedPercentage.toFixed(1)}%</span>
            </div>
            <div className={`overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700 ${compact ? 'h-1.5' : 'h-2'}`}>
                <div
                    className="h-full rounded-full bg-gradient-to-r from-cyan-500 to-teal-500 transition-all"
                    style={{ width: `${Math.min(100, resolvedPercentage)}%` }}
                />
            </div>
        </div>
    );
}
