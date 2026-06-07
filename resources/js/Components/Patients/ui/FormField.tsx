import { Label, Select, TextInput } from 'flowbite-react';
import { ReactNode } from 'react';

interface FormFieldProps {
    label: string;
    icon?: string;
    required?: boolean;
    error?: string;
    hint?: string;
    className?: string;
    compact?: boolean;
    children?: ReactNode;
}

export function FormField({
    label,
    icon,
    required,
    error,
    hint,
    className = '',
    compact = false,
    children,
}: FormFieldProps) {
    return (
        <div className={`${compact ? 'space-y-1' : 'space-y-1.5'} ${className}`}>
            <Label
                className={`flex items-center gap-1 font-medium text-gray-700 dark:text-gray-300 ${
                    compact ? 'text-xs' : 'text-sm'
                }`}
            >
                {icon && <i className={`bx ${icon} text-gray-400 ${compact ? 'text-sm' : 'text-base'}`} />}
                {label}
                {required && <span className="text-red-500">*</span>}
            </Label>
            {children}
            {hint && !error && (
                <p className={`text-gray-500 dark:text-gray-400 ${compact ? 'text-[10px]' : 'text-xs'}`}>{hint}</p>
            )}
            {error && (
                <p className={`text-red-600 dark:text-red-400 ${compact ? 'text-[10px]' : 'text-xs'}`}>{error}</p>
            )}
        </div>
    );
}

interface IconTextInputProps {
    id: string;
    icon: string;
    value: string;
    onChange: (value: string) => void;
    type?: string;
    required?: boolean;
    readOnly?: boolean;
    placeholder?: string;
    min?: number;
    max?: number;
    compact?: boolean;
}

export function IconTextInput({
    id,
    icon,
    value,
    onChange,
    type = 'text',
    required,
    readOnly,
    placeholder,
    min,
    max,
    compact = false,
}: IconTextInputProps) {
    return (
        <div className="relative">
            <div
                className={`pointer-events-none absolute inset-y-0 start-0 flex items-center ${
                    compact ? 'ps-2' : 'ps-3.5'
                }`}
            >
                <i className={`bx ${icon} text-gray-400 ${compact ? 'text-sm' : 'text-lg'}`} />
            </div>
            <TextInput
                id={id}
                type={type}
                sizing={compact ? 'sm' : undefined}
                required={required}
                readOnly={readOnly}
                placeholder={placeholder}
                min={min}
                max={max}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className={compact ? 'ps-7 text-sm' : 'ps-10'}
            />
        </div>
    );
}

interface IconSelectProps {
    id: string;
    icon: string;
    value: string;
    onChange: (value: string) => void;
    required?: boolean;
    disabled?: boolean;
    compact?: boolean;
    children: ReactNode;
}

export function IconSelect({
    id,
    icon,
    value,
    onChange,
    required,
    disabled,
    compact = false,
    children,
}: IconSelectProps) {
    return (
        <div className="relative">
            <div
                className={`pointer-events-none absolute inset-y-0 start-0 z-10 flex items-center ${
                    compact ? 'ps-2' : 'ps-3.5'
                }`}
            >
                <i className={`bx ${icon} text-gray-400 ${compact ? 'text-sm' : 'text-lg'}`} />
            </div>
            <Select
                id={id}
                sizing={compact ? 'sm' : undefined}
                required={required}
                disabled={disabled}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className={compact ? 'ps-7 text-sm' : 'ps-10'}
            >
                {children}
            </Select>
        </div>
    );
}

export function GridDivider({ icon, title }: { icon: string; title: string }) {
    return (
        <div className="col-span-full mt-1 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-700">
            <i className={`bx ${icon} text-sm text-blue-500`} />
            <span className="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {title}
            </span>
        </div>
    );
}
