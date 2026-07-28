import { router, usePage } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import AppointmentPagination from '../../../Components/Appointments/AppointmentPagination';
import LaboratoryPriorityBadge from '../../../Components/Laboratory/LaboratoryPriorityBadge';
import LaboratoryStatusBadge from '../../../Components/Laboratory/LaboratoryStatusBadge';
import {
    ReportAnalyticsSection,
    ReportExportButtons,
    ReportFilterPanel,
    ReportKpiGrid,
    ReportPageShell,
    ReportResultsCard,
} from '../../../Components/Reports';
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
import { SharedPageProps } from '../../../types';
import {
    perPageFilterOptionsWithAll,
    selectOptionsWithAll,
    statusFilterOptions,
} from '../../../utils/laboratoryFilterOptions';
import { PaginationLink } from '../../../types/appointment';
import {
    LaboratoryDetailedReportRow,
    SectionOption,
    SelectOption,
} from '../../../types/laboratory';

interface ReportDetailedProps {
    items: {
        data: LaboratoryDetailedReportRow[];
        links?: PaginationLink[];
        meta?: {
            from: number | null;
            to: number | null;
            total: number;
        };
    } | null;
    filters: Record<string, string>;
    filterOptions: {
        labTypes: SelectOption[];
        branches: SelectOption[];
        departments: SelectOption[];
        doctors: SelectOption[];
        users: SelectOption[];
        sections: SectionOption[];
    };
    summary: { total_registrations: number; test_type_count: number; completed_count: number } | null;
    analytics: { by_type: Array<{ name: string; count: number }> } | null;
    urls: {
        report: string;
        export: string;
    };
}

