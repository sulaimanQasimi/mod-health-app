import { Link } from '@inertiajs/react';
import { ReactNode } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { settingsActionClasses } from '../../utils/settingsUi';

export type TableActionKind = 'view' | 'edit' | 'delete' | 'accept' | 'custom';

export type TableActionVariant = 'icon' | 'labeled';

const DEFAULT_ICONS: Record<Exclude<TableActionKind, 'custom'>, string> = {
    view: 'bx-show',
    edit: 'bx-edit',
    delete: 'bx-trash',
    accept: 'bx-check',
};

const DEFAULT_TITLES: Record<Exclude<TableActionKind, 'custom'>, string> = {
    view: 'global.show',
    edit: 'global.edit',
    delete: 'global.delete',
    accept: 'global.accept',
};

const ICON_CLASSES: Record<Exclude<TableActionKind, 'custom'>, string> = {
    view: settingsActionClasses.view,
    edit: settingsActionClasses.edit,
    delete: settingsActionClasses.delete,
    accept:
        'inline-flex h-8 shrink-0 items-center gap-1 rounded-lg bg-emerald-600 px-2.5 text-xs font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-emerald-700 dark:hover:bg-emerald-600',
};

export interface TableActionButtonProps {
    kind: TableActionKind;
    /** When false the action is not rendered. Defaults to true. */
    permission?: boolean;
    href?: string;
    /** Render a native anchor (e.g. external / print links). */
    external?: boolean;
    onClick?: () => void;
    label?: string;
    title?: string;
    icon?: string;
    variant?: TableActionVariant;
    /** Shows a confirm dialog before running onClick (typical for delete). */
    confirm?: string;
    disabled?: boolean;
    className?: string;
    children?: ReactNode;
}

export default function TableActionButton({
    kind,
    permission = true,
    href,
    external = false,
    onClick,
    label,
    title,
    icon,
    variant,
    confirm,
    disabled = false,
    className,
    children,
}: TableActionButtonProps) {
    const { t } = useTranslation();

    if (!permission) {
        return null;
    }

    const resolvedVariant = variant ?? (kind === 'accept' ? 'labeled' : 'icon');
    const resolvedIcon = icon ?? (kind !== 'custom' ? DEFAULT_ICONS[kind] : 'bx-dots-horizontal-rounded');
    const resolvedTitle = title ?? (kind !== 'custom' ? t(DEFAULT_TITLES[kind]) : undefined);
    const resolvedLabel = label ?? (kind === 'accept' ? t('global.accept') : undefined);

    const handleClick = () => {
        if (disabled) {
            return;
        }
        if (confirm && !window.confirm(confirm)) {
            return;
        }
        onClick?.();
    };

    if (kind === 'custom') {
        if (href) {
            if (external) {
                return (
                    <a
                        href={href}
                        target="_blank"
                        rel="noreferrer"
                        title={resolvedTitle}
                        className={className ?? settingsActionClasses.edit}
                    >
                        {children ?? <i className={`bx ${resolvedIcon} text-lg`} />}
                    </a>
                );
            }

            return (
                <Link href={href} title={resolvedTitle} className={className ?? settingsActionClasses.view}>
                    {children ?? <i className={`bx ${resolvedIcon} text-lg`} />}
                </Link>
            );
        }

        return (
            <button
                type="button"
                title={resolvedTitle}
                disabled={disabled}
                onClick={handleClick}
                className={className ?? settingsActionClasses.edit}
            >
                {children ?? <i className={`bx ${resolvedIcon} text-lg`} />}
            </button>
        );
    }

    if (resolvedVariant === 'labeled') {
        return (
            <button
                type="button"
                title={resolvedTitle}
                disabled={disabled}
                onClick={handleClick}
                className={className ?? ICON_CLASSES[kind]}
            >
                <i className={`bx ${resolvedIcon}`} />
                {resolvedLabel && <span>{resolvedLabel}</span>}
            </button>
        );
    }

    if (href) {
        if (external) {
            return (
                <a
                    href={href}
                    target="_blank"
                    rel="noreferrer"
                    title={resolvedTitle}
                    className={className ?? ICON_CLASSES[kind]}
                >
                    <i className={`bx ${resolvedIcon} text-lg`} />
                </a>
            );
        }

        return (
            <Link href={href} title={resolvedTitle} className={className ?? ICON_CLASSES[kind]}>
                <i className={`bx ${resolvedIcon} text-lg`} />
            </Link>
        );
    }

    return (
        <button
            type="button"
            title={resolvedTitle}
            disabled={disabled}
            onClick={handleClick}
            className={className ?? ICON_CLASSES[kind]}
        >
            <i className={`bx ${resolvedIcon} text-lg`} />
        </button>
    );
}
