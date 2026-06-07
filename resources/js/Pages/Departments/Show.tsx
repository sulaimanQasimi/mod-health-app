import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';

export default function ShowDepartment({
    department,
    permissions,
    urls,
}: {
    department: {
        id: number;
        name: string;
        room_number: string | null;
        category_name: string | null;
        created_at: string | null;
        updated_at: string | null;
    };
    permissions: { edit: boolean; delete: boolean };
    urls: { index: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    return (
        <DashboardLayout>
            <Head title={department.name} />
            <div className="mx-auto max-w-3xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.departments')}
                        subtitle={department.name}
                        icon="bx-buildings"
                        accent="from-blue-500 to-indigo-600"
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
                                    <Button
                                        color="failure"
                                        disabled={deleting}
                                        onClick={() => {
                                            if (window.confirm(t('global.are_you_sure'))) {
                                                setDeleting(true);
                                                router.delete(urls.destroy, {
                                                    onFinish: () => setDeleting(false),
                                                });
                                            }
                                        }}
                                    >
                                        <i className="bx bx-trash me-2" />
                                        {t('global.delete')}
                                    </Button>
                                )}
                            </div>
                        }
                    />
                    <dl className="grid gap-4 sm:grid-cols-2">
                        {[
                            [t('global.name'), department.name],
                            [t('global.room_number'), department.room_number ?? '—'],
                            [t('global.category'), department.category_name ?? '—'],
                            [t('global.created_at'), department.created_at ?? '—'],
                            [t('global.updated_at'), department.updated_at ?? '—'],
                        ].map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700 dark:bg-gray-800/40"
                            >
                                <dt className="text-xs font-semibold uppercase text-gray-500">{label}</dt>
                                <dd className="mt-1 text-sm font-medium text-gray-900 dark:text-white">{value}</dd>
                            </div>
                        ))}
                    </dl>
                </Card>
            </div>
        </DashboardLayout>
    );
}
