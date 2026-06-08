import { Card, Label, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import PersianDateInput from '../ui/PersianDateInput';
import SearchableSelect from '../ui/SearchableSelect';
import SettingsFilterActions from '../Settings/SettingsFilterActions';
import { useTranslation } from '../../hooks/useTranslation';
import {
    NephrologyRegistrationFilterOptions,
    NephrologyRegistrationFilters as Filters,
} from '../../types/nephrologyRegistration';

interface NephrologyRegistrationFiltersProps {
    filters: Filters;
    filterOptions: NephrologyRegistrationFilterOptions;
    processing: boolean;
    onChange: (filters: Filters) => void;
    onSubmit: (filters: Filters) => void;
    onReset: () => void;
}

const selectClassName =
    'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white';

export default function NephrologyRegistrationFilters({
    filters,
    filterOptions,
    processing,
    onChange,
    onSubmit,
    onReset,
}: NephrologyRegistrationFiltersProps) {
    const { t } = useTranslation();

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onSubmit(filters);
    };

    const branchOptions = filterOptions.branches.map((item) => ({
        value: String(item.id),
        label: item.name,
    }));

    const doctorOptions = filterOptions.doctors.map((item) => ({
        value: String(item.id),
        label: item.name,
    }));

    return (
        <Card className="shadow-sm">
            <form onSubmit={handleSubmit} className="space-y-4">
                <h2 className="text-sm font-semibold text-gray-900 dark:text-white">{t('global.filters')}</h2>
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <Label htmlFor="patient_id">{t('global.patient_id')}</Label>
                        <TextInput
                            id="patient_id"
                            value={filters.patient_id}
                            onChange={(event) => onChange({ ...filters, patient_id: event.target.value })}
                            placeholder={t('global.search_by_patient_id')}
                        />
                    </div>
                    <div>
                        <Label htmlFor="patient_name">{t('global.patient_name')}</Label>
                        <TextInput
                            id="patient_name"
                            value={filters.patient_name}
                            onChange={(event) => onChange({ ...filters, patient_name: event.target.value })}
                        />
                    </div>
                    <div>
                        <Label htmlFor="status">{t('global.status')}</Label>
                        <select
                            id="status"
                            className={selectClassName}
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
                        <Label>{t('global.doctor')}</Label>
                        <SearchableSelect
                            value={filters.doctor_id}
                            onChange={(value) => onChange({ ...filters, doctor_id: value })}
                            options={doctorOptions}
                            placeholder={t('global.all')}
                        />
                    </div>
                    <div>
                        <Label>{t('global.from_date')}</Label>
                        <PersianDateInput
                            value={filters.visit_date_from}
                            onChange={(value) => onChange({ ...filters, visit_date_from: value })}
                        />
                    </div>
                    <div>
                        <Label>{t('global.to_date')}</Label>
                        <PersianDateInput
                            value={filters.visit_date_to}
                            onChange={(value) => onChange({ ...filters, visit_date_to: value })}
                        />
                    </div>
                </div>
                <SettingsFilterActions processing={processing} onReset={onReset} />
            </form>
        </Card>
    );
}
