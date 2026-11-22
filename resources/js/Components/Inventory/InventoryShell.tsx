import { Head } from '@inertiajs/react';
import { ReactNode } from 'react';
import DashboardLayout from '../Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import InventoryModuleNav, { InventoryNavKey, InventoryNavUrls } from './InventoryModuleNav';

export default function InventoryShell({
    title,
    active,
    urls,
    action,
    children,
}: {
    title: string;
    active: InventoryNavKey;
    urls: InventoryNavUrls;
    action?: ReactNode;
    children: ReactNode;
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={title} />
            <div className="mx-auto max-w-[1400px] px-1 sm:px-2">
                <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <div className="flex items-center gap-2">
                        <span className="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white">
                            <i className="bx bx-store-alt text-lg" />
                        </span>
                        <div>
                            <h1 className="text-base font-semibold text-gray-900 dark:text-white sm:text-lg">
                                {t('global.inventory')}
                            </h1>
                            <p className="text-xs text-gray-500 dark:text-gray-400">{title}</p>
                        </div>
                    </div>
                    {action ? <div className="flex flex-wrap items-center gap-1.5">{action}</div> : null}
                </div>

                <InventoryModuleNav active={active} urls={urls} />

                <div className="rounded-lg border border-gray-200 bg-white p-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-3">
                    {children}
                </div>
            </div>
        </DashboardLayout>
    );
}
