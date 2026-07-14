import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button } from 'flowbite-react';
import { ReactNode, useState } from 'react';
import CreateAppointmentModal from '../../Components/Patients/CreateAppointmentModal';
import ForeignCountryReferralSection from '../../Components/Patients/Sections/ForeignCountryReferralSection';
import PatientQrCode from '../../Components/Patients/PatientQrCode';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import BackLink from '../../Components/ui/BackLink';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HemodialysisSessionItem,
    NephrologyRegistrationItem,
    PatientAppointmentItem,
    PatientDetail,
    PatientDiagnosesGroup,
    PatientDiagnosisItem,
    PatientShowAppointmentForm,
    PatientShowPermissions,
    PatientShowUrls,
} from '../../types/patient';

interface ShowPatientProps {
    patient: PatientDetail;
    appointments: PatientAppointmentItem[];
    diagnoses: PatientDiagnosesGroup;
    nephrologyRegistrations: NephrologyRegistrationItem[];
    hemodialysisSessions: HemodialysisSessionItem[];
    appointmentForm: PatientShowAppointmentForm;
    permissions: PatientShowPermissions;
    urls: PatientShowUrls;
}

interface DetailFieldProps {
    label: string;
    value: string | null | undefined;
    icon?: string;
}

interface SectionCardProps {
    title: string;
    action?: ReactNode;
    children: ReactNode;
}

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

function displayValue(value: string | number | null | undefined) {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return String(value);
}

function DetailField({ label, value, icon }: DetailFieldProps) {
    return (
        <div className="min-w-0 rounded-md border border-gray-200/80 px-2.5 py-2 dark:border-gray-700/80">
            <p className="mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">{label}</p>
            <p className="flex min-w-0 items-start gap-1.5 text-sm text-gray-900 dark:text-white">
                {icon && <i className={`bx ${icon} mt-0.5 shrink-0 text-base text-cyan-500`} />}
                <span className="break-words">{displayValue(value)}</span>
            </p>
        </div>
    );
}

function SectionCard({ title, action, children }: SectionCardProps) {
    return (
        <section className="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-3 dark:border-gray-700">
                <h2 className="text-sm font-semibold text-gray-900 dark:text-white">{title}</h2>
                {action}
            </div>
            <div className="min-w-0 p-4">{children}</div>
        </section>
    );
}

function DiagnosisList({
    items,
    variant,
}: {
    items: PatientDiagnosisItem[];
    variant: 'primary' | 'final';
}) {
    const { t } = useTranslation();
    const isPrimary = variant === 'primary';

    if (items.length === 0) {
        return (
            <p className="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                {t('global.no_results_found')}
            </p>
        );
    }

    return (
        <ul className="space-y-2">
            {items.map((item) => (
                <li
                    key={item.id}
                    className={mergeClasses(
                        'flex flex-wrap items-start gap-2 rounded-lg border p-3 text-sm',
                        isPrimary
                            ? 'border-amber-200/80 bg-amber-50/50 dark:border-amber-900/30 dark:bg-amber-950/20'
                            : 'border-emerald-200/80 bg-emerald-50/50 dark:border-emerald-900/30 dark:bg-emerald-950/20',
                    )}
                >
                    <Badge color={isPrimary ? 'warning' : 'success'} className="shrink-0">
                        {item.date}
                    </Badge>
                    <span className="text-gray-800 dark:text-gray-200">{item.description}</span>
                </li>
            ))}
        </ul>
    );
}

function EmptyTableRow({ colSpan, message }: { colSpan: number; message: string }) {
    return (
        <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
            <TableCell colSpan={colSpan} align="center" muted className="py-8">
                {message}
            </TableCell>
        </TableRow>
    );
}

