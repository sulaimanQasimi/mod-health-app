import { Link, usePage } from '@inertiajs/react';
import { ReactNode, useEffect, useMemo, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps, SidebarMenuItem } from '../../types';
import MenuIcon from './MenuIcon';

interface SidebarProps {
    isOpen: boolean;
    onClose: () => void;
    isCollapsed: boolean;
    onCollapseToggle: () => void;
    isRtl: boolean;
}

const SIDEBAR_WIDTH = '16.25rem';
const SIDEBAR_COLLAPSED_WIDTH = '5.25rem';

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

function MenuToggleChevron({ open, isRtl }: { open: boolean; isRtl: boolean }) {
    return (
        <span
            aria-hidden
            className={mergeClasses(
                'pointer-events-none absolute top-1/2 block h-[0.42em] w-[0.42em] -translate-y-1/2 border border-current transition-transform duration-300',
                isRtl ? 'start-4 border-b-0 border-e-0' : 'end-4 border-b-0 border-s-0',
                open
                    ? isRtl
                        ? '-rotate-[135deg]'
                        : 'rotate-[135deg]'
                    : isRtl
                      ? '-rotate-45'
                      : 'rotate-45',
            )}
        />
    );
}

function NavIcon({ icon, isCollapsed = false }: { icon: string | null; isCollapsed?: boolean }) {
    return (
        <span
            className={mergeClasses(
                'menu-icon flex w-6 shrink-0 items-center justify-center text-xl',
                isCollapsed ? 'me-0' : 'me-2',
            )}
        >
            <MenuIcon icon={icon} className="text-[1.25rem]" />
        </span>
    );
}

function topLevelLinkClasses(active: boolean, isCollapsed = false) {
    return mergeClasses(
        'menu-link relative flex w-full min-w-0 items-center rounded-md py-2.5 text-[0.9375rem] transition-colors duration-300',
        isCollapsed ? 'justify-center px-0' : 'px-4',
        active
            ? 'bg-blue-600/15 font-semibold text-blue-600 dark:bg-blue-600 dark:text-white'
            : 'text-[#697a8d] hover:bg-[rgba(67,89,113,0.04)] hover:text-[#566a7f] dark:text-[#c4cdd5] dark:hover:bg-white/4 dark:hover:text-white',
    );
}

function subLinkClasses(active: boolean, isRtl: boolean) {
    return mergeClasses(
        'menu-link relative flex w-full min-w-0 items-center rounded-md py-2.5 text-[0.9375rem] transition-colors duration-300',
        isRtl ? 'pl-4 pr-12' : 'pl-12 pr-4',
        'before:absolute before:top-1/2 before:size-1.5 before:-translate-y-1/2 before:rounded-full before:content-[""]',
        isRtl ? 'before:right-[1.4375rem]' : 'before:left-[1.4375rem]',
        active
            ? mergeClasses(
                  'font-semibold text-blue-600 dark:text-white',
                  'before:size-3.5 before:border-[3px] before:border-blue-100 before:bg-blue-600 dark:before:border-blue-500/40',
                  isRtl ? 'before:right-[1.1875rem]' : 'before:left-[1.1875rem]',
              )
            : 'text-[#697a8d] before:bg-[#b4bdc6] hover:bg-[rgba(67,89,113,0.04)] hover:text-[#566a7f] dark:text-[#c4cdd5] dark:before:bg-[#a3a4cc] dark:hover:bg-white/4 dark:hover:text-white',
    );
}

function activeAccentClasses(isRtl: boolean) {
    return mergeClasses(
        'pointer-events-none absolute top-1/2 z-20 h-[2.6845rem] w-1 -translate-y-1/2 bg-blue-600',
        isRtl ? 'left-0 rounded-e-md' : 'right-0 rounded-s-md',
    );
}

function TopLevelItemShell({
    isRtl,
    showAccent,
    children,
}: {
    isRtl: boolean;
    showAccent: boolean;
    children: ReactNode;
}) {
    return (
        <div className="relative">
            {children}
            {showAccent && <span aria-hidden className={activeAccentClasses(isRtl)} />}
        </div>
    );
}

