import { router, usePage } from '@inertiajs/react';
import { Badge, Button, Card, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import PacuNavTabs from '../../Components/Pacus/PacuNavTabs';
import {
    ReportAnalyticsSection,
    ReportExportButtons,
    ReportFilterPanel,
    ReportKpiGrid,
    ReportPageShell,
    ReportResultsCard,
} from '../../Components/Reports';
import PersianDateInput from '../../Components/ui/PersianDateInput';
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
import { PacuListUrls, PacuReportFilters, PacuReportItem } from '../../types/pacu';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    items: PacuReportItem[];
    hasSearch: boolean;
    summary: { total: number; new: number; completed: number };
    analytics: { by_status: { name: string; count: number }[]; by_department: { name: string; count: number }[] };
    filters: PacuReportFilters;
    urls: PacuListUrls & { current: string; export: string };
}

function statusBadge(status: string, t: (key: string) => string) {
    if (status === 'new') {
        return <Badge color="info">{t('global.new_pacus')}</Badge>;
    }
    return <Badge color="success">{t('global.completed_pacus')}</Badge>;
}

export default function PacusReport({ items, hasSearch, summary, analytics, filters, urls }: ReportProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [form, setForm] = useState(filters);
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.get(
            urls.current,
            {
                patient_name: form.patient_name,
                status: form.status,
                date_from: form.date_from,
                date_to: form.date_to,
                search: '1',
            },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            }
        );
    };

    const handleReset = () => {
        const empty = { patient_name: '', status: '', date_from: '', date_to: '' };
        setForm(empty);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <ReportPageShell title={t('global.reports')} subtitle={t('global.pacus')} accent="from-violet-600 to-purple-700" backLabel={t('global.back')}
            action={hasSearch && items.length ? <ReportExportButtons action={urls.export} csrfToken={csrfToken} fields={{ data: JSON.stringify(items.map((item) => item.id)) }} /> : undefined}>
            {hasSearch ? <ReportKpiGrid stats={[
                { key: 'total', label: t('global.total'), value: summary.total, icon: 'bx-building-house', accent: 'from-cyan-500 to-blue-600' },
                { key: 'new', label: t('global.new_pacus'), value: summary.new, icon: 'bx-time-five', accent: 'from-amber-500 to-orange-600' },
                { key: 'completed', label: t('global.completed_pacus'), value: summary.completed, icon: 'bx-check-circle', accent: 'from-emerald-500 to-teal-600' },
            ]} /> : null}
            {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={[
                { key: 'status', title: t('global.status'), type: 'donut', labels: analytics.by_status.map((item) => item.name === 'new' ? t('global.new_pacus') : t('global.completed_pacus')), values: analytics.by_status.map((item) => item.count), colors: ['#0ea5e9', '#10b981'] },
                { key: 'department', title: t('global.department'), type: 'bar', labels: analytics.by_department.map((item) => item.name), values: analytics.by_department.map((item) => item.count), color: '#2563eb' },
            ]} /> : null}
                    <PacuNavTabs active="report" urls={urls} />

                <ReportFilterPanel title={t('global.documents.search')} onSubmit={handleSubmit} actions={<><Button type="submit" color="blue" size="sm" disabled={processing}><i className="bx bx-search me-1" />{t('global.documents.search')}</Button><Button type="button" color="light" size="sm" disabled={processing} onClick={handleReset}>{t('global.reset')}</Button></>}>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label htmlFor="pacu-report-patient-name">{t('global.patient_name')}</Label>
                            <TextInput
                                id="pacu-report-patient-name"
                                sizing="sm"
                                value={form.patient_name}
                                onChange={(e) => setForm((prev) => ({ ...prev, patient_name: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="pacu-report-status">{t('global.status')}</Label>
                            <Select
                                id="pacu-report-status"
                                sizing="sm"
                                value={form.status}
                                onChange={(e) => setForm((prev) => ({ ...prev, status: e.target.value }))}
                            >
                                <option value="">{t('global.select')}</option>
                                <option value="new">{t('global.new_pacus')}</option>
                                <option value="completed">{t('global.completed_pacus')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.from')}</Label>
                            <PersianDateInput
                                value={form.date_from}
                                onChange={(date_from) => setForm((prev) => ({ ...prev, date_from }))}
                            />
                        </div>
                        <div>
                            <Label>{t('global.to')}</Label>
                            <PersianDateInput
                                value={form.date_to}
                                onChange={(date_to) => setForm((prev) => ({ ...prev, date_to }))}
                            />
                        </div>
                    </div>
                </ReportFilterPanel>

                <ReportResultsCard title={t('global.reports')} hasSearch={hasSearch} resultCount={items.length} resultsLabel={t('global.records')} emptyMessage={t('global.search_and_filters')}>
                    <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('global.number')}</TableHead>
                                    <TableHead>{t('global.patient_name')}</TableHead>
                                    <TableHead>{t('global.status')}</TableHead>
                                    <TableHead>{t('global.branch')}</TableHead>
                                    <TableHead>{t('global.date')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                                        <TableCell>{statusBadge(item.status, t)}</TableCell>
                                        <TableCell>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell dir="ltr">{item.created_at ?? '—'}</TableCell>
                                    </TableRow>
                                ))}
                                {items.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="py-10 text-center text-gray-500">
                                            {t('global.no_item_is_found')}
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </ReportResultsCard>
        </ReportPageShell>
    );
}
