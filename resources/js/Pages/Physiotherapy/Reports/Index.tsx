import { Head, router, usePage } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, Tabs } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader, { SettingsPageActions } from '../../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../../Components/ui/PersianDateInput';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../../Components/ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface Summary {
    total_procedures: number;
    completed_procedures: number;
    in_progress_procedures: number;
    pending_procedures: number;
    cancelled_procedures: number;
    total_duration: number;
    average_duration: number;
    completion_rate: number;
}

interface DetailedItem {
    id: number;
    patient_name: string | null;
    type_name: string | null;
    doctor_name: string | null;
    status: string;
    duration: number | null;
    start_date: string | null;
}

interface ByTypeItem {
    type_id: number;
    type_name: string;
    total_procedures: number;
    completed_procedures: number;
    completion_rate: number;
    total_duration: number;
    average_duration: number;
}

interface ByPhysiotherapistItem {
    name: string;
    email: string | null;
    total_procedures: number;
    completed_procedures: number;
    completion_rate: number;
    total_duration: number;
    average_duration: number;
}

interface IndexProps {
    hasSearch: boolean;
    error: string | null;
    filters: { start_date: string; end_date: string };
    summary: Summary | null;
    detailed: DetailedItem[];
    byType: ByTypeItem[];
    byPhysiotherapist: ByPhysiotherapistItem[];
    urls: { current: string; export: string };
}

const EMPTY_FILTERS = { start_date: '', end_date: '' };

function statusColor(status: string): 'success' | 'info' | 'warning' | 'failure' | 'gray' {
    const map: Record<string, 'success' | 'info' | 'warning' | 'failure' | 'gray'> = {
        completed: 'success',
        in_progress: 'info',
        pending: 'warning',
        cancelled: 'failure',
    };
    return map[status] ?? 'gray';
}

