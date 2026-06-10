import { Badge, Button, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { IcuListFilters } from '../../types/icu';
import IcuDischargeTabs from './IcuDischargeTabs';

export const EMPTY_ICU_FILTERS: IcuListFilters = {
    search: '',
    patient_name: '',
    card_number: '',
    father_name: '',
    per_page: '15',
    discharge_filter: 'in_icu',
};

interface IcuFiltersProps {
    filters: IcuListFilters;
    processing: boolean;
    showDischargeTabs?: boolean;
    onChange: (filters: IcuListFilters) => void;
    onApply: (filters: IcuListFilters) => void;
    onReset: () => void;
}

function countActiveFilters(filters: IcuListFilters, showDischargeTabs: boolean): number {
    let count = 0;
    if (filters.search) count++;
    if (filters.patient_name) count++;
    if (filters.card_number) count++;
    if (filters.father_name) count++;
    if (filters.per_page && filters.per_page !== '15') count++;
    if (showDischargeTabs && filters.discharge_filter && filters.discharge_filter !== 'in_icu') count++;
    return count;
}

export default function IcuFilters({
    filters,
    processing,
    showDischargeTabs = false,
    onChange,
    onApply,
    onReset,
}: IcuFiltersProps) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(() => countActiveFilters(filters, showDischargeTabs) > 0);
    const activeCount = useMemo(
        () => countActiveFilters(filters, showDischargeTabs),
        [filters, showDischargeTabs]
    );

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onApply(filters);
    };

    const removeFilter = (key: keyof IcuListFilters) => {
        const next = {
            ...filters,
            [key]: key === 'per_page' ? '15' : key === 'discharge_filter' ? 'in_icu' : '',
        };
        onChange(next);
        onApply(next);
    };

    return (
        <div className="space-y-4">
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                className="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-left dark:border-gray-700 dark:bg-gray-800/50"
            >
                <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i className="bx bx-filter-alt text-blue-500" />
                    {t('global.advanced_filters')}
                    {activeCount > 0 && (
                        <Badge color="info" size="sm">
                            {activeCount}
                        </Badge>
                    )}
                </span>
                <i className={`bx bx-chevron-down transition-transform ${open ? 'rotate-180' : ''}`} />
            </button>

            {open && (
                <form onSubmit={handleSubmit} className="space-y-4 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    {showDischargeTabs && (
                        <IcuDischargeTabs
                            value={filters.discharge_filter ?? 'in_icu'}
                            disabled={processing}
                            onChange={(discharge_filter) => {
                                const next = { ...filters, discharge_filter };
                                onChange(next);
                                onApply(next);
                            }}
                        />
                    )}

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label htmlFor="icu-search">{t('global.search')}</Label>
                            <TextInput
                                id="icu-search"
                                sizing="sm"
                                placeholder={t('global.search_patient_placeholder')}
                                value={filters.search}
                                onChange={(e) => onChange({ ...filters, search: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label htmlFor="icu-patient-name">{t('global.patient_name')}</Label>
                            <TextInput
                                id="icu-patient-name"
                                sizing="sm"
                                placeholder={t('global.search_by_patient_name')}
                                value={filters.patient_name}
                                onChange={(e) => onChange({ ...filters, patient_name: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label htmlFor="icu-card-number">{t('global.card_number')}</Label>
                            <TextInput
                                id="icu-card-number"
                                sizing="sm"
                                placeholder={t('global.search_by_card_number')}
                                value={filters.card_number}
                                onChange={(e) => onChange({ ...filters, card_number: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label htmlFor="icu-father-name">{t('global.father_name')}</Label>
                            <TextInput
                                id="icu-father-name"
                                sizing="sm"
                                placeholder={t('global.search_by_father_name')}
                                value={filters.father_name}
                                onChange={(e) => onChange({ ...filters, father_name: e.target.value })}
                            />
                        </div>
                    </div>

                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div className="w-full sm:w-40">
                            <Label htmlFor="icu-per-page">{t('global.per_page')}</Label>
                            <Select
                                id="icu-per-page"
                                sizing="sm"
                                value={filters.per_page}
                                onChange={(e) => onChange({ ...filters, per_page: e.target.value })}
                            >
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </Select>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button type="submit" color="blue" size="sm" disabled={processing}>
                                <i className="bx bx-filter me-1" />
                                {t('global.apply_filters')}
                            </Button>
                            <Button type="button" color="light" size="sm" disabled={processing} onClick={onReset}>
                                <i className="bx bx-refresh me-1" />
                                {t('global.reset')}
                            </Button>
                        </div>
                    </div>
                </form>
            )}

            {activeCount > 0 && (
                <div className="flex flex-wrap items-center gap-2">
                    <span className="text-xs font-semibold text-gray-500">{t('global.active_filters')}:</span>
                    {filters.search && (
                        <button
                            type="button"
                            onClick={() => removeFilter('search')}
                            className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs text-green-800"
                        >
                            {t('global.search')}: {filters.search}
                            <i className="bx bx-x" />
                        </button>
                    )}
                    {filters.patient_name && (
                        <button
                            type="button"
                            onClick={() => removeFilter('patient_name')}
                            className="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800"
                        >
                            {t('global.patient_name')}: {filters.patient_name}
                            <i className="bx bx-x" />
                        </button>
                    )}
                    {filters.card_number && (
                        <button
                            type="button"
                            onClick={() => removeFilter('card_number')}
                            className="inline-flex items-center gap-1 rounded-full bg-cyan-100 px-2 py-1 text-xs text-cyan-800"
                        >
                            {t('global.card_number')}: {filters.card_number}
                            <i className="bx bx-x" />
                        </button>
                    )}
                    {filters.father_name && (
                        <button
                            type="button"
                            onClick={() => removeFilter('father_name')}
                            className="inline-flex items-center gap-1 rounded-full bg-gray-200 px-2 py-1 text-xs text-gray-800"
                        >
                            {t('global.father_name')}: {filters.father_name}
                            <i className="bx bx-x" />
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