function SidebarLink({
    item,
    currentRoute,
    onNavigate,
    isRtl,
    isCollapsed,
    nested = false,
}: {
    item: SidebarMenuItem;
    currentRoute: string | null;
    onNavigate: () => void;
    isRtl: boolean;
    isCollapsed: boolean;
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
                isCollapsed={isCollapsed}
            />
        );
    }

    if (nested) {
        return (
            <li className={mergeClasses('menu-item', active && 'active')}>
                <Link href={item.href ?? '#'} onClick={onNavigate} className={subLinkClasses(active, isRtl)}>
                    <span className="truncate">{t(item.label)}</span>
                </Link>
            </li>
        );
    }

    return (
        <li className={mergeClasses('menu-item', active && 'active')}>
            <TopLevelItemShell isRtl={isRtl} showAccent={active}>
                <Link
                    href={item.href ?? '#'}
                    onClick={onNavigate}
                    className={topLevelLinkClasses(active, isCollapsed)}
                >
                    <NavIcon icon={item.icon} isCollapsed={isCollapsed} />
                    {!isCollapsed && <span className="truncate">{t(item.label)}</span>}
                </Link>
            </TopLevelItemShell>
        </li>
    );
}

function SidebarGroup({
    item,
    currentRoute,
    initiallyOpen,
    onNavigate,
    isRtl,
    isCollapsed,
}: {
    item: SidebarMenuItem;
    currentRoute: string | null;
    initiallyOpen: boolean;
    onNavigate: () => void;
    isRtl: boolean;
    isCollapsed: boolean;
}) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(initiallyOpen);
    const exactActive = isItemExactActive(item, currentRoute);
    const childActive = hasActiveChild(item, currentRoute);
    const groupActive = exactActive || childActive;

    return (
        <li className={mergeClasses('menu-item', groupActive && 'active', open && 'open')}>
            <TopLevelItemShell isRtl={isRtl} showAccent={groupActive}>
                <button
                    type="button"
                    onClick={() => setOpen((value) => !value)}
                    className={mergeClasses(
                        topLevelLinkClasses(groupActive, isCollapsed),
                        'menu-toggle text-start',
                        !isCollapsed && (isRtl ? 'pe-4 ps-[calc(1rem+1.26em)]' : 'pe-[calc(1rem+1.26em)] ps-4'),
                        open && !groupActive && 'bg-[rgba(67,89,113,0.04)] text-[#566a7f] dark:bg-white/4 dark:text-white',
                    )}
                >
                    <NavIcon icon={item.icon} isCollapsed={isCollapsed} />
                    {!isCollapsed && <span className="truncate">{t(item.label)}</span>}
                    {!isCollapsed && <MenuToggleChevron open={open} isRtl={isRtl} />}
                </button>
            </TopLevelItemShell>
            {open && !isCollapsed && (
                <ul className="menu-sub py-1.5">
                    {item.children.map((child) => (
                        <SidebarLink
                            key={child.key}
                            item={child}
                            currentRoute={currentRoute}
                            onNavigate={onNavigate}
                            isRtl={isRtl}
                            isCollapsed={isCollapsed}
                            nested
                        />
                    ))}
                </ul>
            )}
        </li>
    );
}

function handleMenuToggle(onClose: () => void, onCollapseToggle: () => void) {
    if (window.matchMedia('(max-width: 1023px)').matches) {
        onClose();
        return;
    }

    onCollapseToggle();
}

