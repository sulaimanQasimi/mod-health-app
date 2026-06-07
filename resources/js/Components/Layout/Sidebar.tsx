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

function isItemExactActive(item: SidebarMenuItem, currentRoute: string | null): boolean {
    if (item.route && currentRoute === item.route) {
        return true;
    }

    if (item.activePatterns && matchesRoute(currentRoute, item.activePatterns)) {
        return true;
    }

    return false;
}

function hasActiveChild(item: SidebarMenuItem, currentRoute: string | null): boolean {
    return item.children.some((child) => isItemActive(child, currentRoute));
}

function isItemActive(item: SidebarMenuItem, currentRoute: string | null): boolean {
    if (isItemExactActive(item, currentRoute)) {
        return true;
    }

    return item.children.some((child) => isItemActive(child, currentRoute));
}

function NavIcon({ icon, active }: { icon: string | null; active: boolean }) {
    return (
        <span
            className={mergeClasses(
                'flex w-5 shrink-0 items-center justify-center transition-colors',
                active
                    ? 'text-blue-600 dark:text-blue-400'
                    : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300',
            )}
        >
            <MenuIcon icon={icon} className="text-[1.125rem]" />
        </span>
    );
}

function navItemClasses(active: boolean, nested = false) {
    return mergeClasses(
        'group flex items-center rounded-lg outline-none transition-all duration-150 focus-visible:ring-2 focus-visible:ring-blue-500/30',
        nested ? 'py-2 pe-3 ps-11 text-[13px]' : 'gap-3 px-3 py-2.5 text-sm',
        active
            ? nested
                ? 'bg-blue-50 font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300'
                : 'bg-white font-medium text-gray-900 shadow-sm ring-1 ring-gray-200/70 dark:bg-gray-800 dark:text-white dark:ring-gray-600/60'
            : 'text-gray-600 hover:bg-white/70 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800/50 dark:hover:text-white',
    );
}

function parentItemClasses(exactActive: boolean, childActive: boolean) {
    return mergeClasses(
        'group flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-sm outline-none transition-all duration-150 focus-visible:ring-2 focus-visible:ring-blue-500/30',
        exactActive
            ? 'bg-white font-medium text-gray-900 shadow-sm ring-1 ring-gray-200/70 dark:bg-gray-800 dark:text-white dark:ring-gray-600/60'
            : childActive
              ? 'font-medium text-blue-700 dark:text-blue-300'
              : 'text-gray-600 hover:bg-white/70 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800/50 dark:hover:text-white',
    );
}

function SidebarLink({
    item,
    currentRoute,
    onNavigate,
    isRtl,
    nested = false,
}: {
    item: SidebarMenuItem;
    currentRoute: string | null;
    onNavigate: () => void;
    isRtl: boolean;
    nested?: boolean;
}) {
    const { t } = useTranslation();
    const active = isItemExactActive(item, currentRoute);

    if (item.children.length > 0) {
        return (
            <SidebarGroup
                item={item}
                currentRoute={currentRoute}
                initiallyOpen={isItemActive(item, currentRoute)}
                onNavigate={onNavigate}
                isRtl={isRtl}
            />
        );
    }

    return (
        <li>
            <Link
                href={item.href ?? '#'}
                onClick={onNavigate}
                className={navItemClasses(active, nested)}
            >
                {!nested && <NavIcon icon={item.icon} active={active} />}
                <span className="truncate leading-5">{t(item.label)}</span>
            </Link>
        </li>
    );
}

