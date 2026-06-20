import {
    Children,
    CSSProperties,
    ReactNode,
    isValidElement,
    useEffect,
    useId,
    useLayoutEffect,
    useMemo,
    useRef,
    useState,
} from 'react';
import { createPortal } from 'react-dom';
import { useTranslation } from '../../hooks/useTranslation';

export interface SearchableSelectOption {
    value: string;
    label: string;
    disabled?: boolean;
}

interface SearchableSelectProps {
    id?: string;
    value: string;
    onChange: (value: string) => void;
    options?: SearchableSelectOption[];
    children?: ReactNode;
    placeholder?: string;
    searchPlaceholder?: string;
    emptyMessage?: string;
    required?: boolean;
    disabled?: boolean;
    compact?: boolean;
    className?: string;
    dir?: 'ltr' | 'rtl';
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

export function optionsFromChildren(children: ReactNode): SearchableSelectOption[] {
    const options: SearchableSelectOption[] = [];

    Children.forEach(children, (child) => {
        if (!isValidElement<{ value?: string; disabled?: boolean; children?: ReactNode }>(child)) {
            return;
        }

        if (child.type === 'option') {
            options.push({
                value: String(child.props.value ?? ''),
                label: String(child.props.children ?? ''),
                disabled: child.props.disabled,
            });
        }
    });

    return options;
}

export default function SearchableSelect({
    id,
    value,
    onChange,
    options: optionsProp,
    children,
    placeholder,
    searchPlaceholder,
    emptyMessage,
    required,
    disabled,
    compact = false,
    className = '',
    dir: dirProp,
    open: openProp,
    onOpenChange,
}: SearchableSelectProps) {
    const { t, direction } = useTranslation();
    const dir = dirProp ?? direction;
    const isRtl = dir === 'rtl';
    const listboxId = useId();
    const containerRef = useRef<HTMLDivElement>(null);
    const triggerRef = useRef<HTMLButtonElement>(null);
    const dropdownRef = useRef<HTMLDivElement>(null);
    const searchRef = useRef<HTMLInputElement>(null);
    const [uncontrolledOpen, setUncontrolledOpen] = useState(false);
    const isOpen = openProp ?? uncontrolledOpen;

    const setOpen = (next: boolean) => {
        if (openProp === undefined) {
            setUncontrolledOpen(next);
        }
        onOpenChange?.(next);
    };
    const [searchQuery, setSearchQuery] = useState('');
    const [dropdownStyle, setDropdownStyle] = useState<CSSProperties>({});
    const [listMaxHeight, setListMaxHeight] = useState(224);

    const options = useMemo(
        () => optionsProp ?? optionsFromChildren(children),
        [optionsProp, children],
    );

    const selectedOption = options.find((option) => option.value === value);
    const displayLabel = selectedOption?.label || placeholder || t('global.select');

    const filteredOptions = useMemo(() => {
        const query = searchQuery.trim().toLowerCase();
        if (!query) {
            return options;
        }

        return options.filter((option) => option.label.toLowerCase().includes(query));
    }, [options, searchQuery]);

    useEffect(() => {
        if (disabled && isOpen) {
            setOpen(false);
            setSearchQuery('');
        }
    }, [disabled, isOpen]);

    useEffect(() => {
        if (!isOpen) {
            return;
        }

        const handleClickOutside = (event: MouseEvent) => {
            const target = event.target as Node;

            if (
                containerRef.current?.contains(target) ||
                dropdownRef.current?.contains(target)
            ) {
                return;
            }

            setOpen(false);
            setSearchQuery('');
        };

        const handleEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
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

    useLayoutEffect(() => {
        if (!isOpen || !triggerRef.current) {
            return;
        }

        const DROPDOWN_GAP = 4;
        const SEARCH_HEADER_HEIGHT = 52;
        const PREFERRED_LIST_HEIGHT = 224;

        const updatePosition = () => {
            if (!triggerRef.current) {
                return;
            }

            const rect = triggerRef.current.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const spaceBelow = viewportHeight - rect.bottom - DROPDOWN_GAP;
            const spaceAbove = rect.top - DROPDOWN_GAP;
            const preferredTotalHeight = SEARCH_HEADER_HEIGHT + PREFERRED_LIST_HEIGHT;
            const measuredHeight =
                dropdownRef.current?.offsetHeight ?? preferredTotalHeight;
            const openAbove =
                spaceBelow < measuredHeight && spaceAbove > spaceBelow;
            const availableSpace = openAbove ? spaceAbove : spaceBelow;
            const totalHeight = Math.min(measuredHeight, availableSpace);
            const nextListMaxHeight = Math.max(80, totalHeight - SEARCH_HEADER_HEIGHT);

            setListMaxHeight(nextListMaxHeight);
            setDropdownStyle({
                position: 'fixed',
                top: openAbove
                    ? rect.top - DROPDOWN_GAP - totalHeight
                    : rect.bottom + DROPDOWN_GAP,
                left: rect.left,
                width: rect.width,
                zIndex: 9999,
            });
        };

        updatePosition();
        searchRef.current?.focus({ preventScroll: true });
        window.addEventListener('scroll', updatePosition, true);
        window.addEventListener('resize', updatePosition);

        let resizeObserver: ResizeObserver | undefined;
        const rafId = requestAnimationFrame(() => {
            updatePosition();
            if (!dropdownRef.current) {
                return;
            }

            resizeObserver = new ResizeObserver(updatePosition);
            resizeObserver.observe(dropdownRef.current);
        });

        return () => {
            cancelAnimationFrame(rafId);
            window.removeEventListener('scroll', updatePosition, true);
            window.removeEventListener('resize', updatePosition);
            resizeObserver?.disconnect();
        };
    }, [isOpen, filteredOptions.length]);

    const handleSelect = (optionValue: string) => {
        onChange(optionValue);
        setOpen(false);
        setSearchQuery('');
        triggerRef.current?.focus({ preventScroll: true });
    };

    const toggleOpen = () => {
        if (disabled) {
            return;
        }
        if (isOpen) {
            setSearchQuery('');
        }
        setOpen(!isOpen);
    };

    const triggerPadding = compact ? 'px-3 py-2' : 'px-3.5 py-2.5';
    const textSize = compact ? 'text-sm' : 'text-sm';

    return (
        <div ref={containerRef} className={mergeClasses('relative w-full', className)} dir={dir}>
            <button
                ref={triggerRef}
                id={id}
                type="button"
                disabled={disabled}
                aria-haspopup="listbox"
                aria-expanded={isOpen}
                aria-controls={listboxId}
                onClick={toggleOpen}
                className={mergeClasses(
                    'flex w-full items-center justify-between rounded-lg border bg-gray-50 text-gray-900 transition-colors',
                    'border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20',
                    'dark:border-gray-600 dark:bg-gray-700 dark:text-white',
                    disabled && 'cursor-not-allowed opacity-60',
                    triggerPadding,
                    textSize,
                    isRtl ? 'text-right' : 'text-left',
                )}
            >
                <span
                    className={mergeClasses(
                        'truncate',
                        !selectedOption && 'text-gray-500 dark:text-gray-400',
                    )}
                >
                    {displayLabel}
                </span>
                <i
                    className={mergeClasses(
                        'bx bx-chevron-down shrink-0 text-lg text-gray-400 transition-transform',
                        isOpen && 'rotate-180',
                        isRtl ? 'me-2' : 'ms-2',
                    )}
                />
            </button>

            {/* Hidden native select for HTML5 form validation */}
            <select
                tabIndex={-1}
                aria-hidden
                required={required}
                value={value}
                onChange={() => undefined}
                className="pointer-events-none absolute h-0 w-0 opacity-0"
            >
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>

            {isOpen &&
                createPortal(
                    <div
                        ref={dropdownRef}
                        style={dropdownStyle}
                        dir={dir}
                        className={mergeClasses(
                            'overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg',
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
                            style={{ maxHeight: listMaxHeight }}
                            className={mergeClasses(
                                'overflow-y-auto py-1',
                                isRtl ? 'text-right' : 'text-left',
                            )}
                        >
                            {filteredOptions.length === 0 ? (
                                <li className="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    {emptyMessage ?? t('global.no_results_found')}
                                </li>
                            ) : (
                                filteredOptions.map((option) => {
                                    const isSelected = option.value === value;

                                    return (
                                        <li
                                            key={`${option.value}-${option.label}`}
                                            role="option"
                                            aria-selected={isSelected}
                                        >
                                            <button
                                                type="button"
                                                disabled={option.disabled}
                                                onClick={() => handleSelect(option.value)}
                                                className={mergeClasses(
                                                    'flex w-full items-center gap-2 px-3 py-2 text-sm transition-colors',
                                                    isRtl ? 'flex-row-reverse text-right' : 'text-left',
                                                    isSelected
                                                        ? 'bg-blue-50 font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                                        : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700/60',
                                                    option.disabled && 'cursor-not-allowed opacity-50',
                                                )}
                                            >
                                                {isSelected && (
                                                    <i className="bx bx-check shrink-0 text-base text-blue-600 dark:text-blue-400" />
                                                )}
                                                <span className="truncate">{option.label}</span>
                                            </button>
                                        </li>
                                    );
                                })
                            )}
                        </ul>
                    </div>,
                    document.body,
                )}
        </div>
    );
}
