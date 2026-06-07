import { Head, Link } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import {
    AdviceSection,
    AnesthesiaSection,
    BloodBankSection,
    ConsultationSection,
    DentistSection,
    DiagnosisSection,
    HospitalizationCheckupSection,
    HospitalizationSection,
    HospitalizationVisitsSection,
    IcuSection,
    IcuVisitsSection,
    LabTestSection,
    NephrologySection,
    OperationSection,
    PhysiotherapySection,
    PrescriptionSection,
    ReferDepartmentSection,
    RelatedVisitsSection,
    UnderReviewSection,
} from '../../Components/Appointments/Sections';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';

interface ShowAppointmentHeader {
    id: number;
    patient_id: number;
    patient_name: string | null;
    patient_last_name: string | null;
    id_card: string | null;
    doctor_name: string | null;
    department_name: string | null;
    date: string | null;
    time: string | null;
    is_completed: boolean;
    processed_by: boolean;
}

interface PatientHistoryDiagnosis {
    description: string;
    date: string | null;
}

interface ShowAppointmentProps {
    appointment: ShowAppointmentHeader;
    patientHistory: {
        primary: PatientHistoryDiagnosis[];
        final: PatientHistoryDiagnosis[];
    };
    permissions: {
        updateStatus: boolean;
        edit: boolean;
        printToken: boolean;
    };
    urls: {
        index: string;
        edit: string;
        printToken: string;
        changeStatus: string;
        legacyShow: string;
    };
}

export default function ShowAppointment({
    appointment,
    patientHistory,
    permissions,
    urls,
}: ShowAppointmentProps) {
    const { t } = useTranslation();
    const id = appointment.id;

    return (
        <DashboardLayout>
            <Head title={t('global.appointment_details')} />

            <div className="mx-auto max-w-6xl space-y-6">
                <Card className="shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h1 className="flex items-center gap-2 text-xl font-bold text-gray-900 dark:text-white">
                                <i className="bx bx-calendar-check text-cyan-500" />
                                {t('global.appointment_details')}
                            </h1>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">#{id}</p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {appointment.is_completed ? (
                                <Badge color="success">{t('global.appointment_completed')}</Badge>
                            ) : (
                                permissions.printToken && (
                                    <Button size="sm" color="success" as="a" href={urls.printToken} target="_blank">
                                        <i className="bx bx-printer me-2" />
                                        {t('global.token')}
                                    </Button>
                                )
                            )}
                            {permissions.edit && (
                                <Button size="sm" color="light" as={Link} href={urls.edit}>
                                    <i className="bx bx-edit me-2" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            <Button size="sm" color="light" as={Link} href={urls.index}>
                                <i className="bx bx-arrow-back me-2" />
                                {t('global.back')}
                            </Button>
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {[
                            { label: t('global.patient_name'), value: appointment.patient_name, icon: 'bx-user' },
                            { label: t('global.referred_to'), value: appointment.doctor_name, icon: 'bx-user-check' },
                            { label: t('global.date'), value: appointment.date, icon: 'bx-calendar' },
                            { label: t('global.time'), value: appointment.time, icon: 'bx-time' },
                        ].map((field) => (
                            <div
                                key={field.label}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-4 text-center dark:border-gray-700/60 dark:bg-gray-800/40"
                            >
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {field.label}
                                </p>
                                <p className="mt-2 flex items-center justify-center gap-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    <i className={`bx ${field.icon} text-cyan-500`} />
                                    {field.value ?? '—'}
                                </p>
                            </div>
                        ))}
                    </div>
                </Card>

                <Card className="shadow-sm">
                    <h2 className="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                        <i className="bx bx-history text-violet-500" />
                        {t('global.patient_history')}
                    </h2>
                    <div className="grid gap-4 lg:grid-cols-2">
                        <div>
                            <h3 className="mb-3 rounded-lg bg-amber-50 px-3 py-2 text-center text-sm font-semibold text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                                {t('global.primary_diagnoses')}
                            </h3>
                            <ul className="space-y-2">
                                {patientHistory.primary.map((item, index) => (
                                    <li key={`primary-${index}`} className="rounded-lg border-s-4 border-amber-400 bg-gray-50 p-3 text-sm dark:bg-gray-800/40">
                                        <Badge color="warning" className="mb-1">{item.date ?? '—'}</Badge>
                                        <p>{item.description}</p>
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <div>
                            <h3 className="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-center text-sm font-semibold text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">
                                {t('global.final_diagnoses')}
                            </h3>
                            <ul className="space-y-2">
                                {patientHistory.final.map((item, index) => (
                                    <li key={`final-${index}`} className="rounded-lg border-s-4 border-emerald-400 bg-gray-50 p-3 text-sm dark:bg-gray-800/40">
                                        <Badge color="success" className="mb-1">{item.date ?? '—'}</Badge>
                                        <p>{item.description}</p>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </Card>

                <div className="space-y-4">
                    <BloodBankSection appointmentId={id} />
                    <DiagnosisSection appointmentId={id} />
                    <PrescriptionSection appointmentId={id} />
                    <AdviceSection appointmentId={id} />
                    <LabTestSection appointmentId={id} />
                    <HospitalizationCheckupSection appointmentId={id} />
                    <ConsultationSection appointmentId={id} />
                    <ReferDepartmentSection appointmentId={id} />
                    <UnderReviewSection appointmentId={id} />
                    <RelatedVisitsSection appointmentId={id} />
                    <HospitalizationSection appointmentId={id} />
                    <HospitalizationVisitsSection appointmentId={id} />
                    <AnesthesiaSection appointmentId={id} />
                    <OperationSection appointmentId={id} />
                    <IcuSection appointmentId={id} />
                    <IcuVisitsSection appointmentId={id} />
                    <PhysiotherapySection appointmentId={id} />
                    <DentistSection appointmentId={id} />
                    <NephrologySection appointmentId={id} />
                </div>
            </div>
        </DashboardLayout>
    );
}
