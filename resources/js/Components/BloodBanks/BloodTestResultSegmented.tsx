import { BLOOD_UNIT_TEST_RESULT_OPTIONS } from './bloodBankUi';

interface BloodTestResultSegmentedProps {
    value: string;
    onChange: (value: string) => void;
    options?: readonly string[];
}

const RESULT_META: Record<
    string,
    { icon: string; selected: string; idle: string }
> = {
    pending: {
        icon: 'bx-time-five',
        selected:
            'bg-amber-100 text-amber-900 shadow-sm ring-1 ring-amber-300 dark:bg-amber-950/50 dark:text-amber-200 dark:ring-amber-700',
        idle: 'text-amber-700/70 hover:bg-amber-50 dark:text-amber-400/80 dark:hover:bg-amber-950/30',
    },
    negative: {
        icon: 'bx-check-circle',
        selected:
            'bg-emerald-100 text-emerald-900 shadow-sm ring-1 ring-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-200 dark:ring-emerald-700',
        idle: 'text-emerald-700/70 hover:bg-emerald-50 dark:text-emerald-400/80 dark:hover:bg-emerald-950/30',
    },
    positive: {
        icon: 'bx-x-circle',
        selected:
            'bg-red-100 text-red-900 shadow-sm ring-1 ring-red-300 dark:bg-red-950/50 dark:text-red-200 dark:ring-red-700',
        idle: 'text-red-700/70 hover:bg-red-50 dark:text-red-400/80 dark:hover:bg-red-950/30',
    },
    inconclusive: {
        icon: 'bx-help-circle',
        selected:
            'bg-sky-100 text-sky-900 shadow-sm ring-1 ring-sky-300 dark:bg-sky-950/50 dark:text-sky-200 dark:ring-sky-700',
        idle: 'text-sky-700/70 hover:bg-sky-50 dark:text-sky-400/80 dark:hover:bg-sky-950/30',
    },
};

function formatLabel(value: string): string {
    return value.charAt(0).toUpperCase() + value.slice(1);
}

export default function BloodTestResultSegmented({
    value,
    onChange,
    options = BLOOD_UNIT_TEST_RESULT_OPTIONS,
}: BloodTestResultSegmentedProps) {
    return (
        <div className="grid grid-cols-2 gap-1 rounded-xl border border-gray-200 bg-gray-50/80 p-1 dark:border-gray-700 dark:bg-gray-800/40">
            {options.map((option) => {
                const selected = value === option;
                const meta = RESULT_META[option] ?? RESULT_META.pending;

                return (
                    <button
                        key={option}
                        type="button"
                        onClick={() => onChange(option)}
                        className={`flex items-center justify-center gap-1 rounded-lg px-2 py-2 text-xs font-semibold transition-all sm:text-sm ${
                            selected ? meta.selected : `text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 ${meta.idle}`
                        }`}
                    >
                        <i className={`bx ${meta.icon} text-base`} />
                        <span className="capitalize">{formatLabel(option)}</span>
                    </button>
                );
            })}
        </div>
    );
}
