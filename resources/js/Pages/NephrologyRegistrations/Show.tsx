import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
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
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

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

    const handleUpdate = (data: NephrologyClinicalFormData) => {
        if (!urls.update) {
            return;
        }

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
                {
                    preserveScroll: true,
                    onFinish: finishAction,
                },
            ),
        );
    };

    const handleMarkCompleted = () => {
        if (!urls.markCompleted) {
            return;
        }
        runAction(() => router.post(urls.markCompleted!, {}, { preserveScroll: true, onFinish: finishAction }));
    };

    const handleMarkInProgress = () => {
        if (!urls.markInProgress) {
            return;
        }
        runAction(() => router.post(urls.markInProgress!, {}, { preserveScroll: true, onFinish: finishAction }));
    };

    const handleCancel = () => {
        if (!urls.cancel || !window.confirm(t('global.confirm_cancel_registration'))) {
            return;
        }
        runAction(() => router.post(urls.cancel!, {}, { preserveScroll: true, onFinish: finishAction }));
    };

    const handleDelete = () => {
        if (!urls.destroy || !window.confirm(t('global.confirm_delete'))) {
            return;
        }
        runAction(() => router.delete(urls.destroy!, { onFinish: finishAction }));
    };

    const diseaseLabel = registration.disease_name
        ? registration.disease_category_name
            ? `${registration.disease_category_name} — ${registration.disease_name}`
            : registration.disease_name
        : '—';

    return (
        <DashboardLayout>
            <Head title={t('global.nephrology_visit')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.nephrology_visit')}
                    subtitle={`${t('global.ref_no')}: ${registration.ref_no ?? '—'}`}
                    icon="bx-droplet"
                    accent="from-cyan-500 to-blue-600"
                />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    {[
                        { label: t('global.patient_name'), value: registration.patient_name ?? '—' },
                        { label: t('global.doctor'), value: registration.doctor_name ?? t('global.not_available') },
                        { label: t('global.visit_date'), value: registration.visit_date ?? '—', dir: 'ltr' as const },
                        { label: t('global.diseases'), value: diseaseLabel },
                        {
                            label: t('global.status'),
                            value: <NephrologyRegistrationStatusBadge status={registration.status} />,
                        },
                    ].map((item) => (
                        <Card key={item.label} className="shadow-sm">
                            <div className="text-center">
                                <p className="mb-1 text-xs text-gray-500">{item.label}</p>
                                <div
                                    className="truncate font-semibold text-gray-900 dark:text-white"
                                    dir={'dir' in item ? item.dir : undefined}
                                >
                                    {item.value}
                                </div>
                            </div>
                        </Card>
                    ))}
                </div>

                <div className="flex flex-nowrap gap-2 overflow-x-auto">
                    {tabs
                        .filter((tab) => !tab.hidden)
                        .map((tab) => (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => setActiveTab(tab.key)}
                                className={`inline-flex shrink-0 items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition ${
                                    activeTab === tab.key
                                        ? 'bg-blue-600 text-white shadow'
                                        : 'border border-gray-200 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200'
                                }`}
                            >
                                <i className={`bx ${tab.icon}`} />
                                {tab.label}
                                {tab.count !== undefined && tab.count > 0 && (
                                    <Badge color="info" className="ms-1">
                                        {tab.count}
                                    </Badge>
                                )}
                            </button>
                        ))}
                </div>

                {activeTab === 'clinical' && (
                    <Card className="shadow-sm">
                        <div className="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-700">
                            <i className="bx bx-edit-alt text-blue-500" />
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                {t('global.nephrology_clinical_record')}
                            </h2>
                        </div>

                        {formOptions ? (
                            <>
                                <NephrologyClinicalForm
                                    registration={registration}
                                    formOptions={formOptions}
                                    disabled={!permissions.edit || processing}
                                    onSubmit={handleUpdate}
                                />

                                {permissions.edit && (
                                    <div className="mt-6 flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                                        {registration.status !== 'completed' && (
                                            <Button color="success" disabled={processing} onClick={handleMarkCompleted}>
                                                <i className="bx bx-check me-1" />
                                                {t('global.mark_completed')}
                                            </Button>
                                        )}
                                        {registration.status !== 'in_progress' && (
                                            <Button color="info" disabled={processing} onClick={handleMarkInProgress}>
                                                <i className="bx bx-play me-1" />
                                                {t('global.mark_in_progress')}
                                            </Button>
                                        )}
                                        {registration.status !== 'cancelled' && (
                                            <Button color="failure" outline disabled={processing} onClick={handleCancel}>
                                                <i className="bx bx-x me-1" />
                                                {t('global.cancel')}
                                            </Button>
                                        )}
                                        {permissions.delete && (
                                            <Button color="gray" outline disabled={processing} onClick={handleDelete}>
                                                <i className="bx bx-trash me-1" />
                                                {t('global.delete')}
                                            </Button>
                                        )}
                                        <Button
                                            type="submit"
                                            form="nephrology-clinical-form"
                                            color="blue"
                                            disabled={processing}
                                        >
                                            <i className="bx bx-save me-1" />
                                            {t('global.save')}
                                        </Button>
                                    </div>
                                )}
                            </>
                        ) : (
                            <p className="text-sm text-gray-500">{t('global.not_available')}</p>
                        )}
                    </Card>
                )}

                {activeTab === 'diagnose' && registration.appointment_id && (
                    <Card className="shadow-sm">
                        <DiagnosisSection appointmentId={registration.appointment_id} />
                    </Card>
                )}

                {activeTab === 'lab' && registration.appointment_id && (
                    <LabTestSection appointmentId={registration.appointment_id} embedded />
                )}

                {activeTab === 'prescription' && registration.appointment_id && (
                    <PrescriptionSection appointmentId={registration.appointment_id} embedded />
                )}

                {activeTab === 'hemodialysis' && (
                    <Card className="shadow-sm">
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3 dark:border-gray-700">
                            <div className="flex items-center gap-2">
                                <i className="bx bx-water text-blue-500" />
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('global.hemodialysis_sessions')}
                                </h2>
                            </div>
                            <div className="flex gap-2">
                                {urls.hemodialysisCreate && (
                                    <Button as={Link} href={urls.hemodialysisCreate} size="sm" color="blue">
                                        <i className="bx bx-plus me-1" />
                                        {t('global.add_hemodialysis_session')}
                                    </Button>
                                )}
                                {urls.hemodialysisIndex && (
                                    <Button as={Link} href={urls.hemodialysisIndex} size="sm" color="gray" outline>
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
                                                <Badge color="info">{t(`global.${session.status}`)}</Badge>
                                            </TableCell>
                                            <TableCell align="center">
                                                <Button as="a" href={session.show_url} size="xs" color="blue" outline>
                                                    <i className="bx bx-show" />
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </Card>
                )}
            </div>
        </DashboardLayout>
    );
}
