import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { ReactNode, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import BackArrowIcon from '../../Components/ui/BackArrowIcon';
import { useTranslation } from '../../hooks/useTranslation';
import { DoctorDetail, DoctorShowPermissions, DoctorShowUrls } from '../../types/doctor';

interface ShowDoctorProps {
    doctor: DoctorDetail;
    permissions: DoctorShowPermissions;
    urls: DoctorShowUrls;
}

interface DetailFieldProps {
    label: string;
    value: string | null | undefined;
    icon?: string;
}

interface SectionCardProps {
    icon: string;
    title: string;
    accent?: 'blue' | 'cyan' | 'violet' | 'amber' | 'emerald' | 'rose';
    action?: ReactNode;
    children: ReactNode;
}

const accentStyles = {
    blue: 'from-blue-500 to-indigo-600',
    cyan: 'from-cyan-500 to-blue-600',
    violet: 'from-violet-500 to-purple-600',
    amber: 'from-amber-500 to-orange-600',
    emerald: 'from-emerald-500 to-teal-600',
    rose: 'from-rose-500 to-pink-600',
};

function DetailField({ label, value, icon }: DetailFieldProps) {
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

function SectionCard({ icon, title, accent = 'blue', action, children }: SectionCardProps) {
    return (
        <Card className="overflow-hidden shadow-sm">
            <div
                className={`flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gradient-to-r ${accentStyles[accent]} px-5 py-3.5 dark:border-gray-700`}
            >
                <h2 className="flex items-center gap-2 text-sm font-semibold text-white">
                    <i className={`bx ${icon} text-lg`} />
                    {title}
                </h2>
                {action}
            </div>
            <div className="p-5">{children}</div>
        </Card>
    );
}

export default function ShowDoctor({ doctor, permissions, urls }: ShowDoctorProps) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    const clinicTypeLabel = () => {
        if (doctor.clinic_type === 'hospital') return t('global.hospital');
        if (doctor.clinic_type === 'clinic') return t('global.clinic');
        return '—';
    };

    const genderLabel = () => {
        if (doctor.gender === 'Male') return t('global.male');
        if (doctor.gender === 'Female') return t('global.female');
        if (doctor.gender === 'Other') return t('global.other');
        return '—';
    };

    const handleDelete = () => {
        if (!window.confirm(`${t('global.are_you_sure')} ${doctor.name}?`)) {
            return;
        }

        setDeleting(true);
        router.delete(urls.destroy, {
            onFinish: () => setDeleting(false),
        });
    };

    return (
        <DashboardLayout>
            <Head title={doctor.name} />

            <div className="mx-auto max-w-7xl space-y-6">
                <Card className="shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-600 text-white shadow-md">
                                <i className="bx bx-user-md text-2xl" />
                            </div>
                            <div>
                                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {doctor.name}
                                </h1>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    <Badge color={doctor.active_status ? 'success' : 'gray'}>
                                        {doctor.active_status
                                            ? t('global.active')
                                            : t('global.inactive') || t('global.deactive')}
                                    </Badge>
                                    {doctor.is_dentist && (
                                        <Badge color="info">{t('global.dentist')}</Badge>
                                    )}
                                    {doctor.is_nephrologist && (
                                        <Badge color="purple">{t('global.nephrology')}</Badge>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button color="light" as={Link} href={urls.index}>
                                <BackArrowIcon className="me-2 text-lg" />
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

                    <dl className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <DetailField label={t('global.father_name')} value={doctor.father_name} icon="bx-user" />
                        <DetailField label={t('global.gender')} value={genderLabel()} icon="bx-male-female" />
                        <DetailField
                            label={t('global.contact_number')}
                            value={doctor.contact_number}
                            icon="bx-phone"
                        />
                        <DetailField
                            label={t('global.specialization')}
                            value={doctor.specialization}
                            icon="bx-briefcase"
                        />
                        <DetailField
                            label={t('global.qualification')}
                            value={doctor.qualification}
                            icon="bx-certification"
                        />
                        <DetailField label={t('global.room_no')} value={doctor.room_no} icon="bx-door-open" />
                        <DetailField
                            label={t('global.clinic_type')}
                            value={clinicTypeLabel()}
                            icon="bx-building"
                        />
                        <DetailField label={t('global.join_date')} value={doctor.join_date} icon="bx-calendar" />
                        <DetailField
                            label={t('global.department')}
                            value={doctor.department_name}
                            icon="bx-buildings"
                        />
                        <DetailField label={t('global.branch')} value={doctor.branch_name} icon="bx-map" />
                    </dl>

                    {doctor.address && (
                        <div className="mt-4 rounded-xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-700/60 dark:bg-gray-800/40">
                            <p className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {t('global.address')}
                            </p>
                            <p className="mt-2 text-sm text-gray-900 dark:text-white">{doctor.address}</p>
                        </div>
                    )}
                </Card>

                <SectionCard icon="bx-user-circle" title={t('global.user')} accent="violet">
                    {doctor.linked_user ? (
                        <div className="space-y-4">
                            <dl className="grid gap-3 sm:grid-cols-2">
                                <DetailField
                                    label={t('global.name')}
                                    value={doctor.linked_user.name}
                                    icon="bx-user"
                                />
                                <DetailField
                                    label={t('global.email')}
                                    value={doctor.linked_user.email}
                                    icon="bx-envelope"
                                />
                            </dl>

                            <div>
                                <h3 className="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    {t('global.roles')}
                                </h3>
                                {doctor.linked_user.roles.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {doctor.linked_user.roles.map((role) => (
                                            <Badge key={role.id} color="failure">
                                                {role.name}
                                            </Badge>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        {t('global.no_results_found')}
                                    </p>
                                )}
                            </div>

                            <div>
                                <h3 className="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    {t('global.permissions')}
                                </h3>
                                {doctor.linked_user.permissions.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {doctor.linked_user.permissions.map((permission) => (
                                            <Badge key={permission.id} color="info">
                                                {permission.name}
                                            </Badge>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        {t('global.no_results_found')}
                                    </p>
                                )}
                            </div>
                        </div>
                    ) : (
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            {t('global.no_results_found')}
                        </p>
                    )}
                </SectionCard>
            </div>
        </DashboardLayout>
    );
}
