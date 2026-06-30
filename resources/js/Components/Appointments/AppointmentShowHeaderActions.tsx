import { Link } from '@inertiajs/react';
import { Badge, Button } from 'flowbite-react';
import AppointmentDoctorSelect from './AppointmentDoctorSelect';
import { AppointmentPageActions } from './AppointmentPageHeader';
import BackLink from '../ui/BackLink';
import { useTranslation } from '../../hooks/useTranslation';

interface AppointmentShowHeaderActionsProps {
    appointment: {
        department_id: number | null;
        doctor_id: number | null;
        is_completed: boolean;
        is_processed: boolean;
        doctor_reassigned: boolean;
        can_change_doctor: boolean;
    };
    permissions: {
        complete: boolean;
        edit: boolean;
        printToken: boolean;
    };
    formData: {
        doctorsByDepartment: string;
    };
    urls: {
        index: string;
        edit: string;
        printToken: string;
        assignDoctor: string;
    };
    onCompleteClick: () => void;
}

export default function AppointmentShowHeaderActions({
    appointment,
    permissions,
    formData,
    urls,
    onCompleteClick,
}: AppointmentShowHeaderActionsProps) {
    const { t } = useTranslation();

    return (
        <AppointmentPageActions>
            <AppointmentDoctorSelect
                departmentId={appointment.department_id}
                doctorId={appointment.doctor_id}
                canChangeDoctor={appointment.can_change_doctor}
                isCompleted={appointment.is_completed}
                isProcessed={appointment.is_processed}
                doctorReassigned={appointment.doctor_reassigned}
                doctorsByDepartmentUrl={formData.doctorsByDepartment}
                assignUrl={urls.assignDoctor}
            />

            {appointment.is_completed ? (
                <Badge color="success" className="h-9 px-3">
                    {t('global.appointment_completed')}
                </Badge>
            ) : (
                <>
                    {permissions.complete && (
                        <Button type="button" size="sm" color="blue" onClick={onCompleteClick}>
                            <i className="bx bx-check-shield me-2" />
                            {t('global.complete_appointment')}
                        </Button>
                    )}
                    {permissions.printToken && (
                        <a href={urls.printToken} target="_blank" rel="noopener noreferrer">
                            <Button size="sm" color="light">
                                <i className="bx bx-printer me-2" />
                                {t('global.token')}
                            </Button>
                        </a>
                    )}
                </>
            )}

            {permissions.edit && (
                <Link href={urls.edit}>
                    <Button size="sm" color="light">
                        <i className="bx bx-edit me-2" />
                        {t('global.edit')}
                    </Button>
                </Link>
            )}

            <BackLink href={urls.index}>{t('global.back')}</BackLink>
        </AppointmentPageActions>
    );
}
