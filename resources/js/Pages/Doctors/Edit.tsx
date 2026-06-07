import { Head, Link } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import DoctorForm from '../../Components/Doctors/DoctorForm';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import BackArrowIcon from '../../Components/ui/BackArrowIcon';
import { useTranslation } from '../../hooks/useTranslation';
import { DoctorFormData, DoctorFormUrls, DoctorFormValues } from '../../types/doctor';

interface EditDoctorProps {
    mode: 'edit';
    doctor: DoctorFormValues;
    formData: DoctorFormData;
    urls: DoctorFormUrls;
}

export default function EditDoctor({ doctor, formData, urls }: EditDoctorProps) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.edit_doctor')} />

            <div className="mx-auto max-w-7xl">
                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 text-white shadow-md">
                                <i className="bx bx-edit text-xl" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {t('global.edit_doctor')}
                                </h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    {doctor.name}
                                </p>
                            </div>
                        </div>
                        <Button color="light" as={Link} href={urls.back} className="w-fit">
                            <BackArrowIcon className="me-2 text-lg" />
                            {t('global.back')}
                        </Button>
                    </div>

                    <DoctorForm mode="edit" formData={formData} urls={urls} doctor={doctor} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
