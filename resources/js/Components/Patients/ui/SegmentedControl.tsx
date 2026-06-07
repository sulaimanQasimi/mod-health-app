interface SegmentOption {
    value: string;
    label: string;
    icon?: string;
}

interface SegmentedControlProps {
    value: string;
    options: SegmentOption[];
    onChange: (value: string) => void;
    compact?: boolean;
}

export default function SegmentedControl({ value, options, onChange, compact = false }: SegmentedControlProps) {
    return (
        <div
            className={`inline-flex w-full rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 ${
                compact ? 'p-0.5' : 'p-1'
            }`}
        >
            {options.map((option) => {
                const selected = value === option.value;

                return (
                    <button
                        key={option.value}
                        type="button"
                        onClick={() => onChange(option.value)}
                        className={`flex flex-1 items-center justify-center gap-1 rounded-md font-medium transition-all ${
                            compact ? 'px-2 py-1.5 text-xs' : 'px-3 py-2.5 text-sm'
                        } ${
                            selected
                                ? 'bg-white text-blue-700 shadow-sm dark:bg-gray-800 dark:text-blue-300'
                                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
                        }`}
                    >
                        {option.icon && <i className={`bx ${option.icon} ${compact ? 'text-sm' : 'text-lg'}`} />}
                        {option.label}
                    </button>
                );
            })}
        </div>
    );
}
