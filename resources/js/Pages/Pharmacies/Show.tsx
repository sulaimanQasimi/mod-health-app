import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_WIDE_FORM_WIDTH, settingsHeaderButtonClass } from '../../utils/settingsUi';

interface PharmacyUser {
    id: number;
    full_name: string;
    email: string;
    role: string;
    joined_at: string | null;
}

export default function ShowPharmacy({
    pharmacy,
    statistics,
    permissions,
    urls,
}: {
    pharmacy: {
        id: number;
        name: string;
        phone: string;
        address: string;
        created_at: string | null;
        updated_at: string | null;
        created_by_name: string | null;
        users: PharmacyUser[];
    };
    statistics: {
        total_users: number;
        managers_count: number;
        staff_count: number;
        total_outcomes: number;
    };
    permissions: { edit: boolean; delete: boolean; manage_users: boolean };
    urls: { index: string; edit: string; destroy: string; manageUsers: string };
}) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    const roleLabel = (role: string) => {
        if (role === 'manager') return t('global.manager');
        if (role === 'staff') return t('global.staff');
        if (role === 'procurement') return t('global.procurement');
        if (role === 'viewer') return t('global.viewer');
        return role;
    };

    const roleColor = (role: string) => {
        if (role === 'manager') return 'failure';
        if (role === 'staff') return 'info';
        if (role === 'procurement') return 'warning';
        return 'gray';
    };

    return (
        <DashboardLayout>
            <Head title={pharmacy.name} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH} space-y-6`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={pharmacy.name}
                        subtitle={t('global.pharmacy_details')}
                        icon="bx-clinic"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                        action={
                            <SettingsPageActions>
                                {permissions.manage_users && (
                                    <Button
                                        as={Link}
                                        href={urls.manageUsers}
                                        size="sm"
                                        className={settingsHeaderButtonClass.secondary}
                                    >
                                        <i className="bx bx-user-plus me-2" />
                                        {t('global.manage_users')}
                                    </Button>
                                )}
                                {permissions.edit && (
                                    <Button
                                        as={Link}
                                        href={urls.edit}
                                        size="sm"
                                        className={settingsHeaderButtonClass.warning}
                                    >
                                        <i className="bx bx-edit me-2" />
                                        {t('global.edit')}
                                    </Button>
                                )}
                                {permissions.delete && (
                                    <Button
                                        size="sm"
                                        disabled={deleting}
                                        className={settingsHeaderButtonClass.danger}
                                        onClick={() => {
                                            if (window.confirm(t('global.are_you_sure_delete_pharmacy'))) {
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
                            </SettingsPageActions>
                        }
                    />

                    <dl className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {[
                            [t('global.pharmacy_name'), pharmacy.name],
                            [t('global.pharmacy_phone'), pharmacy.phone],
                            [t('global.pharmacy_address'), pharmacy.address],
                            [t('global.created_at'), pharmacy.created_at ?? '—'],
                            [t('global.updated_at'), pharmacy.updated_at ?? '—'],
                            [t('global.created_by'), pharmacy.created_by_name ?? '—'],
                        ].map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40"
                            >
                                <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {label}
                                </dt>
                                <dd className="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">
                                    {value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </Card>

                <Card className="shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.pharmacy_statistics')}
                    </h2>
                    <dl className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        {[
                            [t('global.total_users'), String(statistics.total_users)],
                            [t('global.managers'), String(statistics.managers_count)],
                            [t('global.staff'), String(statistics.staff_count)],
                            [t('global.pharmacy_outcome'), String(statistics.total_outcomes)],
                        ].map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40"
                            >
                                <dt className="text-xs font-semibold uppercase text-gray-500">{label}</dt>
                                <dd className="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{value}</dd>
                            </div>
                        ))}
                    </dl>
                </Card>

                <Card className="shadow-sm">
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.pharmacy_users')}
                        </h2>
                        {permissions.manage_users && (
                            <Button color="light" size="sm" as={Link} href={urls.manageUsers}>
                                {t('global.manage_users')}
                            </Button>
                        )}
                    </div>
                    {pharmacy.users.length > 0 ? (
                        <div className="grid gap-3 md:grid-cols-2">
                            {pharmacy.users.map((user) => (
                                <div
                                    key={user.id}
                                    className="flex items-start gap-3 rounded-xl border border-gray-100 p-3 dark:border-gray-700"
                                >
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        {user.full_name.charAt(0).toUpperCase()}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="font-medium text-gray-900 dark:text-white">
                                            {user.full_name}
                                        </p>
                                        <p className="text-sm text-gray-500">{user.email}</p>
                                        <Badge color={roleColor(user.role)} className="mt-2">
                                            {roleLabel(user.role)}
                                        </Badge>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-sm text-gray-500">{t('global.no_users_assigned')}</p>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
