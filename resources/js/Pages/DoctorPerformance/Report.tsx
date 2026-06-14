import { Head, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
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
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface PerformanceResult {
    user: string | null;
    appointments: number;
    prescriptions: number;
    lab_tests: number;
    anesthesias: number;
    total: number;
}

interface DoctorOption {
    id: number;
    name: string;
    specialization: string | null;
    department_name: string | null;
}

interface ReportProps {
    results: PerformanceResult[];
    summary: {
        appointments: number;
        prescriptions: number;
        lab_tests: number;
        anesthesias: number;
        total: number;
    };
    hasSearch: boolean;
    error: string | null;
    filters: {
        startDate: string;
        endDate: string;
        doctorId: string;
    };
    filterOptions: {
        doctors: DoctorOption[];
    };
    urls: {
        current: string;
    };
}

const EMPTY_FILTERS = {
    startDate: '',
    endDate: '',
    doctorId: '',
};

export default function DoctorPerformanceReport({
    results,
    summary,
    hasSearch,
    error,
    filters: serverFilters,
    filterOptions,
    urls,
}: ReportProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [filtersOpen, setFiltersOpen] = useState(true);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const grandTotal = summary.total;

    const resultsWithPercentage = useMemo(
        () =>
            results.map((row) => ({
                ...row,
                percentage: grandTotal > 0 ? (row.total / grandTotal) * 100 : 0,
            })),
        [results, grandTotal]
    );

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!filters.startDate || !filters.endDate) {
            return;
        }
        setProcessing(true);
        const params: Record<string, string> = {
            search: '1',
            startDate: filters.startDate,
            endDate: filters.endDate,
        };
        if (filters.doctorId) {
            params.doctorId = filters.doctorId;
        }
        router.get(urls.current, params, {
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

    return (
        <DashboardLayout>
            <Head title={t('global.user_performance_report')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.user_performance_report')}
                    icon="bx-line-chart"
                    accent="from-rose-600 to-pink-700"
                />

                {error && (
                    <Card className="border-rose-200 bg-rose-50 !shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
                        <p className="text-sm text-rose-700 dark:text-rose-300">{t(error)}</p>
                    </Card>
                )}

                {hasSearch && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        {[
                            {
                                label: t('global.appointments'),
                                value: summary.appointments,
                                icon: 'bx-calendar',
                                accent: 'from-blue-500 to-cyan-600',
                            },
                            {
                                label: t('global.prescriptions'),
                                value: summary.prescriptions,
                                icon: 'bx-receipt',
                                accent: 'from-emerald-500 to-teal-600',
                            },
                            {
                                label: t('global.lab_tests'),
                                value: summary.lab_tests,
                                icon: 'bx-test-tube',
                                accent: 'from-violet-500 to-purple-600',
                            },
                            {
                                label: t('global.anesthesias'),
                                value: summary.anesthesias,
                                icon: 'bx-first-aid',
                                accent: 'from-amber-500 to-orange-600',
                            },
                            {
                                label: t('global.total'),
                                value: summary.total,
                                icon: 'bx-calculator',
                                accent: 'from-rose-500 to-pink-600',
                            },
                        ].map((stat) => (
                            <Card key={stat.label} className="overflow-hidden !shadow-sm">
                                <div className="flex items-center gap-3">
                                    <div
                                        className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${stat.accent} text-white shadow-md`}
                                    >
                                        <i className={`bx ${stat.icon} text-lg`} />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">{stat.label}</p>
                                        <p className="text-xl font-bold text-gray-900 dark:text-white">
                                            {stat.value.toLocaleString()}
                                        </p>
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
                            <i className="bx bx-filter-alt text-rose-500" />
                            {t('global.advanced_filters')}
                        </span>
                        <i className={`bx ${filtersOpen ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
                    </button>

                    {filtersOpen && (
                        <form onSubmit={handleSubmit} className="space-y-4 pt-4">
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <Label>{t('global.from')}</Label>
                                    <PersianDateInput
                                        value={filters.startDate}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, startDate: value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.to')}</Label>
                                    <PersianDateInput
                                        value={filters.endDate}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, endDate: value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.doctor')}</Label>
                                    <SearchableSelect
                                        value={filters.doctorId}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, doctorId: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.doctors.map((doctor) => ({
                                                value: String(doctor.id),
                                                label: doctor.department_name
                                                    ? `${doctor.name} — ${doctor.department_name}`
                                                    : doctor.name,
                                            })),
                                        ]}
                                    />
                                </div>
                            </div>
                            <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                                <Button
                                    type="submit"
                                    color="blue"
                                    disabled={processing || !filters.startDate || !filters.endDate}
                                >
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
                    {!hasSearch ? (
                        <div className="flex flex-col items-center gap-3 py-16 text-center text-gray-500 dark:text-gray-400">
                            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 dark:bg-rose-950/30">
                                <i className="bx bx-line-chart text-2xl text-rose-500" />
                            </div>
                            <p className="text-sm">{t('global.search_and_filters')}</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <Table embedded>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.user')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.appointments')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.prescriptions')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.lab_tests')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.anesthesias')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.total')}</TableHeader>
                                        <TableHeader className="text-center">{t('global.percentage')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {resultsWithPercentage.length === 0 ? (
                                        <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                            <TableCell colSpan={7} align="center" muted className="py-12">
                                                {t('global.no_records_found')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        resultsWithPercentage.map((row, index) => (
                                            <TableRow key={`${row.user}-${index}`}>
                                                <TableCell className="font-medium">{row.user ?? '—'}</TableCell>
                                                <TableCell align="center">
                                                    <Badge color="info">{row.appointments.toLocaleString()}</Badge>
                                                </TableCell>
                                                <TableCell align="center">
                                                    <Badge color="success">{row.prescriptions.toLocaleString()}</Badge>
                                                </TableCell>
                                                <TableCell align="center">
                                                    <Badge color="purple">{row.lab_tests.toLocaleString()}</Badge>
                                                </TableCell>
                                                <TableCell align="center">
                                                    <Badge color="warning">{row.anesthesias.toLocaleString()}</Badge>
                                                </TableCell>
                                                <TableCell align="center" className="font-semibold">
                                                    {row.total.toLocaleString()}
                                                </TableCell>
                                                <TableCell align="center" muted>
                                                    {row.percentage.toFixed(1)}%
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
