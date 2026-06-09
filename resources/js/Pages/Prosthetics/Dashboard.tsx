import { Head, Link } from '@inertiajs/react';
import { Badge, Card } from 'flowbite-react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface DashboardProps {
    stats: {
        referral_pending: number;
        waiting_approval: number;
        in_production: number;
        work_orders_active: number;
    };
    statusCounts: Record<string, number>;
    recentCases: Array<{
        id: number;
        case_number: string;
        status: string;
        patient_name: string;
        updated_at: string | null;
    }>;
    urls: {
        reports: string;
        cases: string;
        caseShow: string;
    };
}

const HIGHLIGHT_STATUSES = [
    'new',
    'under_assessment',
    'waiting_approval',
    'approved',
    'in_production',
    'trial_fit',
    'delivered',
];

export default function ProstheticsDashboard({ stats, statusCounts, recentCases, urls }: DashboardProps) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.prosthetics_dashboard')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.prosthetics_dashboard')}
                    icon="bx-cog"
                    accent="from-violet-500 to-purple-600"
                    action={
                        <Link
                            href={urls.reports}
                            className="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            {t('global.reports')}
                        </Link>
                    }
                />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {[
                        { key: 'referral_pending', label: 'global.prosthetics_pending_referrals', value: stats.referral_pending },
                        { key: 'waiting_approval', label: 'global.prosthetics_waiting_approval', value: stats.waiting_approval },
                        { key: 'in_production', label: 'global.prosthetics_in_production', value: stats.in_production },
                        { key: 'work_orders_active', label: 'global.prosthetics_active_work_orders', value: stats.work_orders_active },
                    ].map((item) => (
                        <Card key={item.key}>
                            <p className="text-sm text-gray-500 dark:text-gray-400">{t(item.label)}</p>
                            <p className="text-3xl font-semibold text-gray-900 dark:text-white">{item.value}</p>
                        </Card>
                    ))}
                </div>

                <div className="flex flex-wrap gap-2">
                    {HIGHLIGHT_STATUSES.map((status) => (
                        <Badge key={status} color="info" className="rounded-full px-3 py-1">
                            {t(`global.prosthetics_case_status_${status}`)}: {statusCounts[status] ?? 0}
                        </Badge>
                    ))}
                </div>

                <Card>
                    <div className="mb-4 flex items-center justify-between">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.prosthetics_recent_cases')}
                        </h3>
                        <Link href={urls.cases} className="text-sm text-blue-600 hover:underline">
                            {t('global.view_all')}
                        </Link>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                            <thead className="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th className="px-3 py-2">{t('global.prosthetics_case_number')}</th>
                                    <th className="px-3 py-2">{t('global.patient_name')}</th>
                                    <th className="px-3 py-2">{t('global.status')}</th>
                                    <th className="px-3 py-2" />
                                </tr>
                            </thead>
                            <tbody>
                                {recentCases.map((item) => (
                                    <tr key={item.id} className="border-b border-gray-100 dark:border-gray-700">
                                        <td className="px-3 py-2 font-mono text-sm">{item.case_number}</td>
                                        <td className="px-3 py-2">{item.patient_name || '—'}</td>
                                        <td className="px-3 py-2">
                                            {t(`global.prosthetics_case_status_${item.status}`)}
                                        </td>
                                        <td className="px-3 py-2 text-right">
                                            <Link
                                                href={`${urls.caseShow}/${item.id}`}
                                                className="text-blue-600 hover:underline"
                                            >
                                                {t('global.show')}
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {recentCases.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-3 py-6 text-center text-gray-500">
                                            {t('global.no_records_found')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
