import { Head, Link } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import AppointmentEditForm from '../../Components/Appointments/AppointmentEditForm';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import {
    AppointmentEditFormData,
    AppointmentEditPermissions,
    AppointmentEditUrls,
    AppointmentFormValues,
} from '../../types/appointment';

interface EditAppointmentProps {
    appointment: AppointmentFormValues;
    formData: AppointmentEditFormData;
    permissions: AppointmentEditPermissions;
    urls: AppointmentEditUrls;
}

export default function EditAppointment({
    appointment,
    formData,
    permissions,
    urls,
}: EditAppointmentProps) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.edit_appointment')} />

            <div className="mx-auto max-w-4xl">
                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-md">
                                <i className="bx bx-edit text-xl" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {t('global.edit_appointment')}
                                </h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    #{appointment.id}
                                </p>
                            </div>
                        </div>
                        <Button color="light" as={Link} href={urls.index} className="w-fit">
                            <i className="bx bx-arrow-back me-2 text-lg" />
                            {t('global.back')}
                        </Button>
                    </div>

                    <AppointmentEditForm
                        appointment={appointment}
                        formData={formData}
                        permissions={permissions}
                        urls={urls}
                    />
                </Card>
            </div>
        </DashboardLayout>
    );
}
