import { Head, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
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
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

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
    hasSearch,
    filters: serverFilters,
    filterOptions,
    urls,
}: DepartmentReportProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [filtersOpen, setFiltersOpen] = useState(true);

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

    return (
        <DashboardLayout>
            <Head title={t('global.department_report')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.department_report')}
                    subtitle={t('global.appointments')}
                    icon="bx-building"
                    accent="from-indigo-600 to-violet-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                {hasSearch && (
                    <Card className="!shadow-sm">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md">
                                <i className="bx bx-group text-xl" />
                            </div>
                            <div>
                                <p className="text-sm text-gray-500 dark:text-gray-400">{t('global.total_records')}</p>
                                <p className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {appointments.meta.total}
                                </p>
                            </div>
                        </div>
                    </Card>
                )}

                <Card className="!shadow-sm">
                    <button
                        type="button"
                        onClick={() => setFiltersOpen((open) => !open)}
                        className="flex w-full items-center justify-between gap-3 border-b border-gray-100 px-1 pb-4 text-start dark:border-gray-700"
                    >
                        <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-filter-alt text-indigo-500" />
                            {t('global.advanced_filters')}
                        </span>
                        <i className={`bx ${filtersOpen ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
                    </button>

                    {filtersOpen && (
                        <form onSubmit={handleSubmit} className="space-y-4 pt-4">
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <Label>{t('global.department')}</Label>
                                    <SearchableSelect
                                        value={filters.department_id}
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, department_id: value }))
                                        }
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
                                        onChange={(value) =>
                                            setFilters((prev) => ({ ...prev, date_from: value }))
                                        }
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
                            <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
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
                            </div>
                        </form>
                    )}
                </Card>

                <Card className="!shadow-sm">
                    {!hasSearch ? (
                        <div className="flex flex-col items-center gap-3 py-16 text-center text-gray-500 dark:text-gray-400">
                            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-950/30">
                                <i className="bx bx-building text-2xl text-indigo-500" />
                            </div>
                            <p className="text-sm">{t('global.please_select_department_and_date_range')}</p>
                        </div>
                    ) : (
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
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
