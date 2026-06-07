import { TextInput } from 'flowbite-react';
import { useTranslation } from '../../../hooks/useTranslation';

interface AgeInputProps {
    year: string;
    month: string;
    day: string;
    onChange: (field: 'age_year' | 'age_month' | 'age_day', value: string) => void;
    error?: string;
    preview?: string;
    compact?: boolean;
}

export default function AgeInput({ year, month, day, onChange, error, preview, compact = false }: AgeInputProps) {
    const { t } = useTranslation();

    return (
        <div className="space-y-1">
            <div className="grid grid-cols-3 gap-1.5">
                {[
                    { field: 'age_year' as const, label: t('global.year'), max: 150 },
                    { field: 'age_month' as const, label: t('global.month'), max: 11 },
                    { field: 'age_day' as const, label: t('global.day'), max: 31 },
                ].map((item) => (
                    <TextInput
                        key={item.field}
                        type="number"
                        sizing="sm"
                        min={0}
                        max={item.max}
                        placeholder={item.label}
                        value={item.field === 'age_year' ? year : item.field === 'age_month' ? month : day}
                        onChange={(event) => onChange(item.field, event.target.value)}
                        className="text-center text-sm"
                    />
                ))}
            </div>
            {preview && (
                <p className={`text-blue-600 dark:text-blue-400 ${compact ? 'text-[10px]' : 'text-xs'}`}>
                    <i className="bx bx-info-circle me-1" />
                    {preview}
                </p>
            )}
            {error && <p className="text-[10px] text-red-600 dark:text-red-400">{error}</p>}
        </div>
    );
}
