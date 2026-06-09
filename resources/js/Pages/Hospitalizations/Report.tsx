import { Head, router } from '@inertiajs/react';
import { Badge, Button, Label, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import HospitalizationPanel from '../../Components/Hospitalizations/HospitalizationPanel';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
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
import {
    HospitalizationOption,
    HospitalizationReportFilters,
    HospitalizationReportItem,
} from '../../types/hospitalization';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    items: HospitalizationReportItem[];
    filters: HospitalizationReportFilters;
    filterOptions: {
        doctors: HospitalizationOption[];
        rooms: HospitalizationOption[];
        foodTypes: HospitalizationOption[];
    };
    urls: { current: string; index: string; export: string };
}

export default function HospitalizationsReport({ items, filters, filterOptions, urls }: ReportProps) {
    const { t } = useTranslation();
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
        <DashboardLayout>
            <Head title={t('global.reports')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.reports')}
                    subtitle={t('global.hospitalizations')}
                    icon="bx-bar-chart-alt-2"
                    accent="from-emerald-600 to-teal-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                <HospitalizationPanel
                    variant="filter"
                    title={t('global.search')}
                    icon="bx-filter-alt"
                    action={
                        <Button as="a" href={urls.export} color="light" size="sm" target="_blank">
                            <i className="bx bx-export me-2" />
                            {t('global.export')}
                        </Button>
                    }
                >
                    <form onSubmit={handleSubmit} className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
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
                        <div className="flex flex-wrap items-end gap-2 lg:col-span-3">
                            <Button type="submit" color="success" size="sm" disabled={processing}>
                                <i className="bx bx-search me-2" />
                                {t('global.search')}
                            </Button>
                        </div>
                    </form>
                </HospitalizationPanel>

                <HospitalizationPanel
                    title={t('global.reports')}
                    icon="bx-spreadsheet"
                    action={
                        <span className="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            {items.length} {t('global.records')}
                        </span>
                    }
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
                </HospitalizationPanel>
            </div>
        </DashboardLayout>
    );
}
