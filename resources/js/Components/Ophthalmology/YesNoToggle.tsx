type Props = {
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
    yesLabel?: string;
    noLabel?: string;
};

export default function YesNoToggle({
    value,
    onChange,
    disabled = false,
    yesLabel = 'بلی',
    noLabel = 'نخیر',
}: Props) {
    return (
        <div className="inline-flex shrink-0 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-600">
            <button
                type="button"
                disabled={disabled}
                onClick={() => onChange(value === 'yes' ? '' : 'yes')}
                className={`px-3 py-1.5 text-xs font-semibold transition ${
                    value === 'yes'
                        ? 'bg-emerald-500 text-white'
                        : 'bg-white text-gray-600 hover:bg-emerald-50 dark:bg-gray-800 dark:text-gray-300'
                } disabled:cursor-not-allowed disabled:opacity-50`}
                title={yesLabel}
            >
                <i className="bx bx-check me-1" />
                {yesLabel}
            </button>
            <button
                type="button"
                disabled={disabled}
                onClick={() => onChange(value === 'no' ? '' : 'no')}
                className={`border-s border-gray-200 px-3 py-1.5 text-xs font-semibold transition dark:border-gray-600 ${
                    value === 'no'
                        ? 'bg-rose-500 text-white'
                        : 'bg-white text-gray-600 hover:bg-rose-50 dark:bg-gray-800 dark:text-gray-300'
                } disabled:cursor-not-allowed disabled:opacity-50`}
                title={noLabel}
            >
                <i className="bx bx-x me-1" />
                {noLabel}
            </button>
        </div>
    );
}
