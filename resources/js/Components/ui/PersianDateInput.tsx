import { InputHTMLAttributes, useEffect, useRef } from 'react';
import {
    $,
    DARI_MONTHS,
    DARI_WEEKDAYS,
    DARI_WEEKDAYS_SHORT,
    ensurePersianDatepickerLoaded,
} from '../../lib/persianDatepickerSetup';

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

            $input.persianDatepicker({
                months: DARI_MONTHS,
                dowTitle: DARI_WEEKDAYS,
                shortDowTitle: DARI_WEEKDAYS_SHORT,
                formatDate: 'YYYY/MM/DD',
                showGregorianDate: false,
                persianNumbers: true,
                theme: 'default',
                closeOnBlur: true,
                onSelect: () => notifyChange?.(),
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

            const instance = $input.data('persianDatepicker');
            instance?.calendar?.remove();
            $input.removeData('persianDatepicker');
            $input.off('click focus blur');
            $input.removeClass('pdp-el rtl');
            $input.removeAttr('pdp-id');
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
            onBlur={(event) => onChange(event.target.value)}
            className={className ? `${inputClassName} ${className}` : inputClassName}
        />
    );
}
