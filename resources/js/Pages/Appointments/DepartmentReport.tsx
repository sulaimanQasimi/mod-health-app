import { router } from '@inertiajs/react';
import { Button, Label, Spinner } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import {
    ReportAnalyticsSection,
    ReportFilterPanel,
    ReportKpiGrid,
    ReportPageShell,
    ReportResultsCard,
} from '../../Components/Reports';
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

interface DepartmentOption {
    id: number;
    name: string;
}

interface DepartmentReportItem {
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

interface DepartmentReportProps {
    appointments: {
        data: DepartmentReportItem[];
        meta: { total: number };
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
    };
    filterOptions: {
        departments: DepartmentOption[];
    };
    urls: {
        current: string;
        index: string;
    };
}

const EMPTY_FILTERS = {
    department_id: '',
    date_from: '',
    date_to: '',
};

function genderLabel(value: string | number | null, t: (key: string) => string): string {
    if (value === null || value === '') {
        return '—';
    }
    return String(value) === '1' ? t('global.female') : t('global.male');
}

export default function DepartmentReport({
    appointments,
    summary,
    analytics,
    hasSearch,
    filters: serverFilters,
    filterOptions,
    urls,
}: DepartmentReportProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!filters.department_id) {
            return;
        }
        setProcessing(true);
        const params: Record<string, string> = { search: '1', department_id: filters.department_id };
        if (filters.date_from) {
            params.date_from = filters.date_from;
        }
        if (filters.date_to) {
            params.date_to = filters.date_to;
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

    const kpiStats = hasSearch
        ? [
              {
                  key: 'total',
                  label: t('global.total_records'),
                  value: summary.total,
                  icon: 'bx-group',
                  accent: 'from-indigo-500 to-violet-600',
              },
              {
                  key: 'male',
                  label: t('global.male'),
                  value: summary.male,
                  icon: 'bx-male',
                  accent: 'from-cyan-500 to-blue-600',
              },
              {
                  key: 'female',
                  label: t('global.female'),
                  value: summary.female,
                  icon: 'bx-female',
                  accent: 'from-pink-500 to-rose-600',
              },
          ]
        : [];

    const charts = hasSearch
        ? [
              {
                  key: 'gender',
                  title: t('global.gender'),
                  type: 'donut' as const,
                  labels: (analytics.by_gender ?? []).map((item) =>
                      item.name === 'female' ? t('global.female') : t('global.male')
                  ),
                  values: (analytics.by_gender ?? []).map((item) => item.count),
                  colors: ['#06b6d4', '#ec4899'],
              },
          ]
        : [];

    return (
        <ReportPageShell
            title={t('global.department_report')}
            subtitle={t('global.appointments')}
            icon="bx-building"
            accent="from-indigo-600 to-violet-700"
            backHref={urls.index}
            backLabel={t('global.back')}
        >
            {hasSearch ? <ReportKpiGrid stats={kpiStats} columns="sm:grid-cols-3" /> : null}
            {hasSearch ? <ReportAnalyticsSection charts={charts} /> : null}

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
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <Label>{t('global.department')}</Label>
                        <SearchableSelect
                            value={filters.department_id}
                            onChange={(value) => setFilters((prev) => ({ ...prev, department_id: value }))}
                            options={[
                                { value: '', label: t('global.select') },
                                ...filterOptions.departments.map((department) => ({
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
                </div>
            </ReportFilterPanel>

            <ReportResultsCard
                title={t('global.department_report')}
                hasSearch={hasSearch}
                resultCount={appointments.meta.total}
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
                            {appointments.data.length === 0 ? (
                                <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                    <TableCell colSpan={9} align="center" muted className="py-12">
                                        {t('global.no_appointments_found')}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                appointments.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell className="font-mono text-xs text-gray-500">
                                            {index + 1}
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
            </ReportResultsCard>
        </ReportPageShell>
    );
}