export default function ReportDetailed({
    items,
    summary,
    analytics,
    filters: serverFilters,
    filterOptions,
    urls,
}: ReportDetailedProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [showAdvanced, setShowAdvanced] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const updateFilter = (field: string, value: string) => {
        setFilters((current) => ({ ...current, [field]: value }));
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.get(
            urls.report,
            Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')),
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };
    const hasSearch = items !== null;
    const canExport = hasSearch && (items?.data.length ?? 0) > 0;
    const exportFields = Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => key !== 'per_page' && value !== ''),
    );
    const kpis = summary
        ? [
              { key: 'total', label: t('global.total'), value: summary.total_registrations, icon: 'bx-test-tube', accent: 'from-slate-500 to-gray-700' },
              { key: 'types', label: t('global.test_type'), value: summary.test_type_count, icon: 'bx-category', accent: 'from-indigo-500 to-blue-600' },
              { key: 'completed', label: t('global.completed'), value: summary.completed_count, icon: 'bx-check-circle', accent: 'from-emerald-500 to-teal-600' },
          ]
        : [];
    const charts = analytics
        ? [{
              key: 'types', title: t('global.test_type'), type: 'bar' as const,
              labels: analytics.by_type.map((item) => item.name),
              values: analytics.by_type.map((item) => item.count),
              color: '#475569',
          }]
        : [];

    return (
        <ReportPageShell
            title={t('global.test_registration_report_detailed')}
            subtitle={t('global.reports')}
            accent="from-slate-600 to-gray-800"
            backLabel={t('global.back')}
            action={canExport ? <ReportExportButtons action={urls.export} csrfToken={csrfToken} fields={exportFields} /> : undefined}
        >
            {hasSearch ? <ReportKpiGrid stats={kpis} columns="sm:grid-cols-3" /> : null}
            {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={charts} /> : null}
            <ReportFilterPanel
                title={t('global.advanced_filters')}
                onSubmit={handleSubmit}
                accentIconClass="text-slate-500"
                actions={<Button type="submit" color="blue" disabled={processing}>
                    {processing ? <><Spinner size="sm" className="me-2" />{t('global.loading')}</> : <><i className="bx bx-search me-2" />{t('global.search')}</>}
                </Button>}
            >
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label>{t('global.from')}</Label>
                            <TextInput
                                value={filters.from ?? ''}
                                onChange={(e) => updateFilter('from', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label>{t('global.to')}</Label>
                            <TextInput
                                value={filters.to ?? ''}
                                onChange={(e) => updateFilter('to', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="detailed-test-type">{t('global.test_type')}</Label>
                            <SearchableSelect
                                id="detailed-test-type"
                                value={filters.test_type ?? ''}
                                onChange={(value) => updateFilter('test_type', value)}
                                placeholder={t('global.all')}
                                options={selectOptionsWithAll(t, filterOptions.labTypes)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="detailed-status">{t('global.status')}</Label>
                            <SearchableSelect
                                id="detailed-status"
                                value={filters.status ?? ''}
                                onChange={(value) => updateFilter('status', value)}
                                placeholder={t('global.all')}
                                options={statusFilterOptions(t)}
                            />
                        </div>
                        <div>
                            <Label>{t('global.patient_id')}</Label>
                            <TextInput
                                value={filters.patient_id ?? ''}
                                onChange={(e) => updateFilter('patient_id', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="detailed-doctor">{t('global.doctor')}</Label>
                            <SearchableSelect
                                id="detailed-doctor"
                                value={filters.doctor_id ?? ''}
                                onChange={(value) => updateFilter('doctor_id', value)}
                                placeholder={t('global.all')}
                                options={selectOptionsWithAll(t, filterOptions.doctors)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="detailed-per-page">{t('global.per_page')}</Label>
                            <SearchableSelect
                                id="detailed-per-page"
                                value={filters.per_page ?? '15'}
                                onChange={(value) => updateFilter('per_page', value)}
                                options={perPageFilterOptionsWithAll(t, ['15', '25', '50', '100'])}
                            />
                        </div>
                    </div>

                    <button
                        type="button"
                        onClick={() => setShowAdvanced((v) => !v)}
                        className="text-sm font-medium text-blue-600 hover:underline"
                    >
                        {showAdvanced ? t('global.hide') || 'Hide' : t('global.advanced_filters')}
                    </button>

                    {showAdvanced && (
                        <div className="grid grid-cols-1 gap-4 rounded-lg border border-gray-200 p-4 md:grid-cols-2 lg:grid-cols-4 dark:border-gray-700">
                            <div>
                                <Label htmlFor="detailed-branch">{t('global.branch')}</Label>
                                <SearchableSelect
                                    id="detailed-branch"
                                    value={filters.branch_id ?? ''}
                                    onChange={(value) => updateFilter('branch_id', value)}
                                    placeholder={t('global.all')}
                                    options={selectOptionsWithAll(t, filterOptions.branches)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="detailed-department">{t('global.department')}</Label>
                                <SearchableSelect
                                    id="detailed-department"
                                    value={filters.department_id ?? ''}
                                    onChange={(value) => updateFilter('department_id', value)}
                                    placeholder={t('global.all')}
                                    options={selectOptionsWithAll(t, filterOptions.departments)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="detailed-assigned-to">{t('global.assigned_to')}</Label>
                                <SearchableSelect
                                    id="detailed-assigned-to"
                                    value={filters.assigned_to ?? ''}
                                    onChange={(value) => updateFilter('assigned_to', value)}
                                    placeholder={t('global.all')}
                                    options={selectOptionsWithAll(t, filterOptions.users)}
                                />
                            </div>
                            <div>
                                <Label>{t('global.notes')}</Label>
                                <TextInput
                                    value={filters.notes ?? ''}
                                    onChange={(e) => updateFilter('notes', e.target.value)}
                                />
                            </div>
                        </div>
                    )}

            </ReportFilterPanel>
            <ReportResultsCard
                title={t('global.test_registration_report_detailed')}
                hasSearch={hasSearch}
                resultCount={summary?.total_registrations}
                resultsLabel={t('global.results')}
                emptyMessage={t('global.search_and_filters')}
            >
                    <div className="overflow-x-auto">
                        <Table embedded>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('global.reference_number')}</TableHead>
                                    <TableHead>{t('global.date')}</TableHead>
                                    <TableHead>{t('global.patient')}</TableHead>
                                    <TableHead>{t('global.test_type')}</TableHead>
                                    <TableHead>{t('global.status')}</TableHead>
                                    <TableHead>{t('global.priority')}</TableHead>
                                    <TableHead>{t('global.doctor')}</TableHead>
                                    <TableHead>{t('global.completed_by')}</TableHead>
                                    <TableHead>{t('global.assigned_to')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {(items?.data.length ?? 0) === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={9} className="text-center text-gray-500">
                                            {t('global.no_item_is_found')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    items?.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell className="font-mono text-sm">{row.ref_no}</TableCell>
                                            <TableCell>{row.registration_date ?? '—'}</TableCell>
                                            <TableCell>{row.patient_name ?? '—'}</TableCell>
                                            <TableCell>{row.lab_type_name ?? '—'}</TableCell>
                                            <TableCell>
                                                <LaboratoryStatusBadge status={row.status} />
                                            </TableCell>
                                            <TableCell>
                                                <LaboratoryPriorityBadge priority={row.priority} />
                                            </TableCell>
                                            <TableCell>{row.doctor_name ?? '—'}</TableCell>
                                            <TableCell>{row.completed_by_name ?? '—'}</TableCell>
                                            <TableCell>{row.assigned_to_name ?? '—'}</TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    {items?.links && items.meta && (
                        <AppointmentPagination links={items.links} meta={items.meta} t={t} />
                    )}
            </ReportResultsCard>
        </ReportPageShell>
    );
}
