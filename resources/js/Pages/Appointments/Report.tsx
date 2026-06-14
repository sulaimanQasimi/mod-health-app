import { Head, Link, router, usePage } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import AppointmentPagination from '../../Components/Appointments/AppointmentPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
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
import { SharedPageProps } from '../../types';
import {
    AppointmentReportFilterOptions,
    AppointmentReportFilters,
    AppointmentReportSummary,
    AppointmentReportUrls,
    PaginatedAppointmentReport,
} from '../../types/appointment';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    appointments: PaginatedAppointmentReport;
    summary: AppointmentReportSummary;
    hasSearch: boolean;
    filters: AppointmentReportFilters;
    filterOptions: AppointmentReportFilterOptions;
    urls: AppointmentReportUrls;
}

const EMPTY_FILTERS: AppointmentReportFilters = {
    patient_name: '',
    doctor_id: '',
    processed_by: '',
    registered_by: '',
    is_completed: '',
    start: '',
    end: '',
    time: '',
    clinic_type: '',
    job: '',
    job_type: '',
    gender: '',
    rank: '',
    relation_id: '',
    province_id: '',
    district_id: '',
    per_page: '25',
};

function buildSearchParams(filters: AppointmentReportFilters): Record<string, string> {
    const params: Record<string, string> = { search: '1' };
    Object.entries(filters).forEach(([key, value]) => {
        if (value !== '') {
            params[key] = value;
        }
    });
    if (!params.per_page) {
        params.per_page = filters.per_page || '25';
    }
    return params;
}

function userLabel(user: { name: string; last_name?: string | null }): string {
    return [user.name, user.last_name].filter(Boolean).join(' ').trim();
}

