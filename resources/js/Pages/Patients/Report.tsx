import { Link, router } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import AppointmentPagination from '../../Components/Appointments/AppointmentPagination';
import {
    ReportAnalyticsSection,
    ReportExportButtons,
    ReportFilterPanel,
    ReportKpiGrid,
    ReportPageShell,
    ReportResultsCard,
} from '../../Components/Reports';
import { ReportAnalytics, ReportChartCard, ReportKpiStat } from '../../Components/Reports/reportTypes';
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

interface PatientReportItem {
    id: number;
    patient_name: string | null;
    nid: string | null;
    id_card: string | null;
    referral_name: string | null;
    age: string | number | null;
    gender: string | number | null;
    job_category: string | number | null;
    type: string | number | null;
    referred_by_name: string | null;
    province_name: string | null;
    district_name: string | null;
    registration_date: string | null;
    urls: { show: string };
}

interface ReportProps {
    patients: {
        data: PatientReportItem[];
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
    summary: { total: number; male: number; female: number; military: number; civilian: number };
    analytics: ReportAnalytics;
    hasSearch: boolean;
    filters: Record<string, string>;
    filterOptions: {
        provinces: Array<{ id: number; name_dr: string }>;
        districts: Array<{ id: number; name_dr: string; province_id: number }>;
        recipients: Array<{ id: number; name: string }>;
    };
    urls: {
        current: string;
        index: string;
        export: string;
    };
}

const FILTER_KEYS = [
    'patient_name',
    'nid',
    'id_card',
    'referral_name',
    'age',
    'gender',
    'job_category',
    'type',
    'referred_by',
    'province_id',
    'district_id',
    'from',
    'to',
    'per_page',
] as const;

const EMPTY_FILTERS = Object.fromEntries(FILTER_KEYS.map((key) => [key, key === 'per_page' ? '15' : '']));

function buildSearchParams(filters: Record<string, string>): Record<string, string> {
    const params: Record<string, string> = { search: '1' };
    Object.entries(filters).forEach(([key, value]) => {
        if (value !== '') {
            params[key] = value;
        }
    });
    return params;
}

export default function PatientsReport({
    patients,
    summary,
    analytics,
    hasSearch,
    filters: serverFilters,
    filterOptions,
    urls,
}: ReportProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const filteredDistricts = useMemo(() => {
        if (!filters.province_id) {
            return filterOptions.districts;
        }
        return filterOptions.districts.filter(
            (district) => String(district.province_id) === filters.province_id
        );
    }, [filterOptions.districts, filters.province_id]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.get(urls.current, buildSearchParams(filters), {
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

    const exportFields = useMemo(() => {
        const fields: Record<string, string> = {};
        Object.entries(filters).forEach(([key, value]) => {
            if (value !== '' && key !== 'per_page') {
                fields[key] = value;
            }
        });
        return fields;
    }, [filters]);

    const genderLabel = (value: string | number | null) => {
        if (value === null || value === '') {
            return '—';
        }
        return String(value) === '1' ? t('global.female') : t('global.male');
    };

    const jobCategoryLabel = (value: string | number | null) => {
        if (value === null || value === '') {
            return '—';
        }
        return String(value) === '0' ? t('global.military') : t('global.civilian');
    };

    const typeLabel = (value: string | number | null) => {
        if (value === null || value === '') {
            return '—';
        }
        const map: Record<string, string> = {
            '0': t('global.mod'),
            '1': t('global.recipient'),
            '2': t('global.family'),
        };
        return map[String(value)] ?? String(value);
    };

    const canExport = hasSearch && patients.data.length > 0;
    const kpiStats: ReportKpiStat[] = hasSearch
        ? [
              { key: 'total', label: t('global.total'), value: summary.total, icon: 'bx-user', accent: 'from-emerald-500 to-teal-600' },
              { key: 'male', label: t('global.male'), value: summary.male, icon: 'bx-male', accent: 'from-blue-500 to-cyan-600' },
              { key: 'female', label: t('global.female'), value: summary.female, icon: 'bx-female', accent: 'from-pink-500 to-rose-600' },
              { key: 'military', label: t('global.military'), value: summary.military, icon: 'bx-shield-quarter', accent: 'from-violet-500 to-purple-600' },
              { key: 'civilian', label: t('global.civilian'), value: summary.civilian, icon: 'bx-briefcase', accent: 'from-amber-500 to-orange-600' },
          ]
        : [];
    const charts: ReportChartCard[] = hasSearch
        ? [
              { key: 'gender', title: t('global.gender'), type: 'donut', labels: (analytics.by_gender ?? []).map((item) => item.name === 'female' ? t('global.female') : item.name === 'male' ? t('global.male') : '—'), values: (analytics.by_gender ?? []).map((item) => item.count), colors: ['#06b6d4', '#ec4899', '#94a3b8'] },
              { key: 'type', title: t('global.disease_type'), type: 'bar', labels: (analytics.by_type ?? []).map((item) => item.name === 'mod' ? t('global.mod') : item.name === 'recipient' ? t('global.recipient') : item.name === 'family' ? t('global.family') : '—'), values: (analytics.by_type ?? []).map((item) => item.count), color: '#10b981' },
              { key: 'date', title: t('global.registration_date'), type: 'trend', labels: (analytics.by_date ?? []).map((item) => item.date), values: (analytics.by_date ?? []).map((item) => item.count), color: '#6366f1' },
          ]
        : [];

    return (
        <ReportPageShell title={t('global.reports')} subtitle={t('global.patients')} accent="from-emerald-600 to-teal-700" backHref={urls.index} backLabel={t('global.back')} action={canExport ? <ReportExportButtons action={urls.export} method="GET" fields={exportFields} typeField="export" excelValue="excel" pdfValue="print" /> : undefined}>
            {hasSearch ? <ReportKpiGrid stats={kpiStats} /> : null}
            {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={charts} /> : null}
            <ReportFilterPanel title={t('global.advanced_filters')} accentIconClass="text-emerald-500" onSubmit={handleSubmit} actions={<><Button type="submit" color="blue" disabled={processing}>{processing ? <><Spinner size="sm" className="me-2" />{t('global.loading')}</> : <><i className="bx bx-search me-2" />{t('global.search')}</>}</Button><Button type="button" color="light" onClick={handleReset} disabled={processing}><i className="bx bx-refresh me-2" />{t('global.reset')}</Button></>}>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                <div>
                                    <Label>{t('global.patient_name')}</Label>
                                    <TextInput
                                        value={filters.patient_name}
                                        onChange={(e) =>
                                            setFilters((prev) => ({ ...prev, patient_name: e.target.value }))
                                        }
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.nid')}</Label>
                                    <TextInput
                                        value={filters.nid}
                                        onChange={(e) => setFilters((prev) => ({ ...prev, nid: e.target.value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.id_card')}</Label>
                                    <TextInput
                                        value={filters.id_card}
                                        onChange={(e) => setFilters((prev) => ({ ...prev, id_card: e.target.value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.referral_name')}</Label>
                                    <TextInput
                                        value={filters.referral_name}
                                        onChange={(e) =>
                                            setFilters((prev) => ({ ...prev, referral_name: e.target.value }))
                                        }
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.age')}</Label>
                                    <TextInput
                                        value={filters.age}
                                        onChange={(e) => setFilters((prev) => ({ ...prev, age: e.target.value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.gender')}</Label>
                                    <SearchableSelect
                                        value={filters.gender}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, gender: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            { value: '0', label: t('global.male') },
                                            { value: '1', label: t('global.female') },
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.job_category')}</Label>
                                    <SearchableSelect
                                        value={filters.job_category}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, job_category: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            { value: '0', label: t('global.military') },
                                            { value: '1', label: t('global.civilian') },
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.disease_type')}</Label>
                                    <SearchableSelect
                                        value={filters.type}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, type: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            { value: '0', label: t('global.mod') },
                                            { value: '1', label: t('global.recipient') },
                                            { value: '2', label: t('global.family') },
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.referred_by')}</Label>
                                    <SearchableSelect
                                        value={filters.referred_by}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, referred_by: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.recipients.map((recipient) => ({
                                                value: String(recipient.id),
                                                label: recipient.name,
                                            })),
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.province')}</Label>
                                    <SearchableSelect
                                        value={filters.province_id}
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, province_id: value, district_id: '' }))
                                        }
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.provinces.map((province) => ({
                                                value: String(province.id),
                                                label: province.name_dr,
                                            })),
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.district')}</Label>
                                    <SearchableSelect
                                        value={filters.district_id}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, district_id: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filteredDistricts.map((district) => ({
                                                value: String(district.id),
                                                label: district.name_dr,
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
            </ReportFilterPanel>
            <ReportResultsCard title={t('global.reports')} hasSearch={hasSearch} resultCount={patients.meta.total} resultsLabel={t('global.results')} emptyMessage={t('global.search_and_filters')}>
                            <div className="overflow-x-auto">
                                <Table embedded>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>#</TableHeader>
                                            <TableHeader>{t('global.patient_name')}</TableHeader>
                                            <TableHeader>{t('global.nid')}</TableHeader>
                                            <TableHeader>{t('global.id_card')}</TableHeader>
                                            <TableHeader>{t('global.referral_name')}</TableHeader>
                                            <TableHeader>{t('global.age')}</TableHeader>
                                            <TableHeader>{t('global.gender')}</TableHeader>
                                            <TableHeader>{t('global.job_category')}</TableHeader>
                                            <TableHeader>{t('global.disease_type')}</TableHeader>
                                            <TableHeader>{t('global.referred_by')}</TableHeader>
                                            <TableHeader>{t('global.province')}</TableHeader>
                                            <TableHeader>{t('global.district')}</TableHeader>
                                            <TableHeader>{t('global.registration_date')}</TableHeader>
                                            <TableHeader className="text-end">{t('global.actions')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {patients.data.length === 0 ? (
                                            <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                                <TableCell colSpan={14} align="center" muted className="py-12">
                                                    {t('global.no_records_found')}
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            patients.data.map((item, index) => (
                                                <TableRow key={item.id}>
                                                    <TableCell className="font-mono text-xs text-gray-500">
                                                        {(patients.meta.from ?? 1) + index}
                                                    </TableCell>
                                                    <TableCell className="font-medium">{item.patient_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.nid ?? '—'}</TableCell>
                                                    <TableCell muted>{item.id_card ?? '—'}</TableCell>
                                                    <TableCell muted>{item.referral_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.age ?? '—'}</TableCell>
                                                    <TableCell muted>{genderLabel(item.gender)}</TableCell>
                                                    <TableCell muted>{jobCategoryLabel(item.job_category)}</TableCell>
                                                    <TableCell muted>{typeLabel(item.type)}</TableCell>
                                                    <TableCell muted>{item.referred_by_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.province_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.district_name ?? '—'}</TableCell>
                                                    <TableCell muted dir="ltr">{item.registration_date ?? '—'}</TableCell>
                                                    <TableCell align="right">
                                                        <Link
                                                            href={item.urls.show}
                                                            className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/30"
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
                            {patients.links.length > 3 && (
                                <AppointmentPagination links={patients.links} meta={patients.meta} t={t} />
                            )}
            </ReportResultsCard>
        </ReportPageShell>
    );
}
