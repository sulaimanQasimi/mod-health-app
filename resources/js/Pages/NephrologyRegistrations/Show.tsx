import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { ReactNode, useState } from 'react';
import DiagnosisSection from '../../Components/Appointments/Sections/DiagnosisSection';
import LabTestSection from '../../Components/Appointments/Sections/LabTestSection';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import NephrologyClinicalForm, {
    NephrologyClinicalFormData,
} from '../../Components/NephrologyRegistrations/NephrologyClinicalForm';
import NephrologyRegistrationStatusBadge from '../../Components/NephrologyRegistrations/NephrologyRegistrationStatusBadge';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
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
    NephrologyRegistrationDetail,
    NephrologyRegistrationFormOptions,
    NephrologyRegistrationShowPermissions,
} from '../../types/nephrologyRegistration';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActions } from '../../Components/ui/TableActions';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import HemodialysisSessionStatusBadge from '../../Components/HemodialysisSessions/HemodialysisSessionStatusBadge';

interface ShowNephrologyRegistrationProps {
    registration: NephrologyRegistrationDetail;
    formOptions: NephrologyRegistrationFormOptions | null;
    permissions: NephrologyRegistrationShowPermissions;
    urls: Record<string, string | null>;
}

type TabKey = 'clinical' | 'diagnose' | 'lab' | 'prescription' | 'hemodialysis';

