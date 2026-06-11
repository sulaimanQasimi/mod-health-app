export interface SegmentOption {
    value: string;
    label: string;
    icon?: string;
    tone?: 'rose' | 'amber' | 'emerald' | 'red' | 'sky' | 'gray';
}

interface BloodFormSegmentedProps {
    value: string;
    options: SegmentOption[];
    onChange: (value: string) => void;
    columns?: 2 | 3 | 4;
    allowEmpty?: boolean;
    size?: 'sm' | 'md';
    track?: 'rose' | 'neutral';
}

const GRID_COLS = {
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
} as const;

const TRACK_CLASS = {
    rose: 'border-rose-200/70 bg-gradient-to-b from-rose-50/90 to-rose-100/50 dark:border-rose-900/50 dark:from-rose-950/30 dark:to-rose-950/10',
    neutral: 'border-gray-200/80 bg-gradient-to-b from-gray-50 to-gray-100/70 dark:border-gray-700 dark:from-gray-800/80 dark:to-gray-900/50',
} as const;

const SELECTED_TONE_CLASS = {
    rose: 'bg-gradient-to-b from-rose-500 to-rose-600 text-white shadow-md shadow-rose-500/30 ring-1 ring-rose-400/40 dark:from-rose-600 dark:to-rose-700 dark:shadow-rose-900/40',
    amber: 'bg-gradient-to-b from-amber-500 to-amber-600 text-white shadow-md shadow-amber-500/25 ring-1 ring-amber-400/40',
    emerald: 'bg-gradient-to-b from-emerald-500 to-emerald-600 text-white shadow-md shadow-emerald-500/25 ring-1 ring-emerald-400/40',
    red: 'bg-gradient-to-b from-red-500 to-red-600 text-white shadow-md shadow-red-500/25 ring-1 ring-red-400/40',
    sky: 'bg-gradient-to-b from-sky-500 to-sky-600 text-white shadow-md shadow-sky-500/25 ring-1 ring-sky-400/40',
    gray: 'bg-gradient-to-b from-gray-500 to-gray-600 text-white shadow-md shadow-gray-500/20 ring-1 ring-gray-400/30',
} as const;

const IDLE_HINT_CLASS = {
    rose: 'hover:bg-white/70 hover:text-rose-800 dark:hover:bg-rose-950/40 dark:hover:text-rose-200',
    amber: 'hover:bg-amber-50/80 hover:text-amber-800 dark:hover:bg-amber-950/30 dark:hover:text-amber-200',
    emerald: 'hover:bg-emerald-50/80 hover:text-emerald-800 dark:hover:bg-emerald-950/30 dark:hover:text-emerald-200',
    red: 'hover:bg-red-50/80 hover:text-red-800 dark:hover:bg-red-950/30 dark:hover:text-red-200',
    sky: 'hover:bg-sky-50/80 hover:text-sky-800 dark:hover:bg-sky-950/30 dark:hover:text-sky-200',
    gray: 'hover:bg-white/60 hover:text-gray-900 dark:hover:bg-gray-800/60 dark:hover:text-gray-200',
} as const;

export default function BloodFormSegmented({
    value,
    options,
    onChange,
    columns = 2,
    allowEmpty = false,
    size = 'md',
    track = 'rose',
}: BloodFormSegmentedProps) {
    const isCompact = size === 'sm';
    const stacked = columns >= 3;

    return (
        <div
            className={`grid ${GRID_COLS[columns]} gap-1.5 rounded-2xl border p-1.5 shadow-inner ${TRACK_CLASS[track]}`}
            role="group"
        >
            {options.map((option) => {
                const selected = value === option.value;
                const tone = option.tone ?? 'rose';
                const selectedClass = SELECTED_TONE_CLASS[tone];
                const idleHint = IDLE_HINT_CLASS[tone];

                return (
                    <button
                        key={option.value}
                        type="button"
                        aria-pressed={selected}
                        onClick={() => {
                            if (allowEmpty && selected) {
                                onChange('');
                                return;
                            }
                            onChange(option.value);
                        }}
                        className={`relative flex items-center justify-center rounded-xl font-semibold transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/50 ${
                            isCompact ? 'gap-1 px-2.5 py-2.5 text-xs' : 'gap-1.5 px-3 py-3 text-sm'
                        } ${
                            stacked ? 'flex-col' : 'flex-row'
                        } ${
                            selected
                                ? selectedClass
                                : `text-gray-600 dark:text-gray-400 ${idleHint}`
                        }`}
                    >
                        {option.icon && (
                            <i
                                className={`bx ${option.icon} shrink-0 ${
                                    isCompact ? 'text-base' : stacked ? 'text-xl' : 'text-lg'
                                }`}
                            />
                        )}
                        <span className={stacked && !isCompact ? 'text-xs' : ''}>{option.label}</span>
                    </button>
                );
            })}
        </div>
    );
}
