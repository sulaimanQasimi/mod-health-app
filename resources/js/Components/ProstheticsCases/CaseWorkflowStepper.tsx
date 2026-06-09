import { useTranslation } from '../../hooks/useTranslation';

interface CaseWorkflowStepperProps {
    steps: string[];
    currentRank: number;
}

export default function CaseWorkflowStepper({ steps, currentRank }: CaseWorkflowStepperProps) {
    const { t } = useTranslation();

    return (
        <div className="overflow-x-auto pb-1">
            <ol className="flex min-w-max items-center gap-0">
                {steps.map((step, index) => {
                    const isComplete = index < currentRank;
                    const isCurrent = index === currentRank;
                    const isUpcoming = index > currentRank;

                    return (
                        <li key={step} className="flex items-center">
                            <div
                                className={[
                                    'flex items-center gap-2 rounded-md px-2.5 py-1.5 text-xs font-medium transition-colors',
                                    isComplete && 'text-slate-700 dark:text-slate-300',
                                    isCurrent && 'bg-slate-800 text-white shadow-sm dark:bg-slate-200 dark:text-slate-900',
                                    isUpcoming && 'text-gray-400 dark:text-gray-500',
                                ]
                                    .filter(Boolean)
                                    .join(' ')}
                            >
                                <span
                                    className={[
                                        'flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-semibold',
                                        isComplete && 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                                        isCurrent && 'bg-white/20 text-inherit dark:bg-slate-900/20',
                                        isUpcoming && 'bg-gray-100 text-gray-400 dark:bg-gray-800',
                                    ]
                                        .filter(Boolean)
                                        .join(' ')}
                                >
                                    {isComplete ? <i className="bx bx-check text-sm" /> : index + 1}
                                </span>
                                <span className="max-w-[8rem] truncate sm:max-w-none">
                                    {t(`global.prosthetics_case_status_${step}`)}
                                </span>
                            </div>
                            {index < steps.length - 1 && (
                                <span
                                    className={[
                                        'mx-1 hidden h-px w-4 sm:inline-block',
                                        index < currentRank ? 'bg-slate-400' : 'bg-gray-200 dark:bg-gray-700',
                                    ].join(' ')}
                                    aria-hidden
                                />
                            )}
                        </li>
                    );
                })}
            </ol>
        </div>
    );
}
