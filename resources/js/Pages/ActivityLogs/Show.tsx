import { Head, Link } from '@inertiajs/react';
import { Badge, Card } from 'flowbite-react';
import { ReactNode } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ActivityDetail {
    id: number;
    description: string;
    event: string;
    event_label: string;
    log_name: string;
    subject_type: string | null;
    subject_type_full: string | null;
    subject_id: number | null;
    causer: { id: number; name: string; email: string } | null;
    created_at: string | null;
    properties: Record<string, unknown>;
}

export default function ActivityLogsShow({
    activity,
    urls,
}: {
    activity: ActivityDetail;
    urls: { index: string };
}) {
    const { t } = useTranslation();

    const oldValues = (activity.properties?.old as Record<string, unknown> | undefined) ?? {};
    const newValues = (activity.properties?.attributes as Record<string, unknown> | undefined) ?? activity.properties ?? {};

    return (
        <DashboardLayout>
            <Head title={t('activity_log.details')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('activity_log.details')}
                        subtitle={`#${activity.id}`}
                        icon="bx-history"
                        accent="from-violet-500 to-purple-600"
                        backLabel={t('global.back')}
                        backHref={urls.index}
                    />

                    <div className="grid gap-4 md:grid-cols-2">
                        <DetailRow label={t('activity_log.description')} value={activity.description} />
                        <DetailRow label={t('activity_log.event')} value={<Badge color="purple">{activity.event_label}</Badge>} />
                        <DetailRow
                            label={t('activity_log.subject')}
                            value={activity.subject_type ? `${activity.subject_type} #${activity.subject_id}` : '—'}
                        />
                        <DetailRow
                            label={t('activity_log.causer')}
                            value={activity.causer ? `${activity.causer.name} (${activity.causer.email})` : t('activity_log.system')}
                        />
                        <DetailRow label={t('activity_log.created_at')} value={activity.created_at ?? '—'} />
                        <DetailRow label={t('activity_log.subject_type')} value={activity.subject_type_full ?? '—'} />
                    </div>

                    <div className="mt-8 grid gap-6 md:grid-cols-2">
                        <PropertiesBlock title={t('activity_log.old_values')} data={oldValues} />
                        <PropertiesBlock title={t('activity_log.new_values')} data={newValues} />
                    </div>

                    <div className="mt-6">
                        <Link href={urls.index} className="text-sm font-medium text-blue-600 hover:underline">
                            {t('global.back')}
                        </Link>
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}

function DetailRow({ label, value }: { label: string; value: ReactNode }) {
    return (
        <div className="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <div className="text-sm text-gray-500 dark:text-gray-400">{label}</div>
            <div className="mt-1 text-sm font-medium text-gray-900 dark:text-white">{value}</div>
        </div>
    );
}

function PropertiesBlock({ title, data }: { title: string; data: Record<string, unknown> }) {
    const entries = Object.entries(data);

    return (
        <div className="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <h3 className="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{title}</h3>
            {entries.length === 0 ? (
                <p className="text-sm text-gray-500">—</p>
            ) : (
                <pre className="max-h-96 overflow-auto rounded bg-gray-900 p-4 text-xs text-green-200">
                    {JSON.stringify(data, null, 2)}
                </pre>
            )}
        </div>
    );
}
