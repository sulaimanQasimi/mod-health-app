import { Badge, Button, Label, Select, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { OperationListFilters, SelectOption } from '../../types/operation';
import PersianDateInput from '../ui/PersianDateInput';
import { OPERATION_APPROVE_BTN_CLASS } from './operationUi';

export const EMPTY_OPERATION_FILTERS: OperationListFilters = {
    search: '',
    branch_id: '',
    department_id: '',
    operation_type_id: '',
    surgeon_id: '',
    date_from: '',
    date_to: '',
    sort_by: 'date',
    sort_order: 'desc',
    per_page: '15',
};

interface OperationFiltersProps {
    filters: OperationListFilters;
    processing: boolean;
    branches: SelectOption[];
    departments: SelectOption[];
    operationTypes: SelectOption[];
    surgeons: SelectOption[];
    embedded?: boolean;
    onChange: (filters: OperationListFilters) => void;
    onApply: (filters: OperationListFilters) => void;
    onReset: () => void;
}

function countActiveFilters(filters: OperationListFilters): number {
    let count = 0;
    if (filters.search) count++;
    if (filters.branch_id) count++;
    if (filters.department_id) count++;
    if (filters.operation_type_id) count++;
    if (filters.surgeon_id) count++;
    if (filters.date_from) count++;
    if (filters.date_to) count++;
    if (filters.sort_by !== 'date') count++;
    if (filters.sort_order !== 'desc') count++;
    if (filters.per_page && filters.per_page !== '15') count++;
    return count;
}

export default function OperationFilters({
    filters,
    processing,
    branches,
    departments,
    operationTypes,
    surgeons,
    embedded = false,
    onChange,
    onApply,
    onReset,
}: OperationFiltersProps) {
    const { t } = useTranslation();
    const [expanded, setExpanded] = useState(countActiveFilters(filters) > 0);
    const activeCount = useMemo(() => countActiveFilters(filters), [filters]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onApply(filters);
    };

    const update = (patch: Partial<OperationListFilters>) => onChange({ ...filters, ...patch });

    return (
        <div className={embedded ? '' : 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900'}>
            <button
                type="button"
                onClick={() => setExpanded((value) => !value)}
                className="flex w-full items-center justify-between gap-3 text-left"
            >
                <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i className="bx bx-filter-alt text-amber-500" />
                    {t('global.advanced_filters')}
                    {activeCount > 0 && (
                        <Badge color="warning" className="font-normal">
                            {activeCount}
                        </Badge>
                    )}
                </span>
                <i className={`bx ${expanded ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
            </button>

            {expanded && (
                <form onSubmit={handleSubmit} className="mt-4 space-y-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div className="md:col-span-2">
                            <Label htmlFor="operation-search">{t('global.search')}</Label>
                            <TextInput
                                id="operation-search"
                                value={filters.search}
                                onChange={(e) => update({ search: e.target.value })}
                                placeholder={t('global.search_by_patient_operation')}
                            />
                        </div>
                        <div>
                            <Label htmlFor="operation-branch">{t('global.branch')}</Label>
                            <Select
                                id="operation-branch"
                                value={filters.branch_id}
                                onChange={(e) => update({ branch_id: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                {branches.map((branch) => (
                                    <option key={branch.id} value={branch.id}>
                                        {branch.name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="operation-department">{t('global.department')}</Label>
                            <Select
                                id="operation-department"
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
                            <Label htmlFor="operation-type">{t('global.operation_type')}</Label>
                            <Select
                                id="operation-type"
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
                            <Label htmlFor="operation-surgeon">{t('global.operation_surgion')}</Label>
                            <Select
                                id="operation-surgeon"
                                value={filters.surgeon_id}
                                onChange={(e) => update({ surgeon_id: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                {surgeons.map((surgeon) => (
                                    <option key={surgeon.id} value={surgeon.id}>
                                        {surgeon.name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <PersianDateInput
                                value={filters.date_from}
                                onChange={(value) => update({ date_from: value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <PersianDateInput
                                value={filters.date_to}
                                onChange={(value) => update({ date_to: value })}
                            />
                        </div>
                        <div>
                            <Label htmlFor="operation-sort">{t('global.sort_by')}</Label>
                            <Select
                                id="operation-sort"
                                value={filters.sort_by}
                                onChange={(e) => update({ sort_by: e.target.value })}
                            >
                                <option value="date">{t('global.date')}</option>
                                <option value="created_at">{t('global.created_at')}</option>
                                <option value="time">{t('global.time')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="operation-per-page">{t('global.per_page')}</Label>
                            <Select
                                id="operation-per-page"
                                value={filters.per_page}
                                onChange={(e) => update({ per_page: e.target.value })}
                            >
                                {['10', '15', '25', '50', '100'].map((value) => (
                                    <option key={value} value={value}>
                                        {value}
                                    </option>
                                ))}
                            </Select>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <button type="submit" className={OPERATION_APPROVE_BTN_CLASS} disabled={processing}>
                            {processing ? <Spinner size="sm" /> : <i className="bx bx-search" />}
                            {t('global.search')}
                        </button>
                        <Button type="button" color="light" onClick={onReset} disabled={processing}>
                            {t('global.reset')}
                        </Button>
                    </div>
                </form>
            )}
        </div>
    );
}
