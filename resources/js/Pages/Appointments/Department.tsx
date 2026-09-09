import { Head, router } from '@inertiajs/react';
import { Badge, Card } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import {
    AppointmentActionGroup,
    AppointmentIconLink,
    AppointmentInfoTip,
    AppointmentPillButton,
} from '../../Components/Appointments/AppointmentTableActions';
import AppointmentPageHeader from '../../Components/Appointments/AppointmentPageHeader';
import AppointmentPagination from '../../Components/Appointments/AppointmentPagination';
import ChangeDepartmentModal from '../../Components/Appointments/ChangeDepartmentModal';
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
    AppointmentIndexFilterOptions,
    DepartmentAppointmentFilters,
    MyVisitPermissions,
    MyVisitUrls,
    PaginatedDepartmentAppointments,
} from '../../types/appointment';

interface DepartmentProps {
    appointments: PaginatedDepartmentAppointments;
    filters: DepartmentAppointmentFilters;
    filterOptions: AppointmentIndexFilterOptions;
    permissions: MyVisitPermissions;
    urls: MyVisitUrls;
}

const EMPTY_FILTERS: MyVisitFilterValues = {
    search: '',
    token_id: '',
    patient_id: '',
};

const DEPARTMENT_ONLY = ['appointments', 'filters', 'filterOptions', 'permissions', 'urls'] as const;

function cleanFilters(filters: MyVisitFilterValues): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function Department({
    appointments,
    filters: serverFilters,
    filterOptions,
    urls,
}: DepartmentProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<MyVisitFilterValues>(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [acceptingId, setAcceptingId] = useState<number | null>(null);
    const [changeDepartmentTarget, setChangeDepartmentTarget] = useState<{
        id: number;
        departmentId: number | null;
    } | null>(null);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: MyVisitFilterValues) => {
            setProcessing(true);
            router.get(urls.department, cleanFilters(nextFilters), {
                only: [...DEPARTMENT_ONLY],
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.department],
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

    const handleAccept = (appointmentId: number) => {
        if (!window.confirm(t('global.are_you_sure_accept_appointment'))) {
            return;
        }

        setAcceptingId(appointmentId);
        router.post(
            `${urls.accept}/${appointmentId}/accept`,
            {},
            {
                only: [...DEPARTMENT_ONLY],
                preserveScroll: true,
                preserveState: true,
                onFinish: () => setAcceptingId(null),
            },
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.department_appointments')} />

            <div className="mx-auto max-w-[1600px]">
                <Card className="shadow-sm">
                    <AppointmentPageHeader
                        title={t('global.department_appointments')}
                        subtitle={t('global.appointments_referred_by_doctors')}
                        icon="bx-building"
                        accent="from-amber-500 to-orange-600"
                    />

                    <MyVisitFilters
                        filters={filters}
                        processing={processing}
                        onFilterChange={updateFilter}
                        onSubmit={handleFilterSubmit}
                        onReset={handleReset}
                        showSearch
                    />

                    <div
                        className={
                            processing || acceptingId !== null
                                ? 'pointer-events-none opacity-60 transition-opacity'
                                : 'transition-opacity'
                        }
                        aria-busy={processing || acceptingId !== null}
                    >
                    <Table id="department-appointments-table">
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>{t('global.id')}</TableHeader>
                                <TableHeader>{t('global.card_number')}</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.father_name')}</TableHeader>
                                <TableHeader>{t('global.department')}</TableHeader>
                                <TableHeader>{t('global.date')}</TableHeader>
                                <TableHeader>{t('global.time')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {appointments.data.length === 0 ? (
                                <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                    <TableCell
                                        colSpan={9}
                                        align="center"
                                        muted
                                        className="py-12 text-base"
                                    >
                                        <div className="flex flex-col items-center gap-2">
                                            <div className="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                <i className="bx bx-calendar-x text-xl text-gray-400" />
                                            </div>
                                            {t('global.no_appointments_found')}
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
                                        <TableCell>{appointment.department_name ?? '—'}</TableCell>
                                        <TableCell muted>{appointment.date ?? '—'}</TableCell>
                                        <TableCell muted>{appointment.time ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={appointment.is_accepted ? 'success' : 'warning'}>
                                                {appointment.is_accepted
                                                    ? t('global.accepted')
                                                    : t('global.pending')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell align="center">
                                            <AppointmentActionGroup>
                                                {appointment.permissions.accept && (
                                                    <AppointmentPillButton
                                                        icon="bx-check"
                                                        label={t('global.accept')}
                                                        variant="accept"
                                                        disabled={acceptingId === appointment.id}
                                                        onClick={() => handleAccept(appointment.id)}
                                                    />
                                                )}
                                                {appointment.permissions.changeDepartment && (
                                                    <AppointmentPillButton
                                                        icon="bx-transfer"
                                                        label={t('global.change_department')}
                                                        variant="changeDepartment"
                                                        onClick={() =>
                                                            setChangeDepartmentTarget({
                                                                id: appointment.id,
                                                                departmentId: appointment.department_id,
                                                            })
                                                        }
                                                    />
                                                )}
                                                {appointment.permissions.view && (
                                                    <AppointmentIconLink
                                                        href={`${urls.show}/${appointment.id}`}
                                                        icon="bx-expand"
                                                        title={t('global.view')}
                                                        variant="view"
                                                    />
                                                )}
                                                {appointment.refferal_remarks && (
                                                    <AppointmentInfoTip
                                                        icon="bx-info-circle"
                                                        title={appointment.refferal_remarks}
                                                        variant="info"
                                                    />
                                                )}
                                                {appointment.referring_doctor_name && (
                                                    <AppointmentInfoTip
                                                        icon="bx-user"
                                                        title={`${t('global.introduced_by')}: ${appointment.referring_doctor_name}`}
                                                        variant="user"
                                                    />
                                                )}
                                            </AppointmentActionGroup>
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
                        only={[...DEPARTMENT_ONLY]}
                    />
                    </div>
                </Card>
            </div>

            <ChangeDepartmentModal
                show={changeDepartmentTarget !== null}
                appointmentId={changeDepartmentTarget?.id ?? null}
                currentDepartmentId={changeDepartmentTarget?.departmentId ?? null}
                departments={filterOptions.departments}
                changeDepartmentUrl={urls.changeDepartment}
                only={[...DEPARTMENT_ONLY]}
                onClose={() => setChangeDepartmentTarget(null)}
            />
        </DashboardLayout>
    );
}
