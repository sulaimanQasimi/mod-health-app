import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsPermissions } from '../../types/settings';

interface NurseDetail {
    id: number;
    full_name: string;
    first_name: string;
    last_name: string;
    gender: string | null;
    date_of_birth: string | null;
    phone: string | null;
    email: string | null;
    address: string | null;
    employee_id: string;
    department_name: string | null;
    branch_name: string | null;
    specialization: string | null;
    shift: string | null;
    employment_status: string | null;
    date_of_joining: string | null;
    linked_user: { id: number; name: string; email: string } | null;
}

interface ShowNurseProps {
    nurse: NurseDetail;
    permissions: SettingsPermissions;
    urls: { index: string; edit: string; destroy: string };
}

function DetailField({ label, value, icon }: { label: string; value: string; icon?: string }) {
    return (
        <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40">
            <dt className="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {icon && <i className={`bx ${icon} text-sm`} />}
                {label}
            </dt>
            <dd className="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{value || '—'}</dd>
        </div>
    );
}

export default function ShowNurse({ nurse, permissions, urls }: ShowNurseProps) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    const genderLabel = () => {
        if (nurse.gender === 'male') return t('global.male');
        if (nurse.gender === 'female') return t('global.female');
        return '—';
    };

    const shiftLabel = () => {
        if (nurse.shift === 'morning') return t('global.morning_shift');
        if (nurse.shift === 'evening') return t('global.evening_shift');
        if (nurse.shift === 'night') return t('global.night_shift');
        return '—';
    };

    const statusLabel = () => {
        if (nurse.employment_status === 'active') return t('global.active');
        if (nurse.employment_status === 'on_leave') return t('global.on_leave');
        if (nurse.employment_status === 'inactive') return t('global.inactive');
        return '—';
    };

    const handleDelete = () => {
        if (!window.confirm(`${t('global.are_you_sure')} ${nurse.full_name}?`)) return;
        setDeleting(true);
        router.delete(urls.destroy, { onFinish: () => setDeleting(false) });
    };

    return (
        <DashboardLayout>
            <Head title={nurse.full_name} />
            <div className="mx-auto max-w-7xl space-y-6">
                <Card className="shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 text-white shadow-md">
                                <i className="bx bx-user text-2xl" />
                            </div>
                            <div>
                                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {nurse.full_name}
                                </h1>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {nurse.employment_status && (
                                        <Badge
                                            color={
                                                nurse.employment_status === 'active'
                                                    ? 'success'
                                                    : nurse.employment_status === 'on_leave'
                                                      ? 'warning'
                                                      : 'gray'
                                            }
                                        >
                                            {statusLabel()}
                                        </Badge>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button color="light" as={Link} href={urls.index}>
                                <i className="bx bx-arrow-back me-2 text-lg" />
                                {t('global.back')}
                            </Button>
                            {permissions.edit && (
                                <Button color="warning" as={Link} href={urls.edit}>
                                    <i className="bx bx-edit me-2 text-lg" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            {permissions.delete && (
                                <Button color="failure" onClick={handleDelete} disabled={deleting}>
                                    <i className="bx bx-trash me-2 text-lg" />
                                    {t('global.delete')}
                                </Button>
                            )}
                        </div>
                    </div>

                    <div className="mt-6">
                        <h2 className="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {t('global.user_account')}
                        </h2>
                        {nurse.linked_user ? (
                            <dl className="grid gap-3 sm:grid-cols-2">
                                <DetailField
                                    label={t('global.name')}
                                    value={nurse.linked_user.name}
                                    icon="bx-user"
                                />
                                <DetailField
                                    label={t('global.email')}
                                    value={nurse.linked_user.email}
                                    icon="bx-envelope"
                                />
                            </dl>
                        ) : (
                            <p className="text-sm text-gray-500">{t('global.no_user_account')}</p>
                        )}
                    </div>

                    <dl className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <DetailField label={t('global.gender')} value={genderLabel()} icon="bx-male-female" />
                        <DetailField
                            label={t('global.date_of_birth')}
                            value={nurse.date_of_birth ?? ''}
                            icon="bx-calendar"
                        />
                        <DetailField label={t('global.phone')} value={nurse.phone ?? ''} icon="bx-phone" />
                        <DetailField label={t('global.email')} value={nurse.email ?? ''} icon="bx-envelope" />
                        <DetailField
                            label={t('global.employee_id')}
                            value={nurse.employee_id}
                            icon="bx-id-card"
                        />
                        <DetailField
                            label={t('global.department')}
                            value={nurse.department_name ?? ''}
                            icon="bx-buildings"
                        />
                        <DetailField label={t('global.branch')} value={nurse.branch_name ?? ''} icon="bx-map" />
                        <DetailField
                            label={t('global.specialization')}
                            value={nurse.specialization ?? ''}
                            icon="bx-briefcase"
                        />
                        <DetailField label={t('global.shift')} value={shiftLabel()} icon="bx-time" />
                        <DetailField
                            label={t('global.date_of_joining')}
                            value={nurse.date_of_joining ?? ''}
                            icon="bx-calendar-check"
                        />
                    </dl>

                    {nurse.address && (
                        <div className="mt-4 rounded-xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-700/60 dark:bg-gray-800/40">
                            <p className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {t('global.address')}
                            </p>
                            <p className="mt-2 text-sm text-gray-900 dark:text-white">{nurse.address}</p>
                        </div>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
