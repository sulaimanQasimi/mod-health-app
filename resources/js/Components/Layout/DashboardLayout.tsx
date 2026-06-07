import { ReactNode, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { SharedPageProps } from '../../types';
import Navbar from './Navbar';
import Sidebar from './Sidebar';

interface DashboardLayoutProps {
    children: ReactNode;
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
    const { direction } = usePage<SharedPageProps>().props;

    useEffect(() => {
        document.documentElement.setAttribute('dir', direction);
        document.body.setAttribute('dir', direction);
    }, [direction]);

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <Sidebar />
            <div className={direction === 'rtl' ? 'mr-72' : 'ml-72'}>
                <Navbar />
                <main className="p-6">{children}</main>
            </div>
        </div>
    );
}
