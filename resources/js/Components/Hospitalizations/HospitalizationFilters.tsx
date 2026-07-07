import { Button, Label, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import PersianDateInput from '../ui/PersianDateInput';
import { useTranslation } from '../../hooks/useTranslation';
import { HospitalizationActiveFilters, HospitalizationOption } from '../../types/hospitalization';

export const EMPTY_HOSPITALIZATION_FILTERS: HospitalizationActiveFilters = {
    q: '',
    room_id: '',
    date_from: '',
    date_to: '',
};

interface HospitalizationFiltersProps {
    filters: HospitalizationActiveFilters;
    rooms: HospitalizationOption[];
    processing: boolean;
    onChange: (filters: HospitalizationActiveFilters) => void;
    onApply: (filters: HospitalizationActiveFilters) => void;
    onReset: () => void;
}

export default function HospitalizationFilters({
    filters,
    rooms,
    processing,
    onChange,
    onApply,
    onReset,
}: HospitalizationFiltersProps) {
    const { t } = useTranslation();

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onApply(filters);
    };

    return (
        <form
            onSubmit={handleSubmit}
            className="space-y-4 [&_label]:text-xs [&_label]:font-semibold [&_label]:uppercase [&_label]:tracking-wide [&_label]:text-gray-500 dark:[&_label]:text-gray-400"
        >
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-6 xl:items-end">
                <div className="min-w-0 md:col-span-2 xl:col-span-2">
                    <Label htmlFor="hospitalization-q" className="mb-2 block">
                        {t('global.search')}
                    </Label>
                    <TextInput
                        id="hospitalization-q"
                        sizing="sm"
                        placeholder={t('global.search_by_patient_room_bed')}
                        value={filters.q}
                        onChange={(e) => onChange({ ...filters, q: e.target.value })}
                    />
                </div>
                <div className="min-w-0 md:col-span-2 xl:col-span-2">
                    <Label htmlFor="hospitalization-room" className="mb-2 block">
                        {t('global.room')}
                    </Label>
                    <SearchableSelect
                        id="hospitalization-room"
                        value={filters.room_id}
                        onChange={(value) => onChange({ ...filters, room_id: value })}
                        options={[
                            { value: '', label: t('global.all') },
                            ...rooms.map((room) => ({ value: String(room.id), label: room.name })),
                        ]}
                        placeholder={t('global.all')}
                    />
                </div>
                <div className="min-w-0">
                    <Label htmlFor="hospitalization-date-from" className="mb-2 block">
                        {t('global.date_from')}
                    </Label>
                    <PersianDateInput
                        id="hospitalization-date-from"
                        value={filters.date_from}
                        onChange={(date_from) => onChange({ ...filters, date_from })}
                    />
                </div>
                <div className="min-w-0">
                    <Label htmlFor="hospitalization-date-to" className="mb-2 block">
                        {t('global.date_to')}
                    </Label>
                    <PersianDateInput
                        id="hospitalization-date-to"
                        value={filters.date_to}
                        onChange={(date_to) => onChange({ ...filters, date_to })}
                    />
                </div>
            </div>
            <div className="flex flex-wrap gap-2">
                <Button type="submit" color="light" size="sm" disabled={processing}>
                    {t('global.search')}
                </Button>
                <Button type="button" color="light" size="sm" disabled={processing} onClick={onReset}>
                    {t('global.reset')}
                </Button>
            </div>
        </form>
    );
}
