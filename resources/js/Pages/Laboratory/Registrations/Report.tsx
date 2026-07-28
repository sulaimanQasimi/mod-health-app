import { router, usePage } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import AppointmentPagination from '../../../Components/Appointments/AppointmentPagination';
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
} from '../../../utils/laboratoryFilterOptions';
import { PaginationLink } from '../../../types/appointment';
import { LaboratoryReportRow, SelectOption } from '../../../types/laboratory';

interface ReportProps {
    items: {
        data: LaboratoryReportRow[];
        links?: PaginationLink[];
        meta?: {
            from: number | null;
            to: number | null;
            total: number;
        };
    } | null;
    filters: {
        from: string;
        to: string;
        test_type: string;
        patient_id: string;
        per_page: string;
    };
    filterOptions: { labTypes: SelectOption[] };
    summary: { test_type_count: number; total_registrations: number } | null;
    analytics: { by_type: Array<{ name: string; count: number }> } | null;
    urls: {
        report: string;
        export: string;
    };
}

export default function Report({ items, summary, analytics, filters: serverFilters, filterOptions, urls }: ReportProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

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
              { key: 'types', label: t('global.test_type'), value: summary.test_type_count, icon: 'bx-category', accent: 'from-indigo-500 to-blue-600' },
              { key: 'registrations', label: t('global.registrations') || 'Registrations', value: summary.total_registrations, icon: 'bx-test-tube', accent: 'from-cyan-500 to-blue-600' },
          ]
        : [];
    const charts = analytics
        ? [{
              key: 'types',
              title: t('global.test_type'),
              type: 'bar' as const,
              labels: analytics.by_type.map((item) => item.name),
              values: analytics.by_type.map((item) => item.count),
              color: '#4f46e5',
          }]
        : [];

    return (
        <ReportPageShell
            title={t('global.test_registration_report')}
            subtitle={t('global.reports')}
            accent="from-indigo-500 to-blue-600"
            backLabel={t('global.back')}
            action={canExport ? <ReportExportButtons action={urls.export} csrfToken={csrfToken} fields={exportFields} /> : undefined}
        >
            {hasSearch ? <ReportKpiGrid stats={kpis} columns="sm:grid-cols-2" /> : null}
            {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={charts} /> : null}
            <ReportFilterPanel
                title={t('global.advanced_filters')}
                onSubmit={handleSubmit}
                accentIconClass="text-indigo-500"
                actions={<Button type="submit" color="blue" disabled={processing}>
                    {processing ? <><Spinner size="sm" className="me-2" />{t('global.loading')}</> : <><i className="bx bx-search me-2" />{t('global.search')}</>}
                </Button>}
            >
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label>{t('global.from')}</Label>
                            <TextInput
                                value={filters.from}
                                onChange={(e) => setFilters({ ...filters, from: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.to')}</Label>
                            <TextInput
                                value={filters.to}
                                onChange={(e) => setFilters({ ...filters, to: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label htmlFor="report-test-type">{t('global.test_type')}</Label>
                            <SearchableSelect
                                id="report-test-type"
                                value={filters.test_type}
                                onChange={(value) => setFilters({ ...filters, test_type: value })}
                                placeholder={t('global.all')}
                                options={selectOptionsWithAll(t, filterOptions.labTypes)}
                            />
                        </div>
                        <div>
                            <Label>{t('global.patient_id')}</Label>
                            <TextInput
                                value={filters.patient_id}
                                onChange={(e) => setFilters({ ...filters, patient_id: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label htmlFor="report-per-page">{t('global.per_page')}</Label>
                            <SearchableSelect
                                id="report-per-page"
                                value={filters.per_page}
                                onChange={(value) => setFilters({ ...filters, per_page: value })}
                                options={perPageFilterOptionsWithAll(t, ['15', '25', '50', '100'])}
                            />
                        </div>
                    </div>
            </ReportFilterPanel>
            <ReportResultsCard
                title={t('global.test_registration_report')}
                hasSearch={hasSearch}
                resultCount={summary?.total_registrations}
                resultsLabel={t('global.registrations') || 'registrations'}
                emptyMessage={t('global.search_and_filters')}
            >
                        <div className="overflow-x-auto">
                            <Table embedded>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>#</TableHeader>
                                        <TableHeader>{t('global.test_type')}</TableHeader>
                                        <TableHeader className="text-end">{t('global.total')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {(items?.data.length ?? 0) === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={3} className="text-center text-gray-500">
                                                {t('global.no_item_is_found')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        items?.data.map((row, index) => (
                                            <TableRow key={row.lab_type_id}>
                                                <TableCell>{index + 1}</TableCell>
                                                <TableCell className="font-medium">
                                                    {row.lab_type_name}
                                                </TableCell>
                                                <TableCell className="text-end">
                                                    <span className="inline-flex min-w-[2.5rem] justify-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-bold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                                                        {row.total_count}
                                                    </span>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                {items?.links && items.meta && <AppointmentPagination links={items.links} meta={items.meta} t={t} />}
            </ReportResultsCard>
        </ReportPageShell>
    );
}
