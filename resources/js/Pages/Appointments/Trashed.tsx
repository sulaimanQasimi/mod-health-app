import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
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
    AppointmentIndexPermissions,
    PaginatedTrashedAppointments,
    PaginationLink,
    TrashedAppointmentFilters,
    TrashedAppointmentUrls,
} from '../../types/appointment';

interface TrashedAppointmentsProps {
    appointments: PaginatedTrashedAppointments;
    filters: TrashedAppointmentFilters;
    permissions: AppointmentIndexPermissions;
    urls: TrashedAppointmentUrls;
}

const EMPTY_FILTERS: TrashedAppointmentFilters = {
    patient_name: '',
    id_card: '',
};

function cleanFilters(filters: TrashedAppointmentFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

function decodePaginationLabel(label: string): string {
    return label
        .replace('&laquo;', '«')
        .replace('&raquo;', '»')
        .replace(/&[^;]+;/g, '')
        .trim();
}

export default function TrashedAppointments({
    appointments,
    filters: serverFilters,
    permissions,
    urls,
}: TrashedAppointmentsProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<TrashedAppointmentFilters>(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: TrashedAppointmentFilters) => {
            setProcessing(true);
            router.get(urls.trashed, cleanFilters(nextFilters), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.trashed],
    );

    const handleFilterSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        applyFilters(EMPTY_FILTERS);
    };

    const handleRestore = (appointmentId: number) => {
        if (!window.confirm(t('global.confirm_restore'))) {
            return;
        }

        router.post(`${urls.restore}/${appointmentId}/restore`, {}, {
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
                    <span
                        className={`${baseClass} ${disabledClass} ${roundedClass}`}
                    >
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
            <Head title={`${t('global.appointments')} — ${t('global.deleted')}`} />

            <div className="mx-auto max-w-[1600px]">
                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-orange-600 text-white shadow-md">
                                <i className="bx bx-trash text-xl" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {t('global.deleted')} {t('global.appointments')}
                                </h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    {appointments.meta.total} {t('global.results')}
                                </p>
                            </div>
                        </div>
                        <Button color="light" as={Link} href={urls.index} className="w-fit">
                            <i className="bx bx-arrow-back me-2 text-lg" />
                            {t('global.back')}
                        </Button>
                    </div>

                    <div className="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                        <h2 className="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-filter-alt text-rose-500" />
                            {t('global.filters')}
                        </h2>
                        <form onSubmit={handleFilterSubmit}>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <Label htmlFor="filter-patient-name">{t('global.patient_name')}</Label>
                                    <TextInput
                                        id="filter-patient-name"
                                        value={filters.patient_name}
                                        onChange={(event) =>
                                            setFilters((current) => ({
                                                ...current,
                                                patient_name: event.target.value,
                                            }))
                                        }
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-id-card">{t('global.card_number')}</Label>
                                    <TextInput
                                        id="filter-id-card"
                                        value={filters.id_card}
                                        onChange={(event) =>
                                            setFilters((current) => ({
                                                ...current,
                                                id_card: event.target.value,
                                            }))
                                        }
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

                    <Table id="trashed-appointments-table">
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
                                <TableHeader>{t('global.deleted')}</TableHeader>
                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {appointments.data.length === 0 ? (
                                <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                    <TableCell colSpan={10} align="center" muted className="py-12 text-base">
                                        <div className="flex flex-col items-center gap-2">
                                            <div className="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                <i className="bx bx-trash text-xl text-gray-400" />
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
                                        <TableCell muted>{appointment.deleted_at ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            {appointment.permissions.restore && permissions.restore && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleRestore(appointment.id)}
                                                    className="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30"
                                                    title={t('global.restore')}
                                                >
                                                    <i className="bx bx-undo text-lg" />
                                                    <span className="text-xs font-medium">{t('global.restore')}</span>
                                                </button>
                                            )}
                                        </TableCell>
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
