import { Head } from '@inertiajs/react';
import DashboardLayout from '../Components/Layout/DashboardLayout';
import { useTranslation } from '../hooks/useTranslation';

interface PlaceholderProps {
    pageTitleKey: string;
}

export default function Placeholder({ pageTitleKey }: PlaceholderProps) {
    const { t } = useTranslation();
    const title = t(pageTitleKey);

    return (
        <DashboardLayout>
            <Head title={title} />
            <div className="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-600 dark:bg-gray-800">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">{title}</h1>
                <p className="mt-3 text-gray-500 dark:text-gray-400">
                    React page scaffold — content migration pending.
                </p>
            </div>
        </DashboardLayout>
    );
}
