import { Card, Label, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import SettingsFilterActions from '../Settings/SettingsFilterActions';
import { useTranslation } from '../../hooks/useTranslation';
import {
    DentistRegistrationFilterOptions,
    DentistRegistrationFilters as Filters,
} from '../../types/dentistRegistration';

interface DentistRegistrationFiltersProps {
    filters: Filters;
    filterOptions: DentistRegistrationFilterOptions;
    processing: boolean;
    onChange: (filters: Filters) => void;
    onSubmit: (filters: Filters) => void;
    onReset: () => void;
}

export default function DentistRegistrationFilters({
    filters,
    filterOptions,
    processing,
    onChange,
    onSubmit,
    onReset,
}: DentistRegistrationFiltersProps) {
    const { t } = useTranslation();

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onSubmit(filters);
    };

    const branchOptions = filterOptions.branches.map((item) => ({
        value: String(item.id),
        label: item.name,
    }));

    const dentistOptions = filterOptions.dentists.map((item) => ({
        value: String(item.id),
        label: item.name,
    }));

    return (
        <Card className="shadow-sm">
            <form onSubmit={handleSubmit} className="space-y-4">
                <h2 className="text-sm font-semibold text-gray-900 dark:text-white">{t('global.filters')}</h2>
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <Label htmlFor="search">{t('global.patient_name')}</Label>
                        <TextInput
                            id="search"
                            value={filters.search}
                            onChange={(event) => onChange({ ...filters, search: event.target.value })}
                            placeholder={t('global.search_by_patient_name')}
                        />
                    </div>
                    <div>
                        <Label htmlFor="status">{t('global.status')}</Label>
                        <select
                            id="status"
                            className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            value={filters.status}
                            onChange={(event) => onChange({ ...filters, status: event.target.value })}
                        >
                            <option value="">{t('global.all')}</option>
                            <option value="pending">{t('global.pending')}</option>
                            <option value="in_progress">{t('global.in_progress')}</option>
                            <option value="completed">{t('global.completed')}</option>
                            <option value="cancelled">{t('global.cancelled')}</option>
                        </select>
                    </div>
                    <div>
                        <Label>{t('global.branch')}</Label>
                        <SearchableSelect
                            value={filters.branch_id}
                            onChange={(value) => onChange({ ...filters, branch_id: value })}
                            options={branchOptions}
                            placeholder={t('global.all')}
                        />
                    </div>
                    <div>
                        <Label>{t('global.dentist')}</Label>
                        <SearchableSelect
                            value={filters.dentist_id}
                            onChange={(value) => onChange({ ...filters, dentist_id: value })}
                            options={dentistOptions}
                            placeholder={t('global.all')}
                        />
                    </div>
                </div>
                <SettingsFilterActions processing={processing} onReset={onReset} />
            </form>
        </Card>
    );
}
