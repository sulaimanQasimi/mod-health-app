import { Button, Label } from 'flowbite-react';
import PersianDateInput from '../ui/PersianDateInput';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { BloodRequestFilterOptions, BloodRequestListFilters } from '../../types/bloodBank';

export const EMPTY_BLOOD_REQUEST_FILTERS: BloodRequestListFilters = {
    q: '',
    department_id: '',
    group: '',
    rh: '',
    type: '',
    from: '',
    to: '',
    per_page: '15',
};

interface BloodBankFiltersProps {
    filters: BloodRequestListFilters;
    processing: boolean;
    filterOptions: BloodRequestFilterOptions;
    embedded?: boolean;
    onChange: (filters: BloodRequestListFilters) => void;
    onApply: (filters: BloodRequestListFilters) => void;
    onReset: () => void;
}

export default function BloodBankFilters({
    filters,
    processing,
    filterOptions,
    onChange,
    onApply,
    onReset,
}: BloodBankFiltersProps) {
    const { t } = useTranslation();

    const departmentOptions = filterOptions.departments.map((d) => ({
        value: String(d.id),
        label: d.name,
    }));

    const groupOptions = filterOptions.bloodGroups.map((g) => ({ value: g, label: g }));
    const rhOptions = [
        { value: '+', label: 'Rh+' },
        { value: '-', label: 'Rh−' },
    ];
    const typeOptions = filterOptions.bloodComponentTypes.map((type) => ({
        value: type,
        label: type,
    }));

    return (
        <form
            className="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
            onSubmit={(e) => {
                e.preventDefault();
                onApply(filters);
            }}
        >
            <div className="xl:col-span-2">
                <Label className="mb-2 block">{t('global.search')}</Label>
                <input
                    type="text"
                    className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-rose-500 focus:ring-rose-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    value={filters.q}
                    onChange={(e) => onChange({ ...filters, q: e.target.value })}
                    placeholder={`${t('global.patient_name')} / ${t('global.card_number')}`}
                />
            </div>
            <div>
                <Label className="mb-2 block">{t('global.requested_department')}</Label>
                <SearchableSelect
                    value={filters.department_id}
                    onChange={(value) => onChange({ ...filters, department_id: value })}
                    options={departmentOptions}
                    placeholder={t('global.all')}
                />
            </div>
            <div>
                <Label className="mb-2 block">{t('global.blood_group')}</Label>
                <SearchableSelect
                    value={filters.group}
                    onChange={(value) => onChange({ ...filters, group: value })}
                    options={groupOptions}
                    placeholder={t('global.all')}
                />
            </div>
            <div>
                <Label className="mb-2 block">{t('global.rh')}</Label>
                <SearchableSelect
                    value={filters.rh}
                    onChange={(value) => onChange({ ...filters, rh: value })}
                    options={rhOptions}
                    placeholder={t('global.all')}
                />
            </div>
            <div>
                <Label className="mb-2 block">{t('global.blood_type')}</Label>
                <SearchableSelect
                    value={filters.type}
                    onChange={(value) => onChange({ ...filters, type: value })}
                    options={typeOptions}
                    placeholder={t('global.all')}
                />
            </div>
            <div>
                <Label className="mb-2 block">{t('global.from')}</Label>
                <PersianDateInput
                    value={filters.from}
                    onChange={(date) => onChange({ ...filters, from: date })}
                />
            </div>
            <div>
                <Label className="mb-2 block">{t('global.to')}</Label>
                <PersianDateInput value={filters.to} onChange={(date) => onChange({ ...filters, to: date })} />
            </div>
            <div className="flex items-end gap-2 xl:col-span-4">
                <Button type="submit" color="failure" disabled={processing}>
                    <i className="bx bx-search me-2" />
                    {t('global.filter')}
                </Button>
                <Button type="button" color="light" onClick={onReset} disabled={processing}>
                    <i className="bx bx-reset me-2" />
                    {t('global.reset')}
                </Button>
            </div>
        </form>
    );
}
