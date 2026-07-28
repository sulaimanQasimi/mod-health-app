import { router, usePage } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, Tabs } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import {
    ReportAnalyticsSection,
    ReportExportButtons,
    ReportFilterPanel,
    ReportKpiGrid,
    ReportPageShell,
    ReportResultsCard,
} from '../../../Components/Reports';
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
    const kpis = summary
        ? [
              { key: 'total', label: t('global.total'), value: summary.total_procedures, icon: 'bx-list-ul', accent: 'from-sky-500 to-blue-600' },
              { key: 'completed', label: t('global.completed'), value: summary.completed_procedures, icon: 'bx-check-circle', accent: 'from-emerald-500 to-teal-600' },
              { key: 'rate', label: t('global.completion_rate'), value: `${summary.completion_rate}%`, icon: 'bx-trending-up', accent: 'from-violet-500 to-purple-600' },
              { key: 'duration', label: t('global.average_duration'), value: summary.average_duration, icon: 'bx-time', accent: 'from-amber-500 to-orange-600' },
          ]
        : [];
    const charts = summary
        ? [
              {
                  key: 'status', title: t('global.status'), type: 'donut' as const,
                  labels: [t('global.completed'), t('global.in_progress'), t('global.pending'), t('global.cancelled')],
                  values: [summary.completed_procedures, summary.in_progress_procedures, summary.pending_procedures, summary.cancelled_procedures],
                  colors: ['#10b981', '#0ea5e9', '#f59e0b', '#ef4444'],
              },
              {
                  key: 'types', title: t('global.by_type'), type: 'bar' as const,
                  labels: byType.map((item) => item.type_name),
                  values: byType.map((item) => item.total_procedures),
                  color: '#0284c7',
              },
              {
                  key: 'physiotherapists', title: t('global.by_physiotherapist'), type: 'bar' as const,
                  labels: byPhysiotherapist.map((item) => item.name),
                  values: byPhysiotherapist.map((item) => item.total_procedures),
                  color: '#7c3aed',
              },
          ]
        : [];

    return (
        <ReportPageShell
            title={t('global.reports')}
            subtitle={t('global.physiotherapy')}
            icon="bx-dumbbell"
            accent="from-sky-600 to-blue-700"
            backLabel={t('global.back')}
            action={canExport ? <ReportExportButtons action={urls.export} csrfToken={csrfToken} fields={filters} typeField="format" /> : undefined}
        >

                {error && (
                    <Card className="border-rose-200 bg-rose-50 !shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
                        <p className="text-sm text-rose-700 dark:text-rose-300">{t(error)}</p>
                    </Card>
                )}

                {hasSearch ? <ReportKpiGrid stats={kpis} /> : null}
                {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={charts} /> : null}
                <ReportFilterPanel
                    title={t('global.advanced_filters')}
                    onSubmit={handleSubmit}
                    accentIconClass="text-sky-500"
                    actions={<>
                        <Button type="submit" color="blue" disabled={processing || !filters.start_date || !filters.end_date}>
                            {processing ? <><Spinner size="sm" className="me-2" />{t('global.loading')}</> : <><i className="bx bx-search me-2" />{t('global.search')}</>}
                        </Button>
                        <Button type="button" color="light" onClick={handleReset} disabled={processing}><i className="bx bx-refresh me-2" />{t('global.reset')}</Button>
                    </>}
                >
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
                </ReportFilterPanel>
                <ReportResultsCard title={t('global.reports')} hasSearch={hasSearch} resultCount={summary?.total_procedures} resultsLabel={t('global.results')} emptyMessage={t('global.search_and_filters')}>
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
                </ReportResultsCard>
        </ReportPageShell>
    );
}
