import { usePage } from '@inertiajs/react';
import { ReactNode, useEffect, useState } from 'react';
import { SharedPageProps } from '../../types';
import Navbar from './Navbar';
import Sidebar from './Sidebar';

interface DashboardLayoutProps {
    children: ReactNode;
}

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
    const { direction } = usePage<SharedPageProps>().props;
    const { url } = usePage();
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const isRtl = direction === 'rtl';

    useEffect(() => {
        document.documentElement.setAttribute('dir', direction);
        document.body.setAttribute('dir', direction);
    }, [direction]);

    useEffect(() => {
        setSidebarOpen(false);
    }, [url]);

    useEffect(() => {
        const mediaQuery = window.matchMedia('(min-width: 1024px)');

        const handleViewportChange = () => {
            if (mediaQuery.matches) {
                setSidebarOpen(false);
            }
        };

        mediaQuery.addEventListener('change', handleViewportChange);

        return () => mediaQuery.removeEventListener('change', handleViewportChange);
    }, []);

    useEffect(() => {
        document.body.style.overflow = sidebarOpen ? 'hidden' : '';

        return () => {
            document.body.style.overflow = '';
        };
    }, [sidebarOpen]);

    const closeSidebar = () => setSidebarOpen(false);
    const toggleSidebar = () => setSidebarOpen((open) => !open);

    return (
        <div className="min-h-screen overflow-x-hidden bg-gray-50 dark:bg-gray-900">
            {sidebarOpen && (
                <button
                    type="button"
                    className="fixed inset-0 z-30 bg-black/50 lg:hidden"
                    aria-label="Close menu"
                    onClick={closeSidebar}
                />
            )}

            <Sidebar isOpen={sidebarOpen} onClose={closeSidebar} isRtl={isRtl} />

            <div
                className={mergeClasses(
                    'flex min-h-screen min-w-0 flex-col',
                    isRtl ? 'lg:pr-72' : 'lg:pl-72',
                )}
            >
                <Navbar onMenuToggle={toggleSidebar} sidebarOpen={sidebarOpen} />
                <main className="flex-1 p-4 sm:p-6">{children}</main>
            </div>
        </div>
    );
}
