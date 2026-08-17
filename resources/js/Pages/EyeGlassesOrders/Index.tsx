import { Head, router } from '@inertiajs/react';
import { Button, Card, Label, Select, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import TableBadge from '../../Components/ui/TableBadge';
import { useTranslation } from '../../hooks/useTranslation';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import { EYE_GLASSES_STATUS_COLORS, eyeGlassesStatusLabel } from '../../Components/EyeGlasses/status';

interface Order {
    id: number;
    ref_no: string;
    patient_name: string;
    id_card: string | number | null;
    examiner_name: string | null;
    request_date: string | null;
    status: string;
    frame_type: string | null;
    lens_type: string | null;
    amount: string | number | null;
    show_url: string;
    delete_url: string;
}

interface Props {
    orders: {
        data: Order[];
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
    stats: { total: number; requested: number; processing: number; paid: number; delivered: number };
    filters: {
        search: string;
        status: string;
        examiner_id: string;
        date_from: string;
        date_to: string;
        per_page: string;
    };
    doctors: Array<{ id: number; name: string }>;
    permissions: { delete: boolean };
    urls: { current: string };
}

export default function EyeGlassesOrderIndex({
    orders,
    stats,
    filters: initialFilters,
    doctors,
    permissions,
    urls,
}: Props) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(initialFilters);
    const [processing, setProcessing] = useState(false);
    const doctorOptions = doctors.map((doctor) => ({ value: String(doctor.id), label: doctor.name }));
    const summary = buildPaginationSummary(orders.meta, t);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setProcessing(true);
        const query = Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
        router.get(urls.current, query, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => setProcessing(false),
        });
    };

    const reset = () => {
        const empty = {
            search: '',
            status: '',
            examiner_id: '',
            date_from: '',
            date_to: '',
            per_page: '25',
        };
        setFilters(empty);
        setProcessing(true);
        router.get(urls.current, {}, { onFinish: () => setProcessing(false) });
    };

    const optionLabel = (prefix: string, value: string | null) => {
        if (!value) return '—';
        const key = `global.eye_glasses_${prefix}_${value}`;
        const translated = t(key);
        return translated === key ? value : translated;
    };

    return (
        <DashboardLayout>
            <Head title={t('global.eye_glasses_orders')} />
            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.eye_glasses_orders')}
                    subtitle={summary}
                    icon="bx-glasses"
                    accent="from-indigo-500 to-cyan-600"
                    backLabel={t('global.back')}
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {[
                        [t('global.total'), stats.total, 'text-slate-600'],
                        [t('global.eye_glasses_status_requested'), stats.requested, 'text-amber-600'],
                        [t('global.eye_glasses_status_processing'), stats.processing, 'text-blue-600'],
                        [t('global.eye_glasses_status_paid'), stats.paid, 'text-violet-600'],
                        [t('global.eye_glasses_status_delivered'), stats.delivered, 'text-emerald-600'],
                    ].map(([label, value, color]) => (
                        <Card key={String(label)} className="border !shadow-sm">
                            <div className="text-sm text-gray-500">{label}</div>
                            <div className={`text-3xl font-semibold ${color}`}>{value}</div>
                        </Card>
                    ))}
                </div>

                <Card className="border !shadow-sm">
                    <form onSubmit={applyFilters} className="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                        <div className="lg:col-span-2">
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters((current) => ({ ...current, search: event.target.value }))}
                                placeholder={t('global.patient_name')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.status')}</Label>
                            <Select value={filters.status} onChange={(event) => setFilters((current) => ({ ...current, status: event.target.value }))}>
                                <option value="">{t('global.all')}</option>
                                <option value="requested">{t('global.eye_glasses_status_requested')}</option>
                                <option value="processing">{t('global.eye_glasses_status_processing')}</option>
                                <option value="paid">{t('global.eye_glasses_status_paid')}</option>
                                <option value="delivered">{t('global.eye_glasses_status_delivered')}</option>
                                <option value="cancelled">{t('global.status_cancelled')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.examiner')}</Label>
                            <SearchableSelect
                                value={filters.examiner_id}
                                onChange={(examiner_id) => setFilters((current) => ({ ...current, examiner_id }))}
                                options={doctorOptions}
                            />
                        </div>
                        <div>
                            <Label>{t('global.from_date')}</Label>
                            <PersianDateInput value={filters.date_from} onChange={(date_from) => setFilters((current) => ({ ...current, date_from }))} />
                        </div>
                        <div>
                            <Label>{t('global.to_date')}</Label>
                            <PersianDateInput value={filters.date_to} onChange={(date_to) => setFilters((current) => ({ ...current, date_to }))} />
                        </div>
                        <div className="flex items-end gap-2 lg:col-span-6">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" className="me-2" /> : <i className="bx bx-search me-2" />}
                                {t('global.filter')}
                            </Button>
                            <Button type="button" color="light" onClick={reset}>
                                {t('global.reset')}
                            </Button>
                        </div>
                    </form>
                </Card>

                <Card className="border !shadow-sm">
                    {orders.data.length === 0 ? (
                        <SettingsEmptyState message={t('global.eye_glasses_no_orders')} />
                    ) : (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.ref_no')}</TableHeader>
                                    <TableHeader>{t('global.patient_name')}</TableHeader>
                                    <TableHeader>{t('global.id_card')}</TableHeader>
                                    <TableHeader>{t('global.examiner')}</TableHeader>
                                    <TableHeader>{t('global.eye_glasses_request_date')}</TableHeader>
                                    <TableHeader>{t('global.eye_glasses_frame_type')}</TableHeader>
                                    <TableHeader>{t('global.eye_glasses_lens_type')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {orders.data.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>
                                            <TableBadge color="info">{item.ref_no}</TableBadge>
                                        </TableCell>
                                        <TableCell>{item.patient_name || '—'}</TableCell>
                                        <TableCell muted>{item.id_card ?? '—'}</TableCell>
                                        <TableCell muted>{item.examiner_name ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.request_date ?? '—'}
                                        </TableCell>
                                        <TableCell muted>{optionLabel('frame', item.frame_type)}</TableCell>
                                        <TableCell muted>{optionLabel('lens', item.lens_type)}</TableCell>
                                        <TableCell>
                                            <TableBadge color={EYE_GLASSES_STATUS_COLORS[item.status] ?? 'gray'}>
                                                {eyeGlassesStatusLabel(item.status, t)}
                                            </TableBadge>
                                        </TableCell>
                                        <TableActionsCell>
                                            <TableActionButton kind="view" href={item.show_url} />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                confirm={t('global.are_you_sure')}
                                                onClick={() => router.delete(item.delete_url)}
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                    <div className="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
                        <p className="text-sm text-gray-500">{summary}</p>
                        <SettingsPagination links={orders.links} />
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
