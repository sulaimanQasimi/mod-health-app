import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PatientsTable from '../../Components/Patients/PatientsTable';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PaginatedPatients,
    PatientIndexFilterOptions,
    PatientIndexFilters,
    PatientIndexPermissions,
    PatientIndexUrls,
    PaginationLink,
} from '../../types/patient';

interface IndexPatientProps {
    patients: PaginatedPatients;
    filters: PatientIndexFilters;
    filterOptions: PatientIndexFilterOptions;
    permissions: PatientIndexPermissions;
    urls: PatientIndexUrls;
}

const EMPTY_FILTERS: PatientIndexFilters = {
    name: '',
    father_name: '',
    last_name: '',
    phone: '',
    card_search: '',
    militery_type_id: '',
    province_id: '',
    gender: '',
    job_category: '',
};

function cleanFilters(filters: PatientIndexFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

function decodePaginationLabel(label: string): string {
    return label
        .replace('&laquo;', '«')
        .replace('&raquo;', '»')
        .replace(/&[^;]+;/g, '')
        .trim();
}

export default function IndexPatient({
    patients,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: IndexPatientProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<PatientIndexFilters>(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: PatientIndexFilters) => {
            setProcessing(true);
            router.get(urls.index, cleanFilters(nextFilters), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.index],
    );

    const updateFilter = (field: keyof PatientIndexFilters, value: string) => {
        setFilters((current) => ({ ...current, [field]: value }));
    };

    const handleSelectChange = (field: keyof PatientIndexFilters, value: string) => {
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

    const handleDelete = (patientId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        router.delete(`${urls.destroy}/${patientId}`, {
            preserveScroll: true,
        });
    };

    const summaryLabel =
        patients.meta.from && patients.meta.to
            ? `${t('global.showing')} ${patients.meta.from}-${patients.meta.to} ${t('global.of')} ${patients.meta.total} ${t('global.results')}`
            : `${patients.meta.total} ${t('global.results')}`;

    const renderPaginationLink = (link: PaginationLink, index: number) => {
        const label = decodePaginationLabel(link.label);
        const isPrevious = label === '«' || label.toLowerCase().includes('previous');
        const isNext = label === '»' || label.toLowerCase().includes('next');
        const isEllipsis = label === '...';

        if (isEllipsis) {
            return (
                <li key={`ellipsis-${index}`}>
                    <span className="flex h-9 items-center border border-gray-300 bg-white px-3 leading-tight text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        ...
                    </span>
                </li>
            );
        }

        const baseClass = 'flex h-9 items-center border border-gray-300 px-3 leading-tight dark:border-gray-700';
        const activeClass =
            'z-10 border-blue-300 bg-blue-50 text-blue-600 dark:border-gray-700 dark:bg-gray-700 dark:text-white';
        const inactiveClass =
            'bg-white text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white';
        const disabledClass = 'cursor-not-allowed bg-white text-gray-300 dark:bg-gray-800 dark:text-gray-600';
        const roundedClass = isPrevious ? 'rounded-s-lg' : isNext ? 'rounded-e-lg' : '';

        if (!link.url) {
            return (
                <li key={`${label}-${index}`}>
                    <span className={`${baseClass} ${disabledClass} ${roundedClass}`}>
                        {isPrevious ? (
                            <i className="bx bx-chevron-left text-lg" />
                        ) : isNext ? (
                            <i className="bx bx-chevron-right text-lg" />
                        ) : (
                            label
                        )}
                    </span>
                </li>
            );
        }

        return (
            <li key={`${label}-${index}`}>
                <Link
                    href={link.url}
                    preserveScroll
                    className={`${baseClass} ${link.active ? activeClass : inactiveClass} ${roundedClass}`}
                >
                    {isPrevious ? (
                        <i className="bx bx-chevron-left text-lg" />
                    ) : isNext ? (
                        <i className="bx bx-chevron-right text-lg" />
                    ) : (
                        label
                    )}
                </Link>
            </li>
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.patients_list')} />

            <div className="mx-auto max-w-[1600px] min-w-0">
                <Card className="shadow-sm [&>div]:justify-start! [&>div]:gap-0 [&>div]:p-0">
                    <div className="border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h1 className="text-xl font-semibold text-gray-900 dark:text-white">
                                    {t('global.patients_list')}
                                </h1>
                                <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {patients.meta.total} {t('global.patients')}
                                </p>
                            </div>
                            {permissions.create && (
                                <Button color="blue" as={Link} href={urls.create} className="w-fit shrink-0">
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.create')}
                                </Button>
                            )}
                        </div>
                    </div>

                    <div className="border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                        <h2 className="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                            <i className="bx bx-filter-alt text-blue-500" />
                            {t('global.filters')}
                        </h2>
                        <form onSubmit={handleFilterSubmit}>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                                <div>
                                    <Label htmlFor="filter-name">{t('global.name')}</Label>
                                    <TextInput
                                        id="filter-name"
                                        value={filters.name}
                                        placeholder={t('global.search_by_name')}
                                        onChange={(event) => updateFilter('name', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-father-name">{t('global.father_name')}</Label>
                                    <TextInput
                                        id="filter-father-name"
                                        value={filters.father_name}
                                        placeholder={t('global.search_by_father_name')}
                                        onChange={(event) => updateFilter('father_name', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-last-name">{t('global.last_name')}</Label>
                                    <TextInput
                                        id="filter-last-name"
                                        value={filters.last_name}
                                        placeholder={t('global.search_by_last_name')}
                                        onChange={(event) => updateFilter('last_name', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-phone">{t('global.phone')}</Label>
                                    <TextInput
                                        id="filter-phone"
                                        value={filters.phone}
                                        placeholder={t('global.search_by_phone')}
                                        onChange={(event) => updateFilter('phone', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-card">{t('global.id_card')}</Label>
                                    <TextInput
                                        id="filter-card"
                                        value={filters.card_search}
                                        placeholder={t('global.search_by_card')}
                                        onChange={(event) => updateFilter('card_search', event.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="filter-militery-type">{t('global.militery_type')}</Label>
                                    <SearchableSelect
                                        id="filter-militery-type"
                                        value={filters.militery_type_id}
                                        onChange={(value) => handleSelectChange('militery_type_id', value)}
                                        placeholder={t('global.all')}
                                    >
                                        <option value="">{t('global.all')}</option>
                                        {filterOptions.militeryTypes.map((type) => (
                                            <option key={type.id} value={type.id}>
                                                {type.name}
                                            </option>
                                        ))}
                                    </SearchableSelect>
                                </div>
                                <div>
                                    <Label htmlFor="filter-province">{t('global.province')}</Label>
                                    <SearchableSelect
                                        id="filter-province"
                                        value={filters.province_id}
                                        onChange={(value) => handleSelectChange('province_id', value)}
                                        placeholder={t('global.all')}
                                    >
                                        <option value="">{t('global.all')}</option>
                                        {filterOptions.provinces.map((province) => (
                                            <option key={province.id} value={province.id}>
                                                {province.name_dr}
                                            </option>
                                        ))}
                                    </SearchableSelect>
                                </div>
                                <div>
                                    <Label htmlFor="filter-gender">{t('global.gender')}</Label>
                                    <SearchableSelect
                                        id="filter-gender"
                                        value={filters.gender}
                                        onChange={(value) => handleSelectChange('gender', value)}
                                        placeholder={t('global.all')}
                                    >
                                        <option value="">{t('global.all')}</option>
                                        <option value="0">{t('global.male')}</option>
                                        <option value="1">{t('global.female')}</option>
                                    </SearchableSelect>
                                </div>
                                <div>
                                    <Label htmlFor="filter-job-category">{t('global.job_category')}</Label>
                                    <SearchableSelect
                                        id="filter-job-category"
                                        value={filters.job_category}
                                        onChange={(value) => handleSelectChange('job_category', value)}
                                        placeholder={t('global.all')}
                                    >
                                        <option value="">{t('global.all')}</option>
                                        <option value="0">{t('global.military')}</option>
                                        <option value="1">{t('global.civilian')}</option>
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

                    <div className="min-w-0 px-6 py-6">
                        <PatientsTable
                            patients={patients.data}
                            permissions={permissions}
                            urls={urls}
                            onDelete={handleDelete}
                        />

                        {patients.links.length > 3 && (
                            <div className="mt-4 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-4 dark:border-gray-700 sm:flex-row">
                                <p className="text-sm text-gray-500 dark:text-gray-400">{summaryLabel}</p>
                                <nav aria-label="Pagination">
                                    <ul className="inline-flex items-center -space-x-px text-sm">
                                        {patients.links.map(renderPaginationLink)}
                                    </ul>
                                </nav>
                            </div>
                        )}
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
