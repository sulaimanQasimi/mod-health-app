import { Head, Link, router, usePage } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import AppointmentPagination from '../../Components/Appointments/AppointmentPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import StatCard from '../../Components/ui/StatCard';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface PrescriptionReportItem {
    id: number;
    patient_name: string | null;
    patient_father_name: string | null;
    patient_id_card: string | null;
    doctor_name: string | null;
    branch_name: string | null;
    pharmacy_name: string | null;
    department_name: string | null;
    processor_name: string | null;
    is_completed: boolean;
    created_at: string | null;
    urls: { show: string };
}

interface PharmacyUser {
    id: number;
    name: string;
}

interface ReportProps {
    prescriptions: {
        data: PrescriptionReportItem[];
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
    summary: { total: number; completed: number; pending: number };
    hasSearch: boolean;
    filters: Record<string, string>;
    filterOptions: {
        pharmacies: Array<{ id: number; name: string }>;
    };
    urls: {
        current: string;
        index: string;
        pharmacyUsers: string;
        export: string;
    };
}

const EMPTY_FILTERS = {
    patient_name: '',
    father_name: '',
    is_completed: '',
    pharmacy_id: '',
    processed_by_user_id: '',
    start: '',
    end: '',
    per_page: '25',
};

function buildSearchParams(filters: Record<string, string>): Record<string, string> {
    const params: Record<string, string> = { search: '1' };
    Object.entries(filters).forEach(([key, value]) => {
        if (value !== '') {
            params[key] = value;
        }
    });
    return params;
}

export default function PrescriptionsReport({
    prescriptions,
    summary,
    hasSearch,
    filters: serverFilters,
    filterOptions,
    urls,
}: ReportProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [filtersOpen, setFiltersOpen] = useState(true);
    const [pharmacyUsers, setPharmacyUsers] = useState<PharmacyUser[]>([]);
    const [loadingUsers, setLoadingUsers] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    useEffect(() => {
        if (!filters.pharmacy_id) {
            setPharmacyUsers([]);
            return;
        }

        setLoadingUsers(true);
        fetch(`${urls.pharmacyUsers}/${filters.pharmacy_id}/users`)
            .then((response) => response.json())
            .then((data: PharmacyUser[]) => setPharmacyUsers(data))
            .catch(() => setPharmacyUsers([]))
            .finally(() => setLoadingUsers(false));
    }, [filters.pharmacy_id, urls.pharmacyUsers]);

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

    const canExport = hasSearch && prescriptions.data.length > 0;

    return (
        <DashboardLayout>
            <Head title={t('global.reports')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.reports')}
                    subtitle={t('global.prescriptions')}
                    icon="bx-bar-chart-alt-2"
                    accent="from-emerald-500 to-teal-600"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        canExport ? (
                            <SettingsPageActions>
                                <form action={urls.export} method="GET" target="_blank" className="inline-flex gap-2">
                                    {Object.entries(exportFields).map(([key, value]) => (
                                        <input key={key} type="hidden" name={key} value={value} />
                                    ))}
                                    <button
                                        type="submit"
                                        name="export"
                                        value="excel"
                                        className="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300"
                                    >
                                        <i className="bx bx-spreadsheet" />
                                        Excel
                                    </button>
                                    <button
                                        type="submit"
                                        name="export"
                                        value="print"
                                        className="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300"
                                    >
                                        <i className="bx bx-file" />
                                        PDF
                                    </button>
                                </form>
                            </SettingsPageActions>
                        ) : undefined
                    }
                />

                {hasSearch && (
                    <div className="grid gap-4 sm:grid-cols-3">
                        <StatCard
                            title={t('global.total')}
                            value={summary.total}
                            subtitle=""
                            iconClass="bx bx-list-ul"
                            iconBgClass="bg-blue-600"
                            borderClass="border-blue-200 dark:border-blue-800"
                            valueClass="text-blue-700 dark:text-blue-300"
                        />
                        <StatCard
                            title={t('global.completed')}
                            value={summary.completed}
                            subtitle=""
                            iconClass="bx bx-check-circle"
                            iconBgClass="bg-emerald-600"
                            borderClass="border-emerald-200 dark:border-emerald-800"
                            valueClass="text-emerald-700 dark:text-emerald-300"
                        />
                        <StatCard
                            title={t('global.pending')}
                            value={summary.pending}
                            subtitle=""
                            iconClass="bx bx-time"
                            iconBgClass="bg-amber-500"
                            borderClass="border-amber-200 dark:border-amber-800"
                            valueClass="text-amber-700 dark:text-amber-300"
                        />
                    </div>
                )}

                <Card className="!shadow-sm">
                    <button
                        type="button"
                        onClick={() => setFiltersOpen((open) => !open)}
                        className="flex w-full items-center justify-between gap-3 border-b border-gray-100 px-1 pb-4 text-start dark:border-gray-700"
                    >
                        <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-filter-alt text-emerald-500" />
                            {t('global.advanced_filters')}
                        </span>
                        <i className={`bx ${filtersOpen ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
                    </button>

                    {filtersOpen && (
                        <form onSubmit={handleSubmit} className="space-y-4 pt-4">
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
                                    <Label>{t('global.father_name')}</Label>
                                    <TextInput
                                        value={filters.father_name}
                                        onChange={(e) =>
                                            setFilters((prev) => ({ ...prev, father_name: e.target.value }))
                                        }
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.status')}</Label>
                                    <SearchableSelect
                                        value={filters.is_completed}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, is_completed: value }))}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            { value: '1', label: t('global.completed') },
                                            { value: '0', label: t('global.pending') },
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.pharmacy')}</Label>
                                    <SearchableSelect
                                        value={filters.pharmacy_id}
                                        onChange={(value) =>
                                            setFilters((prev) => ({
                                                ...prev,
                                                pharmacy_id: value,
                                                processed_by_user_id: '',
                                            }))
                                        }
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...filterOptions.pharmacies.map((pharmacy) => ({
                                                value: String(pharmacy.id),
                                                label: pharmacy.name,
                                            })),
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.processed_by')}</Label>
                                    <SearchableSelect
                                        value={filters.processed_by_user_id}
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, processed_by_user_id: value }))
                                        }
                                        disabled={!filters.pharmacy_id || loadingUsers}
                                        options={[
                                            { value: '', label: t('global.all') },
                                            ...pharmacyUsers.map((user) => ({
                                                value: String(user.id),
                                                label: user.name,
                                            })),
                                        ]}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.from')}</Label>
                                    <PersianDateInput
                                        value={filters.start}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, start: value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.to')}</Label>
                                    <PersianDateInput
                                        value={filters.end}
                                        onChange={(value) => setFilters((prev) => ({ ...prev, end: value }))}
                                    />
                                </div>
                            </div>
                            <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                                <Button type="submit" color="blue" disabled={processing}>
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
                            </div>
                        </form>
                    )}
                </Card>

                <Card className="!shadow-sm">
                    {!hasSearch ? (
                        <div className="flex flex-col items-center gap-3 py-16 text-center text-gray-500">
                            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950/30">
                                <i className="bx bx-search-alt text-2xl text-emerald-500" />
                            </div>
                            <p className="text-sm">{t('global.search_and_filters')}</p>
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <Table embedded>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>#</TableHeader>
                                            <TableHeader>{t('global.patient_name')}</TableHeader>
                                            <TableHeader>{t('global.father_name')}</TableHeader>
                                            <TableHeader>{t('global.id_card')}</TableHeader>
                                            <TableHeader>{t('global.doctor')}</TableHeader>
                                            <TableHeader>{t('global.pharmacy')}</TableHeader>
                                            <TableHeader>{t('global.department')}</TableHeader>
                                            <TableHeader>{t('global.processed_by')}</TableHeader>
                                            <TableHeader>{t('global.status')}</TableHeader>
                                            <TableHeader>{t('global.created_at')}</TableHeader>
                                            <TableHeader className="text-end">{t('global.actions')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {prescriptions.data.length === 0 ? (
                                            <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                                <TableCell colSpan={11} align="center" muted className="py-12">
                                                    {t('global.no_records_found')}
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            prescriptions.data.map((item, index) => (
                                                <TableRow key={item.id}>
                                                    <TableCell className="font-mono text-xs text-gray-500">
                                                        {(prescriptions.meta.from ?? 1) + index}
                                                    </TableCell>
                                                    <TableCell className="font-medium">{item.patient_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.patient_father_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.patient_id_card ?? '—'}</TableCell>
                                                    <TableCell muted>{item.doctor_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.pharmacy_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                                    <TableCell muted>{item.processor_name ?? '—'}</TableCell>
                                                    <TableCell>
                                                        <Badge color={item.is_completed ? 'success' : 'warning'}>
                                                            {item.is_completed ? t('global.completed') : t('global.pending')}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell muted dir="ltr">{item.created_at ?? '—'}</TableCell>
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
                            {prescriptions.links.length > 3 && (
                                <AppointmentPagination
                                    links={prescriptions.links}
                                    meta={prescriptions.meta}
                                    t={t}
                                />
                            )}
                        </>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
