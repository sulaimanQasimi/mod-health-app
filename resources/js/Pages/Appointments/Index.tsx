import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import TableActionButton from '../../Components/ui/TableActionButton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { useTranslation } from '../../hooks/useTranslation';
import {
    AppointmentIndexFilterOptions,
    AppointmentIndexFilters,
    AppointmentIndexPermissions,
    AppointmentIndexUrls,
    PaginatedAppointments,
    PaginationLink,
} from '../../types/appointment';

interface IndexAppointmentProps {
    appointments: PaginatedAppointments;
    filters: AppointmentIndexFilters;
    filterOptions: AppointmentIndexFilterOptions;
    permissions: AppointmentIndexPermissions;
    urls: AppointmentIndexUrls;
}

const EMPTY_FILTERS: AppointmentIndexFilters = {
    patient_name: '',
    id_card: '',
    patient_id: '',
    father_name: '',
    phone: '',
    doctor_id: '',
    department_id: '',
    is_completed: '',
    date_from: '',
    date_to: '',
};

function cleanFilters(filters: AppointmentIndexFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

function decodePaginationLabel(label: string): string {
    return label
        .replace('&laquo;', '«')
        .replace('&raquo;', '»')
        .replace(/&[^;]+;/g, '')
        .trim();
}

export default function IndexAppointment({
    appointments,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: IndexAppointmentProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<AppointmentIndexFilters>(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: AppointmentIndexFilters) => {
            setProcessing(true);
            router.get(urls.index, cleanFilters(nextFilters), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.index],
    );

    const updateFilter = (field: keyof AppointmentIndexFilters, value: string) => {
        setFilters((current) => ({ ...current, [field]: value }));
    };

    const handleSelectChange = (field: keyof AppointmentIndexFilters, value: string) => {
        const nextFilters = { ...filters, [field]: value };
        setFilters(nextFilters);
        applyFilters(nextFilters);
    };

    const handleFilterSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        applyFilters(EMPTY_FILTERS);
    };

    const handleDelete = (appointmentId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        router.delete(`${urls.destroy}/${appointmentId}`, {
            preserveScroll: true,
        });
    };

    const summaryLabel =
        appointments.meta.from && appointments.meta.to
            ? `${t('global.showing')} ${appointments.meta.from}-${appointments.meta.to} ${t('global.of')} ${appointments.meta.total} ${t('global.results')}`
            : `${appointments.meta.total} ${t('global.results')}`;

    const renderPaginationLink = (link: PaginationLink, index: number) => {
        const label = decodePaginationLabel(link.label);
        const isPrevious = label === '«' || label.toLowerCase().includes('previous');
        const isNext = label === '»' || label.toLowerCase().includes('next');
        const isEllipsis = label === '...';

        if (isEllipsis) {
            return (
                <li key={`ellipsis-${index}`}>
                    <span className="flex h-9 items-center border border-gray-300 bg-white px-3 leading-tight text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        ...
                    </span>
                </li>
            );
        }

        const baseClass = 'flex h-9 items-center border border-gray-300 px-3 leading-tight dark:border-gray-700';
        const activeClass =
            'z-10 border-blue-300 bg-blue-50 text-blue-600 dark:border-gray-700 dark:bg-gray-700 dark:text-white';
        const inactiveClass =
            'bg-white text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white';
        const disabledClass = 'cursor-not-allowed bg-white text-gray-300 dark:bg-gray-800 dark:text-gray-600';
        const roundedClass = isPrevious ? 'rounded-s-lg' : isNext ? 'rounded-e-lg' : '';

        if (!link.url) {
            return (
                <li key={`${label}-${index}`}>
                    <span className={`${baseClass} ${disabledClass} ${roundedClass}`}>
                        {isPrevious ? (
                            <i className="bx bx-chevron-left text-lg" />
                        ) : isNext ? (
                            <i className="bx bx-chevron-right text-lg" />
                        ) : (
                            label
                        )}
                    </span>
                </li>
            );
        }

        return (
            <li key={`${label}-${index}`}>
                <Link
                    href={link.url}
                    preserveScroll
                    className={`${baseClass} ${link.active ? activeClass : inactiveClass} ${roundedClass}`}
                >
                    {isPrevious ? (
                        <i className="bx bx-chevron-left text-lg" />
                    ) : isNext ? (
                        <i className="bx bx-chevron-right text-lg" />
                    ) : (
                        label
                    )}
                </Link>
            </li>
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.appointments_list')} />

            <div className="mx-auto max-w-[1600px]">
                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-md">
                                <i className="bx bx-calendar text-xl" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {t('global.appointments_list')}
                                </h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    {appointments.meta.total} {t('global.appointments')}
                                </p>
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {permissions.restore && (
                                <Button color="light" as={Link} href={urls.trashed} className="w-fit">
                                    <i className="bx bx-trash me-2 text-lg" />
                                    {t('global.deleted')}
                                </Button>
                            )}
                            {permissions.create && (
                                <Button color="blue" as={Link} href={urls.patientsCreate} className="w-fit">
                                    <i className="bx bx-user-plus me-2 text-lg" />
                                    {t('global.create_patient')}
                                </Button>
                            )}
                        </div>
                    </div>

                    <div className="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                        <h2 className="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-filter-alt text-cyan-500" />
                            {t('global.filters')}
                        </h2>
                        <form onSubmit={handleFilterSubmit}>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                <div>
                                    <Label htmlFor="filter-patient-name">{t('global.patient_name')}</Label>
                                    <TextInput
                                        id="filter-patient-name"
                                        value={filters.patient_name}
                                        placeholder={t('global.search_by_patient_name')}
                                        onChange={(event) => updateFilter('patient_name', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-id-card">{t('global.id_card')}</Label>
                                    <TextInput
                                        id="filter-id-card"
                                        value={filters.id_card}
                                        placeholder={t('global.search_by_card')}
                                        onChange={(event) => updateFilter('id_card', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-patient-id">{t('global.patient_id')}</Label>
                                    <TextInput
                                        id="filter-patient-id"
                                        value={filters.patient_id}
                                        placeholder={t('global.search_by_patient_id')}
                                        onChange={(event) => updateFilter('patient_id', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-doctor">{t('global.doctor')}</Label>
                                    <SearchableSelect
                                        id="filter-doctor"
                                        value={filters.doctor_id}
                                        onChange={(value) => handleSelectChange('doctor_id', value)}
                                        placeholder={t('global.all')}
                                    >
                                        <option value="">{t('global.all')}</option>
                                        {filterOptions.doctors.map((doctor) => (
                                            <option key={doctor.id} value={doctor.id}>
                                                {doctor.name}
                                            </option>
                                        ))}
                                    </SearchableSelect>
                                </div>
                                <div>
                                    <Label htmlFor="filter-department">{t('global.department')}</Label>
                                    <SearchableSelect
                                        id="filter-department"
                                        value={filters.department_id}
                                        onChange={(value) => handleSelectChange('department_id', value)}
                                        placeholder={t('global.all')}
                                    >
                                        <option value="">{t('global.all')}</option>
                                        {filterOptions.departments.map((department) => (
                                            <option key={department.id} value={department.id}>
                                                {department.name}
                                            </option>
                                        ))}
                                    </SearchableSelect>
                                </div>
                                <div>
                                    <Label htmlFor="filter-status">{t('global.status')}</Label>
                                    <SearchableSelect
                                        id="filter-status"
                                        value={filters.is_completed}
                                        onChange={(value) => handleSelectChange('is_completed', value)}
                                        placeholder={t('global.all')}
                                    >
                                        <option value="">{t('global.all')}</option>
                                        <option value="0">{t('global.pending')}</option>
                                        <option value="1">{t('global.completed')}</option>
                                    </SearchableSelect>
                                </div>
                                <div>
                                    <Label htmlFor="filter-date-from">{t('global.date_from')}</Label>
                                    <PersianDateInput
                                        id="filter-date-from"
                                        value={filters.date_from}
                                        onChange={(value) => updateFilter('date_from', value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-date-to">{t('global.date_to')}</Label>
                                    <PersianDateInput
                                        id="filter-date-to"
                                        value={filters.date_to}
                                        onChange={(value) => updateFilter('date_to', value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-father-name">{t('global.father_name')}</Label>
                                    <TextInput
                                        id="filter-father-name"
                                        value={filters.father_name}
                                        placeholder={t('global.search_by_father_name')}
                                        onChange={(event) => updateFilter('father_name', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-phone">{t('global.phone')}</Label>
                                    <TextInput
                                        id="filter-phone"
                                        value={filters.phone}
                                        placeholder={t('global.phone')}
                                        onChange={(event) => updateFilter('phone', event.target.value)}
                                    />
                                </div>
                            </div>
                            <div className="mt-4 flex flex-wrap justify-end gap-2">
                                <Button type="submit" color="blue" disabled={processing}>
                                    {processing ? (
                                        <>
                                            <Spinner size="sm" className="me-2" />
                                            {t('global.loading')}
                                        </>
                                    ) : (
                                        <>
                                            <i className="bx bx-search me-2 text-lg" />
                                            {t('global.search')}
                                        </>
                                    )}
                                </Button>
                                <Button type="button" color="gray" onClick={handleReset} disabled={processing}>
                                    <i className="bx bx-refresh me-2 text-lg" />
                                    {t('global.reset')}
                                </Button>
                            </div>
                        </form>
                    </div>

                    <Table id="appointments-table">
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>{t('global.id')}</TableHeader>
                                <TableHeader>{t('global.card_number')}</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.father_name')}</TableHeader>
                                <TableHeader>{t('global.referred_to')}</TableHeader>
                                <TableHeader>{t('global.department')}</TableHeader>
                                <TableHeader>{t('global.date')}</TableHeader>
                                <TableHeader>{t('global.time')}</TableHeader>
                                <TableHeader>{t('global.phone')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                                <TableHeader>{t('global.processed_by')}</TableHeader>
                                <TableHeader align="center" className="min-w-[9rem]">
                                    {t('global.actions')}
                                </TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {appointments.data.length === 0 ? (
                                <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                    <TableCell
                                        colSpan={12}
                                        align="center"
                                        muted
                                        className="py-12 text-base"
                                    >
                                        <div className="flex flex-col items-center gap-2">
                                            <div className="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                <i className="bx bx-calendar-x text-xl text-gray-400" />
                                            </div>
                                            {t('global.no_records_found')}
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
                                        <TableCell>{appointment.department_name ?? '—'}</TableCell>
                                        <TableCell muted>{appointment.date ?? '—'}</TableCell>
                                        <TableCell muted>{appointment.time ?? '—'}</TableCell>
                                        <TableCell muted>{appointment.phone ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={appointment.is_completed ? 'success' : 'warning'}>
                                                {appointment.is_completed
                                                    ? t('global.completed')
                                                    : t('global.pending')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{appointment.processed_by ?? '—'}</TableCell>
                                        <TableActionsCell className="whitespace-nowrap">
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${appointment.id}`}
                                            />
                                            <TableActionButton
                                                kind="custom"
                                                href={`${urls.patientHistory}/${appointment.patient_id}`}
                                                external
                                                icon="bx-history"
                                                title={t('global.history')}
                                                permission={Boolean(appointment.patient_id)}
                                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-violet-600 hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-900/30"
                                            />
                                            <TableActionButton
                                                kind="edit"
                                                href={`${urls.edit}/${appointment.id}/edit`}
                                                permission={permissions.edit}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                confirm={t('global.are_you_sure')}
                                                onClick={() => handleDelete(appointment.id)}
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>

                    {appointments.links.length > 3 && (
                        <div className="mt-4 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-4 dark:border-gray-700 sm:flex-row">
                            <p className="text-sm text-gray-500 dark:text-gray-400">{summaryLabel}</p>
                            <nav aria-label="Pagination">
                                <ul className="inline-flex items-center -space-x-px text-sm">
                                    {appointments.links.map(renderPaginationLink)}
                                </ul>
                            </nav>
                        </div>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
