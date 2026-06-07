import { Head } from '@inertiajs/react';
import DashboardLayout from '../Components/Layout/DashboardLayout';
import { useTranslation } from '../hooks/useTranslation';

export default function Dashboard() {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.dashboard')} />
            <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">{t('global.dashboard')}</h1>
                <p className="mt-2 text-gray-600 dark:text-gray-300">
                    {t('global.dashboard')}
                </p>
            </div>
        </DashboardLayout>
    );
}
