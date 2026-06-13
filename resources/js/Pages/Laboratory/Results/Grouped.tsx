import { Head, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import AppointmentPagination from '../../../Components/Appointments/AppointmentPagination';
import LaboratoryPageHeader from '../../../Components/Laboratory/LaboratoryPageHeader';
import LaboratoryPriorityBadge from '../../../Components/Laboratory/LaboratoryPriorityBadge';
import LaboratoryStatsCards from '../../../Components/Laboratory/LaboratoryStatsCards';
import LaboratoryStatusBadge from '../../../Components/Laboratory/LaboratoryStatusBadge';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    perPageFilterOptions,
    priorityFilterOptions,
    selectOptionsWithAll,
    statusFilterOptions,
} from '../../../utils/laboratoryFilterOptions';
import {
    LaboratoryGroupedCategory,
    LaboratoryGroupedFilters,
    LaboratoryGroupedStats,
    LaboratoryNavUrls,
    PaginatedLaboratoryPatients,
    SelectOption,
} from '../../../types/laboratory';

interface GroupedProps {
    groups: PaginatedLaboratoryPatients & { data: LaboratoryGroupedCategory[] };
    stats: LaboratoryGroupedStats;
    filters: LaboratoryGroupedFilters;
    filterOptions: { doctors: SelectOption[] };
    urls: LaboratoryNavUrls;
}

const EMPTY_FILTERS: LaboratoryGroupedFilters = {
    search: '',
    patient_id: '',
    status: '',
    priority: '',
    doctor: '',
    date_from: '',
    date_to: '',
    per_page: '15',
};

function cleanFilters(filters: LaboratoryGroupedFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function Grouped({ groups, stats, filters: serverFilters, filterOptions, urls }: GroupedProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [expanded, setExpanded] = useState<Record<number, boolean>>({});

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: LaboratoryGroupedFilters) => {
            setProcessing(true);
            router.get(urls.grouped, cleanFilters(nextFilters), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.grouped],
    );

    const updateFilter = (field: keyof LaboratoryGroupedFilters, value: string) => {
        setFilters((current) => ({ ...current, [field]: value }));
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        applyFilters(EMPTY_FILTERS);
    };

    return (
        <DashboardLayout>
            <Head title={t('global.grouped_test_results')} />

            <LaboratoryPageHeader
                title={t('global.grouped_test_results')}
                subtitle={t('global.test_results')}
                icon="bx-collection"
                accent="from-violet-500 to-purple-600"
                navUrls={urls}
                activeTab="grouped"
            />

            <LaboratoryStatsCards stats={stats} />

            <Card className="mb-6 shadow-sm">
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-700">
                        <i className="bx bx-filter-alt text-lg text-violet-600" />
                        <h2 className="text-sm font-semibold">{t('global.advanced_filters')}</h2>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label>{t('global.search_patient')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(e) => updateFilter('search', e.target.value)}
                                placeholder={t('global.search_patient_placeholder') || t('global.search_patient')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.patient_id')}</Label>
                            <TextInput
                                value={filters.patient_id}
                                onChange={(e) => updateFilter('patient_id', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="grouped-status">{t('global.status')}</Label>
                            <SearchableSelect
                                id="grouped-status"
                                value={filters.status}
                                onChange={(value) => updateFilter('status', value)}
                                placeholder={t('global.all')}
                                options={statusFilterOptions(t)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="grouped-priority">{t('global.priority')}</Label>
                            <SearchableSelect
                                id="grouped-priority"
                                value={filters.priority}
                                onChange={(value) => updateFilter('priority', value)}
                                placeholder={t('global.all')}
                                options={priorityFilterOptions(t)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="grouped-doctor">{t('global.doctor')}</Label>
                            <SearchableSelect
                                id="grouped-doctor"
                                value={filters.doctor}
                                onChange={(value) => updateFilter('doctor', value)}
                                placeholder={t('global.all')}
                                options={selectOptionsWithAll(t, filterOptions.doctors)}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <TextInput
                                value={filters.date_from}
                                onChange={(e) => updateFilter('date_from', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <TextInput
                                value={filters.date_to}
                                onChange={(e) => updateFilter('date_to', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="grouped-per-page">{t('global.per_page')}</Label>
                            <SearchableSelect
                                id="grouped-per-page"
                                value={filters.per_page}
                                onChange={(value) => updateFilter('per_page', value)}
                                options={perPageFilterOptions(['10', '15', '25', '50'])}
                            />
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Button type="submit" color="blue" disabled={processing}>
                            {t('global.search')}
                        </Button>
                        <Button type="button" color="light" onClick={handleReset} disabled={processing}>
                            {t('global.reset')}
                        </Button>
                    </div>
                </form>
            </Card>

            {groups.data.length === 0 ? (
                <Card className="shadow-sm">
                    <p className="py-12 text-center text-gray-500">{t('global.no_item_is_found')}</p>
                </Card>
            ) : (
                <div className="space-y-4">
                    {groups.data.map((group) => {
                        const isOpen = expanded[group.category_id] ?? false;

                        return (
                            <Card key={group.category_id} className="overflow-hidden shadow-sm">
                                <div className="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setExpanded((c) => ({
                                                ...c,
                                                [group.category_id]: !isOpen,
                                            }))
                                        }
                                        className="flex min-w-0 flex-1 items-center gap-3 text-left"
                                    >
                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">
                                            <i className="bx bx-collection text-xl" />
                                        </div>
                                        <div className="min-w-0">
                                            <h3 className="font-semibold text-gray-900 dark:text-white">
                                                {t('global.test_group')} #{group.category_id}
                                            </h3>
                                            <p className="text-sm text-gray-500">
                                                {group.test_count} {t('global.tests')}
                                                {group.patient_name && ` · ${group.patient_name}`}
                                            </p>
                                        </div>
                                        <i
                                            className={`bx ms-auto text-xl ${isOpen ? 'bx-chevron-up' : 'bx-chevron-down'}`}
                                        />
                                    </button>
                                    <a
                                        href={group.print_group_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"
                                    >
                                        <i className="bx bx-printer" />
                                        {t('global.print_group')}
                                    </a>
                                </div>

                                {isOpen && (
                                    <div className="divide-y divide-gray-100 dark:divide-gray-700">
                                        {group.registrations.map((registration) => (
                                            <div
                                                key={registration.id}
                                                className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between"
                                            >
                                                <div className="min-w-0 flex-1">
                                                    <p className="font-medium">{registration.lab_type_name}</p>
                                                    <p className="font-mono text-sm text-gray-500">
                                                        {registration.ref_no}
                                                    </p>
                                                </div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <LaboratoryStatusBadge status={registration.status} />
                                                    <LaboratoryPriorityBadge priority={registration.priority} />
                                                    <Badge color="gray">
                                                        {registration.doctor_name ?? '—'}
                                                    </Badge>
                                                    <a
                                                        href={registration.print_url}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700"
                                                        title={t('global.print')}
                                                    >
                                                        <i className="bx bx-printer" />
                                                    </a>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </Card>
                        );
                    })}
                </div>
            )}

            {'links' in groups && groups.links && (
                <AppointmentPagination links={groups.links} meta={groups.meta} t={t} />
            )}
        </DashboardLayout>
    );
}
