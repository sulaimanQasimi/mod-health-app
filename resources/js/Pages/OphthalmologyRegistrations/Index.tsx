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

interface Registration {
    id: number;
    ref_no: string;
    patient_name: string;
    id_card: string | number | null;
    examiner_name: string | null;
    registration_date: string | null;
    follow_up_date: string | null;
    status: string;
    diagnosis: string | null;
    show_url: string;
    delete_url: string;
}

interface Props {
    registrations: {
        data: Registration[];
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
    stats: { total: number; pending: number; in_progress: number; completed: number; follow_up_due: number };
    filters: {
        search: string;
        status: string;
        examiner_id: string;
        date_from: string;
        date_to: string;
        follow_up_due: string;
        per_page: string;
    };
    doctors: Array<{ id: number; name: string }>;
    permissions: { delete: boolean };
    urls: { current: string };
}

const STATUS_COLORS: Record<string, 'warning' | 'info' | 'success' | 'failure'> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

export default function OphthalmologyRegistrationIndex({
    registrations,
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
    const summary = buildPaginationSummary(registrations.meta, t);

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
            follow_up_due: '',
            per_page: '25',
        };
        setFilters(empty);
        setProcessing(true);
        router.get(urls.current, {}, { onFinish: () => setProcessing(false) });
    };

    const statusLabel = (status: string) =>
        ({
            pending: t('global.status_pending'),
            in_progress: t('global.status_in_progress'),
            completed: t('global.status_completed'),
            cancelled: t('global.status_cancelled'),
        })[status] ?? status;

    return (
        <DashboardLayout>
            <Head title={t('global.ophthalmology_registrations')} />
            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.ophthalmology_registrations')}
                    subtitle={summary}
                    icon="bx-show"
                    accent="from-cyan-500 to-blue-600"
                    backLabel={t('global.back')}
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {[
                        [t('global.total'), stats.total, 'text-slate-600', ''],
                        [t('global.status_pending'), stats.pending, 'text-amber-600', ''],
                        [t('global.status_in_progress'), stats.in_progress, 'text-blue-600', ''],
                        [t('global.status_completed'), stats.completed, 'text-emerald-600', ''],
                        [t('global.oph_follow_up_due'), stats.follow_up_due, 'text-rose-600', '1'],
                    ].map(([label, value, color, followUpDue]) => (
                        <Card
                            key={String(label)}
                            className={`border !shadow-sm ${followUpDue ? 'cursor-pointer hover:border-rose-300' : ''}`}
                            onClick={() => {
                                if (!followUpDue) return;
                                const next = { ...filters, follow_up_due: '1' };
                                setFilters(next);
                                setProcessing(true);
                                router.get(urls.current, Object.fromEntries(Object.entries(next).filter(([, v]) => v !== '')), {
                                    preserveState: true,
                                    preserveScroll: true,
                                    replace: true,
                                    onFinish: () => setProcessing(false),
                                });
                            }}
                        >
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
                                <option value="pending">{t('global.status_pending')}</option>
                                <option value="in_progress">{t('global.status_in_progress')}</option>
                                <option value="completed">{t('global.status_completed')}</option>
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
                        <div>
                            <Label>{t('global.oph_follow_up_due')}</Label>
                            <Select
                                value={filters.follow_up_due}
                                onChange={(event) => setFilters((current) => ({ ...current, follow_up_due: event.target.value }))}
                            >
                                <option value="">{t('global.all')}</option>
                                <option value="1">{t('global.oph_follow_up_due_only')}</option>
                            </Select>
                        </div>
                        <div className="flex items-end gap-2 lg:col-span-6">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" className="me-2" /> : <i className="bx bx-search me-2" />}
                                {t('global.filter')}
                            </Button>
                            <Button type="button" color="light" onClick={reset}>{t('global.reset')}</Button>
                        </div>
                    </form>
                </Card>

                <Card className="border !shadow-sm">
                    {registrations.data.length === 0 ? (
                        <SettingsEmptyState message={t('global.no_registrations_found')} />
                    ) : (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.ref_no')}</TableHeader>
                                    <TableHeader>{t('global.patient_name')}</TableHeader>
                                    <TableHeader>{t('global.id_card')}</TableHeader>
                                    <TableHeader>{t('global.examiner')}</TableHeader>
                                    <TableHeader>{t('global.registration_date')}</TableHeader>
                                    <TableHeader>{t('global.follow_up_date')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.diagnosis')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {registrations.data.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell><TableBadge color="info">{item.ref_no}</TableBadge></TableCell>
                                        <TableCell>{item.patient_name || '—'}</TableCell>
                                        <TableCell muted>{item.id_card ?? '—'}</TableCell>
                                        <TableCell muted>{item.examiner_name ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">{item.registration_date ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">{item.follow_up_date ?? '—'}</TableCell>
                                        <TableCell><TableBadge color={STATUS_COLORS[item.status] ?? 'gray'}>{statusLabel(item.status)}</TableBadge></TableCell>
                                        <TableCell>{item.diagnosis || '—'}</TableCell>
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
                        <SettingsPagination links={registrations.links} />
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
