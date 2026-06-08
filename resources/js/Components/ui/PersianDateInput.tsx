import { InputHTMLAttributes, useEffect, useRef } from 'react';
import { $, ensurePersianDatepickerLoaded } from '../../lib/persianDatepickerSetup';
import type { PersianDatepickerInstance } from '../../types/jquery-persian-datepicker';

interface PersianDateInputProps
    extends Omit<InputHTMLAttributes<HTMLInputElement>, 'value' | 'onChange' | 'type'> {
    value: string;
    onChange: (value: string) => void;
}

const inputClassName =
    'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500';

export default function PersianDateInput({
    value,
    onChange,
    id,
    placeholder,
    required,
    disabled,
    className,
    ...rest
}: PersianDateInputProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const onChangeRef = useRef(onChange);
    onChangeRef.current = onChange;

    useEffect(() => {
        const input = inputRef.current;
        if (!input) {
            return;
        }

        let picker: PersianDatepickerInstance | null = null;
        let notifyChange: (() => void) | null = null;
        let cancelled = false;

        const init = async () => {
            await ensurePersianDatepickerLoaded();

            if (cancelled || !inputRef.current) {
                return;
            }

            const $input = $(inputRef.current);

            notifyChange = () => {
                onChangeRef.current(String($input.val() ?? ''));
            };

            picker = $input.persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: false,
                observer: true,
                calendar: {
                    persian: {
                        locale: 'fa',
                        showHint: true,
                        leapYearMode: 'algorithmic',
                    },
                    gregorian: {
                        enabled: false,
                    },
                },
                dayPicker: {
                    onSelect: () => notifyChange?.(),
                },
                toolbox: {
                    todayButton: {
                        onToday: () => notifyChange?.(),
                    },
                },
            });

            $input.on('change', notifyChange);

            if (value) {
                $input.val(value);
            }
        };

        init().catch(() => undefined);

        return () => {
            cancelled = true;

            if (!inputRef.current) {
                return;
            }

            const $input = $(inputRef.current);
            if (notifyChange) {
                $input.off('change', notifyChange);
            }
            picker?.destroy();
        };
    }, []);

    useEffect(() => {
        const input = inputRef.current;
        if (!input || typeof $.fn.persianDatepicker !== 'function') {
            return;
        }

        const $input = $(input);
        const current = String($input.val() ?? '');
        if (current !== value) {
            $input.val(value);
        }
    }, [value]);

    return (
        <input
            {...rest}
            ref={inputRef}
            id={id}
            type="text"
            autoComplete="off"
            dir="rtl"
            placeholder={placeholder}
            required={required}
            disabled={disabled}
            className={className ? `${inputClassName} ${className}` : inputClassName}
        />
    );
}
