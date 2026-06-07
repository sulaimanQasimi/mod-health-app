import { Button, Label, Select, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PatientIndexFilterOptions,
    PatientIndexFilters,
} from '../../types/patient';

interface PatientFiltersProps {
    filters: PatientIndexFilters;
    filterOptions: PatientIndexFilterOptions;
    processing: boolean;
    onChange: (field: keyof PatientIndexFilters, value: string) => void;
    onSubmit: () => void;
    onReset: () => void;
    onSelectChange?: (field: keyof PatientIndexFilters, value: string) => void;
}

export default function PatientFilters({
    filters,
    filterOptions,
    processing,
    onChange,
    onSubmit,
    onReset,
    onSelectChange,
}: PatientFiltersProps) {
    const { t } = useTranslation();

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onSubmit();
    };

    const handleSelectChange = (field: keyof PatientIndexFilters, value: string) => {
        if (onSelectChange) {
            onSelectChange(field, value);
            return;
        }

        onChange(field, value);
    };

    return (
        <form onSubmit={handleSubmit}>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                <div>
                    <Label htmlFor="filter-name">{t('global.name')}</Label>
                    <TextInput
                        id="filter-name"
                        value={filters.name}
                        placeholder={t('global.search_by_name')}
                        onChange={(event) => onChange('name', event.target.value)}
                    />
                </div>
                <div>
                    <Label htmlFor="filter-father-name">{t('global.father_name')}</Label>
                    <TextInput
                        id="filter-father-name"
                        value={filters.father_name}
                        placeholder={t('global.search_by_father_name')}
                        onChange={(event) => onChange('father_name', event.target.value)}
                    />
                </div>
                <div>
                    <Label htmlFor="filter-last-name">{t('global.last_name')}</Label>
                    <TextInput
                        id="filter-last-name"
                        value={filters.last_name}
                        placeholder={t('global.search_by_last_name')}
                        onChange={(event) => onChange('last_name', event.target.value)}
                    />
                </div>
                <div>
                    <Label htmlFor="filter-phone">{t('global.phone')}</Label>
                    <TextInput
                        id="filter-phone"
                        value={filters.phone}
                        placeholder={t('global.search_by_phone')}
                        onChange={(event) => onChange('phone', event.target.value)}
                    />
                </div>
                <div>
                    <Label htmlFor="filter-card">{t('global.id_card')}</Label>
                    <TextInput
                        id="filter-card"
                        value={filters.card_search}
                        placeholder={t('global.search_by_card')}
                        onChange={(event) => onChange('card_search', event.target.value)}
                    />
                </div>
                <div>
                    <Label htmlFor="filter-militery-type">{t('global.militery_type')}</Label>
                    <Select
                        id="filter-militery-type"
                        value={filters.militery_type_id}
                        onChange={(event) => handleSelectChange('militery_type_id', event.target.value)}
                    >
                        <option value="">{t('global.all')}</option>
                        {filterOptions.militeryTypes.map((type) => (
                            <option key={type.id} value={type.id}>
                                {type.name}
                            </option>
                        ))}
                    </Select>
                </div>
                <div>
                    <Label htmlFor="filter-province">{t('global.province')}</Label>
                    <Select
                        id="filter-province"
                        value={filters.province_id}
                        onChange={(event) => handleSelectChange('province_id', event.target.value)}
                    >
                        <option value="">{t('global.all')}</option>
                        {filterOptions.provinces.map((province) => (
                            <option key={province.id} value={province.id}>
                                {province.name_dr}
                            </option>
                        ))}
                    </Select>
                </div>
                <div>
                    <Label htmlFor="filter-gender">{t('global.gender')}</Label>
                    <Select
                        id="filter-gender"
                        value={filters.gender}
                        onChange={(event) => handleSelectChange('gender', event.target.value)}
                    >
                        <option value="">{t('global.all')}</option>
                        <option value="0">{t('global.male')}</option>
                        <option value="1">{t('global.female')}</option>
                    </Select>
                </div>
                <div>
                    <Label htmlFor="filter-job-category">{t('global.job_category')}</Label>
                    <Select
                        id="filter-job-category"
                        value={filters.job_category}
                        onChange={(event) => handleSelectChange('job_category', event.target.value)}
                    >
                        <option value="">{t('global.all')}</option>
                        <option value="0">{t('global.military')}</option>
                        <option value="1">{t('global.civilian')}</option>
                    </Select>
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
                <Button type="button" color="gray" onClick={onReset} disabled={processing}>
                    <i className="bx bx-refresh me-2 text-lg" />
                    {t('global.reset')}
                </Button>
            </div>
        </form>
    );
}