export default function ShowPatient({
    patient,
    appointments,
    diagnoses,
    nephrologyRegistrations,
    hemodialysisSessions,
    appointmentForm,
    permissions,
    urls,
}: ShowPatientProps) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);
    const [appointmentModalOpen, setAppointmentModalOpen] = useState(false);

    const fullName = [patient.name, patient.last_name].filter(Boolean).join(' ');

    const genderLabel =
        patient.gender === 0 || patient.gender === '0'
            ? t('global.male')
            : patient.gender === 1 || patient.gender === '1'
              ? t('global.female')
              : '—';

    const jobCategoryLabel =
        patient.job_category === 0 || patient.job_category === '0'
            ? t('global.military')
            : patient.job_category === 1 || patient.job_category === '1'
              ? t('global.civilian')
              : '—';

    const handleDelete = () => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        setDeleting(true);
        router.delete(urls.destroy, {
            onFinish: () => setDeleting(false),
        });
    };

    const statusLabel = (status: string) => {
        const key = `global.${status}`;
        const translated = t(key);
        return translated === key ? status : translated;
    };

    return (
        <DashboardLayout>
            <Head title={t('global.view_patient')} />

            <div className="mx-auto min-w-0 max-w-7xl space-y-6">
                <div className="rounded-xl border border-gray-200 bg-white px-5 py-5 dark:border-gray-700 dark:bg-gray-800">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div className="flex min-w-0 items-start gap-4">
                            <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700">
                                {patient.image ? (
                                    <img src={patient.image} alt={fullName} className="h-full w-full object-cover" />
                                ) : (
                                    <i className="bx bx-user text-2xl text-gray-400" />
                                )}
                            </div>
                            <div className="min-w-0">
                                <p className="text-xs font-medium text-gray-500 dark:text-gray-400">#{patient.id}</p>
                                <h1 className="truncate text-xl font-semibold text-gray-900 dark:text-white">{fullName}</h1>
                                <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                                    {patient.id_card && (
                                        <span className="inline-flex items-center gap-1">
                                            <i className="bx bx-id-card" />
                                            {patient.id_card}
                                        </span>
                                    )}
                                    {patient.phone && (
                                        <span className="inline-flex items-center gap-1">
                                            <i className="bx bx-phone" />
                                            {patient.phone}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="flex shrink-0 flex-wrap gap-2">
                            <BackLink href={urls.index}>{t('global.back')}</BackLink>
                            {permissions.edit && (
                                <Link href={urls.edit}>
                                    <Button color="warning">
                                        <i className="bx bx-edit me-2 text-lg" />
                                        {t('global.edit')}
                                    </Button>
                                </Link>
                            )}
                            {permissions.delete && (
                                <Button color="failure" outline disabled={deleting} onClick={handleDelete}>
                                    <i className="bx bx-trash me-2 text-lg" />
                                    {deleting ? t('global.deleting') : t('global.delete')}
                                </Button>
                            )}
                        </div>
                    </div>
                </div>

                <div className="min-w-0 space-y-6">
                    <SectionCard title={t('global.personal_information')}>
                        <div className="grid min-w-0 gap-4 lg:grid-cols-[minmax(0,1fr)_240px]">
                            <div className="grid gap-x-4 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
                                <DetailField label={t('global.patient_name')} value={patient.name} icon="bx-user" />
                                <DetailField label={t('global.last_name')} value={patient.last_name} icon="bx-user" />
                                <DetailField label={t('global.father_name')} value={patient.father_name} icon="bx-user" />
                                <DetailField label={t('global.nid')} value={patient.nid} icon="bx-id-card" />
                                <DetailField label={t('global.phone')} value={patient.phone} icon="bx-phone" />
                                <DetailField label={t('global.age')} value={patient.age} icon="bx-calendar" />
                                <DetailField label={t('global.gender')} value={genderLabel} icon="bx-male-female" />
                                <DetailField label={t('global.job')} value={patient.job} icon="bx-briefcase" />
                                <DetailField label={t('global.job_category')} value={jobCategoryLabel} icon="bx-group" />
                                <DetailField label={t('global.rank')} value={patient.rank} icon="bx-medal" />
                                <DetailField label={t('global.militery_type')} value={patient.militery_type} icon="bx-shield" />
                                <DetailField label={t('global.province')} value={patient.province} icon="bx-map" />
                                <DetailField label={t('global.district')} value={patient.district} icon="bx-map-pin" />
                                <DetailField label={t('global.referred_by')} value={patient.referred_by} icon="bx-user-plus" />
                                <DetailField label={t('global.commanded_by')} value={patient.commanded_by} icon="bx-user-voice" />
                                <DetailField label={t('global.creation_date')} value={patient.created_at} icon="bx-time" />
                                <DetailField label={t('global.created_by')} value={patient.created_by} icon="bx-user-check" />
                            </div>

                            <aside className="min-w-0 space-y-4">
                                <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-700/60 dark:bg-gray-800/40">
                                    <div className="space-y-4">
                                        <div className="text-center">
                                            <p className="mb-2 text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                {t('global.qr_code')}
                                            </p>
                                            <div className="flex justify-center">
                                                <PatientQrCode patientId={patient.id} size={96} />
                                            </div>
                                        </div>

                                        <div className="border-t border-gray-200 pt-4 dark:border-gray-700">
                                            <p className="mb-2 text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                {t('global.patient_image')}
                                            </p>
                                            <div className="mx-auto flex aspect-square max-w-[160px] items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700/50">
                                                {patient.image ? (
                                                    <img
                                                        src={patient.image}
                                                        alt={fullName}
                                                        className="h-full w-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="flex flex-col items-center gap-2 text-gray-400">
                                                        <i className="bx bx-user text-5xl" />
                                                        <span className="text-xs">{t('global.no_image')}</span>
                                                    </div>
                                                )}
                                            </div>
                                        </div>

                                        <div className="flex flex-col gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                                            {permissions.printCard && (
                                                <a
                                                    href={urls.printCard}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="block"
                                                >
                                                    <Button size="sm" color="blue" className="w-full">
                                                        <i className="bx bx-printer me-2" />
                                                        {t('global.print_card')}
                                                    </Button>
                                                </a>
                                            )}
                                            {permissions.createAppointment && (
                                                <Button
                                                    size="sm"
                                                    color="blue"
                                                    className="w-full"
                                                    onClick={() => setAppointmentModalOpen(true)}
                                                >
                                                    <i className="bx bx-calendar-plus me-2" />
                                                    {t('global.assign_appointment')}
                                                </Button>
                                            )}
                                            {permissions.uploadImage && (
                                                <a href={urls.webcam} className="block">
                                                    <Button size="sm" color="success" className="w-full">
                                                        <i className="bx bx-camera me-2" />
                                                        {t('global.take_image')}
                                                    </Button>
                                                </a>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </SectionCard>

                    {(patient.referral_name || patient.referral_nid) && (
                        <SectionCard title={t('global.referred_person')}>
                            <div className="grid gap-x-4 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
                                <DetailField label={t('global.name')} value={patient.referral_name} icon="bx-user" />
                                <DetailField label={t('global.last_name')} value={patient.referral_last_name} icon="bx-user" />
                                <DetailField label={t('global.father_name')} value={patient.referral_father_name} icon="bx-user" />
                                <DetailField label={t('global.nid')} value={patient.referral_nid} icon="bx-id-card" />
                                <DetailField label={t('global.id_card')} value={patient.referral_id_card} icon="bx-id-card" />
                                <DetailField label={t('global.phone')} value={patient.referral_phone} icon="bx-phone" />
                                <DetailField label={t('global.relation')} value={patient.relation} icon="bx-link" />
                            </div>
                        </SectionCard>
                    )}

                    <SectionCard title={t('global.previous_appointments')}>
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.doctor_name')}</TableHeader>
                                    <TableHeader>{t('global.date')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {appointments.length === 0 ? (
                                    <EmptyTableRow colSpan={3} message={t('global.no_results_found')} />
                                ) : (
                                    appointments.map((appointment) => (
                                        <TableRow key={appointment.id}>
                                            <TableCell className="font-medium">{appointment.number}</TableCell>
                                            <TableCell>{displayValue(appointment.doctor_name)}</TableCell>
                                            <TableCell muted>{appointment.date}</TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </SectionCard>

                        {/* {permissions.nephrology && (
                            <SectionCard
                                title={t('global.nephrology_history')}
                                action={
                                    <Button size="xs" color="light" as="a" href={urls.hemodialysisCreate}>
                                        <i className="bx bx-plus me-1" />
                                        {t('global.add_hemodialysis_session')}
                                    </Button>
                                }
                            >
                                <h3 className="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {t('global.nephrology_registrations')}
                                </h3>
                                <div className="mb-6 min-w-0">
                                    <Table>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.ref_no')}</TableHeader>
                                                <TableHeader>{t('global.visit_date')}</TableHeader>
                                                <TableHeader>{t('global.doctor')}</TableHeader>
                                                <TableHeader>{t('global.diagnosis')}</TableHeader>
                                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {nephrologyRegistrations.length === 0 ? (
                                                <EmptyTableRow
                                                    colSpan={5}
                                                    message={t('global.no_registrations_found')}
                                                />
                                            ) : (
                                                nephrologyRegistrations.map((registration) => (
                                                    <TableRow key={registration.id}>
                                                        <TableCell className="font-medium">
                                                            {displayValue(registration.ref_no)}
                                                        </TableCell>
                                                        <TableCell muted>{displayValue(registration.visit_date)}</TableCell>
                                                        <TableCell>{displayValue(registration.doctor_name)}</TableCell>
                                                        <TableCell className="max-w-[200px] truncate">
                                                            {displayValue(registration.diagnosis)}
                                                        </TableCell>
                                                        <TableCell align="center">
                                                            <a
                                                                href={registration.show_url}
                                                                className="inline-flex h-8 w-8 items-center justify-center rounded-md text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                            >
                                                                <i className="bx bx-show text-lg" />
                                                            </a>
                                                        </TableCell>
                                                    </TableRow>
                                                ))
                                            )}
                                        </TableBody>
                                    </Table>
                                </div>

                                <h3 className="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {t('global.hemodialysis_sessions')}
                                </h3>
                                <div className="min-w-0">
                                    <Table>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.ref_no')}</TableHeader>
                                                <TableHeader>{t('global.session_date')}</TableHeader>
                                                <TableHeader>{t('global.duration_minutes')}</TableHeader>
                                                <TableHeader>{t('global.status')}</TableHeader>
                                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {hemodialysisSessions.length === 0 ? (
                                                <EmptyTableRow
                                                    colSpan={5}
                                                    message={t('global.no_hemodialysis_sessions_found')}
                                                />
                                            ) : (
                                                hemodialysisSessions.map((session) => (
                                                    <TableRow key={session.id}>
                                                        <TableCell className="font-medium">
                                                            {displayValue(session.ref_no)}
                                                        </TableCell>
                                                        <TableCell muted>{displayValue(session.session_date)}</TableCell>
                                                        <TableCell>{displayValue(session.duration_minutes)}</TableCell>
                                                        <TableCell>
                                                            <Badge color="info">{statusLabel(session.status)}</Badge>
                                                        </TableCell>
                                                        <TableCell align="center">
                                                            <a
                                                                href={session.show_url}
                                                                className="inline-flex h-8 w-8 items-center justify-center rounded-md text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                            >
                                                                <i className="bx bx-show text-lg" />
                                                            </a>
                                                        </TableCell>
                                                    </TableRow>
                                                ))
                                            )}
                                        </TableBody>
                                    </Table>
                                </div>

                                {hemodialysisSessions.length > 0 && (
                                    <div className="mt-4">
                                        <Button color="light" size="sm" as="a" href={urls.hemodialysisIndex}>
                                            {t('global.view_all_hemodialysis_sessions')}
                                        </Button>
                                    </div>
                                )}
                            </SectionCard>
                        )} */}

                        <SectionCard title={t('global.all_diagnoses')}>
                            <div className="grid gap-6 lg:grid-cols-2">
                                <div className="min-w-0">
                                    <h3 className="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-center text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                                        {t('global.primary_diagnoses')}
                                    </h3>
                                    <DiagnosisList items={diagnoses.primary} variant="primary" />
                                </div>
                                <div className="min-w-0">
                                    <h3 className="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">
                                        {t('global.final_diagnoses')}
                                    </h3>
                                    <DiagnosisList items={diagnoses.final} variant="final" />
                                </div>
                            </div>
                        </SectionCard>

                        {permissions.foreignCountryReferral && (
                            <ForeignCountryReferralSection patientId={patient.id} />
                        )}
                </div>
            </div>

            {permissions.createAppointment && (
                <CreateAppointmentModal
                    open={appointmentModalOpen}
                    onClose={() => setAppointmentModalOpen(false)}
                    patient={patient}
                    formData={appointmentForm}
                    urls={{
                        appointmentStore: urls.appointmentStore,
                        doctorsByDepartment: urls.doctorsByDepartment,
                    }}
                    onCreated={() => router.reload({ only: ['appointments'] })}
                />
            )}
        </DashboardLayout>
    );
}
