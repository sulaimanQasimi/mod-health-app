import { router, usePage } from '@inertiajs/react';
import { Badge, Button, Label, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import {
    ReportAnalyticsSection,
    ReportExportButtons,
    ReportFilterPanel,
    ReportKpiGrid,
    ReportPageShell,
    ReportResultsCard,
} from '../../Components/Reports';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import TableActionButton from '../../Components/ui/TableActionButton';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    HospitalizationOption,
    HospitalizationReportFilters,
    HospitalizationReportItem,
} from '../../types/hospitalization';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    items: HospitalizationReportItem[];
    hasSearch: boolean;
    summary: { total: number; active: number; discharged: number };
    analytics: { by_status: { name: string; count: number }[]; by_department: { name: string; count: number }[] };
    filters: HospitalizationReportFilters;
    filterOptions: {
        doctors: HospitalizationOption[];
        rooms: HospitalizationOption[];
        foodTypes: HospitalizationOption[];
    };
    urls: { current: string; index: string; export: string };
}

export default function HospitalizationsReport({ items, hasSearch, summary, analytics, filters, filterOptions, urls }: ReportProps) {
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

    return (
        <ReportPageShell
            title={t('global.reports')}
            subtitle={t('global.hospitalizations')}
            accent="from-emerald-600 to-teal-700"
            backHref={urls.index}
            backLabel={t('global.back')}
            action={hasSearch && items.length ? <ReportExportButtons action={urls.export} csrfToken={csrfToken} fields={{ data: JSON.stringify(items.map((item) => item.id)) }} /> : undefined}
        >
            <ReportKpiGrid stats={hasSearch ? [
                { key: 'total', label: t('global.total'), value: summary.total, icon: 'bx-building-house', accent: 'from-cyan-500 to-blue-600' },
                { key: 'active', label: t('global.active'), value: summary.active, icon: 'bx-pulse', accent: 'from-emerald-500 to-teal-600' },
                { key: 'discharged', label: t('global.discharged'), value: summary.discharged, icon: 'bx-exit', accent: 'from-slate-500 to-gray-600' },
            ] : []} />
            {hasSearch ? <ReportAnalyticsSection title={t('global.reports')} charts={[
                { key: 'status', title: t('global.status'), type: 'donut', labels: analytics.by_status.map((item) => item.name === 'active' ? t('global.active') : t('global.discharged')), values: analytics.by_status.map((item) => item.count), colors: ['#10b981', '#64748b'] },
                { key: 'department', title: t('global.department'), type: 'bar', labels: analytics.by_department.map((item) => item.name), values: analytics.by_department.map((item) => item.count), color: '#0d9488' },
            ]} /> : null}
                <ReportFilterPanel
                    title={t('global.search')}
                    onSubmit={handleSubmit}
                    actions={<Button type="submit" color="success" size="sm" disabled={processing}><i className="bx bx-search me-2" />{t('global.search')}</Button>}
                >
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <Label htmlFor="patient_name">{t('global.patient_name')}</Label>
                            <TextInput
                                id="patient_name"
                                sizing="sm"
                                value={form.patient_name}
                                onChange={(e) => setForm((prev) => ({ ...prev, patient_name: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="doctor_id">{t('global.doctor')}</Label>
                            <SearchableSelect
                                id="doctor_id"
                                value={form.doctor_id}
                                onChange={(value) => setForm((prev) => ({ ...prev, doctor_id: value }))}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.doctors.map((doctor) => ({
                                        value: String(doctor.id),
                                        label: doctor.name,
                                    })),
                                ]}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label htmlFor="room_id">{t('global.room')}</Label>
                            <SearchableSelect
                                id="room_id"
                                value={form.room_id}
                                onChange={(value) => setForm((prev) => ({ ...prev, room_id: value }))}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...filterOptions.rooms.map((room) => ({
                                        value: String(room.id),
                                        label: room.name,
                                    })),
                                ]}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label htmlFor="is_discharged">{t('global.status')}</Label>
                            <SearchableSelect
                                id="is_discharged"
                                value={form.is_discharged}
                                onChange={(value) => setForm((prev) => ({ ...prev, is_discharged: value }))}
                                options={[
                                    { value: '', label: t('global.all') },
                                    { value: '0', label: t('global.active') },
                                    { value: '1', label: t('global.discharged') },
                                ]}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div>
                            <Label htmlFor="date_from">{t('global.date_from')}</Label>
                            <TextInput
                                id="date_from"
                                sizing="sm"
                                dir="ltr"
                                placeholder="1403/01/01"
                                value={form.date_from}
                                onChange={(e) => setForm((prev) => ({ ...prev, date_from: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="date_to">{t('global.date_to')}</Label>
                            <TextInput
                                id="date_to"
                                sizing="sm"
                                dir="ltr"
                                placeholder="1403/01/01"
                                value={form.date_to}
                                onChange={(e) => setForm((prev) => ({ ...prev, date_to: e.target.value }))}
                            />
                        </div>
                    </div>
                </ReportFilterPanel>

                <ReportResultsCard
                    title={t('global.reports')}
                    hasSearch={hasSearch}
                    resultCount={items.length}
                    resultsLabel={t('global.records')}
                    emptyMessage={t('global.search_and_filters')}
                >
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-gray-50/80 dark:bg-gray-800/60">
                                    <TableHead>{t('global.id')}</TableHead>
                                    <TableHead>{t('global.patient_name')}</TableHead>
                                    <TableHead>{t('global.doctor')}</TableHead>
                                    <TableHead>{t('global.room')}</TableHead>
                                    <TableHead>{t('global.bed')}</TableHead>
                                    <TableHead>{t('global.hospitalization_date')}</TableHead>
                                    <TableHead>{t('global.discharge_date')}</TableHead>
                                    <TableHead>{t('global.status')}</TableHead>
                                    <TableHead className="text-end">{t('global.actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.map((item) => (
                                    <TableRow
                                        key={item.id}
                                        className="hover:bg-emerald-50/40 dark:hover:bg-emerald-950/10"
                                    >
                                        <TableCell className="font-mono text-xs text-gray-500">#{item.id}</TableCell>
                                        <TableCell className="font-medium">{item.patient_name ?? '—'}</TableCell>
                                        <TableCell className="text-gray-600">{item.doctor_name ?? '—'}</TableCell>
                                        <TableCell className="text-gray-600">{item.room_name ?? '—'}</TableCell>
                                        <TableCell className="text-gray-600">{item.bed_number ?? '—'}</TableCell>
                                        <TableCell dir="ltr">{item.admission_date ?? '—'}</TableCell>
                                        <TableCell dir="ltr">{item.discharged_at ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={item.is_discharged ? 'gray' : 'success'} className="w-fit">
                                                {item.is_discharged ? t('global.discharged') : t('global.active')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-end">
                                            <TableActionButton
                                                kind="view"
                                                href={item.urls.show}
                                                title={t('global.view')}
                                            />
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {items.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={9} className="py-14 text-center text-gray-500">
                                            {t('global.no_records_found')}
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
