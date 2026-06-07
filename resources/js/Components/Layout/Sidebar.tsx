import { Link, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps, SidebarMenuItem } from '../../types';
import MenuIcon from './MenuIcon';

interface SidebarProps {
    isOpen: boolean;
    onClose: () => void;
    isRtl: boolean;
}

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

function matchesRoute(currentRoute: string | null, patterns: string[]): boolean {
    if (!currentRoute) {
        return false;
    }

    return patterns.some((pattern) => {
        if (pattern.endsWith('.*')) {
            return currentRoute.startsWith(pattern.slice(0, -2));
        }

        return currentRoute === pattern;
    });
}

function isItemActive(item: SidebarMenuItem, currentRoute: string | null): boolean {
    if (item.route && currentRoute === item.route) {
        return true;
    }

    if (item.activePatterns && matchesRoute(currentRoute, item.activePatterns)) {
        return true;
    }

    return item.children.some((child) => isItemActive(child, currentRoute));
}

function SidebarLink({
    item,
    currentRoute,
    onNavigate,
}: {
    item: SidebarMenuItem;
    currentRoute: string | null;
    onNavigate: () => void;
}) {
    const { t } = useTranslation();
    const active = isItemActive(item, currentRoute);

    if (item.children.length > 0) {
        return (
            <SidebarGroup
                item={item}
                currentRoute={currentRoute}
                initiallyOpen={active}
                onNavigate={onNavigate}
            />
        );
    }

    return (
        <li>
            <Link
                href={item.href ?? '#'}
                onClick={onNavigate}
                className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
                    active
                        ? 'bg-blue-600 text-white'
                        : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700'
                }`}
            >
                <MenuIcon icon={item.icon} />
                <span>{t(item.label)}</span>
            </Link>
        </li>
    );
}

function SidebarGroup({
    item,
    currentRoute,
    initiallyOpen,
    onNavigate,
}: {
    item: SidebarMenuItem;
    currentRoute: string | null;
    initiallyOpen: boolean;
    onNavigate: () => void;
}) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(initiallyOpen);
    const active = isItemActive(item, currentRoute);

    return (
        <li>
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                className={`flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
                    active
                        ? 'bg-blue-50 text-blue-700 dark:bg-gray-700 dark:text-white'
                        : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700'
                }`}
            >
                <span className="flex items-center gap-3">
                    <MenuIcon icon={item.icon} />
                    <span>{t(item.label)}</span>
                </span>
                <i className={`bx bx-chevron-${open ? 'down' : 'right'} text-base`} />
            </button>
            {open && (
                <ul className="mt-1 space-y-1 border-s border-gray-200 ps-6 dark:border-gray-700">
                    {item.children.map((child) => (
                        <SidebarLink
                            key={child.key}
                            item={child}
                            currentRoute={currentRoute}
                            onNavigate={onNavigate}
                        />
                    ))}
                </ul>
            )}
        </li>
    );
}

export default function Sidebar({ isOpen, onClose, isRtl }: SidebarProps) {
    const { sidebarMenu, currentRoute } = usePage<SharedPageProps>().props;
    const { t } = useTranslation();

    const menu = useMemo(() => sidebarMenu, [sidebarMenu]);

    return (
        <aside
            className={mergeClasses(
                'fixed inset-y-0 z-40 flex w-72 max-w-[85vw] flex-col border-e border-gray-200 bg-white transition-transform duration-300 ease-in-out dark:border-gray-700 dark:bg-gray-800',
                isRtl ? 'right-0' : 'left-0',
                isOpen
                    ? 'translate-x-0'
                    : isRtl
                      ? 'translate-x-full lg:translate-x-0'
                      : '-translate-x-full lg:translate-x-0',
            )}
        >
            <div className="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 px-4 dark:border-gray-700">
                <Link href="/react" className="flex min-w-0 items-center gap-2" onClick={onClose}>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        className="shrink-0 text-green-600"
                    >
                        <path
                            fill="currentColor"
                            d="m22 3.41-.12-1.26-1.2.4a13.84 13.84 0 0 1-6.41.64 11.87 11.87 0 0 0-6.68.9A7.23 7.23 0 0 0 3.3 9.5a9 9 0 0 0 .39 4.58 16.6 16.6 0 0 1 1.18-2.2 9.85 9.85 0 0 1 4.07-3.43 11.16 11.16 0 0 1 5.06-1A12.08 12.08 0 0 0 9.34 9.2a9.48 9.48 0 0 0-1.86 1.53 11.38 11.38 0 0 0-1.39 1.91 16.39 16.39 0 0 0-1.57 4.54A26.42 26.42 0 0 0 4 22h2a30.69 30.69 0 0 1 .59-4.32 9.25 9.25 0 0 0 4.52 1.11 11 11 0 0 0 4.28-.87C23 14.67 22 3.86 22 3.41z"
                        />
                    </svg>
                    <span className="truncate text-base font-bold text-gray-900 dark:text-white">
                        {t('global.system_name')}
                    </span>
                </Link>

                <button
                    type="button"
                    className="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 lg:hidden dark:text-gray-300 dark:hover:bg-gray-700"
                    aria-label="Close menu"
                    onClick={onClose}
                >
                    <i className="bx bx-x text-2xl" />
                </button>
            </div>

            <nav className="flex-1 overflow-y-auto overscroll-contain px-3 py-4">
                <ul className="space-y-1">
                    {menu.map((item) => (
                        <SidebarLink
                            key={item.key}
                            item={item}
                            currentRoute={currentRoute}
                            onNavigate={onClose}
                        />
                    ))}
                </ul>
            </nav>
        </aside>
    );
}