export default function Sidebar({
    isOpen,
    onClose,
    isCollapsed,
    onCollapseToggle,
    isRtl,
}: SidebarProps) {
    const { sidebarMenu, currentRoute } = usePage<SharedPageProps>().props;
    const { t } = useTranslation();

    const menu = useMemo(() => sidebarMenu, [sidebarMenu]);
    const [isDesktop, setIsDesktop] = useState(false);

    useEffect(() => {
        const mediaQuery = window.matchMedia('(min-width: 1024px)');
        const updateViewport = () => setIsDesktop(mediaQuery.matches);

        updateViewport();
        mediaQuery.addEventListener('change', updateViewport);

        return () => mediaQuery.removeEventListener('change', updateViewport);
    }, []);

    const effectiveCollapsed = isCollapsed && isDesktop;
    const sidebarWidth = effectiveCollapsed ? SIDEBAR_COLLAPSED_WIDTH : SIDEBAR_WIDTH;

    return (
        <aside
            style={{ width: sidebarWidth }}
            className={mergeClasses(
                'menu menu-vertical bg-menu-theme fixed inset-y-0 z-40 flex max-w-[85vw] flex-col overflow-visible bg-white text-[#697a8d] shadow-[0_0.125rem_0.375rem_0_rgba(161,172,184,0.12)] transition-transform duration-300 ease-in-out dark:bg-[#191924] dark:text-[#c4cdd5] dark:shadow-[0_0.125rem_0.375rem_0_rgba(0,0,0,0.25)]',
                isRtl ? 'right-0' : 'left-0',
                isOpen
                    ? 'translate-x-0'
                    : isRtl
                      ? 'translate-x-full lg:translate-x-0'
                      : '-translate-x-full lg:translate-x-0',
            )}
        >
            <div
                className={mergeClasses(
                    'app-brand relative flex w-full shrink-0 items-center overflow-visible py-6',
                    effectiveCollapsed ? 'justify-center px-4' : 'px-8',
                )}
            >
                <Link
                    href="/react"
                    className={mergeClasses(
                        'app-brand-link flex min-w-0 items-center',
                        effectiveCollapsed ? 'justify-center' : 'flex-1 pe-6',
                    )}
                    onClick={onClose}
                >
                    <span className="app-brand-logo shrink-0">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            className="block"
                        >
                            <path
                                fill="rgb(17, 170, 4)"
                                d="m22 3.41-.12-1.26-1.2.4a13.84 13.84 0 0 1-6.41.64 11.87 11.87 0 0 0-6.68.9A7.23 7.23 0 0 0 3.3 9.5a9 9 0 0 0 .39 4.58 16.6 16.6 0 0 1 1.18-2.2 9.85 9.85 0 0 1 4.07-3.43 11.16 11.16 0 0 1 5.06-1A12.08 12.08 0 0 0 9.34 9.2a9.48 9.48 0 0 0-1.86 1.53 11.38 11.38 0 0 0-1.39 1.91 16.39 16.39 0 0 0-1.57 4.54A26.42 26.42 0 0 0 4 22h2a30.69 30.69 0 0 1 .59-4.32 9.25 9.25 0 0 0 4.52 1.11 11 11 0 0 0 4.28-.87C23 14.67 22 3.86 22 3.41z"
                            />
                        </svg>
                    </span>
                    {!effectiveCollapsed && (
                        <span className="app-brand-text menu-text ms-2 truncate text-[1.05rem] font-bold text-[#566a7f] dark:text-white">
                            {t('global.system_name')}
                        </span>
                    )}
                </Link>

                <button
                    type="button"
                    className={mergeClasses(
                        'layout-menu-toggle menu-link absolute top-1/2 z-50 inline-flex size-[2.375rem] -translate-y-1/2 items-center justify-center rounded-full border-[7px] border-white bg-[#696cff] text-white shadow-sm transition-all duration-300 lg:border-[#f5f5f9] dark:border-[#191924] dark:lg:border-[#191924]',
                        isRtl ? '-left-[1.1875rem]' : '-right-[1.1875rem]',
                        isOpen ? 'max-lg:flex' : 'max-lg:hidden',
                        'lg:flex',
                    )}
                    aria-label={effectiveCollapsed ? 'Expand menu' : 'Collapse menu'}
                    onClick={() => handleMenuToggle(onClose, onCollapseToggle)}
                >
                    <i
                        className={mergeClasses(
                            'bx bx-chevron-left bx-sm align-middle size-6 transition-transform duration-300',
                            isRtl && 'scale-x-[-1]',
                            effectiveCollapsed && (isRtl ? '-rotate-180' : 'rotate-180'),
                        )}
                    />
                </button>
            </div>

            <div className="relative flex min-h-0 min-w-0 flex-1 flex-col overflow-x-hidden">
                <nav className="menu-inner flex-1 overflow-x-hidden overflow-y-auto overscroll-contain px-4 py-1">
                    <ul className="m-0 min-w-0 list-none p-0">
                        {menu.map((item) =>
                            item.children.length > 0 ? (
                                <SidebarGroup
                                    key={item.key}
                                    item={item}
                                    currentRoute={currentRoute}
                                    initiallyOpen={isItemActive(item, currentRoute)}
                                    onNavigate={onClose}
                                    isRtl={isRtl}
                                    isCollapsed={effectiveCollapsed}
                                />
                            ) : (
                                <SidebarLink
                                    key={item.key}
                                    item={item}
                                    currentRoute={currentRoute}
                                    onNavigate={onClose}
                                    isRtl={isRtl}
                                    isCollapsed={effectiveCollapsed}
                                />
                            ),
                        )}
                    </ul>
                </nav>
            </div>
        </aside>
    );
}

export { SIDEBAR_COLLAPSED_WIDTH, SIDEBAR_WIDTH };
