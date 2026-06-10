import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { depotStatusBadgeColor } from './depotUi';

interface DepotRequestWorkflowStepperProps {
    steps: string[];
    currentRank: number;
    status: string;
}

export default function DepotRequestWorkflowStepper({
    steps,
    currentRank,
    status,
}: DepotRequestWorkflowStepperProps) {
    const { t } = useTranslation();
    const isTerminal = status === 'rejected' || status === 'cancelled';

    return (
        <div className="space-y-3">
            {isTerminal && (
                <Badge color={depotStatusBadgeColor(status)} className="w-fit text-sm">
                    {status}
                </Badge>
            )}
            <div className="overflow-x-auto pb-1">
                <ol className="flex min-w-max items-center gap-0">
                    {steps.map((step, index) => {
                        const isComplete = !isTerminal && index < currentRank;
                        const isCurrent = !isTerminal && index === currentRank;
                        const isUpcoming = !isTerminal && index > currentRank;

                        return (
                            <li key={step} className="flex items-center">
                                <div
                                    className={[
                                        'flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold transition-colors',
                                        isComplete && 'text-violet-700 dark:text-violet-300',
                                        isCurrent && 'bg-violet-600 text-white shadow-md dark:bg-violet-500',
                                        isUpcoming && 'text-gray-400 dark:text-gray-500',
                                        isTerminal && 'text-gray-400 dark:text-gray-500',
                                    ]
                                        .filter(Boolean)
                                        .join(' ')}
                                >
                                    <span
                                        className={[
                                            'flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[11px] font-bold',
                                            isComplete && 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-200',
                                            isCurrent && 'bg-white/20 text-inherit',
                                            (isUpcoming || isTerminal) && 'bg-gray-100 text-gray-400 dark:bg-gray-800',
                                        ]
                                            .filter(Boolean)
                                            .join(' ')}
                                    >
                                        {isComplete ? <i className="bx bx-check text-base" /> : index + 1}
                                    </span>
                                    <span>{t(`global.depot.request_status_${step}`)}</span>
                                </div>
                                {index < steps.length - 1 && (
                                    <span
                                        className={[
                                            'mx-1 hidden h-px w-6 sm:inline-block',
                                            isComplete ? 'bg-violet-400' : 'bg-gray-200 dark:bg-gray-700',
                                        ].join(' ')}
                                        aria-hidden
                                    />
                                )}
                            </li>
                        );
                    })}
                </ol>
            </div>
        </div>
    );
}
