import { Badge, Button, Label, Select, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { AnesthesiaListFilters, SelectOption } from '../../types/anesthesia';
import PersianDateInput from '../ui/PersianDateInput';
import { ANESTHESIA_APPLY_BTN_CLASS } from './anesthesiaUi';

export const EMPTY_ANESTHESIA_FILTERS: AnesthesiaListFilters = {
    search: '',
    operation_type_id: '',
    department_id: '',
    anesthesia_type: '',
    date_from: '',
    date_to: '',
    per_page: '15',
};

interface AnesthesiaFiltersProps {
    filters: AnesthesiaListFilters;
    processing: boolean;
    operationTypes: SelectOption[];
    departments: SelectOption[];
    embedded?: boolean;
    onChange: (filters: AnesthesiaListFilters) => void;
    onApply: (filters: AnesthesiaListFilters) => void;
    onReset: () => void;
}

function countActiveFilters(filters: AnesthesiaListFilters): number {
    let count = 0;
    if (filters.search) count++;
    if (filters.operation_type_id) count++;
    if (filters.department_id) count++;
    if (filters.anesthesia_type) count++;
    if (filters.date_from) count++;
    if (filters.date_to) count++;
    if (filters.per_page && filters.per_page !== '15') count++;
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

export default function AnesthesiaFilters({
    filters,
    processing,
    operationTypes,
    departments,
    embedded = false,
    onChange,
    onApply,
    onReset,
}: AnesthesiaFiltersProps) {
    const { t } = useTranslation();
    const [expanded, setExpanded] = useState(countActiveFilters(filters) > 0);

    const activeCount = useMemo(() => countActiveFilters(filters), [filters]);

    const operationTypeName = operationTypes.find(
        (item) => String(item.id) === filters.operation_type_id
    )?.name;
    const departmentName = departments.find((item) => String(item.id) === filters.department_id)?.name;

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onApply(filters);
    };

    const update = (patch: Partial<AnesthesiaListFilters>) => onChange({ ...filters, ...patch });

    return (
        <div className={embedded ? '' : 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900'}>
            <button
                type="button"
                onClick={() => setExpanded((value) => !value)}
                className="flex w-full items-center justify-between gap-3 text-left"
            >
                <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i className="bx bx-filter-alt text-violet-500" />
                    {t('global.advanced_filters')}
                    {activeCount > 0 && (
                        <Badge color="purple" className="font-normal">
                            {activeCount}
                        </Badge>
                    )}
                </span>
                <i className={`bx ${expanded ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
            </button>

            {expanded && (
                <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div className="lg:col-span-2">
                            <Label htmlFor="anesthesia-search">{t('global.search')}</Label>
                            <TextInput
                                id="anesthesia-search"
                                sizing="sm"
                                placeholder={t('global.search_by_patient_operation')}
                                value={filters.search}
                                onChange={(e) => update({ search: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label htmlFor="anesthesia-operation-type">{t('global.operation_type')}</Label>
                            <Select
                                id="anesthesia-operation-type"
                                sizing="sm"
                                value={filters.operation_type_id}
                                onChange={(e) => update({ operation_type_id: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                {operationTypes.map((type) => (
                                    <option key={type.id} value={type.id}>
                                        {type.name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="anesthesia-department">{t('global.department')}</Label>
                            <Select
                                id="anesthesia-department"
                                sizing="sm"
                                value={filters.department_id}
                                onChange={(e) => update({ department_id: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                {departments.map((department) => (
                                    <option key={department.id} value={department.id}>
                                        {department.name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="anesthesia-type-filter">{t('global.anesthesia_type')}</Label>
                            <Select
                                id="anesthesia-type-filter"
                                sizing="sm"
                                value={filters.anesthesia_type}
                                onChange={(e) => update({ anesthesia_type: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                <option value="local">{t('global.local')}</option>
                                <option value="spinal">{t('global.spinal')}</option>
                                <option value="general">{t('global.general')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <PersianDateInput
                                value={filters.date_from}
                                onChange={(date_from) => update({ date_from })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <PersianDateInput
                                value={filters.date_to}
                                onChange={(date_to) => update({ date_to })}
                            />
                        </div>
                        <div>
                            <Label htmlFor="anesthesia-per-page">{t('global.per_page')}</Label>
                            <Select
                                id="anesthesia-per-page"
                                sizing="sm"
                                value={filters.per_page}
                                onChange={(e) => update({ per_page: e.target.value })}
                            >
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </Select>
                        </div>
                    </div>

                    {activeCount > 0 && (
                        <div className="flex flex-wrap gap-2">
                            {filters.search && (
                                <FilterChip
                                    label={t('global.search')}
                                    value={filters.search}
                                    onRemove={() => update({ search: '' })}
                                    colorClass="bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200"
                                />
                            )}
                            {filters.operation_type_id && operationTypeName && (
                                <FilterChip
                                    label={t('global.operation_type')}
                                    value={operationTypeName}
                                    onRemove={() => update({ operation_type_id: '' })}
                                    colorClass="bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200"
                                />
                            )}
                            {filters.department_id && departmentName && (
                                <FilterChip
                                    label={t('global.department')}
                                    value={departmentName}
                                    onRemove={() => update({ department_id: '' })}
                                    colorClass="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200"
                                />
                            )}
                            {filters.anesthesia_type && (
                                <FilterChip
                                    label={t('global.anesthesia_type')}
                                    value={filters.anesthesia_type}
                                    onRemove={() => update({ anesthesia_type: '' })}
                                    colorClass="bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200"
                                />
                            )}
                        </div>
                    )}

                    <div className="flex flex-wrap gap-2">
                        <button type="submit" disabled={processing} className={ANESTHESIA_APPLY_BTN_CLASS}>
                            {processing ? <Spinner size="sm" /> : <i className="bx bx-filter" />}
                            {t('global.apply_filters')}
                        </button>
                        <Button type="button" color="light" size="sm" disabled={processing} onClick={onReset}>
                            <i className="bx bx-refresh me-1" />
                            {t('global.reset')}
                        </Button>
                    </div>
                </form>
            )}
        </div>
    );
}