export default function PhysiotherapyReportsIndex({
    hasSearch,
    error,
    filters: serverFilters,
    summary,
    detailed,
    byType,
    byPhysiotherapist,
    urls,
}: IndexProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [filtersOpen, setFiltersOpen] = useState(true);
    const [activeTab, setActiveTab] = useState(0);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!filters.start_date || !filters.end_date) {
            return;
        }
        setProcessing(true);
        router.get(
            urls.current,
            { search: '1', start_date: filters.start_date, end_date: filters.end_date },
            { preserveScroll: true, onFinish: () => setProcessing(false) }
        );
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const canExport = hasSearch && summary !== null;

    return (
        <DashboardLayout>
            <Head title={t('global.reports')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.reports')}
                    subtitle={t('global.physiotherapy')}
                    icon="bx-dumbbell"
                    accent="from-sky-600 to-blue-700"
                    action={
                        canExport ? (
                            <SettingsPageActions>
                                <form action={urls.export} method="POST" target="_blank" className="inline-flex gap-2">
                                    <input type="hidden" name="_token" value={csrfToken} />
                                    <input type="hidden" name="start_date" value={filters.start_date} />
                                    <input type="hidden" name="end_date" value={filters.end_date} />
                                    <button
                                        type="submit"
                                        name="format"
                                        value="excel"
                                        className="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300"
                                    >
                                        <i className="bx bx-spreadsheet" />
                                        Excel
                                    </button>
                                    <button
                                        type="submit"
                                        name="format"
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

                {error && (
                    <Card className="border-rose-200 bg-rose-50 !shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
                        <p className="text-sm text-rose-700 dark:text-rose-300">{t(error)}</p>
                    </Card>
                )}

                {hasSearch && summary && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {[
                            { label: t('global.total'), value: summary.total_procedures, icon: 'bx-list-ul' },
                            { label: t('global.completed'), value: summary.completed_procedures, icon: 'bx-check-circle' },
                            { label: t('global.completion_rate'), value: `${summary.completion_rate}%`, icon: 'bx-trending-up' },
                            { label: t('global.average_duration'), value: summary.average_duration, icon: 'bx-time' },
                        ].map((stat) => (
                            <Card key={stat.label} className="!shadow-sm">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400">
                                        <i className={`bx ${stat.icon} text-lg`} />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500">{stat.label}</p>
                                        <p className="text-xl font-bold">{stat.value}</p>
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
                            <i className="bx bx-filter-alt text-sky-500" />
                            {t('global.advanced_filters')}
                        </span>
                        <i className={`bx ${filtersOpen ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
                    </button>

                    {filtersOpen && (
                        <form onSubmit={handleSubmit} className="space-y-4 pt-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label>{t('global.from')}</Label>
                                    <PersianDateInput
                                        value={filters.start_date}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, start_date: value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.to')}</Label>
                                    <PersianDateInput
                                        value={filters.end_date}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, end_date: value }))}
                                    />
                                </div>
                            </div>
                            <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                                <Button
                                    type="submit"
                                    color="blue"
                                    disabled={processing || !filters.start_date || !filters.end_date}
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
                        <div className="flex flex-col items-center gap-3 py-16 text-center text-gray-500">
                            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-sky-50 dark:bg-sky-950/30">
                                <i className="bx bx-dumbbell text-2xl text-sky-500" />
                            </div>
                            <p className="text-sm">{t('global.search_and_filters')}</p>
                        </div>
                    ) : (
                        <Tabs aria-label="Physiotherapy report tabs" onActiveTabChange={setActiveTab}>
                            <Tabs.Item active={activeTab === 0} title={t('global.summary')}>
                                {summary && (
                                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        {[
                                            ['global.total', summary.total_procedures],
                                            ['global.completed', summary.completed_procedures],
                                            ['global.in_progress', summary.in_progress_procedures],
                                            ['global.pending', summary.pending_procedures],
                                            ['global.cancelled', summary.cancelled_procedures],
                                            ['global.total_duration', summary.total_duration],
                                        ].map(([key, value]) => (
                                            <div
                                                key={key}
                                                className="rounded-xl border border-gray-100 p-4 dark:border-gray-700"
                                            >
                                                <p className="text-xs text-gray-500">{t(key)}</p>
                                                <p className="text-lg font-semibold">{value}</p>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </Tabs.Item>
                            <Tabs.Item active={activeTab === 1} title={t('global.detailed')}>
                                <div className="overflow-x-auto">
                                    <Table embedded>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>#</TableHeader>
                                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                                <TableHeader>{t('global.type')}</TableHeader>
                                                <TableHeader>{t('global.doctor')}</TableHeader>
                                                <TableHeader>{t('global.status')}</TableHeader>
                                                <TableHeader>{t('global.duration')}</TableHeader>
                                                <TableHeader>{t('global.date')}</TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {detailed.map((item, index) => (
                                                <TableRow key={item.id}>
                                                    <TableCell className="font-mono text-xs">{index + 1}</TableCell>
                                                    <TableCell>{item.patient_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.type_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.doctor_name ?? '—'}</TableCell>
                                                    <TableCell>
                                                        <Badge color={statusColor(item.status)}>{item.status}</Badge>
                                                    </TableCell>
                                                    <TableCell muted>{item.duration ?? '—'}</TableCell>
                                                    <TableCell muted dir="ltr">{item.start_date ?? '—'}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </Tabs.Item>
                            <Tabs.Item active={activeTab === 2} title={t('global.by_type')}>
                                <div className="overflow-x-auto">
                                    <Table embedded>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.type')}</TableHeader>
                                                <TableHeader className="text-center">{t('global.total')}</TableHeader>
                                                <TableHeader className="text-center">{t('global.completed')}</TableHeader>
                                                <TableHeader className="text-center">{t('global.completion_rate')}</TableHeader>
                                                <TableHeader className="text-center">{t('global.average_duration')}</TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {byType.map((item) => (
                                                <TableRow key={item.type_id}>
                                                    <TableCell className="font-medium">{item.type_name}</TableCell>
                                                    <TableCell align="center">{item.total_procedures}</TableCell>
                                                    <TableCell align="center">{item.completed_procedures}</TableCell>
                                                    <TableCell align="center">{item.completion_rate}%</TableCell>
                                                    <TableCell align="center">{item.average_duration}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </Tabs.Item>
                            <Tabs.Item active={activeTab === 3} title={t('global.by_physiotherapist')}>
                                <div className="overflow-x-auto">
                                    <Table embedded>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.name')}</TableHeader>
                                                <TableHeader>{t('global.email')}</TableHeader>
                                                <TableHeader className="text-center">{t('global.total')}</TableHeader>
                                                <TableHeader className="text-center">{t('global.completed')}</TableHeader>
                                                <TableHeader className="text-center">{t('global.completion_rate')}</TableHeader>
                                                <TableHeader className="text-center">{t('global.average_duration')}</TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {byPhysiotherapist.map((item, index) => (
                                                <TableRow key={`${item.name}-${index}`}>
                                                    <TableCell className="font-medium">{item.name}</TableCell>
                                                    <TableCell muted>{item.email ?? '—'}</TableCell>
                                                    <TableCell align="center">{item.total_procedures}</TableCell>
                                                    <TableCell align="center">{item.completed_procedures}</TableCell>
                                                    <TableCell align="center">{item.completion_rate}%</TableCell>
                                                    <TableCell align="center">{item.average_duration}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </Tabs.Item>
                        </Tabs>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
