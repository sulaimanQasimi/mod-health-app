import { Card, Label, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import PersianDateInput from '../ui/PersianDateInput';
import SearchableSelect from '../ui/SearchableSelect';
import SettingsFilterActions from '../Settings/SettingsFilterActions';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PhysiotherapyProcedureFilterOptions,
    PhysiotherapyProcedureFilters as ProcedureFilters,
} from '../../types/physiotherapyProcedure';

interface PhysiotherapyProcedureFiltersProps {
    filters: ProcedureFilters;
    filterOptions: PhysiotherapyProcedureFilterOptions;
    processing: boolean;
    showPhysiotherapistFilter: boolean;
    onChange: (filters: ProcedureFilters) => void;
    onSubmit: (filters: ProcedureFilters) => void;
    onReset: () => void;
}

export default function PhysiotherapyProcedureFilters({
    filters,
    filterOptions,
    processing,
    showPhysiotherapistFilter,
    onChange,
    onSubmit,
    onReset,
}: PhysiotherapyProcedureFiltersProps) {
    const { t } = useTranslation();

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onSubmit(filters);
    };

    const typeOptions = filterOptions.physiotherapy_types.map((item) => ({
        value: String(item.id),
        label: item.name,
    }));

    const physiotherapistOptions = filterOptions.physiotherapists.map((item) => ({
        value: String(item.id),
        label: item.name,
    }));

    return (
        <Card className="shadow-sm">
            <form onSubmit={handleSubmit} className="space-y-4">
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <Label htmlFor="search">{t('global.search')}</Label>
                        <TextInput
                            id="search"
                            value={filters.search}
                            onChange={(event) => onChange({ ...filters, search: event.target.value })}
                            placeholder={t('global.search_patient_name')}
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
                            <option value="">{t('global.all_statuses')}</option>
                            <option value="pending">{t('global.status_pending')}</option>
                            <option value="in_progress">{t('global.status_in_progress')}</option>
                            <option value="completed">{t('global.status_completed')}</option>
                            <option value="cancelled">{t('global.status_cancelled')}</option>
                        </select>
                    </div>
                    <div>
                        <Label>{t('global.physiotherapy_type')}</Label>
                        <SearchableSelect
                            value={filters.physiotherapy_type_id}
                            onChange={(value) => onChange({ ...filters, physiotherapy_type_id: value })}
                            options={typeOptions}
                            placeholder={t('global.all_types')}
                        />
                    </div>
                    {showPhysiotherapistFilter && (
                        <div>
                            <Label>{t('global.physiotherapist')}</Label>
                            <SearchableSelect
                                value={filters.doctor_id}
                                onChange={(value) => onChange({ ...filters, doctor_id: value })}
                                options={physiotherapistOptions}
                                placeholder={t('global.all_physiotherapists')}
                            />
                        </div>
                    )}
                    <div>
                        <Label>{t('global.start_date')}</Label>
                        <PersianDateInput
                            value={filters.start_date}
                            onChange={(start_date) => onChange({ ...filters, start_date })}
                        />
                    </div>
                    <div>
                        <Label>{t('global.end_date')}</Label>
                        <PersianDateInput
                            value={filters.end_date}
                            onChange={(end_date) => onChange({ ...filters, end_date })}
                        />
                    </div>
                </div>
                <SettingsFilterActions processing={processing} onReset={onReset} />
            </form>
        </Card>
    );
}
