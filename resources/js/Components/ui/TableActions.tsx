import { Link } from '@inertiajs/react';
import { ButtonHTMLAttributes, ReactNode } from 'react';

type TableActionVariant = 'view' | 'edit' | 'delete';

const variantClasses: Record<TableActionVariant, string> = {
    view: 'text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30',
    edit: 'text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30',
    delete: 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30',
};

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

interface TableActionsProps {
    children: ReactNode;
    className?: string;
}

export function TableActions({ children, className = '' }: TableActionsProps) {
    return (
        <div className={mergeClasses('flex items-center justify-center gap-0.5', className)}>
            {children}
        </div>
    );
}

interface TableActionLinkProps {
    href: string;
    icon: string;
    title: string;
    variant?: TableActionVariant;
}

export function TableActionLink({ href, icon, title, variant = 'view' }: TableActionLinkProps) {
    return (
        <Link
            href={href}
            className={mergeClasses(
                'inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors',
                variantClasses[variant],
            )}
            title={title}
        >
            <i className={`bx ${icon} text-lg`} />
        </Link>
    );
}

interface TableActionButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    icon: string;
    title: string;
    variant?: TableActionVariant;
}

export function TableActionButton({
    icon,
    title,
    variant = 'delete',
    className = '',
    ...props
}: TableActionButtonProps) {
    return (
        <button
            type="button"
            title={title}
            className={mergeClasses(
                'inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors',
                variantClasses[variant],
                className,
            )}
            {...props}
        >
            <i className={`bx ${icon} text-lg`} />
        </button>
    );
}
