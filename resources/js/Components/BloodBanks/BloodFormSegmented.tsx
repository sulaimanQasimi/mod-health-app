interface SegmentOption {
    value: string;
    label: string;
    icon?: string;
}

interface BloodFormSegmentedProps {
    value: string;
    options: SegmentOption[];
    onChange: (value: string) => void;
    columns?: 2 | 3 | 4;
    allowEmpty?: boolean;
}

export default function BloodFormSegmented({
    value,
    options,
    onChange,
    columns = 2,
    allowEmpty = false,
}: BloodFormSegmentedProps) {
    const gridCols =
        columns === 4 ? 'grid-cols-4' : columns === 3 ? 'grid-cols-3' : 'grid-cols-2';

    return (
        <div
            className={`grid ${gridCols} gap-1 rounded-xl border border-rose-100 bg-rose-50/50 p-1 dark:border-rose-900/30 dark:bg-rose-950/20`}
        >
            {options.map((option) => {
                const selected = value === option.value;

                return (
                    <button
                        key={option.value}
                        type="button"
                        onClick={() => {
                            if (allowEmpty && selected) {
                                onChange('');
                                return;
                            }
                            onChange(option.value);
                        }}
                        className={`flex items-center justify-center gap-1.5 rounded-lg px-2 py-2.5 text-sm font-medium transition-all ${
                            selected
                                ? 'bg-white text-rose-700 shadow-sm ring-1 ring-rose-200 dark:bg-gray-900 dark:text-rose-300 dark:ring-rose-800'
                                : 'text-gray-600 hover:bg-white/70 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800/60 dark:hover:text-gray-200'
                        }`}
                    >
                        {option.icon && <i className={`bx ${option.icon} text-lg`} />}
                        <span>{option.label}</span>
                    </button>
                );
            })}
        </div>
    );
}
