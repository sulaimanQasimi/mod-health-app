import { Button, Label, Spinner } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import AppointmentPagination from '../Appointments/AppointmentPagination';
import PersianDateInput from '../ui/PersianDateInput';
import SearchableSelect from '../ui/SearchableSelect';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import ReportAnalyticsSection from './ReportAnalyticsSection';
import ReportFilterPanel from './ReportFilterPanel';
import ReportKpiGrid from './ReportKpiGrid';
import ReportResultsCard from './ReportResultsCard';

export interface DepartmentReportItem {
    id: number;
    patient_name: string | null;
    father_name: string | null;
    last_name: string | null;
    age: string | number | null;
    gender: string | number | null;
    nid: string | null;
    job: string | null;
    created_at: string | null;
}

export interface DepartmentReportTabData {
    appointments: {
        data: DepartmentReportItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;
        };
    };
    summary: {
        total: number;
        male: number;
        female: number;
    };
    analytics: {
        by_gender: Array<{ name: string; count: number }>;
    };
    hasSearch: boolean;
    filters: {
        department_id: string;
        date_from: string;
        date_to: string;
        per_page: string;
    };
    filterOptions: {
        departments: Array<{ id: number; name: string }>;
    };
}

interface DepartmentReportPanelProps {
    data: DepartmentReportTabData;
    processing: boolean;
    onVisit: (params: Record<string, string>, options?: { replace?: boolean }) => void;
}

const EMPTY_FILTERS = {
    department_id: '',
    date_from: '',
    date_to: '',
    per_page: '25',
};

function genderLabel(value: string | number | null, t: (key: string) => string): string {
    if (value === null || value === '') {
        return '—';
    }
    return String(value) === '1' ? t('global.female') : t('global.male');
}

export default function DepartmentReportPanel({
    data,
    processing,
    onVisit,
}: DepartmentReportPanelProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(data.filters);

    useEffect(() => {
        setFilters({
            department_id: data.filters.department_id ?? '',
            date_from: data.filters.date_from ?? '',
            date_to: data.filters.date_to ?? '',
            per_page: data.filters.per_page || '25',
        });
    }, [data.filters]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!filters.department_id) {
            return;
        }
        const params: Record<string, string> = {
            tab: 'department',
            search: '1',
            department_id: filters.department_id,
        };
        if (filters.date_from) {
            params.date_from = filters.date_from;
        }
        if (filters.date_to) {
            params.date_to = filters.date_to;
        }
        if (filters.per_page) {
            params.per_page = filters.per_page;
        }
        onVisit(params);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        onVisit({ tab: 'department' }, { replace: true });
    };

    const kpiStats = data.hasSearch
        ? [
              {
                  key: 'total',
                  label: t('global.total_records'),
                  value: data.summary.total,
                  icon: 'bx-group',
                  accent: 'from-indigo-500 to-violet-600',
              },
              {
                  key: 'male',
                  label: t('global.male'),
                  value: data.summary.male,
                  icon: 'bx-male',
                  accent: 'from-cyan-500 to-blue-600',
              },
              {
                  key: 'female',
                  label: t('global.female'),
                  value: data.summary.female,
                  icon: 'bx-female',
                  accent: 'from-pink-500 to-rose-600',
              },
          ]
        : [];

    const charts = data.hasSearch
        ? [
              {
                  key: 'gender',
                  title: t('global.gender'),
                  type: 'donut' as const,
                  labels: (data.analytics.by_gender ?? []).map((item) =>
                      item.name === 'female' ? t('global.female') : t('global.male'),
                  ),
                  values: (data.analytics.by_gender ?? []).map((item) => item.count),
                  colors: ['#06b6d4', '#ec4899'],
              },
          ]
        : [];

    return (
        <div className="space-y-5">
            <div>
                <h2 className="text-base font-semibold text-gray-900 dark:text-white">
                    {t('global.department_report')}
                </h2>
                <p className="text-sm text-gray-500 dark:text-gray-400">
                    {t('global.appointments')}
                </p>
            </div>

            {data.hasSearch ? <ReportKpiGrid stats={kpiStats} columns="sm:grid-cols-3" /> : null}
            {data.hasSearch ? <ReportAnalyticsSection charts={charts} /> : null}

            <ReportFilterPanel
                title={t('global.advanced_filters')}
                accentIconClass="text-indigo-500"
                onSubmit={handleSubmit}
                actions={
                    <>
                        <Button type="submit" color="blue" disabled={processing || !filters.department_id}>
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
                    </>
                }
            >
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <Label>{t('global.department')}</Label>
                        <SearchableSelect
                            value={filters.department_id}
                            onChange={(value) => setFilters((prev) => ({ ...prev, department_id: value }))}
                            options={[
                                { value: '', label: t('global.select') },
                                ...data.filterOptions.departments.map((department) => ({
                                    value: String(department.id),
                                    label: department.name,
                                })),
                            ]}
                            placeholder={t('global.select')}
                        />
                    </div>
                    <div>
                        <Label>{t('global.from')}</Label>
                        <PersianDateInput
                            value={filters.date_from}
                            onChange={(value) => setFilters((prev) => ({ ...prev, date_from: value }))}
                        />
                    </div>
                    <div>
                        <Label>{t('global.to')}</Label>
                        <PersianDateInput
                            value={filters.date_to}
                            onChange={(value) => setFilters((prev) => ({ ...prev, date_to: value }))}
                        />
                    </div>
                    <div>
                        <Label>{t('global.per_page')}</Label>
                        <SearchableSelect
                            value={filters.per_page}
                            onChange={(value) => setFilters((prev) => ({ ...prev, per_page: value }))}
                            options={[
                                { value: '10', label: '10' },
                                { value: '25', label: '25' },
                                { value: '50', label: '50' },
                                { value: '100', label: '100' },
                            ]}
                        />
                    </div>
                </div>
            </ReportFilterPanel>

            <ReportResultsCard
                title={t('global.department_report')}
                hasSearch={data.hasSearch}
                resultCount={data.appointments.meta.total}
                resultsLabel={t('global.results')}
                emptyMessage={t('global.please_select_department_and_date_range')}
            >
                <div className="overflow-x-auto">
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.father_name')}</TableHeader>
                                <TableHeader>{t('global.last_name')}</TableHeader>
                                <TableHeader>{t('global.age')}</TableHeader>
                                <TableHeader>{t('global.gender')}</TableHeader>
                                <TableHeader>{t('global.nid')}</TableHeader>
                                <TableHeader>{t('global.job')}</TableHeader>
                                <TableHeader>{t('global.appointment_created_at')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {data.appointments.data.length === 0 ? (
                                <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                    <TableCell colSpan={9} align="center" muted className="py-12">
                                        {t('global.no_appointments_found')}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                data.appointments.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell className="font-mono text-xs text-gray-500">
                                            {(data.appointments.meta.from ?? 1) + index}
                                        </TableCell>
                                        <TableCell className="font-medium">{item.patient_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.father_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.last_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.age ?? '—'}</TableCell>
                                        <TableCell muted>{genderLabel(item.gender, t)}</TableCell>
                                        <TableCell muted>{item.nid ?? '—'}</TableCell>
                                        <TableCell muted>{item.job ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">{item.created_at ?? '—'}</TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
                {data.appointments.links.length > 3 && (
                    <AppointmentPagination
                        links={data.appointments.links}
                        meta={data.appointments.meta}
                        t={t}
                    />
                )}
            </ReportResultsCard>
        </div>
    );
}
