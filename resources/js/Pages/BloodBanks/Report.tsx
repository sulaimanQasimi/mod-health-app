import { router, usePage } from '@inertiajs/react';
import { Badge, Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import { BLOOD_BANK_PANEL_ICON_CLASS, bloodGroupLabel, bloodRhLabel, bloodStatusBadgeColor } from '../../Components/BloodBanks/bloodBankUi';
import {
    ReportAnalyticsSection,
    ReportExportButtons,
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
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    BloodBankListUrls,
    BloodReportFilterOptions,
    BloodReportFilters,
    BloodReportItem,
} from '../../types/bloodBank';

interface ReportProps {
    items: BloodReportItem[];
    hasSearch: boolean;
    summary: {
        total: number;
        by_status: Array<{ name: string; count: number }>;
        by_blood_type: Array<{ name: string; count: number }>;
    };
    filters: BloodReportFilters;
    filterOptions: BloodReportFilterOptions;
    urls: BloodBankListUrls & { current: string; export: string };
}

export default function BloodBanksReport({ items, summary, hasSearch, filters, filterOptions, urls }: ReportProps) {
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
            },
        );
    };

    const handleReset = () => {
        const empty: BloodReportFilters = {
            patient_name: '',
            status: '',
            group: '',
            rh: '',
            department_id: '',
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

    const kpis = [
        { key: 'total', label: t('global.total'), value: summary.total, icon: 'bx-droplet', accent: 'from-rose-500 to-red-600' },
        { key: 'available', label: t('global.approved'), value: summary.by_status.find((item) => item.name === 'approved')?.count ?? 0, icon: 'bx-check-circle', accent: 'from-emerald-500 to-teal-600' },
        { key: 'delivered', label: t('global.delivered'), value: summary.by_status.find((item) => item.name === 'delivered')?.count ?? 0, icon: 'bx-package', accent: 'from-cyan-500 to-blue-600' },
        { key: 'pending', label: t('global.new'), value: summary.by_status.find((item) => item.name === 'new')?.count ?? 0, icon: 'bx-time-five', accent: 'from-amber-500 to-orange-600' },
    ];
    const charts = [
        {
            key: 'blood-types', title: t('global.blood_group'), type: 'bar' as const,
            labels: summary.by_blood_type.map((item) => item.name),
            values: summary.by_blood_type.map((item) => item.count),
            color: '#e11d48',
        },
        {
            key: 'statuses', title: t('global.status'), type: 'donut' as const,
            labels: summary.by_status.map((item) => item.name),
            values: summary.by_status.map((item) => item.count),
            colors: ['#0ea5e9', '#10b981', '#f59e0b', '#ef4444'],
        },
    ];

    return (
        <ReportPageShell
            title={t('global.reports')}
            subtitle={t('global.blood_bank')}
            accent="from-rose-600 to-red-700"
            backHref={urls.dashboard}
            backLabel={t('global.back')}
            action={items.length ? <ReportExportButtons action={urls.export} csrfToken={csrfToken} fields={{ data: JSON.stringify(items.map((item) => item.id)) }} /> : undefined}
        >
                <BloodBankNavTabs active="report" urls={urls} />
                {hasSearch ? <ReportKpiGrid stats={kpis} /> : null}
                {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={charts} /> : null}
                <ReportFilterPanel
                    title={t('global.advanced_filters')}
                    onSubmit={handleSubmit}
                    accentIconClass={BLOOD_BANK_PANEL_ICON_CLASS}
                    actions={<>
                        <Button type="submit" color="failure" disabled={processing}>
                            {processing ? <Spinner size="sm" className="me-2" /> : <i className="bx bx-search me-2" />}
                            {t('global.search')}
                        </Button>
                        <Button type="button" color="light" onClick={handleReset} disabled={processing}>{t('global.reset')}</Button>
                    </>}
                >
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <Label>{t('global.patient_name')}</Label>
                                <TextInput
                                    value={form.patient_name}
                                    onChange={(e) => setForm({ ...form, patient_name: e.target.value })}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">{t('global.status')}</Label>
                                <SearchableSelect
                                    value={form.status}
                                    onChange={(value) => setForm({ ...form, status: value })}
                                    options={filterOptions.statuses.map((status) => ({
                                        value: status,
                                        label: status,
                                    }))}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">{t('global.blood_group')}</Label>
                                <SearchableSelect
                                    value={form.group}
                                    onChange={(value) => setForm({ ...form, group: value })}
                                    options={filterOptions.bloodGroups.map((group) => ({
                                        value: group,
                                        label: group,
                                    }))}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">{t('global.rh')}</Label>
                                <SearchableSelect
                                    value={form.rh}
                                    onChange={(value) => setForm({ ...form, rh: value })}
                                    options={[
                                        { value: '+', label: 'Rh+' },
                                        { value: '-', label: 'Rh−' },
                                    ]}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">{t('global.requested_department')}</Label>
                                <SearchableSelect
                                    value={form.department_id}
                                    onChange={(value) => setForm({ ...form, department_id: value })}
                                    options={filterOptions.departments.map((dept) => ({
                                        value: String(dept.id),
                                        label: dept.name,
                                    }))}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.date_from')}</Label>
                                <PersianDateInput
                                    value={form.from}
                                    onChange={(value) => setForm({ ...form, from: value })}
                                />
                            </div>
                            <div>
                                <Label>{t('global.date_to')}</Label>
                                <PersianDateInput
                                    value={form.to}
                                    onChange={(value) => setForm({ ...form, to: value })}
                                />
                            </div>
                        </div>
                </ReportFilterPanel>
                <ReportResultsCard
                    title={t('global.reports')}
                    hasSearch={hasSearch}
                    resultCount={summary.total}
                    resultsLabel={t('global.results')}
                    emptyMessage={t('global.search_and_filters')}
                >
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.requested_department')}</TableHeader>
                                <TableHeader>{t('global.branch')}</TableHeader>
                                <TableHeader>{t('global.blood_group')}</TableHeader>
                                <TableHeader>{t('global.rh')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {items.length === 0 ? (
                                <TableEmpty colSpan={7} message={t('global.no_records_found')} />
                            ) : (
                                items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell className="font-medium">{item.patient_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color="failure" className="w-fit font-normal">
                                                {bloodGroupLabel(item.group)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{bloodRhLabel(item.rh)}</TableCell>
                                        <TableCell>
                                            <Badge color={bloodStatusBadgeColor(item.status)} className="w-fit font-normal">
                                                {item.status}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </ReportResultsCard>
        </ReportPageShell>
    );
}
