import { Link } from '@inertiajs/react';
import { ReactNode } from 'react';

export type AppointmentActionVariant =
    | 'view'
    | 'history'
    | 'edit'
    | 'delete'
    | 'info'
    | 'user'
    | 'accept'
    | 'changeDepartment'
    | 'restore';

const iconVariantClasses: Record<AppointmentActionVariant, string> = {
    view: 'border-blue-200/80 bg-blue-50 text-blue-700 hover:border-blue-300 hover:bg-blue-100 focus:ring-blue-500/30 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50',
    history:
        'border-violet-200/80 bg-violet-50 text-violet-700 hover:border-violet-300 hover:bg-violet-100 focus:ring-violet-500/30 dark:border-violet-800 dark:bg-violet-950/40 dark:text-violet-300 dark:hover:bg-violet-900/50',
    edit: 'border-amber-200/80 bg-amber-50 text-amber-700 hover:border-amber-300 hover:bg-amber-100 focus:ring-amber-500/30 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300 dark:hover:bg-amber-900/50',
    delete:
        'border-rose-200/80 bg-rose-50 text-rose-700 hover:border-rose-300 hover:bg-rose-100 focus:ring-rose-500/30 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/50',
    info: 'border-cyan-200/80 bg-cyan-50 text-cyan-700 hover:border-cyan-300 hover:bg-cyan-100 focus:ring-cyan-500/30 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-300 dark:hover:bg-cyan-900/50',
    user: 'border-indigo-200/80 bg-indigo-50 text-indigo-700 hover:border-indigo-300 hover:bg-indigo-100 focus:ring-indigo-500/30 dark:border-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-300 dark:hover:bg-indigo-900/50',
    accept:
        'border-emerald-200/80 bg-emerald-50 text-emerald-700 hover:border-emerald-300 hover:bg-emerald-100 focus:ring-emerald-500/30 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50',
    changeDepartment:
        'border-orange-200/80 bg-orange-50 text-orange-700 hover:border-orange-300 hover:bg-orange-100 focus:ring-orange-500/30 dark:border-orange-800 dark:bg-orange-950/40 dark:text-orange-300 dark:hover:bg-orange-900/50',
    restore:
        'border-emerald-200/80 bg-emerald-50 text-emerald-700 hover:border-emerald-300 hover:bg-emerald-100 focus:ring-emerald-500/30 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50',
};

const iconButtonBase =
    'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border shadow-sm transition-all duration-150 hover:-translate-y-px hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:pointer-events-none disabled:opacity-50 dark:focus:ring-offset-gray-900';

const pillButtonBase =
    'inline-flex h-8 shrink-0 items-center gap-1.5 rounded-lg border px-2.5 text-xs font-semibold shadow-sm transition-all duration-150 hover:-translate-y-px hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:pointer-events-none disabled:opacity-50 dark:focus:ring-offset-gray-900';

interface AppointmentActionGroupProps {
    children: ReactNode;
}

export function AppointmentActionGroup({ children }: AppointmentActionGroupProps) {
    return (
        <div className="inline-flex max-w-full flex-wrap items-center justify-center gap-1">
            {children}
        </div>
    );
}

interface AppointmentIconLinkProps {
    href: string;
    icon: string;
    title: string;
    variant?: AppointmentActionVariant;
}

export function AppointmentIconLink({
    href,
    icon,
    title,
    variant = 'view',
}: AppointmentIconLinkProps) {
    return (
        <Link
            href={href}
            className={`${iconButtonBase} ${iconVariantClasses[variant]}`}
            title={title}
        >
            <i className={`bx ${icon} text-lg`} />
        </Link>
    );
}

interface AppointmentIconAnchorProps {
    href: string;
    icon: string;
    title: string;
    variant?: AppointmentActionVariant;
}

export function AppointmentIconAnchor({
    href,
    icon,
    title,
    variant = 'history',
}: AppointmentIconAnchorProps) {
    return (
        <a
            href={href}
            className={`${iconButtonBase} ${iconVariantClasses[variant]}`}
            title={title}
        >
            <i className={`bx ${icon} text-lg`} />
        </a>
    );
}

interface AppointmentIconButtonProps {
    icon: string;
    title: string;
    onClick: () => void;
    variant?: AppointmentActionVariant;
    disabled?: boolean;
}

export function AppointmentIconButton({
    icon,
    title,
    onClick,
    variant = 'delete',
    disabled = false,
}: AppointmentIconButtonProps) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            className={`${iconButtonBase} ${iconVariantClasses[variant]}`}
            title={title}
        >
            <i className={`bx ${icon} text-lg`} />
        </button>
    );
}

interface AppointmentPillButtonProps {
    icon: string;
    label: string;
    title?: string;
    onClick: () => void;
    variant?: AppointmentActionVariant;
    disabled?: boolean;
}

export function AppointmentPillButton({
    icon,
    label,
    title,
    onClick,
    variant = 'accept',
    disabled = false,
}: AppointmentPillButtonProps) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            title={title ?? label}
            className={`${pillButtonBase} ${iconVariantClasses[variant]}`}
        >
            <i className={`bx ${icon} text-base`} />
            <span>{label}</span>
        </button>
    );
}

interface AppointmentInfoTipProps {
    icon: string;
    title: string;
    variant?: AppointmentActionVariant;
}

export function AppointmentInfoTip({
    icon,
    title,
    variant = 'info',
}: AppointmentInfoTipProps) {
    return (
        <span
            className={`${iconButtonBase} ${iconVariantClasses[variant]} cursor-help`}
            title={title}
        >
            <i className={`bx ${icon} text-lg`} />
        </span>
    );
}
