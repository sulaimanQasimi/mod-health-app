import { Head, Link } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import DoctorForm from '../../Components/Doctors/DoctorForm';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { DoctorFormData, DoctorFormUrls } from '../../types/doctor';

interface CreateDoctorProps {
    mode: 'create';
    formData: DoctorFormData;
    urls: DoctorFormUrls;
}

export default function CreateDoctor({ formData, urls }: CreateDoctorProps) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.create_doctor')} />

            <div className="mx-auto max-w-7xl">
                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 text-white shadow-md">
                                <i className="bx bx-user-plus text-xl" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {t('global.create_doctor')}
                                </h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    {t('global.doctors')} / {t('global.create')}
                                </p>
                            </div>
                        </div>
                        <Button color="light" as={Link} href={urls.back} className="w-fit">
                            <i className="bx bx-arrow-back me-2 text-lg" />
                            {t('global.back')}
                        </Button>
                    </div>

                    <DoctorForm mode="create" formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
