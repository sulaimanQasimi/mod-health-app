import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';

export interface MyVisitFilterValues {
    search?: string;
    token_id: string;
    patient_id: string;
    patient_name?: string;
}

interface MyVisitFiltersProps {
    filters: MyVisitFilterValues;
    processing: boolean;
    onFilterChange: (field: keyof MyVisitFilterValues, value: string) => void;
    onSubmit: (event: FormEvent) => void;
    onReset: () => void;
    showSearch?: boolean;
    showPatientName?: boolean;
}

export default function MyVisitFilters({
    filters,
    processing,
    onFilterChange,
    onSubmit,
    onReset,
    showSearch = false,
    showPatientName = false,
}: MyVisitFiltersProps) {
    const { t } = useTranslation();

    return (
        <div className="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <h2 className="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                <i className="bx bx-filter-alt text-cyan-500" />
                {t('global.filters')}
            </h2>
            <form onSubmit={onSubmit}>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {showSearch && (
                        <div className="sm:col-span-2">
                            <Label htmlFor="filter-search">{t('global.search_by_patient_name_card_phone')}</Label>
                            <TextInput
                                id="filter-search"
                                value={filters.search ?? ''}
                                placeholder={t('global.search_by_patient_name_card_phone')}
                                onChange={(event) => onFilterChange('search', event.target.value)}
                            />
                        </div>
                    )}
                    <div>
                        <Label htmlFor="filter-token-id">{t('global.token_id')}</Label>
                        <TextInput
                            id="filter-token-id"
                            value={filters.token_id}
                            placeholder={t('global.search_by_token_id')}
                            onChange={(event) => onFilterChange('token_id', event.target.value)}
                        />
                    </div>
                    <div>
                        <Label htmlFor="filter-patient-id">{t('global.patient_id')}</Label>
                        <TextInput
                            id="filter-patient-id"
                            value={filters.patient_id}
                            placeholder={t('global.search_by_patient_id')}
                            onChange={(event) => onFilterChange('patient_id', event.target.value)}
                        />
                    </div>
                    {showPatientName && (
                        <div>
                            <Label htmlFor="filter-patient-name">{t('global.patient_name')}</Label>
                            <TextInput
                                id="filter-patient-name"
                                value={filters.patient_name ?? ''}
                                placeholder={t('global.search_by_patient_name')}
                                onChange={(event) => onFilterChange('patient_name', event.target.value)}
                            />
                        </div>
                    )}
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
                    <Button type="button" color="gray" onClick={onReset} disabled={processing}>
                        <i className="bx bx-refresh me-2 text-lg" />
                        {t('global.reset')}
                    </Button>
                </div>
            </form>
        </div>
    );
}