export default function ShowNephrologyRegistration({
    registration,
    formOptions,
    permissions,
    urls,
}: ShowNephrologyRegistrationProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [activeTab, setActiveTab] = useState<TabKey>('clinical');

    const hasAppointment = Boolean(registration.appointment_id);

    const tabs: Array<{ key: TabKey; label: string; icon: string; count?: number; hidden?: boolean }> = [
        { key: 'clinical', label: t('global.nephrology_clinical_record'), icon: 'bx-clipboard' },
        {
            key: 'diagnose',
            label: t('global.diagnose'),
            icon: 'bx-pulse',
            count: registration.counts.diagnoses,
            hidden: !hasAppointment,
        },
        {
            key: 'lab',
            label: t('global.lab_test_registrations'),
            icon: 'bx-test-tube',
            count: registration.counts.lab_tests,
            hidden: !hasAppointment,
        },
        {
            key: 'prescription',
            label: t('global.prescription'),
            icon: 'bx-notepad',
            count: registration.counts.prescriptions,
            hidden: !hasAppointment,
        },
        {
            key: 'hemodialysis',
            label: t('global.hemodialysis_sessions'),
            icon: 'bx-water',
            count: registration.counts.hemodialysis,
        },
    ];

    const runAction = (callback: () => void) => {
        setProcessing(true);
        callback();
    };

    const finishAction = () => setProcessing(false);

    const postStatus = (url: string | null | undefined) => {
        if (!url) return;
        runAction(() => router.post(url, {}, { preserveScroll: true, onFinish: finishAction }));
    };

    const handleUpdate = (data: NephrologyClinicalFormData) => {
        if (!urls.update) return;

        runAction(() =>
            router.put(
                urls.update,
                {
                    doctor_id: data.doctor_id ? Number(data.doctor_id) : null,
                    visit_date: data.visit_date,
                    status: data.status,
                    chief_complaint: data.chief_complaint || null,
                    disease_id: data.disease_id ? Number(data.disease_id) : null,
                    ckd_aki_stage: data.ckd_aki_stage || null,
                    dialysis_required: data.dialysis_required,
                    dialysis_type: data.dialysis_required ? data.dialysis_type || null : null,
                    access_type: data.dialysis_required ? data.access_type || null : null,
                    notes: data.notes || null,
                    follow_up_plan: data.follow_up_plan || null,
                },
                { preserveScroll: true, onFinish: finishAction },
            ),
        );
    };

    const handleDelete = () => {
        if (!urls.destroy || !window.confirm(t('global.confirm_delete'))) return;
        runAction(() => router.delete(urls.destroy!, { onFinish: finishAction }));
    };

    const handleCancel = () => {
        if (!urls.cancel || !window.confirm(t('global.confirm_cancel_registration'))) return;
        postStatus(urls.cancel);
    };

    const diseaseLabel = registration.disease_name
        ? registration.disease_category_name
            ? `${registration.disease_category_name} — ${registration.disease_name}`
            : registration.disease_name
        : '—';

    const infoCards: Array<[string, ReactNode]> = [
        [t('global.patient_name'), registration.patient_name ?? '—'],
        [t('global.doctor'), registration.doctor_name ?? t('global.not_available')],
        [t('global.visit_date'), <span dir="ltr">{registration.visit_date ?? '—'}</span>],
        [t('global.diseases'), diseaseLabel],
        [t('global.status'), <NephrologyRegistrationStatusBadge status={registration.status} />],
    ];

    return (
        <DashboardLayout>
            <Head title={t('global.nephrology_visit')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.nephrology_visit')}
                    subtitle={`${t('global.ref_no')}: ${registration.ref_no ?? '—'}`}
                    icon="bx-droplet"
                    accent="from-cyan-500 to-blue-600"
                    backHref={urls.index ?? undefined}
                    backLabel={t('global.back')}
                    action={
                        permissions.delete ? (
                            <Button color="failure" size="sm" onClick={handleDelete} disabled={processing}>
                                <i className="bx bx-trash me-2" />
                                {t('global.delete')}
                            </Button>
                        ) : undefined
                    }
                />

                <Card className="shadow-sm">
                    <h2 className="mb-4 text-center text-sm font-semibold text-gray-900 dark:text-white">
                        {t('global.registration_information')}
                    </h2>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        {infoCards.map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50 p-4 text-center dark:border-gray-700 dark:bg-gray-800/40"
                            >
                                <p className="text-xs text-gray-500">{label}</p>
                                <div className="mt-2 font-semibold text-gray-900 dark:text-white">{value}</div>
                            </div>
                        ))}
                    </div>

                    {permissions.markStatus && (
                        <div className="mt-4 flex flex-wrap justify-end gap-2 dark:text-white">
                            {registration.status !== 'completed' && (
                                <Button
                                    size="sm"
                                    color="success"
                                    disabled={processing}
                                    onClick={() => postStatus(urls.markCompleted)}
                                >
                                    {t('global.mark_completed')}
                                </Button>
                            )}
                            {registration.status !== 'in_progress' && registration.status !== 'completed' && (
                                <Button
                                    size="sm"
                                    color="info"
                                    disabled={processing}
                                    onClick={() => postStatus(urls.markInProgress)}
                                >
                                    {t('global.mark_in_progress')}
                                </Button>
                            )}
                            {registration.status !== 'cancelled' && registration.status !== 'completed' && (
                                <Button
                                    size="sm"
                                    color="failure"
                                    outline
                                    disabled={processing}
                                    onClick={handleCancel}
                                >
                                    {t('global.cancel')}
                                </Button>
                            )}
                        </div>
                    )}
                </Card>

                <div className="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <div className="flex min-w-max gap-1 border-b border-gray-200 p-2 dark:border-gray-700">
                        {tabs
                            .filter((tab) => !tab.hidden)
                            .map((tab) => (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => setActiveTab(tab.key)}
                                    className={`flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition ${
                                        activeTab === tab.key
                                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50'
                                    }`}
                                >
                                    <i className={`bx ${tab.icon}`} />
                                    {tab.label}
                                    {tab.count != null && tab.count > 0 && (
                                        <Badge color="info" size="xs">
                                            {tab.count}
                                        </Badge>
                                    )}
                                </button>
                            ))}
                    </div>

                    <div className="p-4">
                        {activeTab === 'clinical' && (
                            <div>
                                {formOptions ? (
                                    <>
                                        <NephrologyClinicalForm
                                            registration={registration}
                                            formOptions={formOptions}
                                            disabled={!permissions.edit || processing}
                                            onSubmit={handleUpdate}
                                        />
                                        {permissions.edit && (
                                            <div className="mt-6 flex justify-end border-t border-gray-100 pt-4 dark:border-gray-700">
                                                <Button
                                                    type="submit"
                                                    form="nephrology-clinical-form"
                                                    color="blue"
                                                    size="sm"
                                                    disabled={processing}
                                                >
                                                    <i className="bx bx-save me-2" />
                                                    {t('global.save')}
                                                </Button>
                                            </div>
                                        )}
                                    </>
                                ) : (
                                    <p className="text-sm text-gray-500">{t('global.not_available')}</p>
                                )}
                            </div>
                        )}

                        {activeTab === 'diagnose' && registration.appointment_id && (
                            <DiagnosisSection appointmentId={registration.appointment_id} embedded />
                        )}

                        {activeTab === 'lab' && registration.appointment_id && (
                            <LabTestSection appointmentId={registration.appointment_id} embedded />
                        )}

                        {activeTab === 'prescription' && registration.appointment_id && (
                            <PrescriptionSection appointmentId={registration.appointment_id} embedded />
                        )}

                        {activeTab === 'hemodialysis' && (
                            <div className="space-y-4">
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <h3 className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                        <i className="bx bx-water text-blue-500" />
                                        {t('global.hemodialysis_sessions')}
                                    </h3>
                                    <div className="flex flex-wrap gap-2">
                                        {urls.hemodialysisCreate && (
                                            <Button as={Link} href={urls.hemodialysisCreate} size="sm" color="blue">
                                                <i className="bx bx-plus me-2" />
                                                {t('global.add_hemodialysis_session')}
                                            </Button>
                                        )}
                                        {urls.hemodialysisIndex && (
                                            <Button as={Link} href={urls.hemodialysisIndex} size="sm" color="light">
                                                {t('global.view_all_hemodialysis_sessions')}
                                            </Button>
                                        )}
                                    </div>
                                </div>

                                {registration.hemodialysis_sessions.length === 0 ? (
                                    <div className="py-10 text-center text-gray-500">
                                        <i className="bx bx-water mb-2 block text-4xl opacity-50" />
                                        {t('global.no_hemodialysis_sessions_found')}
                                    </div>
                                ) : (
                                    <Table>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.ref_no')}</TableHeader>
                                                <TableHeader>{t('global.session_date')}</TableHeader>
                                                <TableHeader>{t('global.duration_minutes')}</TableHeader>
                                                <TableHeader>{t('global.attending_nephrologist')}</TableHeader>
                                                <TableHeader>{t('global.status')}</TableHeader>
                                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {registration.hemodialysis_sessions.map((session) => (
                                                <TableRow key={session.id}>
                                                    <TableCell>
                                                        <Badge color="info">{session.ref_no ?? '—'}</Badge>
                                                    </TableCell>
                                                    <TableCell muted dir="ltr">
                                                        {session.session_date ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted>{session.duration_minutes ?? '—'}</TableCell>
                                                    <TableCell muted>{session.doctor_name ?? '—'}</TableCell>
                                                    <TableCell>
                                                        <HemodialysisSessionStatusBadge status={session.status} />
                                                    </TableCell>
                                                    <TableCell align="center">
                                                        <TableActions>
                                                            <TableActionButton kind="view" href={session.show_url} />
                                                        </TableActions>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
