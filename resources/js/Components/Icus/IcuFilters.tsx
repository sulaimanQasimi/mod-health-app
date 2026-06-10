import { Badge, Button, Label, Select, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { IcuListFilters } from '../../types/icu';
import IcuDischargeTabs from './IcuDischargeTabs';
import { ICU_APPLY_BTN_CLASS } from './icuUi';

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
    embedded?: boolean;
    onChange: (filters: IcuListFilters) => void;
    onApply: (filters: IcuListFilters) => void;
    onReset: () => void;
}

function dischargeFilterLabel(value: string, t: (key: string) => string): string {
    const keys: Record<string, string> = {
        all: 'global.all_approved',
        in_icu: 'global.in_icu',
        discharged: 'global.discharged',
        recovered: 'global.recovered',
        died: 'global.died',
        moved: 'global.moved',
    };

    return t(keys[value] ?? 'global.in_icu');
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

function FilterChip({
    label,
    value,
    onRemove,
    colorClass,
}: {
    label: string;
    value: string;
    onRemove: () => void;
    colorClass: string;
}) {
    return (
        <button
            type="button"
            onClick={onRemove}
            className={`inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium transition hover:opacity-80 ${colorClass}`}
        >
            <span className="font-semibold">{label}:</span>
            <span>{value}</span>
            <i className="bx bx-x text-sm" />
        </button>
    );
}

export default function IcuFilters({
    filters,
    processing,
    showDischargeTabs = false,
    embedded = false,
    onChange,
    onApply,
    onReset,
}: IcuFiltersProps) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(() => embedded || countActiveFilters(filters, showDischargeTabs) > 0);
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

    const formContent = (
        <form onSubmit={handleSubmit} className="space-y-4">
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
                    <Label htmlFor="icu-search" className="mb-1.5 flex items-center gap-1.5">
                        <i className="bx bx-search text-sky-500" />
                        {t('global.search')}
                    </Label>
                    <TextInput
                        id="icu-search"
                        sizing="sm"
                        placeholder={t('global.search_patient_placeholder')}
                        value={filters.search}
                        onChange={(e) => onChange({ ...filters, search: e.target.value })}
                    />
                </div>
                <div>
                    <Label htmlFor="icu-patient-name" className="mb-1.5 flex items-center gap-1.5">
                        <i className="bx bx-user text-emerald-500" />
                        {t('global.patient_name')}
                    </Label>
                    <TextInput
                        id="icu-patient-name"
                        sizing="sm"
                        placeholder={t('global.search_by_patient_name')}
                        value={filters.patient_name}
                        onChange={(e) => onChange({ ...filters, patient_name: e.target.value })}
                    />
                </div>
                <div>
                    <Label htmlFor="icu-card-number" className="mb-1.5 flex items-center gap-1.5">
                        <i className="bx bx-id-card text-cyan-500" />
                        {t('global.card_number')}
                    </Label>
                    <TextInput
                        id="icu-card-number"
                        sizing="sm"
                        placeholder={t('global.search_by_card_number')}
                        value={filters.card_number}
                        onChange={(e) => onChange({ ...filters, card_number: e.target.value })}
                    />
                </div>
                <div>
                    <Label htmlFor="icu-father-name" className="mb-1.5 flex items-center gap-1.5">
                        <i className="bx bx-user-circle text-violet-500" />
                        {t('global.father_name')}
                    </Label>
                    <TextInput
                        id="icu-father-name"
                        sizing="sm"
                        placeholder={t('global.search_by_father_name')}
                        value={filters.father_name}
                        onChange={(e) => onChange({ ...filters, father_name: e.target.value })}
                    />
                </div>
            </div>

            <div className="flex flex-col gap-4 border-t border-gray-100 pt-4 sm:flex-row sm:items-end dark:border-gray-800">
                <div className="w-full sm:w-40">
                    <Label htmlFor="icu-per-page" className="mb-1.5">
                        {t('global.per_page')}
                    </Label>
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
                    <button type="submit" className={ICU_APPLY_BTN_CLASS} disabled={processing}>
                        {processing ? (
                            <Spinner size="sm" />
                        ) : (
                            <i className="bx bx-filter-alt" />
                        )}
                        {t('global.apply_filters')}
                    </button>
                    <Button type="button" color="light" size="sm" disabled={processing} onClick={onReset}>
                        <i className="bx bx-refresh me-1" />
                        {t('global.reset')}
                    </Button>
                </div>
            </div>
        </form>
    );

    const activeChips = activeCount > 0 && (
        <div className="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
            <span className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                {t('global.active_filters')}:
            </span>
            {filters.search && (
                <FilterChip
                    label={t('global.search')}
                    value={filters.search}
                    onRemove={() => removeFilter('search')}
                    colorClass="bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300"
                />
            )}
            {filters.patient_name && (
                <FilterChip
                    label={t('global.patient_name')}
                    value={filters.patient_name}
                    onRemove={() => removeFilter('patient_name')}
                    colorClass="bg-sky-100 text-sky-800 dark:bg-sky-950/50 dark:text-sky-300"
                />
            )}
            {filters.card_number && (
                <FilterChip
                    label={t('global.card_number')}
                    value={filters.card_number}
                    onRemove={() => removeFilter('card_number')}
                    colorClass="bg-cyan-100 text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300"
                />
            )}
            {filters.father_name && (
                <FilterChip
                    label={t('global.father_name')}
                    value={filters.father_name}
                    onRemove={() => removeFilter('father_name')}
                    colorClass="bg-violet-100 text-violet-800 dark:bg-violet-950/50 dark:text-violet-300"
                />
            )}
            {showDischargeTabs && filters.discharge_filter && filters.discharge_filter !== 'in_icu' && (
                <FilterChip
                    label={t('global.filter_by_discharge')}
                    value={dischargeFilterLabel(filters.discharge_filter ?? 'in_icu', t)}
                    onRemove={() => removeFilter('discharge_filter')}
                    colorClass="bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300"
                />
            )}
        </div>
    );

    if (embedded) {
        return (
            <div className="space-y-4">
                {formContent}
                {activeChips}
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                className="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-left transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:bg-gray-800"
            >
                <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i className="bx bx-filter-alt text-rose-500" />
                    {t('global.advanced_filters')}
                    {activeCount > 0 && (
                        <Badge color="failure" size="sm">
                            {activeCount}
                        </Badge>
                    )}
                </span>
                <i className={`bx bx-chevron-down transition-transform ${open ? 'rotate-180' : ''}`} />
            </button>

            {open && (
                <div className="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    {formContent}
                </div>
            )}

            {activeChips}
        </div>
    );
}
