import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
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
    IcuReferralSection,
    IcuVisitsSection,
    LabTestSection,
    NephrologySection,
    OperationSection,
    PhysiotherapySection,
    PrescriptionSection,
    ProstheticsSection,
    ReferDepartmentSection,
    RelatedVisitsSection,
    UnderReviewSection,
} from '../../Components/Appointments/Sections';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import BackArrowIcon from '../../Components/ui/BackArrowIcon';
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
    is_processed: boolean;
    processed_by_id: number | null;
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
        complete: boolean;
        edit: boolean;
        printToken: boolean;
    };
    sectionPermissions: {
        underReview: boolean;
        hospitalization: boolean;
        blood: boolean;
        icu: boolean;
        anesthesia: boolean;
        operations: boolean;
    };
    urls: {
        index: string;
        edit: string;
        printToken: string;
        complete: string;
        legacyShow: string;
    };
}

export default function ShowAppointment({
    appointment,
    patientHistory,
    permissions,
    sectionPermissions,
    urls,
}: ShowAppointmentProps) {
    const { t } = useTranslation();
    const id = appointment.id;
    const [completeOpen, setCompleteOpen] = useState(false);
    const [statusRemark, setStatusRemark] = useState('');
    const [processing, setProcessing] = useState(false);

    const handleComplete = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.put(
            urls.complete,
            { status_remark: statusRemark },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            }
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.appointment_details')} />

            <div className="mx-auto max-w-6xl space-y-6">
                <Card className="border !shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h1 className="flex items-center gap-2 text-xl text-gray-900 dark:text-white">
                                <i className="bx bx-calendar-check text-cyan-500" />
                                {t('global.appointment_details')}
                            </h1>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">#{id}</p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {appointment.is_completed ? (
                                <Badge color="success">{t('global.appointment_completed')}</Badge>
                            ) : (
                                <>
                                    {permissions.complete && (
                                        <Button
                                            size="sm"
                                            color="info"
                                            onClick={() => setCompleteOpen(true)}
                                        >
                                            <i className="bx bx-check-shield me-2" />
                                            {t('global.complete_appointment')}
                                        </Button>
                                    )}
                                    {permissions.printToken && (
                                        <Button size="sm" color="success" as="a" href={urls.printToken} target="_blank">
                                            <i className="bx bx-printer me-2" />
                                            {t('global.token')}
                                        </Button>
                                    )}
                                </>
                            )}
                            {permissions.edit && (
                                <Button size="sm" color="light" as={Link} href={urls.edit}>
                                    <i className="bx bx-edit me-2" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            <Button size="sm" color="light" as={Link} href={urls.index}>
                                <BackArrowIcon className="me-2" />
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
                                <p className="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {field.label}
                                </p>
                                <p className="mt-2 flex items-center justify-center gap-1 text-sm text-gray-900 dark:text-white">
                                    <i className={`bx ${field.icon} text-cyan-500`} />
                                    {field.value ?? '—'}
                                </p>
                            </div>
                        ))}
                    </div>
                </Card>

                <Card className="border !shadow-sm">
                    <h2 className="mb-4 flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                        <i className="bx bx-history text-violet-500" />
                        {t('global.patient_history')}
                    </h2>
                    <div className="grid gap-4 lg:grid-cols-2">
                        <div>
                            <h3 className="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-center text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                                {t('global.primary_diagnoses')}
                            </h3>
                            <ul className="space-y-2">
                                {patientHistory.primary.map((item, index) => (
                                    <li
                                        key={`primary-${index}`}
                                        className="rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-300"
                                    >
                                        <Badge color="warning" className="mb-1.5 w-fit font-normal">
                                            {item.date ?? '—'}
                                        </Badge>
                                        <p>{item.description}</p>
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <div>
                            <h3 className="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">
                                {t('global.final_diagnoses')}
                            </h3>
                            <ul className="space-y-2">
                                {patientHistory.final.map((item, index) => (
                                    <li
                                        key={`final-${index}`}
                                        className="rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-300"
                                    >
                                        <Badge color="success" className="mb-1.5 w-fit font-normal">
                                            {item.date ?? '—'}
                                        </Badge>
                                        <p>{item.description}</p>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </Card>

                <div className="space-y-4">
                    {sectionPermissions.blood && <BloodBankSection appointmentId={id} />}
                    <DiagnosisSection appointmentId={id} />
                    <PrescriptionSection appointmentId={id} />
                    <AdviceSection appointmentId={id} />
                    <LabTestSection appointmentId={id} />
                    {sectionPermissions.hospitalization && <HospitalizationSection appointmentId={id} />}
                    <PhysiotherapySection appointmentId={id} />
                    <DentistSection appointmentId={id} />
                    <NephrologySection appointmentId={id} />
                    <ProstheticsSection appointmentId={id} />
                    <HospitalizationCheckupSection appointmentId={id} />
                    <ConsultationSection appointmentId={id} />
                    <ReferDepartmentSection appointmentId={id} />
                    {sectionPermissions.underReview && <UnderReviewSection appointmentId={id} />}
                    <RelatedVisitsSection appointmentId={id} />
                    <HospitalizationVisitsSection appointmentId={id} />
                    {sectionPermissions.anesthesia && <AnesthesiaSection appointmentId={id} />}
                    {sectionPermissions.operations && <OperationSection appointmentId={id} />}
                    {sectionPermissions.icu && <IcuReferralSection appointmentId={id} />}
                    <IcuVisitsSection appointmentId={id} />
                </div>
            </div>

            <Modal show={completeOpen} onClose={() => !processing && setCompleteOpen(false)} size="md">
                <form onSubmit={handleComplete}>
                    <ModalHeader>{t('global.make_appointment_completed')}</ModalHeader>
                    <ModalBody>
                        <div>
                            <Label htmlFor="appointment-status-remark">{t('global.status_remark')}</Label>
                            <Textarea
                                id="appointment-status-remark"
                                rows={4}
                                value={statusRemark}
                                onChange={(e) => setStatusRemark(e.target.value)}
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button
                            type="button"
                            color="light"
                            disabled={processing}
                            onClick={() => setCompleteOpen(false)}
                        >
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="success" disabled={processing}>
                            {processing && <Spinner size="sm" className="me-2" />}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </DashboardLayout>
    );
}
