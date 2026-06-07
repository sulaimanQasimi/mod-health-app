import { type MouseEvent, useEffect, useId, useMemo, useRef, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import type { SearchableSelectOption } from './SearchableSelect';

interface SearchableMultiSelectProps {
    id?: string;
    values: string[];
    onChange: (values: string[]) => void;
    options: SearchableSelectOption[];
    placeholder?: string;
    searchPlaceholder?: string;
    emptyMessage?: string;
    disabled?: boolean;
    className?: string;
    dir?: 'ltr' | 'rtl';
}

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

export default function SearchableMultiSelect({
    id,
    values,
    onChange,
    options,
    placeholder,
    searchPlaceholder,
    emptyMessage,
    disabled,
    className = '',
    dir: dirProp,
}: SearchableMultiSelectProps) {
    const { t, direction } = useTranslation();
    const dir = dirProp ?? direction;
    const isRtl = dir === 'rtl';
    const listboxId = useId();
    const containerRef = useRef<HTMLDivElement>(null);
    const searchRef = useRef<HTMLInputElement>(null);
    const [isOpen, setIsOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');

    const selectedOptions = useMemo(
        () => options.filter((option) => values.includes(option.value)),
        [options, values],
    );

    const filteredOptions = useMemo(() => {
        const query = searchQuery.trim().toLowerCase();
        if (!query) {
            return options;
        }
        return options.filter((option) => option.label.toLowerCase().includes(query));
    }, [options, searchQuery]);

    useEffect(() => {
        if (!isOpen) {
            return;
        }

        const handleClickOutside = (event: MouseEvent) => {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                setIsOpen(false);
                setSearchQuery('');
            }
        };

        const handleEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setIsOpen(false);
                setSearchQuery('');
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        document.addEventListener('keydown', handleEscape);

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
            document.removeEventListener('keydown', handleEscape);
        };
    }, [isOpen]);

    useEffect(() => {
        if (isOpen) {
            searchRef.current?.focus();
        }
    }, [isOpen]);

    const toggleValue = (optionValue: string) => {
        if (values.includes(optionValue)) {
            onChange(values.filter((value) => value !== optionValue));
            return;
        }
        onChange([...values, optionValue]);
    };

    const removeValue = (optionValue: string, event: MouseEvent) => {
        event.stopPropagation();
        onChange(values.filter((value) => value !== optionValue));
    };

    const toggleOpen = () => {
        if (disabled) {
            return;
        }
        setIsOpen((current) => !current);
        if (isOpen) {
            setSearchQuery('');
        }
    };

    return (
        <div ref={containerRef} className={mergeClasses('relative w-full', className)} dir={dir}>
            <button
                id={id}
                type="button"
                disabled={disabled}
                aria-haspopup="listbox"
                aria-expanded={isOpen}
                aria-controls={listboxId}
                onClick={toggleOpen}
                className={mergeClasses(
                    'flex min-h-[42px] w-full items-center justify-between gap-2 rounded-lg border bg-gray-50 px-3 py-2 text-sm text-gray-900 transition-colors',
                    'border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20',
                    'dark:border-gray-600 dark:bg-gray-700 dark:text-white',
                    disabled && 'cursor-not-allowed opacity-60',
                    isRtl ? 'text-right' : 'text-left',
                )}
            >
                <span className="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
                    {selectedOptions.length > 0 ? (
                        selectedOptions.map((option) => (
                            <span
                                key={option.value}
                                className="inline-flex max-w-full items-center gap-1 rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-200"
                            >
                                <span className="truncate">{option.label}</span>
                                <button
                                    type="button"
                                    onClick={(event) => removeValue(option.value, event)}
                                    className="rounded hover:bg-blue-200/80 dark:hover:bg-blue-800/60"
                                    aria-label={t('global.delete')}
                                >
                                    <i className="bx bx-x text-sm" />
                                </button>
                            </span>
                        ))
                    ) : (
                        <span className="text-gray-500 dark:text-gray-400">
                            {placeholder ?? t('global.select')}
                        </span>
                    )}
                </span>
                <i
                    className={mergeClasses(
                        'bx bx-chevron-down shrink-0 text-lg text-gray-400 transition-transform',
                        isOpen && 'rotate-180',
                    )}
                />
            </button>

            {isOpen && (
                <div
                    className={mergeClasses(
                        'absolute z-[60] mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg',
                        'dark:border-gray-600 dark:bg-gray-800',
                    )}
                >
                    <div className="border-b border-gray-100 p-2 dark:border-gray-700">
                        <div className="relative">
                            <i
                                className={mergeClasses(
                                    'bx bx-search pointer-events-none absolute top-1/2 -translate-y-1/2 text-gray-400',
                                    isRtl ? 'end-3' : 'start-3',
                                )}
                            />
                            <input
                                ref={searchRef}
                                type="text"
                                value={searchQuery}
                                onChange={(event) => setSearchQuery(event.target.value)}
                                placeholder={searchPlaceholder ?? t('global.search')}
                                dir={dir}
                                className={mergeClasses(
                                    'w-full rounded-md border border-gray-200 bg-gray-50 py-2 text-sm text-gray-900',
                                    'focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500',
                                    'dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
                                    isRtl ? 'pe-9 ps-3 text-right' : 'ps-9 pe-3 text-left',
                                )}
                            />
                        </div>
                    </div>

                    <ul
                        id={listboxId}
                        role="listbox"
                        aria-multiselectable="true"
                        className={mergeClasses(
                            'max-h-56 overflow-y-auto py-1',
                            isRtl ? 'text-right' : 'text-left',
                        )}
                    >
                        {filteredOptions.length === 0 ? (
                            <li className="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                {emptyMessage ?? t('global.no_results_found')}
                            </li>
                        ) : (
                            filteredOptions.map((option) => {
                                const isSelected = values.includes(option.value);

                                return (
                                    <li
                                        key={`${option.value}-${option.label}`}
                                        role="option"
                                        aria-selected={isSelected}
                                    >
                                        <button
                                            type="button"
                                            disabled={option.disabled}
                                            onClick={() => toggleValue(option.value)}
                                            className={mergeClasses(
                                                'flex w-full items-center gap-2 px-3 py-2 text-sm transition-colors',
                                                isRtl ? 'flex-row-reverse text-right' : 'text-left',
                                                isSelected
                                                    ? 'bg-blue-50 font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                                    : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700/60',
                                                option.disabled && 'cursor-not-allowed opacity-50',
                                            )}
                                        >
                                            <span
                                                className={mergeClasses(
                                                    'flex h-4 w-4 shrink-0 items-center justify-center rounded border',
                                                    isSelected
                                                        ? 'border-blue-600 bg-blue-600 text-white dark:border-blue-400 dark:bg-blue-500'
                                                        : 'border-gray-300 bg-white dark:border-gray-500 dark:bg-gray-700',
                                                )}
                                            >
                                                {isSelected && <i className="bx bx-check text-xs" />}
                                            </span>
                                            <span className="truncate">{option.label}</span>
                                        </button>
                                    </li>
                                );
                            })
                        )}
                    </ul>
                </div>
            )}
        </div>
    );
}
