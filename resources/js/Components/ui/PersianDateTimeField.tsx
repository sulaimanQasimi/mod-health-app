import { TextInput } from 'flowbite-react';
import PersianDateInput from './PersianDateInput';

interface PersianDateTimeFieldProps {
    dateValue: string;
    timeValue: string;
    onDateChange: (value: string) => void;
    onTimeChange: (value: string) => void;
    dateRequired?: boolean;
    timeHint?: string;
}

export default function PersianDateTimeField({
    dateValue,
    timeValue,
    onDateChange,
    onTimeChange,
    dateRequired = false,
    timeHint,
}: PersianDateTimeFieldProps) {
    return (
        <div className="grid gap-3 sm:grid-cols-2">
            <PersianDateInput value={dateValue} onChange={onDateChange} required={dateRequired} />
            <div>
                <TextInput type="time" value={timeValue} onChange={(e) => onTimeChange(e.target.value)} step={60} />
                {timeHint && <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">{timeHint}</p>}
            </div>
        </div>
    );
}
