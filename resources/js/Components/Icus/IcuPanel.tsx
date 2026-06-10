import { ReactNode } from 'react';
import { ICU_CARD_CLASS, ICU_PANEL_ICON_BG_CLASS, ICU_PANEL_ICON_CLASS } from './icuUi';

interface IcuPanelProps {
    title: string;
    icon: string;
    iconClassName?: string;
    description?: string;
    action?: ReactNode;
    variant?: 'filter' | 'table';
    footer?: ReactNode;
    children: ReactNode;
}

export default function IcuPanel({
    title,
    icon,
    iconClassName = ICU_PANEL_ICON_CLASS,
    description,
    action,
    variant = 'table',
    footer,
    children,
}: IcuPanelProps) {
    const isTable = variant === 'table';

    return (
        <section className={`${ICU_CARD_CLASS} ${isTable ? '' : 'p-5'}`}>
            <div
                className={`flex flex-wrap items-start justify-between gap-3 ${
                    isTable
                        ? 'border-b border-gray-100 px-5 py-4 dark:border-gray-800'
                        : 'mb-4 border-b border-gray-100 pb-4 dark:border-gray-800'
                }`}
            >
                <div className="flex items-start gap-3">
                    <div
                        className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${ICU_PANEL_ICON_BG_CLASS}`}
                    >
                        <i className={`bx ${icon} text-lg ${iconClassName}`} />
                    </div>
                    <div>
                        <h2 className="text-sm font-semibold text-gray-900 dark:text-white">{title}</h2>
                        {description && (
                            <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{description}</p>
                        )}
                    </div>
                </div>
                {action}
            </div>

            {isTable ? children : <div>{children}</div>}

            {footer && (
                <div className="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{footer}</div>
            )}
        </section>
    );
}
