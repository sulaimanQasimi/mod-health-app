import { router, usePage } from '@inertiajs/react';
import { Badge, Button, Card, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import AnesthesiaNavTabs from '../../Components/Anesthesias/AnesthesiaNavTabs';
import {
    anesthesiaStatusBadgeColor,
    anesthesiaStatusLabel,
    anesthesiaTypeLabel,
} from '../../Components/Anesthesias/anesthesiaUi';
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
    AnesthesiaListUrls,
    AnesthesiaReportFilters,
    AnesthesiaReportItem,
    SelectOption,
} from '../../types/anesthesia';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    items: AnesthesiaReportItem[];
    hasSearch: boolean;
    summary: { total: number; new: number; approved: number; rejected: number };
    analytics: { by_status: { name: string; count: number }[]; by_department: { name: string; count: number }[] };
    filters: AnesthesiaReportFilters;
    filterOptions: {
        doctors: SelectOption[];
        operationTypes: SelectOption[];
        departments: SelectOption[];
    };
    urls: AnesthesiaListUrls & { current: string; export: string };
}

export default function AnesthesiasReport({ items, hasSearch, summary, analytics, filters, filterOptions, urls }: ReportProps) {
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
        const empty: AnesthesiaReportFilters = {
            patient_name: '',
            status: '',
            doctor_id: '',
            anesthesia_type: '',
            operation_type_id: '',
            department_id: '',
            time: '',
            from: '',
            to: '',
        };
        setForm(empty);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <ReportPageShell title={t('global.reports')} subtitle={t('global.new_anesthesias')} accent="from-violet-600 to-purple-700" backLabel={t('global.back')}
            action={hasSearch && items.length ? <ReportExportButtons action={urls.export} csrfToken={csrfToken} fields={{ data: JSON.stringify(items.map((item) => item.id)) }} /> : undefined}>
            {hasSearch ? <ReportKpiGrid stats={[
                { key: 'total', label: t('global.total'), value: summary.total, icon: 'bx-calendar', accent: 'from-cyan-500 to-blue-600' },
                { key: 'new', label: t('global.new_anesthesias'), value: summary.new, icon: 'bx-time-five', accent: 'from-amber-500 to-orange-600' },
                { key: 'approved', label: t('global.approved_anesthesias'), value: summary.approved, icon: 'bx-check-circle', accent: 'from-emerald-500 to-teal-600' },
                { key: 'rejected', label: t('global.rejected_anesthesias'), value: summary.rejected, icon: 'bx-x-circle', accent: 'from-rose-500 to-red-600' },
            ]} /> : null}
            {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={[
                { key: 'status', title: t('global.status'), type: 'donut', labels: analytics.by_status.map((item) => anesthesiaStatusLabel(item.name, t)), values: analytics.by_status.map((item) => item.count), colors: ['#0ea5e9', '#10b981', '#ef4444'] },
                { key: 'department', title: t('global.department'), type: 'bar', labels: analytics.by_department.map((item) => item.name), values: analytics.by_department.map((item) => item.count), color: '#7c3aed' },
            ]} /> : null}
                    <AnesthesiaNavTabs active="report" urls={urls} />

                <ReportFilterPanel title={t('global.documents.search')} onSubmit={handleSubmit} actions={<><Button type="submit" color="purple" size="sm" disabled={processing}><i className="bx bx-search me-1" />{t('global.documents.search')}</Button><Button type="button" color="light" size="sm" disabled={processing} onClick={handleReset}>{t('global.reset')}</Button></>}>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label htmlFor="report-patient-name">{t('global.patient_name')}</Label>
                            <TextInput
                                id="report-patient-name"
                                sizing="sm"
                                value={form.patient_name}
                                onChange={(e) => setForm((prev) => ({ ...prev, patient_name: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="report-status">{t('global.status')}</Label>
                            <Select
                                id="report-status"
                                sizing="sm"
                                value={form.status}
                                onChange={(e) => setForm((prev) => ({ ...prev, status: e.target.value }))}
                            >
                                <option value="">{t('global.select')}</option>
                                <option value="new">{t('global.new_anesthesias')}</option>
                                <option value="approved">{t('global.approved_anesthesias')}</option>
                                <option value="rejected">{t('global.rejected_anesthesias')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="report-doctor">{t('global.doctor_name')}</Label>
                            <Select
                                id="report-doctor"
                                sizing="sm"
                                value={form.doctor_id}
                                onChange={(e) => setForm((prev) => ({ ...prev, doctor_id: e.target.value }))}
                            >
                                <option value="">{t('global.select')}</option>
                                {filterOptions.doctors.map((doctor) => (
                                    <option key={doctor.id} value={doctor.id}>
                                        {doctor.name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="report-anesthesia-type">{t('global.anesthesia_type')}</Label>
                            <Select
                                id="report-anesthesia-type"
                                sizing="sm"
                                value={form.anesthesia_type}
                                onChange={(e) => setForm((prev) => ({ ...prev, anesthesia_type: e.target.value }))}
                            >
                                <option value="">{t('global.select')}</option>
                                <option value="local">{t('global.local')}</option>
                                <option value="spinal">{t('global.spinal')}</option>
                                <option value="general">{t('global.general')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="report-operation-type">{t('global.operation_type')}</Label>
                            <Select
                                id="report-operation-type"
                                sizing="sm"
                                value={form.operation_type_id}
                                onChange={(e) =>
                                    setForm((prev) => ({ ...prev, operation_type_id: e.target.value }))
                                }
                            >
                                <option value="">{t('global.select')}</option>
                                {filterOptions.operationTypes.map((type) => (
                                    <option key={type.id} value={type.id}>
                                        {type.name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="report-department">{t('global.department')}</Label>
                            <Select
                                id="report-department"
                                sizing="sm"
                                value={form.department_id}
                                onChange={(e) => setForm((prev) => ({ ...prev, department_id: e.target.value }))}
                            >
                                <option value="">{t('global.select')}</option>
                                {filterOptions.departments.map((department) => (
                                    <option key={department.id} value={department.id}>
                                        {department.name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="report-time">{t('global.time')}</Label>
                            <TextInput
                                id="report-time"
                                type="time"
                                sizing="sm"
                                value={form.time}
                                onChange={(e) => setForm((prev) => ({ ...prev, time: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label>{t('global.from')}</Label>
                            <PersianDateInput
                                value={form.from}
                                onChange={(from) => setForm((prev) => ({ ...prev, from }))}
                            />
                        </div>
                        <div>
                            <Label>{t('global.to')}</Label>
                            <PersianDateInput
                                value={form.to}
                                onChange={(to) => setForm((prev) => ({ ...prev, to }))}
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
                                    <TableHead>{t('global.doctor_name')}</TableHead>
                                    <TableHead>{t('global.anesthesia_type')}</TableHead>
                                    <TableHead>{t('global.branch')}</TableHead>
                                    <TableHead>{t('global.date')}</TableHead>
                                    <TableHead>{t('global.time')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={anesthesiaStatusBadgeColor(item.status)} className="w-fit font-normal">
                                                {anesthesiaStatusLabel(item.status, t)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{item.doctor_name ?? '—'}</TableCell>
                                        <TableCell>{anesthesiaTypeLabel(item.anesthesia_type, t)}</TableCell>
                                        <TableCell>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell dir="ltr">{item.date ?? '—'}</TableCell>
                                        <TableCell dir="ltr">{item.time ?? '—'}</TableCell>
                                    </TableRow>
                                ))}
                                {items.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={8} className="py-10 text-center text-gray-500">
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
