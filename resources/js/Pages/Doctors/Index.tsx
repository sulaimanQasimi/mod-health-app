import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, TextInput, ToggleSwitch } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
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
    DoctorIndexFilterOptions,
    DoctorIndexFilters,
    DoctorIndexPermissions,
    DoctorIndexStats,
    DoctorIndexUrls,
    PaginatedDoctors,
    PaginationLink,
} from '../../types/doctor';

interface IndexDoctorsProps {
    doctors: PaginatedDoctors;
    stats: DoctorIndexStats;
    filters: DoctorIndexFilters;
    filterOptions: DoctorIndexFilterOptions;
    permissions: DoctorIndexPermissions;
    urls: DoctorIndexUrls;
}

const EMPTY_FILTERS: DoctorIndexFilters = {
    search: '',
    department_id: '',
    branch_id: '',
    gender: '',
    clinic_type: '',
    active_status: '',
    join_date_from: '',
    join_date_to: '',
    per_page: '15',
};

function cleanFilters(filters: DoctorIndexFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

function decodePaginationLabel(label: string): string {
    return label
        .replace('&laquo;', '«')
        .replace('&raquo;', '»')
        .replace(/&[^;]+;/g, '')
        .trim();
}

export default function IndexDoctors({
    doctors,
    stats,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: IndexDoctorsProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<DoctorIndexFilters>(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [statusUpdatingId, setStatusUpdatingId] = useState<number | null>(null);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: DoctorIndexFilters) => {
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

    const handleSelectChange = (field: keyof DoctorIndexFilters, value: string) => {
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

    const handleStatusToggle = (doctorId: number, checked: boolean) => {
        setStatusUpdatingId(doctorId);
        router.post(
            `${urls.updateStatus}/${doctorId}/status`,
            { active_status: checked },
            {
                preserveScroll: true,
                onFinish: () => setStatusUpdatingId(null),
            },
        );
    };

    const handleDelete = (doctorId: number, doctorName: string) => {
        if (!window.confirm(`${t('global.are_you_sure')} ${doctorName}?`)) {
            return;
        }

        setDeletingId(doctorId);
        router.delete(`${urls.destroy}/${doctorId}`, {
            preserveScroll: true,
            onFinish: () => setDeletingId(null),
        });
    };

    const summaryLabel =
        doctors.meta.from && doctors.meta.to
            ? `${t('global.showing')} ${doctors.meta.from}-${doctors.meta.to} ${t('global.of')} ${doctors.meta.total} ${t('global.results')}`
            : `${doctors.meta.total} ${t('global.results')}`;

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

    const clinicTypeLabel = (value: string | null) => {
        if (value === 'hospital') return t('global.hospital');
        if (value === 'clinic') return t('global.clinic');
        return '—';
    };

    const genderLabel = (value: string | null) => {
        if (value === 'Male') return t('global.male');
        if (value === 'Female') return t('global.female');
        if (value === 'Other') return t('global.other');
        return '—';
    };

    return (
        <DashboardLayout>
            <Head title={t('global.doctors')} />

            <div className="mx-auto max-w-[1600px] space-y-6">
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    {[
                        {
                            label: t('global.active'),
                            value: stats.active,
                            icon: 'bx-user-check',
                            color: 'from-emerald-500 to-green-600',
                        },
                        {
                            label: t('global.inactive') || t('global.deactive'),
                            value: stats.inactive,
                            icon: 'bx-user-x',
                            color: 'from-rose-500 to-red-600',
                        },
                        {
                            label: t('global.total'),
                            value: stats.total,
                            icon: 'bx-group',
                            color: 'from-blue-500 to-indigo-600',
                        },
                        {
                            label: t('global.dentist'),
                            value: stats.dentists,
                            icon: 'bx-plus-medical',
                            color: 'from-cyan-500 to-sky-600',
                        },
                        {
                            label: t('global.eye_doctor'),
                            value: stats.eye_doctors,
                            icon: 'bx-show',
                            color: 'from-teal-500 to-cyan-600',
                        },
                    ].map((card) => (
                        <Card key={card.label} className="shadow-sm">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">{card.label}</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                        {card.value}
                                    </p>
                                </div>
                                <div
                                    className={`flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${card.color} text-white shadow-md`}
                                >
                                    <i className={`bx ${card.icon} text-xl`} />
                                </div>
                            </div>
                        </Card>
                    ))}
                </div>

                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 text-white shadow-md">
                                <i className="bx bx-user-md text-xl" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {t('global.list_doctors')}
                                </h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{summaryLabel}</p>
                            </div>
                        </div>
                        {permissions.create && (
                            <Button color="blue" as={Link} href={urls.create} className="w-fit">
                                <i className="bx bx-plus me-2 text-lg" />
                                {t('global.create_doctor')}
                            </Button>
                        )}
                    </div>

                    <form onSubmit={handleFilterSubmit} className="mb-6 space-y-4">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <Label>{t('global.search')}</Label>
                                <TextInput
                                    value={filters.search}
                                    onChange={(event) =>
                                        setFilters({ ...filters, search: event.target.value })
                                    }
                                    placeholder={t('global.search')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.department')}</Label>
                                <SearchableSelect
                                    value={filters.department_id}
                                    onChange={(value) => handleSelectChange('department_id', value)}
                                    options={filterOptions.departments.map((department) => ({
                                        value: String(department.id),
                                        label: department.name,
                                    }))}
                                    placeholder={t('global.all_departments') || t('global.all')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.branch')}</Label>
                                <SearchableSelect
                                    value={filters.branch_id}
                                    onChange={(value) => handleSelectChange('branch_id', value)}
                                    options={filterOptions.branches.map((branch) => ({
                                        value: String(branch.id),
                                        label: branch.name,
                                    }))}
                                    placeholder={t('global.all_branches') || t('global.all')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.gender')}</Label>
                                <SearchableSelect
                                    value={filters.gender}
                                    onChange={(value) => handleSelectChange('gender', value)}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="Male">{t('global.male')}</option>
                                    <option value="Female">{t('global.female')}</option>
                                    <option value="Other">{t('global.other')}</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.clinic_type')}</Label>
                                <SearchableSelect
                                    value={filters.clinic_type}
                                    onChange={(value) => handleSelectChange('clinic_type', value)}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="hospital">{t('global.hospital')}</option>
                                    <option value="clinic">{t('global.clinic')}</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.active_status')}</Label>
                                <SearchableSelect
                                    value={filters.active_status}
                                    onChange={(value) => handleSelectChange('active_status', value)}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="1">{t('global.active')}</option>
                                    <option value="0">{t('global.inactive') || t('global.deactive')}</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.join_date_from') || t('global.join_date')}</Label>
                                <TextInput
                                    value={filters.join_date_from}
                                    onChange={(event) =>
                                        setFilters({ ...filters, join_date_from: event.target.value })
                                    }
                                    placeholder="1403/01/01"
                                />
                            </div>
                            <div>
                                <Label>{t('global.join_date_to') || t('global.join_date')}</Label>
                                <TextInput
                                    value={filters.join_date_to}
                                    onChange={(event) =>
                                        setFilters({ ...filters, join_date_to: event.target.value })
                                    }
                                    placeholder="1403/01/01"
                                />
                            </div>
                            <div>
                                <Label>{t('global.per_page')}</Label>
                                <SearchableSelect
                                    value={filters.per_page || '15'}
                                    onChange={(value) => handleSelectChange('per_page', value)}
                                >
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </SearchableSelect>
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.apply_filters')}
                            </Button>
                            <Button type="button" color="light" onClick={handleReset} disabled={processing}>
                                {t('global.clear_all')}
                            </Button>
                        </div>
                    </form>

                    {doctors.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.qualification')}</TableHeader>
                                    <TableHeader>{t('global.contact_number')}</TableHeader>
                                    <TableHeader>{t('global.department')}</TableHeader>
                                    <TableHeader>{t('global.branch')}</TableHeader>
                                    <TableHeader>{t('global.specialization')}</TableHeader>
                                    <TableHeader>{t('global.gender')}</TableHeader>
                                    <TableHeader>{t('global.clinic_type')}</TableHeader>
                                    <TableHeader>{t('global.active_status')}</TableHeader>
                                    <TableHeader>{t('global.type')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {doctors.data.map((doctor, index) => (
                                    <TableRow key={doctor.id}>
                                        <TableCell>{(doctors.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>
                                            <Link
                                                href={`${urls.show}/${doctor.id}`}
                                                className="font-medium text-blue-600 hover:underline dark:text-blue-400"
                                            >
                                                {doctor.name}
                                            </Link>
                                        </TableCell>
                                        <TableCell muted>{doctor.qualification ?? '—'}</TableCell>
                                        <TableCell muted>{doctor.contact_number ?? '—'}</TableCell>
                                        <TableCell muted>{doctor.department_name ?? '—'}</TableCell>
                                        <TableCell muted>{doctor.branch_name ?? '—'}</TableCell>
                                        <TableCell muted>{doctor.specialization ?? '—'}</TableCell>
                                        <TableCell muted>{genderLabel(doctor.gender)}</TableCell>
                                        <TableCell muted>{clinicTypeLabel(doctor.clinic_type)}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                {permissions.toggleStatus ? (
                                                    <ToggleSwitch
                                                        checked={doctor.active_status}
                                                        disabled={statusUpdatingId === doctor.id}
                                                        onChange={(checked) =>
                                                            handleStatusToggle(doctor.id, checked)
                                                        }
                                                    />
                                                ) : null}
                                                <Badge color={doctor.active_status ? 'success' : 'gray'}>
                                                    {doctor.active_status
                                                        ? t('global.active')
                                                        : t('global.inactive') || t('global.deactive')}
                                                </Badge>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                {doctor.is_dentist && (
                                                    <Badge color="info">{t('global.dentist')}</Badge>
                                                )}
                                                {doctor.is_nephrologist && (
                                                    <Badge color="purple">
                                                        {t('global.nephrology')}
                                                    </Badge>
                                                )}
                                                {doctor.is_eye_doctor && (
                                                    <Badge color="success">{t('global.eye_doctor')}</Badge>
                                                )}
                                                {!doctor.is_dentist && !doctor.is_nephrologist && !doctor.is_eye_doctor && '—'}
                                            </div>
                                        </TableCell>
                                        <TableCell align="center">
                                            <div className="flex items-center justify-center gap-1">
                                                <Link
                                                    href={`${urls.show}/${doctor.id}`}
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                    title={t('global.view')}
                                                >
                                                    <i className="bx bx-show text-lg" />
                                                </Link>
                                                {permissions.edit && (
                                                    <Link
                                                        href={`${urls.edit}/${doctor.id}/edit`}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                        title={t('global.edit')}
                                                    >
                                                        <i className="bx bx-edit text-lg" />
                                                    </Link>
                                                )}
                                                {permissions.delete && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDelete(doctor.id, doctor.name)}
                                                        disabled={deletingId === doctor.id}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50 disabled:opacity-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                        title={t('global.delete')}
                                                    >
                                                        <i className="bx bx-trash text-lg" />
                                                    </button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <div className="rounded-xl border border-dashed border-gray-200 px-6 py-12 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            {t('global.no_doctors_found')}
                        </div>
                    )}

                    {doctors.links.length > 0 && (
                        <div className="mt-6 flex flex-col gap-4 border-t border-gray-200 pt-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-sm text-gray-500 dark:text-gray-400">{summaryLabel}</p>
                            <ul className="inline-flex -space-x-px text-sm">
                                {doctors.links.map((link, index) => renderPaginationLink(link, index))}
                            </ul>
                        </div>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