function SidebarGroup({
    item,
    currentRoute,
    initiallyOpen,
    onNavigate,
    isRtl,
}: {
    item: SidebarMenuItem;
    currentRoute: string | null;
    initiallyOpen: boolean;
    onNavigate: () => void;
    isRtl: boolean;
}) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(initiallyOpen);
    const exactActive = isItemExactActive(item, currentRoute);
    const childActive = hasActiveChild(item, currentRoute);
    const groupActive = exactActive || childActive;

    return (
        <li>
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                className={mergeClasses(
                    parentItemClasses(exactActive, childActive),
                    open && !groupActive && 'bg-white/50 dark:bg-gray-800/40',
                )}
            >
                <span className="flex min-w-0 items-center gap-3">
                    <NavIcon icon={item.icon} active={groupActive} />
                    <span className="truncate leading-5">{t(item.label)}</span>
                </span>
                <i
                    className={mergeClasses(
                        'bx shrink-0 text-lg text-gray-400 transition-transform duration-200 dark:text-gray-500',
                        open ? 'bx-chevron-down' : isRtl ? 'bx-chevron-left' : 'bx-chevron-right',
                    )}
                />
            </button>
            {open && (
                <ul className="mt-0.5 space-y-0.5 pb-1">
                    {item.children.map((child) => (
                        <SidebarLink
                            key={child.key}
                            item={child}
                            currentRoute={currentRoute}
                            onNavigate={onNavigate}
                            isRtl={isRtl}
                            nested
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
                'fixed inset-y-0 z-40 flex w-72 max-w-[85vw] flex-col border-gray-200/70 bg-gray-50/95 backdrop-blur-sm transition-transform duration-300 ease-in-out dark:border-gray-700 dark:bg-gray-900',
                isRtl ? 'right-0 border-s' : 'left-0 border-e',
                isOpen
                    ? 'translate-x-0'
                    : isRtl
                      ? 'translate-x-full lg:translate-x-0'
                      : '-translate-x-full lg:translate-x-0',
            )}
        >
            <div className="shrink-0 border-b border-gray-200/70 px-4 py-4 dark:border-gray-700/80">
                <div className="flex items-center justify-between gap-3">
                    <Link
                        href="/react"
                        className="group flex min-w-0 flex-1 items-center gap-3"
                        onClick={onClose}
                    >
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-emerald-600 to-teal-700 text-white shadow-sm ring-1 ring-black/5 transition-transform duration-150 group-hover:scale-[1.02] dark:ring-white/10">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                className="shrink-0"
                            >
                                <path
                                    fill="currentColor"
                                    d="m22 3.41-.12-1.26-1.2.4a13.84 13.84 0 0 1-6.41.64 11.87 11.87 0 0 0-6.68.9A7.23 7.23 0 0 0 3.3 9.5a9 9 0 0 0 .39 4.58 16.6 16.6 0 0 1 1.18-2.2 9.85 9.85 0 0 1 4.07-3.43 11.16 11.16 0 0 1 5.06-1A12.08 12.08 0 0 0 9.34 9.2a9.48 9.48 0 0 0-1.86 1.53 11.38 11.38 0 0 0-1.39 1.91 16.39 16.39 0 0 0-1.57 4.54A26.42 26.42 0 0 0 4 22h2a30.69 30.69 0 0 1 .59-4.32 9.25 9.25 0 0 0 4.52 1.11 11 11 0 0 0 4.28-.87C23 14.67 22 3.86 22 3.41z"
                                />
                            </svg>
                        </span>
                        <span className="min-w-0 truncate text-sm font-semibold tracking-tight text-gray-900 dark:text-white">
                            {t('global.system_name')}
                        </span>
                    </Link>

                    <button
                        type="button"
                        className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-white hover:text-gray-600 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        aria-label="Close menu"
                        onClick={onClose}
                    >
                        <i className="bx bx-x text-xl" />
                    </button>
                </div>
            </div>

            <nav className="flex-1 overflow-y-auto overscroll-contain px-3 py-4 scrollbar-thin scrollbar-track-transparent scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
                <ul className="space-y-1">
                    {menu.map((item) =>
                        item.children.length > 0 ? (
                            <SidebarGroup
                                key={item.key}
                                item={item}
                                currentRoute={currentRoute}
                                initiallyOpen={isItemActive(item, currentRoute)}
                                onNavigate={onClose}
                                isRtl={isRtl}
                            />
                        ) : (
                            <SidebarLink
                                key={item.key}
                                item={item}
                                currentRoute={currentRoute}
                                onNavigate={onClose}
                                isRtl={isRtl}
                            />
                        ),
                    )}
                </ul>
            </nav>
        </aside>
    );
}
