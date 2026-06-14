import { Head, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import {
    AppointmentActionGroup,
    AppointmentIconLink,
} from '../../Components/Appointments/AppointmentTableActions';
import AppointmentPageHeader from '../../Components/Appointments/AppointmentPageHeader';
import AppointmentPagination from '../../Components/Appointments/AppointmentPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import UnderReviewNavTabs from '../../Components/UnderReviews/UnderReviewNavTabs';
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
import {
    PaginatedUnderReviews,
    UnderReviewFilterOptions,
    UnderReviewFilters,
    UnderReviewWorkflowUrls,
} from '../../types/underReview';

interface DischargedProps {
    underReviews: PaginatedUnderReviews;
    activeTab: 'discharged';
    filters: UnderReviewFilters;
    filterOptions: UnderReviewFilterOptions;
    urls: UnderReviewWorkflowUrls;
}

const EMPTY_FILTERS: UnderReviewFilters = {
    patient_name: '',
    id_card: '',
    father_name: '',
    room_id: '',
    department_id: '',
};

function cleanFilters(filters: UnderReviewFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function UnderReviewsDischarged({
    underReviews,
    activeTab,
    filters: serverFilters,
    filterOptions,
    urls,
}: DischargedProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: UnderReviewFilters) => {
            setProcessing(true);
            router.get(urls.discharged, cleanFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.discharged]
    );

    const updateFilter = (field: keyof UnderReviewFilters, value: string) => {
        setFilters((current) => ({ ...current, [field]: value }));
    };

    const handleSelectChange = (field: keyof UnderReviewFilters, value: string) => {
        const nextFilters = { ...filters, [field]: value };
        setFilters(nextFilters);
        applyFilters(nextFilters);
    };

    const handleFilterSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        applyFilters(EMPTY_FILTERS);
    };

    return (
        <DashboardLayout>
            <Head title={t('global.discharged')} />

            <div className="mx-auto max-w-[1600px]">
                <Card className="shadow-sm">
                    <AppointmentPageHeader
                        title={t('global.under_review_patients')}
                        subtitle={t('global.discharged')}
                        icon="bx-check-circle"
                        accent="from-violet-500 to-purple-600"
                    />

                    <UnderReviewNavTabs activeTab={activeTab} urls={urls} />

                    <div className="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                        <h2 className="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-filter-alt text-cyan-500" />
                            {t('global.filters')}
                        </h2>
                        <form onSubmit={handleFilterSubmit}>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                <div>
                                    <Label htmlFor="filter-patient-name">{t('global.patient_name')}</Label>
                                    <TextInput
                                        id="filter-patient-name"
                                        value={filters.patient_name}
                                        placeholder={t('global.search_by_patient_name')}
                                        onChange={(event) =>
                                            updateFilter('patient_name', event.target.value)
                                        }
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-id-card">{t('global.id_card')}</Label>
                                    <TextInput
                                        id="filter-id-card"
                                        value={filters.id_card}
                                        placeholder={t('global.search_by_card')}
                                        onChange={(event) => updateFilter('id_card', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-father-name">{t('global.father_name')}</Label>
                                    <TextInput
                                        id="filter-father-name"
                                        value={filters.father_name}
                                        placeholder={t('global.father_name')}
                                        onChange={(event) =>
                                            updateFilter('father_name', event.target.value)
                                        }
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-department">{t('global.department')}</Label>
                                    <SearchableSelect
                                        id="filter-department"
                                        value={filters.department_id}
                                        onChange={(value) => handleSelectChange('department_id', value)}
                                        placeholder={t('global.all')}
                                    >
                                        <option value="">{t('global.all')}</option>
                                        {filterOptions.departments.map((department) => (
                                            <option key={department.id} value={department.id}>
                                                {department.name}
                                            </option>
                                        ))}
                                    </SearchableSelect>
                                </div>
                                <div>
                                    <Label htmlFor="filter-room">{t('global.room')}</Label>
                                    <SearchableSelect
                                        id="filter-room"
                                        value={filters.room_id}
                                        onChange={(value) => handleSelectChange('room_id', value)}
                                        placeholder={t('global.all')}
                                    >
                                        <option value="">{t('global.all')}</option>
                                        {filterOptions.rooms.map((room) => (
                                            <option key={room.id} value={room.id}>
                                                {room.name}
                                            </option>
                                        ))}
                                    </SearchableSelect>
                                </div>
                            </div>
                            <div className="mt-4 flex flex-wrap justify-end gap-2">
                                <Button type="submit" color="blue" disabled={processing}>
                                    {processing ? (
                                        <>
                                            <Spinner size="sm" className="me-2" />
                                            {t('global.loading')}
                                        </>
                                    ) : (
                                        <>
                                            <i className="bx bx-search me-2 text-lg" />
                                            {t('global.search')}
                                        </>
                                    )}
                                </Button>
                                <Button type="button" color="gray" onClick={handleReset} disabled={processing}>
                                    <i className="bx bx-refresh me-2 text-lg" />
                                    {t('global.reset')}
                                </Button>
                            </div>
                        </form>
                    </div>

                    <Table id="under-reviews-discharged-table">
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>{t('global.id')}</TableHeader>
                                <TableHeader>{t('global.card_number')}</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.father_name')}</TableHeader>
                                <TableHeader>{t('global.department')}</TableHeader>
                                <TableHeader>{t('global.room')}</TableHeader>
                                <TableHeader>{t('global.bed')}</TableHeader>
                                <TableHeader>{t('global.hospitalization_date')}</TableHeader>
                                <TableHeader>{t('global.processed_by')}</TableHeader>
                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {underReviews.data.length === 0 ? (
                                <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                    <TableCell colSpan={10} align="center" muted className="py-12 text-base">
                                        <div className="flex flex-col items-center gap-2">
                                            <div className="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                <i className="bx bx-user-x text-xl text-gray-400" />
                                            </div>
                                            {t('global.no_records_found')}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ) : (
                                underReviews.data.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell className="font-medium text-gray-900 dark:text-white">
                                            {item.id}
                                        </TableCell>
                                        <TableCell>{item.patient_id_card ?? '—'}</TableCell>
                                        <TableCell className="font-medium text-gray-900 dark:text-white">
                                            {item.patient_name ?? '—'}
                                        </TableCell>
                                        <TableCell>{item.father_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.room_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.bed_number ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.admission_date ?? '—'}
                                        </TableCell>
                                        <TableCell muted>{item.processed_by ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <AppointmentActionGroup>
                                                <AppointmentIconLink
                                                    href={item.urls.show}
                                                    icon="bx-expand"
                                                    title={t('global.view')}
                                                    variant="view"
                                                />
                                            </AppointmentActionGroup>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>

                    <AppointmentPagination
                        links={underReviews.links}
                        meta={underReviews.meta}
                        t={t}
                    />
                </Card>
            </div>
        </DashboardLayout>
    );
}
