import { TOOTH_CONDITION_COLORS } from '../../lib/dentalChartOptions';
import { DentalChartEntry } from '../../types/dentistRegistration';

interface DentalToothButtonProps {
    toothNumber: number;
    entry: DentalChartEntry | null;
    isSelected: boolean;
    onClick: (toothNumber: number) => void;
}

export default function DentalToothButton({
    toothNumber,
    entry,
    isSelected,
    onClick,
}: DentalToothButtonProps) {
    const condition = entry?.tooth_condition ?? 'no_data';
    const backgroundColor = TOOTH_CONDITION_COLORS[condition] ?? TOOTH_CONDITION_COLORS.no_data;

    return (
        <button
            type="button"
            onClick={() => onClick(toothNumber)}
            title={entry ? `FDI ${toothNumber}: ${entry.tooth_condition}` : `FDI ${toothNumber}`}
            className="group inline-flex w-11 flex-col items-center gap-1 transition-transform hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
        >
            <span
                className="flex h-10 w-9 items-center justify-center rounded-full border-2 text-xs font-semibold text-white shadow-sm"
                style={{
                    backgroundColor,
                    borderColor: isSelected ? '#2563eb' : '#111827',
                }}
            >
                {toothNumber}
            </span>
            <span className="text-[10px] font-medium text-gray-500 dark:text-gray-400">{toothNumber}</span>
        </button>
    );
}
