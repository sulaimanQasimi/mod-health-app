import {
    HTMLAttributes,
    TableHTMLAttributes,
    TdHTMLAttributes,
    ThHTMLAttributes,
} from 'react';

type TableAlign = 'left' | 'center' | 'right';
type TableRowVariant = 'body' | 'header' | 'footer';
type SortDirection = 'asc' | 'desc' | null;

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

const alignClasses: Record<TableAlign, string> = {
    left: 'text-left',
    center: 'text-center',
    right: 'text-right',
};

const rowVariantClasses: Record<TableRowVariant, string> = {
    header: 'border-0 bg-transparent hover:bg-transparent dark:hover:bg-transparent',
    body: 'border-b border-gray-100 transition-colors duration-150 last:border-b-0 hover:bg-gray-50/80 dark:border-gray-800 dark:hover:bg-gray-800/40',
    footer: 'border-t border-gray-200 bg-gray-50 font-medium dark:border-gray-700 dark:bg-gray-800/60',
};

interface TableProps extends TableHTMLAttributes<HTMLTableElement> {
    embedded?: boolean;
}

export function Table({ className = '', embedded = false, children, ...props }: TableProps) {
    const table = (
        <table
            className={mergeClasses('w-full min-w-[960px] border-collapse text-sm', className)}
            {...props}
        >
            {children}
        </table>
    );

    if (embedded) {
        return <div className="min-w-0 w-full overflow-x-auto">{table}</div>;
    }

    return (
        <div className="min-w-0 w-full overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div className="overflow-x-auto">{table}</div>
        </div>
    );
}

export function TableCaption({
    className = '',
    children,
    ...props
}: HTMLAttributes<HTMLTableCaptionElement>) {
    return (
        <caption
            className={mergeClasses(
                'border-b border-gray-100 px-4 py-3 text-start text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400',
                className,
            )}
            {...props}
        >
            {children}
        </caption>
    );
}

export function TableHead({ className = '', children, ...props }: HTMLAttributes<HTMLTableSectionElement>) {
    return (
        <thead
            className={mergeClasses(
                'border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/80',
                className,
            )}
            {...props}
        >
            {children}
        </thead>
    );
}

export function TableBody({ className = '', children, ...props }: HTMLAttributes<HTMLTableSectionElement>) {
    return <tbody className={mergeClasses('', className)} {...props}>{children}</tbody>;
}

export function TableFooter({ className = '', children, ...props }: HTMLAttributes<HTMLTableSectionElement>) {
    return (
        <tfoot
            className={mergeClasses(
                'border-t border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/70',
                className,
            )}
            {...props}
        >
            {children}
        </tfoot>
    );
}

interface TableRowProps extends HTMLAttributes<HTMLTableRowElement> {
    variant?: TableRowVariant;
    selected?: boolean;
}

export function TableRow({
    className = '',
    variant = 'body',
    selected = false,
    children,
    ...props
}: TableRowProps) {
    return (
        <tr
            data-selected={selected || undefined}
            className={mergeClasses(
                'group',
                rowVariantClasses[variant],
                selected && 'bg-blue-50/70 dark:bg-blue-900/20',
                className,
            )}
            {...props}
        >
            {children}
        </tr>
    );
}

interface TableHeaderProps extends ThHTMLAttributes<HTMLTableCellElement> {
    align?: TableAlign;
    sortable?: boolean;
}

export function TableHeader({
    className = '',
    align = 'right',
    sortable = false,
    children,
    ...props
}: TableHeaderProps) {
    return (
        <th
            scope="col"
            className={mergeClasses(
                'whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400',
                alignClasses[align],
                sortable && 'select-none',
                className,
            )}
            {...props}
        >
            {children}
        </th>
    );
}

interface TableCellProps extends TdHTMLAttributes<HTMLTableCellElement> {
    align?: TableAlign;
    muted?: boolean;
}

export function TableCell({
    className = '',
    align = 'right',
    muted = false,
    children,
    ...props
}: TableCellProps) {
    return (
        <td
            className={mergeClasses(
                'px-4 py-3 align-middle',
                alignClasses[align],
                muted ? 'text-gray-500 dark:text-gray-400' : 'text-gray-700 dark:text-gray-300',
                className,
            )}
            {...props}
        >
            {children}
        </td>
    );
}

interface TableSortIconProps {
    direction?: SortDirection;
    className?: string;
}

export function TableSortIcon({ direction = null, className = '' }: TableSortIconProps) {
    const activeClass = direction ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500';

    return (
        <span
            className={mergeClasses(
                'ms-1.5 inline-flex h-4 w-4 shrink-0 flex-col items-center justify-center transition-colors duration-150',
                activeClass,
                className,
            )}
            aria-hidden="true"
        >
            <svg
                className={mergeClasses(
                    'h-2 w-2 -mb-px transition-opacity duration-150',
                    direction === 'desc' ? 'opacity-30' : direction === 'asc' ? 'opacity-100' : 'opacity-60',
                )}
                viewBox="0 0 8 5"
                fill="currentColor"
            >
                <path d="M4 0 8 5H0L4 0Z" />
            </svg>
            <svg
                className={mergeClasses(
                    'h-2 w-2 -mt-px transition-opacity duration-150',
                    direction === 'asc' ? 'opacity-30' : direction === 'desc' ? 'opacity-100' : 'opacity-60',
                )}
                viewBox="0 0 8 5"
                fill="currentColor"
            >
                <path d="M4 5 0 0h8L4 5Z" />
            </svg>
        </span>
    );
}

interface TableEmptyProps {
    colSpan: number;
    icon?: string;
    title?: string;
    description?: string;
    className?: string;
}

export function TableEmpty({
    colSpan,
    icon = 'bx-search-alt',
    title,
    description,
    className = '',
}: TableEmptyProps) {
    return (
        <TableRow variant="body" className="hover:bg-transparent dark:hover:bg-transparent">
            <TableCell colSpan={colSpan} align="center" className={mergeClasses('py-12', className)}>
                <div className="mx-auto flex max-w-sm flex-col items-center gap-2 text-center">
                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                        <i className={`bx ${icon} text-xl`} />
                    </div>
                    {title && (
                        <p className="text-sm font-medium text-gray-700 dark:text-gray-300">{title}</p>
                    )}
                    {description && (
                        <p className="text-sm text-gray-500 dark:text-gray-400">{description}</p>
                    )}
                </div>
            </TableCell>
        </TableRow>
    );
}

interface TableSortableLabelProps {
    label: string;
    direction?: SortDirection;
    className?: string;
}

export function TableSortableLabel({ label, direction = null, className = '' }: TableSortableLabelProps) {
    return (
        <span
            className={mergeClasses(
                'inline-flex cursor-pointer items-center gap-0.5 rounded-md px-1 -mx-1 py-0.5 transition-colors duration-150',
                'hover:text-gray-900 dark:hover:text-white',
                direction && 'text-gray-900 dark:text-white',
                className,
            )}
        >
            {label}
            <TableSortIcon direction={direction} />
        </span>
    );
}
