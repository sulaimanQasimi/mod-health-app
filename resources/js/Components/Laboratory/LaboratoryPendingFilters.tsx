import { Button, Card, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { LaboratoryPendingFilters as Filters } from '../../types/laboratory';
import { useTranslation } from '../../hooks/useTranslation';

interface LaboratoryPendingFiltersProps {
    filters: Filters;
    onChange: (field: keyof Filters, value: string) => void;
    onSubmit: (event: FormEvent) => void;
    onReset: () => void;
    processing?: boolean;
}

export default function LaboratoryPendingFilters({
    filters,
    onChange,
    onSubmit,
    onReset,
    processing = false,
}: LaboratoryPendingFiltersProps) {
    const { t } = useTranslation();

    return (
        <Card className="mb-6 shadow-sm">
            <form onSubmit={onSubmit} className="space-y-4">
                <div className="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-700">
                    <i className="bx bx-filter-alt text-lg text-teal-600" />
                    <h2 className="text-sm font-semibold text-gray-900 dark:text-white">
                        {t('global.search_and_filters')}
                    </h2>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <Label htmlFor="pending-search">{t('global.search_patient')}</Label>
                        <TextInput
                            id="pending-search"
                            value={filters.search}
                            onChange={(e) => onChange('search', e.target.value)}
                            placeholder={t('global.search_by_patient_name')}
                        />
                    </div>
                    <div>
                        <Label htmlFor="pending-patient-id">{t('global.patient_id')}</Label>
                        <TextInput
                            id="pending-patient-id"
                            value={filters.patient_id}
                            onChange={(e) => onChange('patient_id', e.target.value)}
                            placeholder={t('global.search_by_patient_id')}
                        />
                    </div>
                    <div>
                        <Label htmlFor="pending-priority">{t('global.priority')}</Label>
                        <Select
                            id="pending-priority"
                            value={filters.priority}
                            onChange={(e) => onChange('priority', e.target.value)}
                        >
                            <option value="">{t('global.all')}</option>
                            <option value="normal">{t('global.normal')}</option>
                            <option value="urgent">{t('global.urgent')}</option>
                            <option value="stat">{t('global.stat')}</option>
                        </Select>
                    </div>
                    <div>
                        <Label htmlFor="pending-date-from">{t('global.date_from')}</Label>
                        <TextInput
                            id="pending-date-from"
                            value={filters.date_from}
                            onChange={(e) => onChange('date_from', e.target.value)}
                            placeholder="1403/01/01"
                        />
                    </div>
                    <div>
                        <Label htmlFor="pending-date-to">{t('global.date_to')}</Label>
                        <TextInput
                            id="pending-date-to"
                            value={filters.date_to}
                            onChange={(e) => onChange('date_to', e.target.value)}
                            placeholder="1403/12/29"
                        />
                    </div>
                    <div>
                        <Label htmlFor="pending-per-page">{t('global.per_page')}</Label>
                        <Select
                            id="pending-per-page"
                            value={filters.per_page}
                            onChange={(e) => onChange('per_page', e.target.value)}
                        >
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </Select>
                    </div>
                </div>

                <div className="flex flex-wrap gap-2">
                    <Button type="submit" color="blue" disabled={processing}>
                        <i className="bx bx-search me-1" />
                        {t('global.search')}
                    </Button>
                    <Button type="button" color="light" onClick={onReset} disabled={processing}>
                        <i className="bx bx-refresh me-1" />
                        {t('global.reset')}
                    </Button>
                </div>
            </form>
        </Card>
    );
}
