import { Head, Link } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

interface ShowVitalSignProps {
    vitalSign: {
        id: number;
        vital_sign_type_name: string | null;
        morphable_type: string | null;
        morphable_id: number | null;
        morphable_label: string | null;
        morphable_url: string | null;
        schedules_count: number;
        created_at: string | null;
        updated_at: string | null;
        created_by_name: string | null;
    };
    urls: { index: string };
}

function DetailField({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40">
            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {label}
            </dt>
            <dd className="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{value || '—'}</dd>
        </div>
    );
}

export default function ShowVitalSign({ vitalSign, urls }: ShowVitalSignProps) {
    const { t } = useTranslation();
    const title = `${t('global.vital_sign')} #${vitalSign.id}`;

    return (
        <DashboardLayout>
            <Head title={title} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={title}
                        subtitle={vitalSign.vital_sign_type_name ?? t('global.vital_signs')}
                        icon="bx-heart-circle"
                        accent="from-red-500 to-orange-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />

                    {vitalSign.vital_sign_type_name && (
                        <div className="mb-6">
                            <Badge color="info">{vitalSign.vital_sign_type_name}</Badge>
                        </div>
                    )}

                    <dl className="grid gap-3 sm:grid-cols-2">
                        <DetailField label={t('global.id')} value={String(vitalSign.id)} />
                        <DetailField
                            label={t('global.vital_sign_type')}
                            value={vitalSign.vital_sign_type_name ?? ''}
                        />
                        <DetailField
                            label={t('global.morphable_type')}
                            value={vitalSign.morphable_type ?? ''}
                        />
                        <DetailField
                            label={t('global.morphable_id')}
                            value={vitalSign.morphable_id ? String(vitalSign.morphable_id) : ''}
                        />
                        <DetailField
                            label={t('global.schedules')}
                            value={String(vitalSign.schedules_count)}
                        />
                        <DetailField label={t('global.created_at')} value={vitalSign.created_at ?? ''} />
                        <DetailField label={t('global.updated_at')} value={vitalSign.updated_at ?? ''} />
                        <DetailField
                            label={t('global.created_by')}
                            value={vitalSign.created_by_name ?? ''}
                        />
                    </dl>

                    <div className="mt-6">
                        <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {t('global.related_record')}
                        </p>
                        {vitalSign.morphable_label ? (
                            vitalSign.morphable_url ? (
                                <Button color="light" as={Link} href={vitalSign.morphable_url} className="w-fit">
                                    {vitalSign.morphable_label}
                                </Button>
                            ) : (
                                <p className="text-sm font-medium text-gray-900 dark:text-white">
                                    {vitalSign.morphable_label}
                                </p>
                            )
                        ) : (
                            <p className="text-sm text-gray-500">—</p>
                        )}
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