export default function AppointmentsReport({
    appointments,
    summary,
    hasSearch,
    filters: serverFilters,
    filterOptions,
    urls,
}: ReportProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [filtersOpen, setFiltersOpen] = useState(true);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const filteredDistricts = useMemo(() => {
        if (!filters.province_id) {
            return filterOptions.districts;
        }
        return filterOptions.districts.filter(
            (district) => String(district.province_id) === filters.province_id
        );
    }, [filterOptions.districts, filters.province_id]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.get(urls.current, buildSearchParams(filters), {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const handlePerPageChange = (value: string) => {
        const next = { ...filters, per_page: value };
        setFilters(next);
        setProcessing(true);
        router.get(urls.current, buildSearchParams(next), {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const exportFields = useMemo(() => {
        const fields: Record<string, string> = {};
        Object.entries(filters).forEach(([key, value]) => {
            if (value !== '') {
                fields[key] = value;
            }
        });
        if (hasSearch && appointments.data.length > 0) {
            fields.data = JSON.stringify(appointments.data.map((item) => item.id));
        }
        return fields;
    }, [appointments.data, filters, hasSearch]);

    const clinicTypeLabel = (value: string | null) => {
        if (value === 'hospital') {
            return t('global.hospital');
        }
        if (value === 'clinic') {
            return t('global.clinic');
        }
        return '—';
    };

    const jobTypeLabel = (value: string | null) => {
        if (!value) {
            return '—';
        }
        const key = `global.${value}` as 'global.civilian';
        const translated = t(key);
        return translated !== key ? translated : value;
    };

    const genderLabel = (value: string | number | null) => {
        if (value === null || value === '') {
            return '—';
        }
        return String(value) === '1' ? t('global.female') : t('global.male');
    };

    const canExport = hasSearch && appointments.data.length > 0;

    return (
        <DashboardLayout>
            <Head title={t('global.reports')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.reports')}
                    subtitle={t('global.appointments')}
                    icon="bx-bar-chart-alt-2"
                    accent="from-cyan-600 to-blue-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        canExport ? (
                            <SettingsPageActions>
                                <form action={urls.export} method="POST" target="_blank" className="inline-flex gap-2">
                                    <input type="hidden" name="_token" value={csrfToken} />
                                    {Object.entries(exportFields).map(([key, value]) => (
                                        <input key={key} type="hidden" name={key} value={value} />
                                    ))}
                                    <button
                                        type="submit"
                                        name="type"
                                        value="excel"
                                        className="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300"
                                    >
                                        <i className="bx bx-spreadsheet" />
                                        Excel
                                    </button>
                                    <button
                                        type="submit"
                                        name="type"
                                        value="pdf"
                                        className="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300"
                                    >
                                        <i className="bx bx-file" />
                                        PDF
                                    </button>
                                </form>
                            </SettingsPageActions>
                        ) : undefined
                    }
                />

                {hasSearch && (
                    <div className="grid gap-4 sm:grid-cols-3">
                        {[
                            {
                                label: t('global.total'),
                                value: summary.total,
                                icon: 'bx-calendar',
                                accent: 'from-cyan-500 to-blue-600',
                            },
                            {
                                label: t('global.completed_appointments'),
                                value: summary.completed,
                                icon: 'bx-check-circle',
                                accent: 'from-emerald-500 to-teal-600',
                            },
                            {
                                label: t('global.ongoing_appointments'),
                                value: summary.ongoing,
                                icon: 'bx-time-five',
                                accent: 'from-amber-500 to-orange-600',
                            },
                        ].map((stat) => (
                            <Card key={stat.label} className="overflow-hidden !shadow-sm">
                                <div className="flex items-center gap-4">
                                    <div
                                        className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${stat.accent} text-white shadow-md`}
                                    >
                                        <i className={`bx ${stat.icon} text-xl`} />
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-500 dark:text-gray-400">{stat.label}</p>
                                        <p className="text-2xl font-bold text-gray-900 dark:text-white">{stat.value}</p>
                                    </div>
                                </div>
                            </Card>
                        ))}
                    </div>
                )}

                <Card className="!shadow-sm">
                    <button
                        type="button"
                        onClick={() => setFiltersOpen((open) => !open)}
                        className="flex w-full items-center justify-between gap-3 border-b border-gray-100 px-1 pb-4 text-start dark:border-gray-700"
                    >
                        <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-filter-alt text-cyan-500" />
                            {t('global.advanced_filters')}
                        </span>
                        <i className={`bx ${filtersOpen ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
                    </button>

                    {filtersOpen && (
                        <form onSubmit={handleSubmit} className="space-y-4 pt-4">
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                <div>
                                    <Label htmlFor="patient_name">{t('global.patient_name')}</Label>
                                    <TextInput
                                        id="patient_name"
                                        value={filters.patient_name}
                                        onChange={(e) =>
                                            setFilters((prev) => ({ ...prev, patient_name: e.target.value }))
                                        }
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.doctor')}</Label>
                                    <SearchableSelect
                                        value={filters.doctor_id}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, doctor_id: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.doctors.map((doctor) => ({
                                                value: String(doctor.id),
                                                label: doctor.name,
                                            })),
                                        ]}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.processed_by')}</Label>
                                    <SearchableSelect
                                        value={filters.processed_by}
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, processed_by: value }))
                                        }
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.users.map((user) => ({
                                                value: String(user.id),
                                                label: userLabel(user),
                                            })),
                                        ]}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.registered_by')}</Label>
                                    <SearchableSelect
                                        value={filters.registered_by}
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, registered_by: value }))
                                        }
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.users.map((user) => ({
                                                value: String(user.id),
                                                label: userLabel(user),
                                            })),
                                        ]}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.status')}</Label>
                                    <SearchableSelect
                                        value={filters.is_completed}
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, is_completed: value }))
                                        }
                                        options={[
                                            { value: '', label: t('global.all') },
                                            { value: '1', label: t('global.completed_appointments') },
                                            { value: '0', label: t('global.ongoing_appointments') },
                                        ]}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.clinic_type')}</Label>
                                    <SearchableSelect
                                        value={filters.clinic_type}
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, clinic_type: value }))
                                        }
                                        options={[
                                            { value: '', label: t('global.all') },
                                            { value: 'hospital', label: t('global.hospital') },
                                            { value: 'clinic', label: t('global.clinic') },
                                        ]}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="start">{t('global.from')}</Label>
                                    <PersianDateInput
                                        id="start"
                                        value={filters.start}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, start: value }))}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="end">{t('global.to')}</Label>
                                    <PersianDateInput
                                        id="end"
                                        value={filters.end}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, end: value }))}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="time">{t('global.time')}</Label>
                                    <TextInput
                                        id="time"
                                        type="time"
                                        dir="ltr"
                                        value={filters.time}
                                        onChange={(e) => setFilters((prev) => ({ ...prev, time: e.target.value }))}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="job">{t('global.job')}</Label>
                                    <TextInput
                                        id="job"
                                        value={filters.job}
                                        onChange={(e) => setFilters((prev) => ({ ...prev, job: e.target.value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.job_type')}</Label>
                                    <SearchableSelect
                                        value={filters.job_type}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, job_type: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            { value: 'civilian', label: t('global.civilian') },
                                            { value: 'militant', label: t('global.militant') },
                                            { value: 'retired', label: t('global.retired') },
                                        ]}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.gender')}</Label>
                                    <SearchableSelect
                                        value={filters.gender}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, gender: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            { value: '0', label: t('global.male') },
                                            { value: '1', label: t('global.female') },
                                        ]}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="rank">{t('global.rank')}</Label>
                                    <TextInput
                                        id="rank"
                                        value={filters.rank}
                                        onChange={(e) => setFilters((prev) => ({ ...prev, rank: e.target.value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.relation')}</Label>
                                    <SearchableSelect
                                        value={filters.relation_id}
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, relation_id: value }))
                                        }
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.relations.map((relation) => ({
                                                value: String(relation.id),
                                                label: relation.name,
                                            })),
                                        ]}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.province')}</Label>
                                    <SearchableSelect
                                        value={filters.province_id}
                                        onChange={(value) =>
                                            setFilters((prev) => ({
                                                ...prev,
                                                province_id: value,
                                                district_id: '',
                                            }))
                                        }
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.provinces.map((province) => ({
                                                value: String(province.id),
                                                label: province.name_dr ?? province.name,
                                            })),
                                        ]}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.district')}</Label>
                                    <SearchableSelect
                                        value={filters.district_id}
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, district_id: value }))
                                        }
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filteredDistricts.map((district) => ({
                                                value: String(district.id),
                                                label: district.name_dr ?? district.name,
                                            })),
                                        ]}
                                        placeholder={t('global.all')}
                                        disabled={!filters.province_id}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.per_page')}</Label>
                                    <SearchableSelect
                                        value={filters.per_page || '25'}
                                        onChange={handlePerPageChange}
                                        options={[
                                            { value: '10', label: '10' },
                                            { value: '15', label: '15' },
                                            { value: '25', label: '25' },
                                            { value: '50', label: '50' },
                                            { value: '100', label: '100' },
                                            { value: 'all', label: t('global.all') },
                                        ]}
                                    />
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                                <Button type="submit" color="blue" disabled={processing}>
                                    {processing ? (
                                        <>
                                            <Spinner size="sm" className="me-2" />
                                            {t('global.loading')}
                                        </>
                                    ) : (
                                        <>
                                            <i className="bx bx-search me-2" />
                                            {t('global.search')}
                                        </>
                                    )}
                                </Button>
                                <Button type="button" color="light" onClick={handleReset} disabled={processing}>
                                    <i className="bx bx-refresh me-2" />
                                    {t('global.reset')}
                                </Button>
                            </div>
                        </form>
                    )}
                </Card>

                <Card className="!shadow-sm">
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h2 className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-table text-cyan-500" />
                            {t('global.reports')}
                        </h2>
                        {hasSearch && (
                            <span className="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {appointments.meta.total} {t('global.results')}
                            </span>
                        )}
                    </div>

                    {!hasSearch ? (
                        <div className="flex flex-col items-center gap-3 py-16 text-center text-gray-500 dark:text-gray-400">
                            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-cyan-50 dark:bg-cyan-950/30">
                                <i className="bx bx-search-alt text-2xl text-cyan-500" />
                            </div>
                            <p className="text-sm">{t('global.search_and_filters')}</p>
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <Table embedded>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader className="whitespace-nowrap">#</TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.patient_name')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.doctor_name')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.branch')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.clinic_type')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.processed_by')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.registered_by')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">{t('global.job')}</TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.job_type')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.gender')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">{t('global.rank')}</TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.relation')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.province')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.district')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">
                                                {t('global.status')}
                                            </TableHeader>
                                            <TableHeader className="whitespace-nowrap">{t('global.date')}</TableHeader>
                                            <TableHeader className="whitespace-nowrap">{t('global.time')}</TableHeader>
                                            <TableHeader className="whitespace-nowrap text-end">
                                                {t('global.actions')}
                                            </TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {appointments.data.length === 0 ? (
                                            <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                                <TableCell
                                                    colSpan={18}
                                                    align="center"
                                                    muted
                                                    className="py-12 text-base"
                                                >
                                                    {t('global.no_records_found')}
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            appointments.data.map((item, index) => (
                                                <TableRow key={item.id}>
                                                    <TableCell className="font-mono text-xs text-gray-500">
                                                        {(appointments.meta.from ?? 1) + index}
                                                    </TableCell>
                                                    <TableCell className="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                                        {item.patient_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {item.doctor_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {item.branch_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {clinicTypeLabel(item.clinic_type)}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {item.processed_by_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {item.registered_by_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {item.job ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {jobTypeLabel(item.job_type)}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {genderLabel(item.gender)}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {item.rank ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {item.relation_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {item.province_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {item.district_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge color={item.is_completed ? 'success' : 'info'}>
                                                            {item.is_completed
                                                                ? t('global.completed_appointments')
                                                                : t('global.ongoing_appointments')}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell muted dir="ltr" className="whitespace-nowrap">
                                                        {item.date ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted dir="ltr" className="whitespace-nowrap">
                                                        {item.time ?? '—'}
                                                    </TableCell>
                                                    <TableCell align="right">
                                                        <Link
                                                            href={item.urls.show}
                                                            className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-950/30"
                                                            title={t('global.view')}
                                                        >
                                                            <i className="bx bx-expand" />
                                                        </Link>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </div>

                            {appointments.links.length > 3 && (
                                <AppointmentPagination
                                    links={appointments.links}
                                    meta={appointments.meta}
                                    t={t}
                                />
                            )}
                        </>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
