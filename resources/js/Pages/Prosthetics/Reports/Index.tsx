import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import PersianDateInput from '../../../Components/ui/PersianDateInput';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../../Components/ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import { buildPaginationSummary } from '../../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface ReportCase {
    id: number;
    case_number: string;
    status: string;
    created_at: string | null;
    patient?: { name: string; last_name?: string };
    deliveries?: Array<{ delivered_at: string | null }>;
}

interface IndexProps {
    cases: {
        data: ReportCase[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;
        };
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    statusCounts: Record<string, number>;
    hasSearch: boolean;
    summary: {
        avg_days: number | null;
        delivered_count: number;
        total_cases: number;
    };
    filters: { status: string; from: string; to: string };
    statusOptions: string[];
    urls: { current: string; dashboard: string; caseShow: string };
}

const EMPTY_FILTERS = { status: '', from: '', to: '' };

export default function ProstheticsReportsIndex({
    cases,
    statusCounts,
    hasSearch,
    summary,
    filters: serverFilters,
    statusOptions,
    urls,
}: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [filtersOpen, setFiltersOpen] = useState(true);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: typeof filters) => {
            setProcessing(true);
            const params: Record<string, string> = { search: '1' };
            Object.entries(next).forEach(([key, value]) => {
                if (value !== '') {
                    params[key] = value;
                }
            });
            router.get(urls.current, params, {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current]
    );

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
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
            <Head title={t('global.reports')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.reports')}
                    subtitle={t('global.prosthetics')}
                    icon="bx-bar-chart-alt-2"
                    accent="from-violet-500 to-purple-600"
                    backHref={urls.dashboard}
                    backLabel={t('global.back')}
                />

                {hasSearch && (
                    <div className="grid gap-4 sm:grid-cols-3">
                        {[
                            {
                                label: t('global.prosthetics_avg_turnaround'),
                                value: summary.avg_days ?? '—',
                                icon: 'bx-time-five',
                                accent: 'from-violet-500 to-purple-600',
                            },
                            {
                                label: t('global.prosthetics_delivered_cases'),
                                value: summary.delivered_count,
                                icon: 'bx-package',
                                accent: 'from-emerald-500 to-teal-600',
                            },
                            {
                                label: t('global.total'),
                                value: summary.total_cases,
                                icon: 'bx-folder',
                                accent: 'from-blue-500 to-cyan-600',
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
                            <i className="bx bx-filter-alt text-violet-500" />
                            {t('global.advanced_filters')}
                        </span>
                        <i className={`bx ${filtersOpen ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
                    </button>

                    {filtersOpen && (
                        <form onSubmit={handleSubmit} className="space-y-4 pt-4">
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <Label>{t('global.status')}</Label>
                                    <SearchableSelect
                                        value={filters.status}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, status: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...statusOptions.map((status) => ({
                                                value: status,
                                                label: t(`global.prosthetics_case_status_${status}`),
                                            })),
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.from')}</Label>
                                    <PersianDateInput
                                        value={filters.from}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, from: value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.to')}</Label>
                                    <PersianDateInput
                                        value={filters.to}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, to: value }))}
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
                                            {t('global.filter')}
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
                            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-violet-50 dark:bg-violet-950/30">
                                <i className="bx bx-search-alt text-2xl text-violet-500" />
                            </div>
                            <p className="text-sm">{t('global.search_and_filters')}</p>
                        </div>
                    ) : (
                        <>
                            <div className="mb-6 overflow-x-auto">
                                <Table embedded>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>{t('global.status')}</TableHeader>
                                            <TableHeader>{t('global.count')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {Object.entries(statusCounts).map(([status, count]) => (
                                            <TableRow key={status}>
                                                <TableCell>
                                                    {t(`global.prosthetics_case_status_${status}`)}
                                                </TableCell>
                                                <TableCell muted>{count}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            <div className="mb-3 text-sm text-gray-500">{buildPaginationSummary(cases.meta, t)}</div>
                            <div className="overflow-x-auto">
                                <Table embedded>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>{t('global.prosthetics_case_number')}</TableHeader>
                                            <TableHeader>{t('global.patient_name')}</TableHeader>
                                            <TableHeader>{t('global.status')}</TableHeader>
                                            <TableHeader>{t('global.created_at')}</TableHeader>
                                            <TableHeader>{t('global.prosthetics_delivered')}</TableHeader>
                                            <TableHeader className="text-end">{t('global.actions')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {cases.data.length === 0 ? (
                                            <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                                <TableCell colSpan={6} align="center" muted className="py-12">
                                                    {t('global.no_records_found')}
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            cases.data.map((item) => (
                                                <TableRow key={item.id}>
                                                    <TableCell className="font-mono">{item.case_number}</TableCell>
                                                    <TableCell>
                                                        {item.patient
                                                            ? `${item.patient.name} ${item.patient.last_name ?? ''}`.trim()
                                                            : '—'}
                                                    </TableCell>
                                                    <TableCell muted>
                                                        {t(`global.prosthetics_case_status_${item.status}`)}
                                                    </TableCell>
                                                    <TableCell muted dir="ltr">{item.created_at ?? '—'}</TableCell>
                                                    <TableCell muted dir="ltr">
                                                        {item.deliveries?.[0]?.delivered_at ?? '—'}
                                                    </TableCell>
                                                    <TableCell align="right">
                                                        <Link
                                                            href={`${urls.caseShow}/${item.id}`}
                                                            className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-violet-600 hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-950/30"
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
                            <SettingsPagination links={cases.links} className="mt-4" />
                        </>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
