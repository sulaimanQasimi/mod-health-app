import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsPermissions } from '../../types/settings';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

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

export default function ShowLabType({
    labType,
    permissions,
    urls,
}: {
    labType: {
        id: number;
        name: string;
        category_name: string | null;
        department_name: string | null;
        parameters_count: number;
        registrations_count: number;
        created_at: string | null;
        updated_at: string | null;
    };
    permissions: SettingsPermissions;
    urls: { index: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    const handleDelete = () => {
        if (!window.confirm(t('global.are_you_sure'))) return;
        setDeleting(true);
        router.delete(urls.destroy, { onFinish: () => setDeleting(false) });
    };

    return (
        <DashboardLayout>
            <Head title={labType.name} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={labType.name}
                        subtitle={t('global.lab_type')}
                        icon="bx-test-tube"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                        action={
                            <div className="flex gap-2">
                                {permissions.edit && (
                                    <Button color="warning" as={Link} href={urls.edit}>
                                        <i className="bx bx-edit me-2" />
                                        {t('global.edit')}
                                    </Button>
                                )}
                                {permissions.delete && (
                                    <Button color="failure" onClick={handleDelete} disabled={deleting}>
                                        <i className="bx bx-trash me-2" />
                                        {t('global.delete')}
                                    </Button>
                                )}
                            </div>
                        }
                    />

                    <dl className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <DetailField label={t('global.id')} value={String(labType.id)} />
                        <DetailField label={t('global.name')} value={labType.name} />
                        <DetailField label={t('global.category')} value={labType.category_name ?? ''} />
                        <DetailField label={t('global.department')} value={labType.department_name ?? ''} />
                        <DetailField
                            label={t('global.parameters_count')}
                            value={String(labType.parameters_count)}
                        />
                        <DetailField
                            label={t('global.lab_test_registrations')}
                            value={String(labType.registrations_count)}
                        />
                        <DetailField label={t('global.created_at')} value={labType.created_at ?? ''} />
                        <DetailField label={t('global.updated_at')} value={labType.updated_at ?? ''} />
                    </dl>
                </Card>
            </div>
        </DashboardLayout>
    );
}
