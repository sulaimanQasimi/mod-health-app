import { Head, Link, router } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import AppointmentPageHeader from '../../Components/Appointments/AppointmentPageHeader';
import AppointmentPagination from '../../Components/Appointments/AppointmentPagination';
import MyVisitFilters, { MyVisitFilterValues } from '../../Components/Appointments/MyVisitFilters';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
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
    MyVisitAppointmentFilters,
    MyVisitPermissions,
    MyVisitUrls,
    PaginatedDoctorAppointments,
} from '../../types/appointment';

interface CompletedProps {
    appointments: PaginatedDoctorAppointments;
    filters: MyVisitAppointmentFilters;
    permissions: MyVisitPermissions;
    urls: MyVisitUrls;
}

const EMPTY_FILTERS: MyVisitFilterValues = {
    token_id: '',
    patient_id: '',
    patient_name: '',
};

function cleanFilters(filters: MyVisitFilterValues): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function Completed({
    appointments,
    filters: serverFilters,
    urls,
}: CompletedProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<MyVisitFilterValues>(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: MyVisitFilterValues) => {
            setProcessing(true);
            router.get(urls.completed, cleanFilters(nextFilters), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.completed],
    );

    const updateFilter = (field: keyof MyVisitFilterValues, value: string) => {
        setFilters((current) => ({ ...current, [field]: value }));
    };

    const handleFilterSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        applyFilters(EMPTY_FILTERS);
    };

    return (
        <DashboardLayout>
            <Head title={t('global.completed_appointments')} />

            <div className="mx-auto max-w-[1600px]">
                <Card className="shadow-sm">
                    <AppointmentPageHeader
                        title={t('global.completed_appointments')}
                        subtitle={`${appointments.meta.total} ${t('global.appointments')}`}
                        icon="bx-check-circle"
                        accent="from-violet-500 to-purple-600"
                    />

                    <MyVisitFilters
                        filters={filters}
                        processing={processing}
                        onFilterChange={updateFilter}
                        onSubmit={handleFilterSubmit}
                        onReset={handleReset}
                        showPatientName
                    />

                    <Table id="completed-appointments-table">
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>{t('global.id')}</TableHeader>
                                <TableHeader>{t('global.card_number')}</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.father_name')}</TableHeader>
                                <TableHeader>{t('global.referred_to')}</TableHeader>
                                <TableHeader>{t('global.date')}</TableHeader>
                                <TableHeader>{t('global.time')}</TableHeader>
                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {appointments.data.length === 0 ? (
                                <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                    <TableCell
                                        colSpan={8}
                                        align="center"
                                        muted
                                        className="py-12 text-base"
                                    >
                                        <div className="flex flex-col items-center gap-2">
                                            <div className="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                <i className="bx bx-calendar-x text-xl text-gray-400" />
                                            </div>
                                            {t('global.no_data_found')}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ) : (
                                appointments.data.map((appointment) => (
                                    <TableRow key={appointment.id}>
                                        <TableCell className="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                            {appointment.id}
                                        </TableCell>
                                        <TableCell>{appointment.id_card ?? '—'}</TableCell>
                                        <TableCell className="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                            {appointment.patient_name ?? '—'}
                                        </TableCell>
                                        <TableCell>{appointment.father_name ?? '—'}</TableCell>
                                        <TableCell>{appointment.doctor_name ?? '—'}</TableCell>
                                        <TableCell muted>{appointment.date ?? '—'}</TableCell>
                                        <TableCell muted>{appointment.time ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex items-center justify-center gap-1">
                                                {appointment.permissions.view && (
                                                    <Link
                                                        href={`${urls.show}/${appointment.id}`}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                        title={t('global.view')}
                                                    >
                                                        <i className="bx bx-expand text-lg" />
                                                    </Link>
                                                )}
                                                {appointment.permissions.history && appointment.patient_id && (
                                                    <a
                                                        href={`${urls.patientHistory}/${appointment.patient_id}`}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-violet-600 hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-900/30"
                                                        title={t('global.history')}
                                                    >
                                                        <i className="bx bx-history text-lg" />
                                                    </a>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>

                    <AppointmentPagination
                        links={appointments.links}
                        meta={appointments.meta}
                        t={t}
                    />
                </Card>
            </div>
        </DashboardLayout>
    );
}
