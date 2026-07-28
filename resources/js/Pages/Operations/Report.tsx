import { router, usePage } from '@inertiajs/react';
import { Badge, Button, Card, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import OperationNavTabs from '../../Components/Operations/OperationNavTabs';
import {
    operationApprovalLabel,
    operationReservedLabel,
    OPERATION_APPROVE_BTN_CLASS,
} from '../../Components/Operations/operationUi';
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
import {
    OperationListUrls,
    OperationReportFilters,
    OperationReportItem,
    SelectOption,
} from '../../types/operation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    items: OperationReportItem[];
    hasSearch: boolean;
    summary: { total: number; completed: number; pending: number; approved: number };
    analytics: { by_status: { name: string; count: number }[]; by_department: { name: string; count: number }[] };
    filters: OperationReportFilters;
    filterOptions: {
        operationTypes: SelectOption[];
        surgeons: SelectOption[];
    };
    urls: OperationListUrls & { current: string; export: string };
}

export default function OperationsReport({ items, hasSearch, summary, analytics, filters, filterOptions, urls }: ReportProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [form, setForm] = useState(filters);
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.get(
            urls.current,
            { ...form, search: '1' },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            }
        );
    };

    const handleReset = () => {
        const empty: OperationReportFilters = {
            patient_name: '',
            surgeon_id: '',
            operation_status: '',
            operation_approval: '',
            reserve_status: '',
            operation_type_id: '',
            date_from: '',
            date_to: '',
        };
        setForm(empty);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <ReportPageShell title={t('global.reports')} subtitle={t('global.operations')} accent="from-amber-600 to-orange-700" backLabel={t('global.back')}
            action={hasSearch && items.length ? <ReportExportButtons action={urls.export} csrfToken={csrfToken} fields={{ data: JSON.stringify(items.map((item) => item.id)) }} /> : undefined}>
            {hasSearch ? <ReportKpiGrid stats={[
                { key: 'total', label: t('global.total'), value: summary.total, icon: 'bx-calendar', accent: 'from-cyan-500 to-blue-600' },
                { key: 'completed', label: t('global.completed'), value: summary.completed, icon: 'bx-check-circle', accent: 'from-emerald-500 to-teal-600' },
                { key: 'pending', label: t('global.pending'), value: summary.pending, icon: 'bx-time-five', accent: 'from-amber-500 to-orange-600' },
                { key: 'approved', label: t('global.approved'), value: summary.approved, icon: 'bx-badge-check', accent: 'from-violet-500 to-purple-600' },
            ]} /> : null}
            {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={[
                { key: 'status', title: t('global.status'), type: 'donut', labels: analytics.by_status.map((item) => item.name === 'completed' ? t('global.completed') : t('global.pending')), values: analytics.by_status.map((item) => item.count), colors: ['#10b981', '#f59e0b'] },
                { key: 'department', title: t('global.department'), type: 'bar', labels: analytics.by_department.map((item) => item.name), values: analytics.by_department.map((item) => item.count), color: '#f97316' },
            ]} /> : null}
                <OperationNavTabs active="report" urls={urls} />

                <ReportFilterPanel title={t('global.advanced_filters')} onSubmit={handleSubmit} actions={<><button type="submit" className={OPERATION_APPROVE_BTN_CLASS} disabled={processing}><i className="bx bx-search" />{t('global.search')}</button><Button type="button" color="light" onClick={handleReset} disabled={processing}>{t('global.reset')}</Button></>}>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <Label>{t('global.patient_name')}</Label>
                                <TextInput
                                    value={form.patient_name}
                                    onChange={(e) => setForm({ ...form, patient_name: e.target.value })}
                                />
                            </div>
                            <div>
                                <Label>{t('global.operation_surgion')}</Label>
                                <Select
                                    value={form.surgeon_id}
                                    onChange={(e) => setForm({ ...form, surgeon_id: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    {filterOptions.surgeons.map((surgeon) => (
                                        <option key={surgeon.id} value={surgeon.id}>
                                            {surgeon.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.operation_type')}</Label>
                                <Select
                                    value={form.operation_type_id}
                                    onChange={(e) => setForm({ ...form, operation_type_id: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    {filterOptions.operationTypes.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.status')}</Label>
                                <Select
                                    value={form.operation_status}
                                    onChange={(e) => setForm({ ...form, operation_status: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="0">{t('global.pending')}</option>
                                    <option value="1">{t('global.completed')}</option>
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.operation_approval')}</Label>
                                <Select
                                    value={form.operation_approval}
                                    onChange={(e) => setForm({ ...form, operation_approval: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="0">{t('global.operation_not_approved')}</option>
                                    <option value="1">{t('global.approved')}</option>
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.reserve_operation')}</Label>
                                <Select
                                    value={form.reserve_status}
                                    onChange={(e) => setForm({ ...form, reserve_status: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="0">{t('global.unreserved')}</option>
                                    <option value="1">{t('global.reserved')}</option>
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.date_from')}</Label>
                                <PersianDateInput
                                    value={form.date_from}
                                    onChange={(value) => setForm({ ...form, date_from: value })}
                                />
                            </div>
                            <div>
                                <Label>{t('global.date_to')}</Label>
                                <PersianDateInput
                                    value={form.date_to}
                                    onChange={(value) => setForm({ ...form, date_to: value })}
                                />
                            </div>
                        </div>
                </ReportFilterPanel>

                <ReportResultsCard title={t('global.reports')} hasSearch={hasSearch} resultCount={items.length} resultsLabel={t('global.records')} emptyMessage={t('global.search_and_filters')}>
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.operation_surgion')}</TableHeader>
                                <TableHeader>{t('global.operation_type')}</TableHeader>
                                <TableHeader>{t('global.date')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {items.map((item, index) => (
                                <TableRow key={item.id}>
                                    <TableCell>{index + 1}</TableCell>
                                    <TableCell>{item.patient_name ?? '—'}</TableCell>
                                    <TableCell muted>{item.surgion_name ?? '—'}</TableCell>
                                    <TableCell muted>{item.operation_type_name ?? '—'}</TableCell>
                                    <TableCell muted dir="ltr">
                                        {[item.date, item.time].filter(Boolean).join(' ') || '—'}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex flex-wrap gap-1">
                                            <Badge color={item.is_operation_done ? 'info' : 'warning'}>
                                                {item.is_operation_done ? t('global.completed') : t('global.pending')}
                                            </Badge>
                                            <Badge color={item.is_operation_approved ? 'success' : 'failure'}>
                                                {operationApprovalLabel(item.is_operation_approved, t)}
                                            </Badge>
                                            {item.is_reserved && (
                                                <Badge color="purple">
                                                    {operationReservedLabel(item.is_reserved, t)}
                                                </Badge>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                            {items.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="py-8 text-center text-gray-500">
                                        {t('global.no_records_found')}
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </ReportResultsCard>
        </ReportPageShell>
    );
}
